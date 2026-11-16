<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Mda;
use App\Models\Monitoring;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectPhoto;
use App\Models\User;
use App\Support\OperationalStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class EvidenceDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_mda_officer_cannot_delete_their_own_ministry_evidence(): void
    {
        [$officer, $project, $photo, $document, $visit] = $this->evidence();

        $this->actingAs($officer)
            ->get(route('projects.photos.index', $project))
            ->assertOk()
            ->assertDontSee('Delete this photo');

        $this->actingAs($officer)
            ->get(route('project-documents.index', $project))
            ->assertOk()
            ->assertDontSee('Are you sure you want to delete this document?')
            ->assertSee('Download');

        $this->actingAs($officer)
            ->get(route('monitorings.index'))
            ->assertOk()
            ->assertDontSee('Delete this monitoring record?')
            ->assertSee('View');

        $this->actingAs($officer)
            ->delete(route('projects.photos.destroy', [$project, $photo]))
            ->assertForbidden();

        $this->actingAs($officer)
            ->delete(route('project-documents.destroy', [$project, $document]))
            ->assertForbidden();

        $this->actingAs($officer)
            ->delete(route('monitorings.destroy', $visit))
            ->assertForbidden();

        $this->assertDatabaseHas('project_photos', ['id' => $photo->id]);
        $this->assertDatabaseHas('project_documents', ['id' => $document->id]);
        $this->assertDatabaseHas('monitorings', ['id' => $visit->id]);
        Storage::disk(OperationalStorage::DISK)->assertExists('project_photos/clinic.jpg');
        Storage::disk(OperationalStorage::DISK)->assertExists('project_documents/plan.pdf');
        Storage::disk(OperationalStorage::DISK)->assertExists('monitorings/visit.jpg');
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_a_super_administrator_can_delete_each_type_of_evidence(): void
    {
        [, $project, $photo, $document, $visit] = $this->evidence();
        $admin = User::factory()->create(['role' => 'Super Administrator']);

        $this->assertEvidenceCanBeDeletedBy($admin, $project, $photo, $document, $visit);
    }

    public function test_an_me_officer_can_delete_each_type_of_evidence(): void
    {
        [, $project, $photo, $document, $visit] = $this->evidence();
        $officer = User::factory()->create(['role' => 'M&E Officer']);

        $this->actingAs($officer)
            ->get(route('projects.photos.index', $project))
            ->assertOk()
            ->assertSee('Delete this photo');

        $this->assertEvidenceCanBeDeletedBy($officer, $project, $photo, $document, $visit);
    }

    public function test_viewer_and_development_partner_cannot_delete_evidence(): void
    {
        [, $project, $photo, $document, $visit] = $this->evidence();

        foreach (['Viewer', 'Development Partner'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->delete(route('projects.photos.destroy', [$project, $photo]))
                ->assertForbidden();

            $this->actingAs($user)
                ->delete(route('project-documents.destroy', [$project, $document]))
                ->assertForbidden();

            $this->actingAs($user)
                ->delete(route('monitorings.destroy', $visit))
                ->assertForbidden();
        }

        $this->assertDatabaseHas('project_photos', ['id' => $photo->id]);
        $this->assertDatabaseHas('project_documents', ['id' => $document->id]);
        $this->assertDatabaseHas('monitorings', ['id' => $visit->id]);
        Storage::disk(OperationalStorage::DISK)->assertExists('project_photos/clinic.jpg');
        Storage::disk(OperationalStorage::DISK)->assertExists('project_documents/plan.pdf');
        Storage::disk(OperationalStorage::DISK)->assertExists('monitorings/visit.jpg');
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_an_mda_officer_can_still_upload_and_view_evidence_for_their_ministry(): void
    {
        Storage::fake(OperationalStorage::DISK);
        $health = $this->ministry('Ministry of Health', 'MOH');
        $works = $this->ministry('Ministry of Works', 'MOW');
        $officer = User::factory()->create([
            'role' => 'MDA Officer',
            'mda_id' => $health->id,
        ]);
        $own = $this->project($health, 'Health Clinic');
        $other = $this->project($works, 'Rural Road');

        $this->actingAs($officer)
            ->post(route('projects.photos.store', $own), [
                'photo' => UploadedFile::fake()->image('clinic.jpg'),
                'caption' => 'Clinic front',
            ])
            ->assertRedirect(route('projects.photos.index', $own));

        $photo = ProjectPhoto::firstOrFail();

        $this->actingAs($officer)
            ->get(route('projects.photos.file', [$own, $photo]))
            ->assertOk();

        $this->actingAs($officer)
            ->get(route('projects.photos.file', [$other, $photo]))
            ->assertNotFound();

        $this->actingAs($officer)
            ->post(route('projects.photos.store', $other), [
                'photo' => UploadedFile::fake()->image('road.jpg'),
            ])
            ->assertNotFound();

        $this->actingAs($officer)
            ->post(route('project-documents.store', $own), [
                'title' => 'Clinic plan',
                'document_type' => 'Report',
                'document' => UploadedFile::fake()->image('plan.jpg'),
            ])
            ->assertRedirect(route('project-documents.index', $own));

        $document = ProjectDocument::firstOrFail();

        $this->actingAs($officer)
            ->get(route('project-documents.download', [$own, $document]))
            ->assertOk();

        $this->actingAs($officer)
            ->get(route('project-documents.index', $other))
            ->assertNotFound();

        $this->assertSame(1, ProjectPhoto::count());
        $this->assertSame(1, ProjectDocument::count());
    }

    public function test_a_deleted_project_photograph_is_audited(): void
    {
        [, $project, $photo] = $this->evidence();
        $admin = User::factory()->create(['role' => 'Super Administrator']);

        $this->actingAs($admin)
            ->delete(route('projects.photos.destroy', [$project, $photo]))
            ->assertRedirect(route('projects.photos.index', $project))
            ->assertSessionHas('success');

        $log = AuditLog::firstOrFail();
        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame('Deleted', $log->action);
        $this->assertSame('Project Photo', $log->module);
        $this->assertStringContainsString('Clinic front', $log->description);
        $this->assertStringContainsString($project->project_title, $log->description);
        $this->assertDatabaseMissing('project_photos', ['id' => $photo->id]);
        Storage::disk(OperationalStorage::DISK)->assertMissing('project_photos/clinic.jpg');
    }

    public function test_a_failed_photo_audit_keeps_the_photograph_and_its_file(): void
    {
        [, $project, $photo] = $this->evidence();
        $admin = User::factory()->create(['role' => 'Super Administrator']);

        AuditLog::creating(function () {
            throw new RuntimeException('Audit log failed.');
        });

        $response = $this->actingAs($admin)
            ->delete(route('projects.photos.destroy', [$project, $photo]));

        $response->assertStatus(500);
        $response->assertSessionMissing('success');
        $this->assertDatabaseHas('project_photos', ['id' => $photo->id]);
        Storage::disk(OperationalStorage::DISK)->assertExists('project_photos/clinic.jpg');
        $this->assertDatabaseCount('audit_logs', 0);
    }

    /**
     * @return array{0: User, 1: Project, 2: ProjectPhoto, 3: ProjectDocument, 4: Monitoring}
     */
    private function evidence(): array
    {
        Storage::fake(OperationalStorage::DISK);
        Storage::disk(OperationalStorage::DISK)->put('project_photos/clinic.jpg', 'photo');
        Storage::disk(OperationalStorage::DISK)->put('project_documents/plan.pdf', 'plan');
        Storage::disk(OperationalStorage::DISK)->put('monitorings/visit.jpg', 'visit');

        $ministry = $this->ministry('Ministry of Health', 'MOH');
        $officer = User::factory()->create([
            'role' => 'MDA Officer',
            'mda_id' => $ministry->id,
        ]);
        $project = $this->project($ministry, 'Health Clinic');

        $photo = ProjectPhoto::create([
            'project_id' => $project->id,
            'photo' => 'project_photos/clinic.jpg',
            'caption' => 'Clinic front',
            'uploaded_by' => $officer->id,
        ]);

        $document = ProjectDocument::create([
            'project_id' => $project->id,
            'title' => 'Clinic plan',
            'document_type' => 'Report',
            'file_name' => 'plan.pdf',
            'file_path' => 'project_documents/plan.pdf',
            'uploaded_by' => $officer->id,
        ]);

        $visit = Monitoring::create([
            'project_id' => $project->id,
            'user_id' => $officer->id,
            'monitoring_date' => '2026-10-17',
            'status' => 'Ongoing',
            'progress_before' => 0,
            'progress_after' => 20,
            'photo' => 'monitorings/visit.jpg',
        ]);

        return [$officer, $project, $photo, $document, $visit];
    }

    private function assertEvidenceCanBeDeletedBy(
        User $user,
        Project $project,
        ProjectPhoto $photo,
        ProjectDocument $document,
        Monitoring $visit
    ): void {
        $this->actingAs($user)
            ->delete(route('projects.photos.destroy', [$project, $photo]))
            ->assertRedirect(route('projects.photos.index', $project));

        $this->actingAs($user)
            ->delete(route('project-documents.destroy', [$project, $document]))
            ->assertRedirect(route('project-documents.index', $project));

        $this->actingAs($user)
            ->delete(route('monitorings.destroy', $visit))
            ->assertRedirect(route('monitorings.index'));

        $this->assertDatabaseMissing('project_photos', ['id' => $photo->id]);
        $this->assertDatabaseMissing('project_documents', ['id' => $document->id]);
        $this->assertDatabaseMissing('monitorings', ['id' => $visit->id]);
        Storage::disk(OperationalStorage::DISK)->assertMissing('project_photos/clinic.jpg');
        Storage::disk(OperationalStorage::DISK)->assertMissing('project_documents/plan.pdf');
        Storage::disk(OperationalStorage::DISK)->assertMissing('monitorings/visit.jpg');

        $photoLog = AuditLog::query()
            ->where('module', 'Project Photo')
            ->where('action', 'Deleted')
            ->first();

        $this->assertNotNull($photoLog);
        $this->assertSame($user->id, $photoLog->user_id);
        $this->assertStringContainsString('Health Clinic', $photoLog->description);
    }

    private function ministry(string $name, string $code): Mda
    {
        return Mda::create([
            'name' => $name,
            'code' => $code,
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
