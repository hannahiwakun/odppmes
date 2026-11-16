<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Mda;
use App\Models\Monitoring;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectPhoto;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Phase3DeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_project_with_a_photo_or_document_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'Super Administrator']);
        $mda = $this->mda('Health');
        $withPhoto = $this->project($mda, 'Photo Project');
        $withDocument = $this->project($mda, 'Document Project');

        ProjectPhoto::create([
            'project_id' => $withPhoto->id,
            'photo' => 'project_photos/keep.jpg',
            'uploaded_by' => $admin->id,
        ]);
        ProjectDocument::create([
            'project_id' => $withDocument->id,
            'title' => 'Plan',
            'document_type' => 'Report',
            'file_name' => 'plan.pdf',
            'file_path' => 'project_documents/plan.pdf',
            'uploaded_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('projects.destroy', $withPhoto))
            ->assertRedirect(route('projects.index'))
            ->assertSessionHas('error');

        $this->actingAs($admin)
            ->delete(route('projects.destroy', $withDocument))
            ->assertRedirect(route('projects.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('projects', ['id' => $withPhoto->id]);
        $this->assertDatabaseHas('projects', ['id' => $withDocument->id]);
        $this->assertDatabaseHas('project_photos', ['project_id' => $withPhoto->id]);
        $this->assertDatabaseHas('project_documents', ['project_id' => $withDocument->id]);
    }

    public function test_a_participating_mda_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'Super Administrator']);
        $lead = $this->mda('Health');
        $participant = $this->mda('Works');
        $project = $this->project($lead, 'Shared Clinic');
        $project->participatingMdas()->attach($participant->id);

        $this->actingAs($admin)
            ->delete(route('mdas.destroy', $participant))
            ->assertRedirect(route('mdas.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('mdas', ['id' => $participant->id]);
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
        $this->assertDatabaseHas('project_mda', [
            'project_id' => $project->id,
            'mda_id' => $participant->id,
        ]);
    }

    public function test_an_empty_mda_can_still_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'Super Administrator']);
        $empty = $this->mda('Unused');

        $this->actingAs($admin)
            ->delete(route('mdas.destroy', $empty))
            ->assertRedirect(route('mdas.index'));

        $this->assertDatabaseMissing('mdas', ['id' => $empty->id]);
    }

    public function test_a_user_with_audit_history_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'Super Administrator']);
        $officer = User::factory()->create(['role' => 'M&E Officer']);

        AuditLog::create([
            'user_id' => $officer->id,
            'action' => 'Updated',
            'module' => 'Project',
            'description' => 'Updated a project',
        ]);

        $this->actingAs($admin)
            ->delete(route('users.destroy', $officer))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $officer->id]);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $officer->id]);
    }

    public function test_profile_deletion_keeps_a_user_who_has_monitoring_records(): void
    {
        $officer = User::factory()->create(['role' => 'MDA Officer']);
        $project = $this->project($this->mda('Health'), 'Clinic');

        Monitoring::create([
            'project_id' => $project->id,
            'user_id' => $officer->id,
            'monitoring_date' => '2026-10-17',
            'status' => 'Ongoing',
            'progress_before' => 0,
            'progress_after' => 10,
        ]);

        $this->actingAs($officer)
            ->from('/profile')
            ->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/profile')
            ->assertSessionHasErrorsIn('userDeletion', 'password');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['id' => $officer->id]);
        $this->assertDatabaseHas('monitorings', ['user_id' => $officer->id]);
    }

    public function test_a_viewer_still_cannot_delete_a_project(): void
    {
        $project = $this->project($this->mda('Health'), 'Clinic');

        $this->actingAs(User::factory()->create(['role' => 'Viewer']))
            ->delete(route('projects.destroy', $project))
            ->assertForbidden();

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }

    public function test_a_project_with_only_participating_ministries_can_still_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'Super Administrator']);
        $lead = $this->mda('Health');
        $participant = $this->mda('Works');
        $project = $this->project($lead, 'Shared Clinic');
        $project->participatingMdas()->attach($participant->id);

        $this->actingAs($admin)
            ->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'));

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('project_mda', ['project_id' => $project->id]);
        $this->assertDatabaseHas('mdas', ['id' => $participant->id]);
    }

    public function test_deleting_an_empty_mda_clears_a_user_assignment(): void
    {
        $admin = User::factory()->create(['role' => 'Super Administrator']);
        $empty = $this->mda('Unused Ministry');
        $officer = User::factory()->create([
            'role' => 'MDA Officer',
            'mda_id' => $empty->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('mdas.destroy', $empty))
            ->assertRedirect(route('mdas.index'));

        $this->assertDatabaseMissing('mdas', ['id' => $empty->id]);
        $this->assertNull($officer->fresh()->mda_id);
    }

    public function test_sqlite_schema_restricts_protected_deletes_and_keeps_set_null_rules(): void
    {
        $this->assertSame(
            'sqlite',
            DB::getDriverName(),
            'This assertion describes the in-memory test schema. It does not prove the MySQL rules.'
        );

        $this->assertSame('RESTRICT', $this->sqliteDeleteRule('projects', 'mda_id'));
        $this->assertSame('SET NULL', $this->sqliteDeleteRule('projects', 'development_partner_id'));
        $this->assertSame('CASCADE', $this->sqliteDeleteRule('project_mda', 'project_id'));
        $this->assertSame('RESTRICT', $this->sqliteDeleteRule('project_mda', 'mda_id'));
        $this->assertSame('RESTRICT', $this->sqliteDeleteRule('monitorings', 'project_id'));
        $this->assertSame('RESTRICT', $this->sqliteDeleteRule('monitorings', 'user_id'));
        $this->assertSame('RESTRICT', $this->sqliteDeleteRule('project_documents', 'project_id'));
        $this->assertSame('RESTRICT', $this->sqliteDeleteRule('project_documents', 'uploaded_by'));
        $this->assertSame('RESTRICT', $this->sqliteDeleteRule('project_photos', 'project_id'));
        $this->assertSame('RESTRICT', $this->sqliteDeleteRule('project_photos', 'uploaded_by'));
        $this->assertSame('SET NULL', $this->sqliteDeleteRule('users', 'mda_id'));
        $this->assertSame('SET NULL', $this->sqliteDeleteRule('audit_logs', 'user_id'));
    }

    public function test_sqlite_rejects_a_direct_project_delete_when_monitoring_exists(): void
    {
        $this->assertSame('sqlite', DB::getDriverName());

        $officer = User::factory()->create(['role' => 'M&E Officer']);
        $project = $this->project($this->mda('Health'), 'Clinic');
        Monitoring::create([
            'project_id' => $project->id,
            'user_id' => $officer->id,
            'monitoring_date' => '2026-10-17',
            'status' => 'Ongoing',
            'progress_before' => 0,
            'progress_after' => 10,
        ]);

        try {
            $project->delete();
            $this->fail('The in-memory database allowed a project delete that still has a monitoring record.');
        } catch (QueryException $exception) {
            $this->assertStringContainsStringIgnoringCase('foreign key', $exception->getMessage());
        }

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
        $this->assertDatabaseHas('monitorings', ['project_id' => $project->id]);
    }

    private function sqliteDeleteRule(string $table, string $column): string
    {
        foreach (DB::select('PRAGMA foreign_key_list('.$table.')') as $foreignKey) {
            if ($foreignKey->from === $column) {
                return strtoupper($foreignKey->on_delete);
            }
        }

        $this->fail("No {$table}.{$column} foreign key was found in the in-memory schema.");
    }

    private function mda(string $name): Mda
    {
        return Mda::create([
            'name' => 'Ministry of '.$name,
            'code' => strtoupper(substr($name, 0, 4)).substr(md5($name.uniqid()), 0, 4),
            'status' => true,
        ]);
    }

    private function project(Mda $mda, string $title): Project
    {
        return Project::create([
            'mda_id' => $mda->id,
            'project_title' => $title,
            'sector' => 'Health',
            'lga' => 'Akure South',
            'status' => 'Planning',
            'progress' => 0,
        ]);
    }
}
