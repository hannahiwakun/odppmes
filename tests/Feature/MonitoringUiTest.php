<?php

namespace Tests\Feature;

use App\Models\Mda;
use App\Models\Monitoring;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_page_loads_and_keeps_the_entered_visit_after_an_invalid_status(): void
    {
        $officer = $this->officer($this->ministry('Ministry of Health', 'MOHMON'));
        $project = $this->project($officer->mda, 'Health Clinic');

        $this->actingAs($officer)
            ->get(route('monitorings.create', ['project' => $project->id]))
            ->assertOk()
            ->assertSee('Save Monitoring')
            ->assertSee('for="project_id"', false)
            ->assertSee('for="status"', false)
            ->assertSee('JPG and PNG files up to 4MB are accepted.')
            ->assertSee('value="'.$project->id.'" selected', false)
            ->assertDontSee('Update Monitoring');

        $this->actingAs($officer)
            ->from(route('monitorings.create', ['project' => $project->id]))
            ->post(route('monitorings.store'), [
                'project_id' => $project->id,
                'monitoring_date' => '2026-10-17',
                'status' => 'Delayed',
                'progress_after' => 40,
                'findings' => 'Kept finding',
            ])
            ->assertRedirect(route('monitorings.create', ['project' => $project->id]))
            ->assertSessionHasErrors('status');

        $this->actingAs($officer)
            ->get(route('monitorings.create', ['project' => $project->id]))
            ->assertOk()
            ->assertSee('value="2026-10-17"', false)
            ->assertSee('value="'.$project->id.'" selected', false)
            ->assertSee('Kept finding');
    }

    public function test_edit_page_shows_the_saved_visit_and_a_calendar_date(): void
    {
        $officer = $this->officer($this->ministry('Ministry of Health', 'MOHEDT'));
        $project = $this->project($officer->mda, 'Clinic Support');
        $visit = Monitoring::create([
            'project_id' => $project->id,
            'user_id' => $officer->id,
            'monitoring_date' => '2026-10-17',
            'status' => 'Ongoing',
            'progress_before' => 10,
            'progress_after' => 45,
            'findings' => 'Work is underway.',
            'latitude' => 7.25,
            'longitude' => 5.19,
        ]);

        $this->actingAs($officer)
            ->get(route('monitorings.edit', $visit))
            ->assertOk()
            ->assertSee('Update Monitoring')
            ->assertSee('Work is underway.')
            ->assertSee('value="2026-10-17"', false)
            ->assertSee('for="monitoring-edit-findings"', false)
            ->assertSee('readonly', false)
            ->assertSee('Capture New GPS Location')
            ->assertSee('Saving this visit updates the project’s current status and progress, including when an older visit is edited. The development-register status is left unchanged.');
    }

    public function test_listing_and_details_use_the_protected_photo_route_and_role_checks(): void
    {
        $health = $this->ministry('Ministry of Health', 'MOHVIS');
        $works = $this->ministry('Ministry of Works', 'MOWVIS');
        $officer = $this->officer($health);
        $otherOfficer = $this->officer($works);
        $meo = User::factory()->create(['role' => 'M&E Officer']);
        $project = $this->project($health, 'Clinic Support');
        $other = $this->project($works, 'Rural Road');

        $visit = Monitoring::create([
            'project_id' => $project->id,
            'user_id' => $officer->id,
            'monitoring_date' => '2026-10-17',
            'status' => 'Ongoing',
            'progress_before' => 10,
            'progress_after' => 45,
            'photo' => 'monitorings/visit.jpg',
            'latitude' => 7.25,
            'longitude' => 5.19,
        ]);
        $foreign = Monitoring::create([
            'project_id' => $other->id,
            'user_id' => $otherOfficer->id,
            'monitoring_date' => '2026-10-17',
            'status' => 'Planning',
            'progress_before' => 0,
            'progress_after' => 0,
        ]);

        $this->actingAs($officer)
            ->get(route('monitorings.index'))
            ->assertOk()
            ->assertSee('Monitoring')
            ->assertSee('Field visits recorded for Ondo State development projects.')
            ->assertSee('Clinic Support')
            ->assertDontSee('Rural Road')
            ->assertSee('GPS recorded')
            ->assertSee('alt="Monitoring photo"', false)
            ->assertSee(route('monitorings.photo', $visit), false)
            ->assertDontSee('/storage/monitorings', false)
            ->assertSee('View')
            ->assertDontSee('Delete this monitoring record?');

        $this->actingAs($meo)
            ->get(route('monitorings.index'))
            ->assertOk()
            ->assertSee('Delete this monitoring record?');

        $this->actingAs($officer)
            ->get(route('monitorings.show', $visit))
            ->assertOk()
            ->assertSee('alt="Monitoring photo"', false)
            ->assertSee(route('monitorings.photo', $visit), false)
            ->assertSee('maps.google.com/maps?q=', false);

        $this->actingAs($officer)->get(route('monitorings.show', $foreign))->assertNotFound();
        $this->actingAs($officer)->get(route('monitorings.edit', $foreign))->assertNotFound();
    }

    private function ministry(string $name, string $code): Mda
    {
        return Mda::create([
            'name' => $name,
            'code' => $code,
            'status' => true,
        ]);
    }

    private function officer(Mda $mda): User
    {
        return User::factory()->create([
            'role' => 'MDA Officer',
            'mda_id' => $mda->id,
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
