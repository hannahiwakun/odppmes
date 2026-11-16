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
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase2BMdaAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_mda_officer_can_open_only_projects_led_by_the_assigned_mda(): void
    {
        Storage::fake(OperationalStorage::DISK);
        Storage::disk(OperationalStorage::DISK)->put('project_photos/own.jpg', 'own');
        Storage::disk(OperationalStorage::DISK)->put('project_documents/own.pdf', 'own-doc');

        [$health, $works] = $this->ministries();
        $officer = $this->officer($health);
        $own = $this->project($health, 'Health Clinic');
        $other = $this->project($works, 'Rural Road');

        $photo = ProjectPhoto::create([
            'project_id' => $own->id,
            'photo' => 'project_photos/own.jpg',
            'uploaded_by' => $officer->id,
        ]);
        ProjectDocument::create([
            'project_id' => $own->id,
            'title' => 'Plan',
            'document_type' => 'Report',
            'file_name' => 'own.pdf',
            'file_path' => 'project_documents/own.pdf',
            'uploaded_by' => $officer->id,
        ]);
        $visit = Monitoring::create([
            'project_id' => $own->id,
            'user_id' => $officer->id,
            'monitoring_date' => '2026-10-17',
            'status' => 'Ongoing',
            'progress_before' => 0,
            'progress_after' => 20,
        ]);
        $otherVisit = Monitoring::create([
            'project_id' => $other->id,
            'user_id' => $officer->id,
            'monitoring_date' => '2026-10-17',
            'status' => 'Ongoing',
            'progress_before' => 0,
            'progress_after' => 5,
        ]);

        $this->actingAs($officer)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Health Clinic')
            ->assertDontSee('Rural Road');

        $this->actingAs($officer)->get(route('projects.show', $own))->assertOk();
        $this->actingAs($officer)->get(route('projects.show', $other))->assertNotFound();
        $this->actingAs($officer)->get(route('projects.edit', $other))->assertNotFound();
        $this->actingAs($officer)->get(route('project-documents.index', $own))->assertOk();
        $this->actingAs($officer)->get(route('project-documents.index', $other))->assertNotFound();
        $this->actingAs($officer)->get(route('projects.photos.file', [$own, $photo]))->assertOk();
        $this->actingAs($officer)->get(route('projects.photos.file', [$other, $photo]))->assertNotFound();
        $this->actingAs($officer)->get(route('monitorings.show', $visit))->assertOk();
        $this->actingAs($officer)->get(route('monitorings.show', $otherVisit))->assertNotFound();

        $this->actingAs($officer)
            ->put(route('projects.update', $own), [
                'project_title' => 'Health Clinic',
                'mda_id' => $works->id,
                'sector' => 'Health',
                'lga' => 'Akure South',
                'status' => 'Planning',
                'progress' => 0,
            ])
            ->assertRedirect(route('projects.index'));

        $this->assertSame($health->id, $own->fresh()->mda_id);

        $this->actingAs($officer)
            ->post(route('monitorings.store'), [
                'project_id' => $other->id,
                'monitoring_date' => '2026-10-17',
                'status' => 'Ongoing',
            ])
            ->assertNotFound();

        $this->actingAs($this->user('M&E Officer'))
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Health Clinic')
            ->assertSee('Rural Road');

        $this->actingAs($this->user('Super Administrator'))
            ->get(route('projects.show', $other))
            ->assertOk();
    }

    public function test_an_mda_officer_without_an_assignment_cannot_open_projects(): void
    {
        $health = Mda::create(['name' => 'Ministry of Health', 'code' => 'MOH', 'status' => true]);
        $project = $this->project($health, 'Health Clinic');
        $officer = $this->user('MDA Officer');

        $this->actingAs($officer)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertDontSee('Health Clinic');

        $this->actingAs($officer)
            ->get(route('projects.show', $project))
            ->assertNotFound();
    }

    public function test_reports_are_scoped_and_the_map_remains_closed_to_an_mda_officer(): void
    {
        [$health, $works] = $this->ministries();
        $this->project($health, 'Health Clinic');
        $this->project($works, 'Rural Road');

        $this->actingAs($this->officer($health))
            ->get(route('reports.index', ['mda_id' => $works->id]))
            ->assertOk()
            ->assertSee('Health Clinic')
            ->assertDontSee('Rural Road');

        $this->actingAs($this->officer($health))
            ->get(route('project.map'))
            ->assertForbidden();
    }

    private function ministries(): array
    {
        return [
            Mda::create(['name' => 'Ministry of Health', 'code' => 'MOH', 'status' => true]),
            Mda::create(['name' => 'Ministry of Works', 'code' => 'MOW', 'status' => true]),
        ];
    }

    private function officer(Mda $mda): User
    {
        return User::factory()->create([
            'role' => 'MDA Officer',
            'mda_id' => $mda->id,
        ]);
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
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
