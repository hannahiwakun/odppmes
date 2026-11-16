<?php

namespace Tests\Feature;

use App\Models\DevelopmentPartner;
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
use Tests\TestCase;

class Phase1SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_edit_loads_existing_values_and_partial_update_keeps_other_fields(): void
    {
        $admin = $this->user('Super Administrator');
        $mda = $this->mda('Health');
        $partner = $this->partner('UNICEF');

        $project = Project::create([
            'mda_id' => $mda->id,
            'development_partner_id' => $partner->id,
            'project_title' => 'Original Title',
            'project_code' => 'ODP-100',
            'sector' => 'Health',
            'lga' => 'Akure South',
            'ward' => 'Ward 4',
            'budget' => 2500000.50,
            'funding_source' => 'State Budget',
            'start_date' => '2026-01-15',
            'end_date' => '2026-12-31',
            'status' => 'Ongoing',
            'progress' => 40,
            'latitude' => 7.2500000,
            'longitude' => 5.1900000,
            'description' => 'Keep this description.',
        ]);

        $this->actingAs($admin)
            ->get(route('projects.edit', $project))
            ->assertOk()
            ->assertSee('Original Title')
            ->assertSee('ODP-100')
            ->assertSee('Ward 4')
            ->assertSee('State Budget')
            ->assertSee('Keep this description.');

        $this->actingAs($admin)
            ->put(route('projects.update', $project), [
                'project_title' => 'Updated Title',
                'mda_id' => $mda->id,
                'sector' => 'Health',
                'lga' => 'Akure South',
                'status' => 'Ongoing',
                'progress' => 40,
            ])
            ->assertRedirect(route('projects.index'));

        $project->refresh();

        $this->assertSame('Updated Title', $project->project_title);
        $this->assertSame('ODP-100', $project->project_code);
        $this->assertSame($mda->id, $project->mda_id);
        $this->assertSame($partner->id, $project->development_partner_id);
        $this->assertSame('Health', $project->sector);
        $this->assertSame('Akure South', $project->lga);
        $this->assertSame('Ward 4', $project->ward);
        $this->assertEquals(2500000.50, (float) $project->budget);
        $this->assertSame('State Budget', $project->funding_source);
        $this->assertSame('2026-01-15', substr((string) $project->start_date, 0, 10));
        $this->assertSame('2026-12-31', substr((string) $project->end_date, 0, 10));
        $this->assertSame('Ongoing', $project->status);
        $this->assertSame(40, (int) $project->progress);
        $this->assertEquals(7.25, (float) $project->latitude);
        $this->assertEquals(5.19, (float) $project->longitude);
        $this->assertSame('Keep this description.', $project->description);
    }

    public function test_partner_update_rejects_unauthorized_fields(): void
    {
        $admin = $this->user('Super Administrator');
        $partner = $this->partner('World Bank');
        $originalId = $partner->id;
        $originalCreatedAt = $partner->created_at->toDateTimeString();

        $this->actingAs($admin)
            ->put(route('development-partners.update', $partner), [
                'id' => 9999,
                'partner_name' => 'World Bank Nigeria',
                'partner_type' => 'Multilateral',
                'status' => 1,
                'created_at' => '2000-01-01 00:00:00',
                'user_id' => 77,
                'role' => 'Super Administrator',
            ])
            ->assertRedirect(route('development-partners.index'));

        $partner->refresh();

        $this->assertSame($originalId, $partner->id);
        $this->assertSame('World Bank Nigeria', $partner->partner_name);
        $this->assertSame('Multilateral', $partner->partner_type);
        $this->assertNotSame('2000-01-01 00:00:00', $partner->created_at->toDateTimeString());
        $this->assertSame($originalCreatedAt, $partner->created_at->toDateTimeString());
        $this->assertDatabaseMissing('development_partners', [
            'id' => 9999,
        ]);
    }

    public function test_viewer_cannot_change_a_partner(): void
    {
        $partner = $this->partner('UNDP');

        $this->actingAs($this->user('Viewer'))
            ->put(route('development-partners.update', $partner), [
                'partner_name' => 'Taken Over',
                'partner_type' => 'NGO',
                'status' => 0,
            ])
            ->assertForbidden();

        $this->assertSame('UNDP', $partner->fresh()->partner_name);
    }

    public function test_partner_with_projects_is_not_deleted(): void
    {
        $admin = $this->user('Super Administrator');
        $partner = $this->partner('Kept Partner');
        $project = $this->project();
        $project->update(['development_partner_id' => $partner->id]);

        $this->actingAs($admin)
            ->delete(route('development-partners.destroy', $partner))
            ->assertRedirect(route('development-partners.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('development_partners', ['id' => $partner->id]);
        $this->assertSame($partner->id, $project->fresh()->development_partner_id);
    }

    public function test_partner_detail_page_is_available_to_a_super_administrator(): void
    {
        $partner = $this->partner('WHO');

        $this->actingAs($this->user('Super Administrator'))
            ->get(route('development-partners.index'))
            ->assertOk()
            ->assertSee('WHO');

        $this->actingAs($this->user('Super Administrator'))
            ->get(route('development-partners.show', $partner))
            ->assertOk()
            ->assertSee('WHO');
    }

    public function test_unauthorized_users_cannot_delete_a_project(): void
    {
        $project = $this->project();

        foreach (['Viewer', 'Development Partner', 'MDA Officer'] as $role) {
            $actor = $this->user($role);

            if ($role === 'MDA Officer') {
                $actor->update(['mda_id' => $project->mda_id]);
            }

            $this->actingAs($actor)
                ->delete(route('projects.destroy', $project))
                ->assertForbidden();
        }

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }

    public function test_project_with_related_records_is_not_deleted(): void
    {
        $admin = $this->user('M&E Officer');
        $project = $this->project();

        Monitoring::create([
            'project_id' => $project->id,
            'user_id' => $admin->id,
            'monitoring_date' => '2026-06-01',
            'status' => 'Ongoing',
            'progress_before' => 10,
            'progress_after' => 20,
        ]);

        $this->actingAs($admin)
            ->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
        $this->assertDatabaseHas('monitorings', ['project_id' => $project->id]);
    }

    public function test_authorized_user_can_delete_a_project_without_related_records(): void
    {
        $project = $this->project();
        $other = $this->project();

        $this->actingAs($this->user('Super Administrator'))
            ->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'));

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseHas('projects', ['id' => $other->id]);
    }

    public function test_document_download_requires_the_matching_project(): void
    {
        Storage::fake(OperationalStorage::DISK);
        Storage::disk(OperationalStorage::DISK)->put('project_documents/alpha.pdf', 'alpha-secret');
        Storage::disk(OperationalStorage::DISK)->put('project_documents/beta.pdf', 'beta-secret');
        Storage::disk(OperationalStorage::DISK)->put('secrets.txt', 'do-not-read');

        $projectA = $this->project();
        $projectB = $this->project();
        $officer = User::factory()->create([
            'role' => 'MDA Officer',
            'mda_id' => $projectA->mda_id,
        ]);

        $documentA = ProjectDocument::create([
            'project_id' => $projectA->id,
            'title' => 'Alpha Report',
            'document_type' => 'Report',
            'file_name' => 'alpha.pdf',
            'file_path' => 'project_documents/alpha.pdf',
            'uploaded_by' => $officer->id,
        ]);

        ProjectDocument::create([
            'project_id' => $projectB->id,
            'title' => 'Beta Report',
            'document_type' => 'Report',
            'file_name' => 'beta.pdf',
            'file_path' => 'project_documents/beta.pdf',
            'uploaded_by' => $officer->id,
        ]);

        $this->get(route('project-documents.download', [$projectA, $documentA]))
            ->assertRedirect(route('login'));

        $this->actingAs($this->user('Viewer'))
            ->get(route('project-documents.download', [$projectA, $documentA]))
            ->assertForbidden();

        $this->actingAs($officer)
            ->get(route('project-documents.index', $projectA))
            ->assertOk()
            ->assertSee('Alpha Report');

        $this->actingAs($officer)
            ->get(route('project-documents.create', $projectA))
            ->assertOk();

        $this->actingAs($officer)
            ->get(route('project-documents.download', [$projectA, $documentA]))
            ->assertOk();

        $this->actingAs($officer)
            ->get(route('project-documents.download', [$projectB, $documentA]))
            ->assertNotFound();

        $poisoned = ProjectDocument::create([
            'project_id' => $projectA->id,
            'title' => 'Poisoned',
            'document_type' => 'Other',
            'file_name' => '../secrets.txt',
            'file_path' => 'project_documents/../secrets.txt',
            'uploaded_by' => $officer->id,
        ]);

        $this->actingAs($officer)
            ->get(route('project-documents.download', [$projectA, $poisoned]))
            ->assertNotFound();

        Storage::disk(OperationalStorage::DISK)->assertExists('secrets.txt');
        Storage::disk(OperationalStorage::DISK)->assertExists('project_documents/beta.pdf');
    }

    public function test_document_upload_rejects_a_disallowed_file_type(): void
    {
        Storage::fake(OperationalStorage::DISK);
        $officer = $this->user('M&E Officer');
        $project = $this->project();

        $this->actingAs($officer)
            ->post(route('project-documents.store', $project), [
                'title' => 'Bad File',
                'document_type' => 'Other',
                'document' => UploadedFile::fake()->create('evil.exe', 20, 'application/octet-stream'),
            ])
            ->assertSessionHasErrors('document');

        $this->assertDatabaseCount('project_documents', 0);
    }

    public function test_mda_pages_work_and_deletion_keeps_related_projects(): void
    {
        $admin = $this->user('Super Administrator');

        $this->actingAs($admin)
            ->get(route('mdas.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->post(route('mdas.store'), [
                'name' => 'Ministry of Works',
                'code' => 'MOW',
                'email' => 'works@example.com',
                'phone' => '08031112222',
                'contact_person' => 'Records Officer',
                'address' => 'Akure',
                'focal_name' => 'Ada Okafor',
                'focal_department' => 'Planning',
                'focal_designation' => 'Director',
                'focal_supervisor' => 'Chief Bola',
                'focal_phone' => '08032223333',
                'focal_email' => 'ada@example.com',
                'status' => 1,
            ])
            ->assertRedirect(route('mdas.index'));

        $mda = Mda::where('code', 'MOW')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('mdas.index'))
            ->assertOk()
            ->assertSee('Ministry of Works');

        $this->actingAs($admin)
            ->get(route('mdas.show', $mda))
            ->assertOk()
            ->assertSee('Ministry of Works');

        $this->actingAs($admin)
            ->put(route('mdas.update', $mda), [
                'name' => 'Ministry of Works and Infrastructure',
                'code' => 'MOW',
                'email' => 'works@example.com',
                'phone' => '08031112222',
                'contact_person' => 'Records Officer',
                'address' => 'Akure',
                'focal_name' => 'Ada Okafor',
                'focal_department' => 'Planning',
                'focal_designation' => 'Director',
                'focal_supervisor' => 'Chief Bola',
                'focal_phone' => '08032223333',
                'focal_email' => 'ada@example.com',
                'status' => 1,
            ])
            ->assertRedirect(route('mdas.index'));

        $this->assertSame('Ministry of Works and Infrastructure', $mda->fresh()->name);
        $this->assertSame('works@example.com', $mda->fresh()->email);

        $project = Project::create([
            'mda_id' => $mda->id,
            'project_title' => 'Road Project',
            'sector' => 'Infrastructure',
            'lga' => 'Ondo West',
        ]);

        $this->actingAs($admin)
            ->delete(route('mdas.destroy', $mda))
            ->assertRedirect(route('mdas.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('mdas', ['id' => $mda->id]);
        $this->assertDatabaseHas('projects', ['id' => $project->id]);

        $this->actingAs($admin)
            ->patch(route('mdas.deactivate', $mda))
            ->assertRedirect(route('mdas.index'));

        $this->assertFalse((bool) $mda->fresh()->status);
        $this->assertDatabaseHas('projects', ['id' => $project->id]);

        $empty = $this->mda('Empty Agency');

        $this->actingAs($admin)
            ->delete(route('mdas.destroy', $empty))
            ->assertRedirect(route('mdas.index'));

        $this->assertDatabaseMissing('mdas', ['id' => $empty->id]);
        $this->assertDatabaseHas('mdas', ['id' => $mda->id]);
    }

    public function test_non_administrator_cannot_create_an_mda(): void
    {
        $this->actingAs($this->user('M&E Officer'))
            ->post(route('mdas.store'), [
                'name' => 'Should Not Exist',
                'code' => 'NOPE',
                'status' => 1,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('mdas', ['code' => 'NOPE']);
    }

    public function test_photo_deletion_removes_only_the_selected_photo(): void
    {
        Storage::fake(OperationalStorage::DISK);
        Storage::disk(OperationalStorage::DISK)->put('project_photos/one.jpg', 'one');
        Storage::disk(OperationalStorage::DISK)->put('project_photos/two.jpg', 'two');
        Storage::disk(OperationalStorage::DISK)->put('secrets.txt', 'keep');

        $officer = $this->user('M&E Officer');
        $project = $this->project();
        $otherProject = $this->project();

        $photo = ProjectPhoto::create([
            'project_id' => $project->id,
            'photo' => 'project_photos/one.jpg',
            'caption' => 'One',
            'uploaded_by' => $officer->id,
        ]);

        $kept = ProjectPhoto::create([
            'project_id' => $project->id,
            'photo' => 'project_photos/two.jpg',
            'caption' => 'Two',
            'uploaded_by' => $officer->id,
        ]);

        $foreign = ProjectPhoto::create([
            'project_id' => $otherProject->id,
            'photo' => 'project_photos/two.jpg',
            'caption' => 'Foreign',
            'uploaded_by' => $officer->id,
        ]);

        $this->actingAs($this->user('Viewer'))
            ->delete(route('projects.photos.destroy', [$project, $photo]))
            ->assertForbidden();

        $this->assertDatabaseHas('project_photos', ['id' => $photo->id]);

        $this->actingAs($officer)
            ->delete(route('projects.photos.destroy', [$project, $foreign]))
            ->assertNotFound();

        $this->assertDatabaseHas('project_photos', ['id' => $foreign->id]);

        $unsafe = ProjectPhoto::create([
            'project_id' => $project->id,
            'photo' => 'project_photos/../secrets.txt',
            'caption' => 'Unsafe',
            'uploaded_by' => $officer->id,
        ]);

        $this->actingAs($officer)
            ->delete(route('projects.photos.destroy', [$project, $unsafe]))
            ->assertRedirect(route('projects.photos.index', $project));

        Storage::disk(OperationalStorage::DISK)->assertExists('secrets.txt');
        $this->assertDatabaseMissing('project_photos', ['id' => $unsafe->id]);

        $this->actingAs($officer)
            ->get(route('projects.photos.index', $project))
            ->assertOk()
            ->assertSee('Two');

        $this->actingAs($officer)
            ->delete(route('projects.photos.destroy', [$project, $photo]))
            ->assertRedirect(route('projects.photos.index', $project));

        $this->assertDatabaseMissing('project_photos', ['id' => $photo->id]);
        $this->assertDatabaseHas('project_photos', ['id' => $kept->id]);
        Storage::disk(OperationalStorage::DISK)->assertMissing('project_photos/one.jpg');
        Storage::disk(OperationalStorage::DISK)->assertExists('project_photos/two.jpg');
    }

    private function user(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
        ]);
    }

    private function mda(string $name): Mda
    {
        return Mda::create([
            'name' => $name,
            'code' => strtoupper(substr(md5($name.uniqid()), 0, 8)),
            'status' => true,
        ]);
    }

    private function partner(string $name): DevelopmentPartner
    {
        return DevelopmentPartner::create([
            'partner_name' => $name,
            'partner_type' => 'Multilateral',
            'status' => true,
        ]);
    }

    private function project(): Project
    {
        return Project::create([
            'mda_id' => $this->mda('Project MDA '.uniqid())->id,
            'project_title' => 'Project '.uniqid(),
            'sector' => 'Education',
            'lga' => 'Akure North',
            'status' => 'Planning',
            'progress' => 0,
        ]);
    }
}
