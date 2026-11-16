<?php

namespace Tests\Feature;

use App\Models\Mda;
use App\Models\Monitoring;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectPhoto;
use App\Models\User;
use App\Support\OperationalStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase2AccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_monitoring_is_limited_to_project_roles(): void
    {
        $project = $this->project();

        $this->actingAs($this->user('Viewer'))
            ->get(route('monitorings.index'))
            ->assertForbidden();

        $this->actingAs($this->user('Development Partner'))
            ->post(route('monitorings.store'), [
                'project_id' => $project->id,
                'monitoring_date' => '2026-10-17',
                'status' => 'Ongoing',
            ])
            ->assertForbidden();

        $this->actingAs($this->user('MDA Officer'))
            ->get(route('monitorings.index'))
            ->assertOk();
    }

    public function test_reports_and_map_follow_the_menu_roles(): void
    {
        $this->actingAs($this->user('Viewer'))
            ->get(route('reports.index'))
            ->assertOk();

        $this->actingAs($this->user('Viewer'))
            ->get(route('project.map'))
            ->assertOk();

        $this->actingAs($this->user('MDA Officer'))
            ->get(route('reports.index'))
            ->assertOk();

        $this->actingAs($this->user('MDA Officer'))
            ->get(route('project.map'))
            ->assertForbidden();

        $this->actingAs($this->user('Development Partner'))
            ->get(route('reports.pdf'))
            ->assertForbidden();
    }

    public function test_user_role_must_be_one_of_the_allowed_names(): void
    {
        $admin = $this->user('Super Administrator');

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'New Officer',
                'email' => 'officer@example.com',
                'role' => 'Director',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'officer@example.com']);

        $mda = Mda::create([
            'name' => 'Ministry of Health',
            'code' => 'MOH',
            'status' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'New Officer',
                'email' => 'officer@example.com',
                'role' => 'MDA Officer',
                'mda_id' => $mda->id,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('users.index'));

        $created = User::where('email', 'officer@example.com')->first();

        $this->assertNotNull($created);
        $this->assertSame('MDA Officer', $created->role);
        $this->assertSame($mda->id, $created->mda_id);
        $this->assertTrue(Hash::check('password123', $created->password));
    }

    public function test_project_photo_file_requires_the_matching_project(): void
    {
        Storage::fake(OperationalStorage::DISK);
        Storage::disk(OperationalStorage::DISK)->put('project_photos/one.jpg', 'image-bytes');
        Storage::disk(OperationalStorage::DISK)->put('secrets.txt', 'keep');

        $project = $this->project();
        $other = $this->project();
        $officer = $this->user('M&E Officer');
        $photo = ProjectPhoto::create([
            'project_id' => $project->id,
            'photo' => 'project_photos/one.jpg',
            'uploaded_by' => $officer->id,
        ]);
        $unsafe = ProjectPhoto::create([
            'project_id' => $project->id,
            'photo' => '../secrets.txt',
            'uploaded_by' => $officer->id,
        ]);

        $assigned = User::factory()->create([
            'role' => 'MDA Officer',
            'mda_id' => $project->mda_id,
        ]);

        $this->get(route('projects.photos.file', [$project, $photo]))
            ->assertRedirect(route('login'));

        $this->actingAs($assigned)
            ->get(route('projects.photos.file', [$project, $photo]))
            ->assertOk();

        $this->actingAs($this->user('Viewer'))
            ->get(route('projects.photos.file', [$project, $photo]))
            ->assertForbidden();

        $this->actingAs($assigned)
            ->get(route('projects.photos.file', [$other, $photo]))
            ->assertNotFound();

        $this->actingAs($assigned)
            ->get(route('projects.photos.file', [$project, $unsafe]))
            ->assertNotFound();

        Storage::disk(OperationalStorage::DISK)->assertExists('project_photos/one.jpg');
        Storage::disk(OperationalStorage::DISK)->assertExists('secrets.txt');
    }

    public function test_monitoring_photo_is_served_only_to_project_roles(): void
    {
        Storage::fake(OperationalStorage::DISK);
        Storage::disk(OperationalStorage::DISK)->put('monitorings/visit.jpg', 'visit-bytes');

        $monitoring = Monitoring::create([
            'project_id' => $this->project()->id,
            'user_id' => $this->user('MDA Officer')->id,
            'monitoring_date' => '2026-10-17',
            'progress_before' => 0,
            'progress_after' => 10,
            'status' => 'Ongoing',
            'photo' => 'monitorings/visit.jpg',
        ]);

        $this->get(route('monitorings.photo', $monitoring))
            ->assertRedirect(route('login'));

        $this->actingAs($this->user('Viewer'))
            ->get(route('monitorings.photo', $monitoring))
            ->assertForbidden();

        $this->actingAs($this->user('M&E Officer'))
            ->get(route('monitorings.photo', $monitoring))
            ->assertOk();

        Storage::disk(OperationalStorage::DISK)->assertExists('monitorings/visit.jpg');
    }

    public function test_user_with_related_records_is_not_deleted(): void
    {
        $admin = $this->user('Super Administrator');
        $owner = $this->user('MDA Officer');
        $project = $this->project();

        ProjectDocument::create([
            'project_id' => $project->id,
            'title' => 'Plan',
            'document_type' => 'Report',
            'file_name' => 'plan.pdf',
            'file_path' => 'project_documents/plan.pdf',
            'uploaded_by' => $owner->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('users.destroy', $owner))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $owner->id]);
        $this->assertDatabaseHas('project_documents', ['uploaded_by' => $owner->id]);

        $spare = $this->user('Viewer');

        $this->actingAs($admin)
            ->delete(route('users.destroy', $spare))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $spare->id]);
    }

    private function user(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
        ]);
    }

    private function project(): Project
    {
        $mda = Mda::create([
            'name' => 'Ministry '.uniqid(),
            'code' => strtoupper(substr(md5(uniqid('', true)), 0, 8)),
            'status' => true,
        ]);

        return Project::create([
            'mda_id' => $mda->id,
            'project_title' => 'Project '.uniqid(),
            'sector' => 'Education',
            'lga' => 'Akure North',
            'status' => 'Planning',
            'progress' => 0,
        ]);
    }
}
