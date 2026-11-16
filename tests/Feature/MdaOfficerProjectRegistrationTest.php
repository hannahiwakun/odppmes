<?php

namespace Tests\Feature;

use App\Models\Mda;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MdaOfficerProjectRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_mda_officer_can_register_a_project_assigned_to_their_mda(): void
    {
        [$health, $works] = $this->ministries();
        $officer = $this->officer($health);

        $form = $this->actingAs($officer)->get(route('projects.create'));
        $form->assertOk();
        $form->assertSee('Add New Project', false);
        $form->assertSee('Ministry of Health');
        $form->assertSee('Assigned automatically from your MDA Officer account.');
        $form->assertDontSee('name="mda_id"', false);
        $form->assertDontSee('Select MDA', false);
        $form->assertDontSee('Ministry of Works');
        $form->assertDontSee('name="verification_status"', false);

        $this->actingAs($officer)
            ->post(route('projects.store'), [
                'project_title' => 'Health Outreach',
                'mda_id' => $works->id,
                'sector' => 'Health',
                'lga' => 'Akure South',
                'status' => 'Ongoing',
                'progress' => 15,
                'verification_status' => 'Verified',
            ])
            ->assertRedirect(route('projects.index'));

        $project = Project::query()->where('project_title', 'Health Outreach')->firstOrFail();
        $this->assertSame($health->id, $project->mda_id);
        $this->assertSame('Ongoing', $project->status);
        $this->assertSame(15, (int) $project->progress);
        $this->assertNull($project->verification_status);
    }

    public function test_an_mda_officer_can_edit_own_projects_but_not_mda_or_verification(): void
    {
        [$health, $works] = $this->ministries();
        $officer = $this->officer($health);
        $project = $this->project($health, 'Health Clinic', [
            'status' => 'Planning',
            'progress' => 10,
            'verification_status' => 'Reported',
        ]);

        $this->actingAs($officer)
            ->get(route('projects.edit', $project))
            ->assertOk()
            ->assertSee('The lead MDA cannot be changed by an MDA Officer.')
            ->assertDontSee('name="mda_id"', false)
            ->assertDontSee('name="verification_status"', false);

        $this->actingAs($officer)
            ->put(route('projects.update', $project), [
                'project_title' => 'Health Clinic Updated',
                'mda_id' => $works->id,
                'sector' => 'Health',
                'lga' => 'Akure South',
                'status' => 'Ongoing',
                'progress' => 45,
                'verification_status' => 'Verified',
            ])
            ->assertRedirect(route('projects.index'));

        $project->refresh();
        $this->assertSame('Health Clinic Updated', $project->project_title);
        $this->assertSame('Ongoing', $project->status);
        $this->assertSame(45, (int) $project->progress);
        $this->assertSame($health->id, $project->mda_id);
        $this->assertSame('Reported', $project->verification_status);
    }

    public function test_an_mda_officer_cannot_delete_or_open_another_mda_project(): void
    {
        [$health, $works] = $this->ministries();
        $officer = $this->officer($health);
        $own = $this->project($health, 'Health Clinic');
        $other = $this->project($works, 'Rural Road');

        $index = $this->actingAs($officer)->get(route('projects.index'));
        $index->assertOk();
        $index->assertSee('Health Clinic');
        $index->assertDontSee('Rural Road');
        $index->assertDontSee('Delete this project?');

        $this->actingAs($officer)->delete(route('projects.destroy', $own))->assertForbidden();
        $this->assertDatabaseHas('projects', ['id' => $own->id]);

        $this->actingAs($officer)->get(route('projects.show', $other))->assertNotFound();
        $this->actingAs($officer)->get(route('projects.edit', $other))->assertNotFound();
        $this->actingAs($officer)
            ->put(route('projects.update', $other), [
                'project_title' => 'Hacked Road',
                'mda_id' => $health->id,
                'sector' => 'Works',
                'lga' => 'Ondo West',
                'status' => 'Ongoing',
                'progress' => 1,
            ])
            ->assertNotFound();
        $this->assertSame('Rural Road', $other->fresh()->project_title);
    }

    public function test_super_administrator_and_me_officer_retain_project_privileges(): void
    {
        [$health, $works] = $this->ministries();
        $project = $this->project($health, 'Health Clinic', [
            'status' => 'Ongoing',
            'progress' => 20,
            'verification_status' => 'Reported',
        ]);

        $admin = $this->user('Super Administrator');
        $this->actingAs($admin)
            ->get(route('projects.create'))
            ->assertOk()
            ->assertSee('name="mda_id"', false)
            ->assertSee('name="verification_status"', false);

        $this->actingAs($admin)
            ->put(route('projects.update', $project), [
                'project_title' => 'Health Clinic',
                'mda_id' => $works->id,
                'sector' => 'Health',
                'lga' => 'Akure South',
                'status' => 'Ongoing',
                'progress' => 20,
                'verification_status' => 'Verified',
                'coordination_form' => '1',
            ])
            ->assertRedirect(route('projects.index'));

        $project->refresh();
        $this->assertSame($works->id, $project->mda_id);
        $this->assertSame('Verified', $project->verification_status);

        $empty = $this->project($health, 'Empty Programme');
        $this->actingAs($admin)
            ->delete(route('projects.destroy', $empty))
            ->assertRedirect(route('projects.index'));
        $this->assertDatabaseMissing('projects', ['id' => $empty->id]);

        $meo = $this->user('M&E Officer');
        $this->actingAs($meo)
            ->put(route('projects.update', $project), [
                'project_title' => 'Health Clinic',
                'mda_id' => $works->id,
                'sector' => 'Health',
                'lga' => 'Akure South',
                'status' => 'Ongoing',
                'progress' => 30,
                'verification_status' => 'Awaiting clarification',
                'coordination_form' => '1',
            ])
            ->assertRedirect(route('projects.index'));

        $this->assertSame('Awaiting clarification', $project->fresh()->verification_status);
        $this->assertSame(30, (int) $project->fresh()->progress);
    }

    public function test_viewer_and_development_partner_cannot_register_projects(): void
    {
        [$health] = $this->ministries();

        $this->actingAs($this->user('Viewer'))
            ->get(route('projects.create'))
            ->assertForbidden();

        $partner = User::factory()->create(['role' => 'Development Partner']);
        $this->actingAs($partner)
            ->post(route('projects.store'), [
                'project_title' => 'Should Not Exist',
                'mda_id' => $health->id,
                'sector' => 'Health',
                'lga' => 'Akure South',
            ])
            ->assertForbidden();
    }

    /**
     * @return array{0: Mda, 1: Mda}
     */
    private function ministries(): array
    {
        return [
            Mda::create(['name' => 'Ministry of Health', 'code' => 'MOHREG', 'status' => true]),
            Mda::create(['name' => 'Ministry of Works', 'code' => 'MOWREG', 'status' => true]),
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

    private function project(Mda $mda, string $title, array $extra = []): Project
    {
        return Project::create(array_merge([
            'mda_id' => $mda->id,
            'project_title' => $title,
            'sector' => 'Health',
            'lga' => 'Akure South',
            'status' => 'Planning',
            'progress' => 0,
        ], $extra));
    }
}
