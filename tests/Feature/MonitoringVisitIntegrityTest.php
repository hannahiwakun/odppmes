<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Mda;
use App\Models\Monitoring;
use App\Models\Project;
use App\Models\User;
use App\Support\OperationalStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class MonitoringVisitIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_valid_ongoing_visit_updates_the_project_status_and_progress(): void
    {
        $officer = $this->officer();
        $project = $this->project($officer->mda, [
            'status' => 'Planning',
            'progress' => 10,
            'initiative_status' => 'Under negotiation',
        ]);

        $this->actingAs($officer)
            ->post(route('monitorings.store'), [
                'project_id' => $project->id,
                'monitoring_date' => '2026-10-17',
                'status' => 'Ongoing',
                'progress_before' => 10,
                'progress_after' => 45,
                'findings' => 'Work is underway.',
            ])
            ->assertRedirect(route('monitorings.index'))
            ->assertSessionHas('success');

        $visit = Monitoring::firstOrFail();

        $this->assertSame($project->id, $visit->project_id);
        $this->assertSame('Ongoing', $visit->status);
        $this->assertSame(45, $visit->progress_after);
        $this->assertSame('Work is underway.', $visit->findings);

        $project->refresh();
        $this->assertSame('Ongoing', $project->status);
        $this->assertSame(45, $project->progress);
        $this->assertSame('Under negotiation', $project->initiative_status);

        $log = AuditLog::firstOrFail();
        $this->assertSame('Created', $log->action);
        $this->assertSame('Monitoring', $log->module);
        $this->assertSame($officer->id, $log->user_id);
    }

    public function test_an_invalid_status_is_rejected_and_leaves_the_project_unchanged(): void
    {
        $officer = $this->officer();
        $project = $this->project($officer->mda, [
            'status' => 'Planning',
            'progress' => 10,
        ]);

        $this->actingAs($officer)
            ->from(route('monitorings.create'))
            ->post(route('monitorings.store'), [
                'project_id' => $project->id,
                'monitoring_date' => '2026-10-17',
                'status' => 'Delayed',
                'progress_after' => 80,
            ])
            ->assertRedirect(route('monitorings.create'))
            ->assertSessionHasErrors('status')
            ->assertSessionMissing('success');

        $this->assertDatabaseCount('monitorings', 0);
        $this->assertDatabaseCount('audit_logs', 0);

        $project->refresh();
        $this->assertSame('Planning', $project->status);
        $this->assertSame(10, $project->progress);
    }

    public function test_editing_an_older_visit_updates_the_parent_project(): void
    {
        $officer = $this->officer();
        $project = $this->project($officer->mda, [
            'status' => 'Ongoing',
            'progress' => 70,
            'initiative_status' => 'Delayed',
        ]);
        $older = $this->visit($project, $officer, [
            'monitoring_date' => '2026-09-01',
            'status' => 'Planning',
            'progress_after' => 5,
        ]);
        $newer = $this->visit($project, $officer, [
            'monitoring_date' => '2026-10-01',
            'status' => 'Ongoing',
            'progress_after' => 70,
        ]);

        $this->actingAs($officer)
            ->put(route('monitorings.update', $older), [
                'project_id' => $project->id,
                'monitoring_date' => '2026-09-01',
                'status' => 'Suspended',
                'progress_before' => 0,
                'progress_after' => 15,
            ])
            ->assertRedirect(route('monitorings.index'))
            ->assertSessionHas('success');

        $older->refresh();
        $newer->refresh();
        $project->refresh();

        $this->assertSame('Suspended', $older->status);
        $this->assertSame(15, $older->progress_after);
        $this->assertSame('Ongoing', $newer->status);
        $this->assertSame(70, $newer->progress_after);
        $this->assertSame('Suspended', $project->status);
        $this->assertSame(15, $project->progress);
        $this->assertSame('Delayed', $project->initiative_status);

        $log = AuditLog::firstOrFail();
        $this->assertSame('Updated', $log->action);
        $this->assertSame('Monitoring', $log->module);
    }

    public function test_an_mda_officer_cannot_record_a_visit_for_another_ministry(): void
    {
        $health = Mda::create(['name' => 'Ministry of Health', 'code' => 'MOH', 'status' => true]);
        $works = Mda::create(['name' => 'Ministry of Works', 'code' => 'MOW', 'status' => true]);
        $officer = User::factory()->create([
            'role' => 'MDA Officer',
            'mda_id' => $health->id,
        ]);
        $other = $this->project($works, [
            'project_title' => 'Rural Road',
            'status' => 'Planning',
            'progress' => 5,
        ]);

        $this->actingAs($officer)
            ->post(route('monitorings.store'), [
                'project_id' => $other->id,
                'monitoring_date' => '2026-10-17',
                'status' => 'Ongoing',
                'progress_after' => 90,
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('monitorings', 0);
        $other->refresh();
        $this->assertSame('Planning', $other->status);
        $this->assertSame(5, $other->progress);
    }

    public function test_a_failed_project_update_rolls_back_the_visit(): void
    {
        $officer = $this->officer();
        $project = $this->project($officer->mda, [
            'status' => 'Planning',
            'progress' => 10,
        ]);

        Project::updating(function () {
            throw new RuntimeException('Project update failed.');
        });

        $response = $this->actingAs($officer)->post(route('monitorings.store'), [
            'project_id' => $project->id,
            'monitoring_date' => '2026-10-17',
            'status' => 'Completed',
            'progress_after' => 100,
        ]);

        $response->assertStatus(500);
        $response->assertSessionMissing('success');
        $this->assertDatabaseCount('monitorings', 0);
        $this->assertDatabaseCount('audit_logs', 0);

        $project->refresh();
        $this->assertSame('Planning', $project->status);
        $this->assertSame(10, $project->progress);
    }

    public function test_photo_replacement_keeps_the_old_photograph_when_the_update_fails(): void
    {
        Storage::fake(OperationalStorage::DISK);
        Storage::disk(OperationalStorage::DISK)->put('monitorings/old-visit.jpg', 'old-photo');

        $officer = $this->officer();
        $project = $this->project($officer->mda, [
            'status' => 'Planning',
            'progress' => 10,
        ]);
        $visit = $this->visit($project, $officer, [
            'photo' => 'monitorings/old-visit.jpg',
            'status' => 'Planning',
            'progress_after' => 10,
        ]);

        Project::updating(function () {
            throw new RuntimeException('Project update failed.');
        });

        $response = $this->actingAs($officer)->put(route('monitorings.update', $visit), [
            'project_id' => $project->id,
            'monitoring_date' => '2026-10-17',
            'status' => 'Ongoing',
            'progress_after' => 40,
            'photo' => UploadedFile::fake()->image('replacement.jpg'),
        ]);

        $response->assertStatus(500);
        $response->assertSessionMissing('success');

        $visit->refresh();
        $project->refresh();

        $this->assertSame('monitorings/old-visit.jpg', $visit->photo);
        $this->assertSame('Planning', $visit->status);
        $this->assertSame('Planning', $project->status);
        $this->assertSame(10, $project->progress);
        $this->assertTrue(Storage::disk(OperationalStorage::DISK)->exists('monitorings/old-visit.jpg'));
        $this->assertSame(
            ['monitorings/old-visit.jpg'],
            Storage::disk(OperationalStorage::DISK)->allFiles('monitorings')
        );
    }

    public function test_a_new_photo_is_removed_when_the_visit_save_fails(): void
    {
        Storage::fake(OperationalStorage::DISK);

        $officer = $this->officer();
        $project = $this->project($officer->mda);

        Project::updating(function () {
            throw new RuntimeException('Project update failed.');
        });

        $response = $this->actingAs($officer)->post(route('monitorings.store'), [
            'project_id' => $project->id,
            'monitoring_date' => '2026-10-17',
            'status' => 'Ongoing',
            'progress_after' => 25,
            'photo' => UploadedFile::fake()->image('new-visit.jpg'),
        ]);

        $response->assertStatus(500);
        $response->assertSessionMissing('success');
        $this->assertDatabaseCount('monitorings', 0);
        $this->assertSame([], Storage::disk(OperationalStorage::DISK)->allFiles('monitorings'));
    }

    public function test_a_successful_photo_replacement_points_at_the_new_file(): void
    {
        Storage::fake(OperationalStorage::DISK);
        Storage::disk(OperationalStorage::DISK)->put('monitorings/old-visit.jpg', 'old-photo');

        $officer = $this->officer();
        $project = $this->project($officer->mda, [
            'status' => 'Planning',
            'progress' => 0,
        ]);
        $visit = $this->visit($project, $officer, [
            'photo' => 'monitorings/old-visit.jpg',
        ]);

        $this->actingAs($officer)
            ->put(route('monitorings.update', $visit), [
                'project_id' => $project->id,
                'monitoring_date' => '2026-10-17',
                'status' => 'Completed',
                'progress_after' => 100,
                'photo' => UploadedFile::fake()->image('finished.jpg'),
            ])
            ->assertRedirect(route('monitorings.index'))
            ->assertSessionHas('success');

        $visit->refresh();
        $project->refresh();

        $this->assertNotSame('monitorings/old-visit.jpg', $visit->photo);
        $this->assertStringStartsWith('monitorings/', (string) $visit->photo);
        $this->assertTrue(Storage::disk(OperationalStorage::DISK)->exists($visit->photo));
        $this->assertFalse(Storage::disk(OperationalStorage::DISK)->exists('monitorings/old-visit.jpg'));
        $this->assertSame('Completed', $project->status);
        $this->assertSame(100, $project->progress);
    }

    public function test_a_failed_audit_log_rolls_back_the_visit_and_project(): void
    {
        $officer = $this->officer();
        $project = $this->project($officer->mda, [
            'status' => 'Planning',
            'progress' => 10,
        ]);

        AuditLog::creating(function () {
            throw new RuntimeException('Audit log failed.');
        });

        $response = $this->actingAs($officer)->post(route('monitorings.store'), [
            'project_id' => $project->id,
            'monitoring_date' => '2026-10-17',
            'status' => 'Ongoing',
            'progress_after' => 30,
        ]);

        $response->assertStatus(500);
        $response->assertSessionMissing('success');
        $this->assertDatabaseCount('monitorings', 0);
        $this->assertDatabaseCount('audit_logs', 0);

        $project->refresh();
        $this->assertSame('Planning', $project->status);
        $this->assertSame(10, $project->progress);
    }

    public function test_a_malicious_monitoring_photo_path_cannot_delete_cms_media(): void
    {
        Storage::fake(OperationalStorage::DISK);
        Storage::disk(OperationalStorage::DISK)->put('cms/media/secret.jpg', 'cms-bytes');

        $officer = $this->officer();
        $visit = $this->visit($this->project($officer->mda), $officer, [
            'photo' => 'cms/media/secret.jpg',
        ]);
        $admin = User::factory()->create(['role' => 'Super Administrator']);

        $this->actingAs($admin)
            ->delete(route('monitorings.destroy', $visit))
            ->assertRedirect(route('monitorings.index'));

        $this->assertDatabaseMissing('monitorings', ['id' => $visit->id]);
        Storage::disk(OperationalStorage::DISK)->assertExists('cms/media/secret.jpg');
        $this->assertSame('cms-bytes', Storage::disk(OperationalStorage::DISK)->get('cms/media/secret.jpg'));

        $traversal = $this->visit($this->project($officer->mda), $officer, [
            'photo' => 'monitorings/../cms/media/secret.jpg',
        ]);

        $this->actingAs($admin)
            ->delete(route('monitorings.destroy', $traversal))
            ->assertRedirect(route('monitorings.index'));

        $this->assertDatabaseMissing('monitorings', ['id' => $traversal->id]);
        Storage::disk(OperationalStorage::DISK)->assertExists('cms/media/secret.jpg');
        $this->assertSame('cms-bytes', Storage::disk(OperationalStorage::DISK)->get('cms/media/secret.jpg'));
    }

    private function officer(): User
    {
        $mda = Mda::create([
            'name' => 'Ministry of Health',
            'code' => 'MOH',
            'status' => true,
        ]);

        return User::factory()->create([
            'role' => 'MDA Officer',
            'mda_id' => $mda->id,
        ]);
    }

    private function project(Mda $mda, array $overrides = []): Project
    {
        return Project::create(array_merge([
            'mda_id' => $mda->id,
            'project_title' => 'Health Clinic',
            'sector' => 'Health',
            'lga' => 'Akure South',
            'status' => 'Planning',
            'progress' => 0,
        ], $overrides));
    }

    private function visit(Project $project, User $officer, array $overrides = []): Monitoring
    {
        return Monitoring::create(array_merge([
            'project_id' => $project->id,
            'user_id' => $officer->id,
            'monitoring_date' => '2026-10-17',
            'status' => 'Planning',
            'progress_before' => 0,
            'progress_after' => 0,
        ], $overrides));
    }
}
