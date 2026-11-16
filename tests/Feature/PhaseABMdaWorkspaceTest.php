<?php

namespace Tests\Feature;

use App\Models\DevelopmentPartner;
use App\Models\Mda;
use App\Models\Monitoring;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseABMdaWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_administrator_manages_mdas_and_sees_portfolio_sections(): void
    {
        $admin = $this->user('Super Administrator');
        [$health, $works] = $this->ministries();
        $officer = $this->officer($health, 'Ada Health');
        $project = $this->project($health, 'Health Clinic', [
            'status' => 'Ongoing',
            'progress' => 40,
            'initiative_status' => 'Delayed',
        ]);
        Monitoring::create([
            'project_id' => $project->id,
            'user_id' => $officer->id,
            'monitoring_date' => '2026-10-17',
            'status' => 'Ongoing',
            'progress_before' => 10,
            'progress_after' => 40,
        ]);

        $this->actingAs($admin)
            ->get(route('mdas.index'))
            ->assertOk()
            ->assertSee('Add New MDA')
            ->assertSee('Ministry of Health')
            ->assertSee('Edit');

        $this->actingAs($admin)
            ->get(route('mdas.show', $health))
            ->assertOk()
            ->assertSee('Ada Health')
            ->assertSee('Health Clinic')
            ->assertSee('Delayed')
            ->assertSee('Assigned MDA officers')
            ->assertSee('Project portfolio')
            ->assertSee('Recent monitoring');

        $this->actingAs($admin)
            ->get(route('mdas.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->put(route('mdas.update', $works), [
                'name' => 'Ministry of Works Updated',
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

        $this->assertSame('Ministry of Works Updated', $works->fresh()->name);
    }

    public function test_me_officer_can_read_mda_profiles_but_cannot_change_them(): void
    {
        $meo = $this->user('M&E Officer');
        [$health] = $this->ministries();

        $this->actingAs($meo)
            ->get(route('mdas.index'))
            ->assertOk()
            ->assertSee('Ministry of Health')
            ->assertDontSee('Add New MDA');

        $this->actingAs($meo)
            ->get(route('mdas.show', $health))
            ->assertOk()
            ->assertDontSee('>Edit</a>', false);

        $this->actingAs($meo)->get(route('mdas.create'))->assertForbidden();
        $this->actingAs($meo)->get(route('mdas.edit', $health))->assertForbidden();
        $this->actingAs($meo)
            ->post(route('mdas.store'), [
                'name' => 'Should Not Exist',
                'code' => 'NOPE',
                'status' => 1,
            ])
            ->assertForbidden();
        $this->actingAs($meo)
            ->put(route('mdas.update', $health), [
                'name' => 'Hacked Health',
                'code' => 'MOH',
                'status' => 1,
            ])
            ->assertForbidden();
        $this->actingAs($meo)->patch(route('mdas.deactivate', $health))->assertForbidden();
        $this->actingAs($meo)->delete(route('mdas.destroy', $health))->assertForbidden();
        $this->actingAs($meo)->get(route('users.index'))->assertForbidden();

        $this->assertSame('Ministry of Health', $health->fresh()->name);
        $this->assertTrue((bool) $health->fresh()->status);
    }

    public function test_mda_officer_workspace_is_limited_to_the_assigned_ministry(): void
    {
        [$health, $works] = $this->ministries();
        $officer = $this->officer($health);
        $this->project($health, 'Health Clinic', [
            'status' => 'Ongoing',
            'progress' => 55,
            'budget' => 1234.56,
            'initiative_status' => 'Delayed',
        ]);
        $this->project($works, 'Rural Road', [
            'status' => 'Completed',
            'progress' => 100,
            'budget' => 987654.32,
        ]);

        $dashboard = $this->actingAs($officer)->get(route('dashboard'));
        $dashboard->assertOk();
        $dashboard->assertSee('My MDA Dashboard');
        $dashboard->assertSee('1,234.56');
        $dashboard->assertDontSee('987,654.32');
        $dashboard->assertDontSee('MDAs (statewide)');
        $dashboard->assertDontSee('Development Partners (statewide)');
        $dashboard->assertDontSee('Rural Road');

        $this->actingAs($officer)
            ->get(route('mdas.mine'))
            ->assertOk()
            ->assertSee('My MDA Profile')
            ->assertSee('Ministry of Health')
            ->assertSee('Health Clinic')
            ->assertDontSee('Ministry of Works')
            ->assertDontSee('Rural Road');

        $this->actingAs($officer)->get(route('mdas.index'))->assertForbidden();
        $this->actingAs($officer)->get(route('mdas.show', $health))->assertForbidden();
        $this->actingAs($officer)->get(route('mdas.show', $works))->assertForbidden();
        $this->actingAs($officer)->get(route('projects.show', Project::where('project_title', 'Rural Road')->first()))->assertNotFound();
    }

    public function test_mda_officer_cannot_change_mda_id_or_mark_a_project_verified(): void
    {
        [$health, $works] = $this->ministries();
        $officer = $this->officer($health);
        $project = $this->project($health, 'Health Clinic', [
            'status' => 'Ongoing',
            'progress' => 20,
            'verification_status' => 'Reported',
        ]);

        $payload = [
            'project_title' => 'Health Clinic',
            'mda_id' => $works->id,
            'sector' => 'Health',
            'lga' => 'Akure South',
            'status' => 'Ongoing',
            'progress' => 25,
            'verification_status' => 'Verified',
        ];

        $this->actingAs($officer)
            ->put(route('projects.update', $project), $payload)
            ->assertRedirect(route('projects.index'));

        $project->refresh();
        $this->assertSame($health->id, $project->mda_id);
        $this->assertSame('Reported', $project->verification_status);

        $this->actingAs($officer)
            ->patch(route('projects.update', $project), $payload)
            ->assertRedirect(route('projects.index'));

        $this->assertSame($health->id, $project->fresh()->mda_id);
        $this->assertSame('Reported', $project->fresh()->verification_status);

        $this->actingAs($officer)
            ->post(route('projects.store'), [
                'project_title' => 'Forged Clinic',
                'mda_id' => $works->id,
                'sector' => 'Health',
                'lga' => 'Akure South',
                'status' => 'Planning',
                'progress' => 0,
                'verification_status' => 'Verified',
            ])
            ->assertRedirect(route('projects.index'));

        $created = Project::query()->where('project_title', 'Forged Clinic')->firstOrFail();
        $this->assertSame($health->id, $created->mda_id);
        $this->assertNull($created->verification_status);

        $this->actingAs($officer)
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), [
                'name' => $officer->name,
                'email' => $officer->email,
                'mda_id' => $works->id,
                'role' => 'Super Administrator',
            ])
            ->assertRedirect(route('profile.edit'));

        $officer->refresh();
        $this->assertSame($health->id, $officer->mda_id);
        $this->assertSame('MDA Officer', $officer->role);
    }

    public function test_authorised_officers_can_set_verification_status(): void
    {
        [$health] = $this->ministries();
        $project = $this->project($health, 'Health Clinic', [
            'status' => 'Ongoing',
            'progress' => 20,
            'verification_status' => 'Reported',
        ]);

        $payload = [
            'project_title' => 'Health Clinic',
            'mda_id' => $health->id,
            'sector' => 'Health',
            'lga' => 'Akure South',
            'status' => 'Ongoing',
            'progress' => 20,
            'verification_status' => 'Verified',
            'coordination_form' => '1',
        ];

        $this->actingAs($this->user('M&E Officer'))
            ->put(route('projects.update', $project), $payload)
            ->assertRedirect(route('projects.index'));

        $this->assertSame('Verified', $project->fresh()->verification_status);

        $this->actingAs($this->user('Super Administrator'))
            ->put(route('projects.update', $project), array_merge($payload, [
                'verification_status' => 'Awaiting clarification',
            ]))
            ->assertRedirect(route('projects.index'));

        $this->assertSame('Awaiting clarification', $project->fresh()->verification_status);
    }

    public function test_viewer_and_partner_permissions_are_unchanged(): void
    {
        [$health] = $this->ministries();
        $partnerOrg = DevelopmentPartner::create([
            'partner_name' => 'Alpha Partner',
            'partner_type' => 'Bilateral',
            'status' => true,
        ]);
        $otherOrg = DevelopmentPartner::create([
            'partner_name' => 'Beta Partner',
            'partner_type' => 'Multilateral',
            'status' => true,
        ]);
        $own = $this->project($health, 'Alpha Clinic', [
            'development_partner_id' => $partnerOrg->id,
            'budget' => 100,
        ]);
        $this->project($health, 'Beta Clinic', [
            'development_partner_id' => $otherOrg->id,
            'budget' => 900,
        ]);

        $viewer = $this->user('Viewer');
        $this->actingAs($viewer)->get(route('mdas.index'))->assertForbidden();
        $this->actingAs($viewer)->get(route('projects.index'))->assertForbidden();
        $this->actingAs($viewer)->get(route('reports.index'))->assertOk();
        $this->actingAs($viewer)->get(route('project.map'))->assertOk();
        $this->actingAs($viewer)->put(route('projects.update', $own), [
            'project_title' => 'Hacked',
            'mda_id' => $health->id,
            'sector' => 'Health',
            'lga' => 'Akure South',
            'status' => 'Ongoing',
            'progress' => 99,
        ])->assertForbidden();

        $partner = User::factory()->create([
            'role' => 'Development Partner',
            'development_partner_id' => $partnerOrg->id,
        ]);
        $dashboard = $this->actingAs($partner)->get(route('dashboard'));
        $dashboard->assertOk();
        $dashboard->assertSee('Total Projects', false);
        $dashboard->assertSee('>1</h2>', false);
        $dashboard->assertSee('Alpha Partner', false);
        $dashboard->assertDontSee('Beta Partner', false);
        $dashboard->assertDontSee('Beta Clinic', false);
        $dashboard->assertDontSee('MDAs (statewide)', false);
        $this->actingAs($partner)->get(route('mdas.index'))->assertForbidden();
        $this->actingAs($partner)->get(route('projects.show', $own))->assertForbidden();
        $this->actingAs($partner)->get(route('reports.index'))->assertForbidden();
    }

    public function test_the_public_homepage_and_login_remain_available(): void
    {
        $this->get('/')->assertOk()->assertSee('ODPPMES', false);
        $this->get(route('login'))->assertOk();
        $this->get(route('dashboard'))->assertRedirect();
    }

    /**
     * @return array{0: Mda, 1: Mda}
     */
    private function ministries(): array
    {
        return [
            Mda::create(['name' => 'Ministry of Health', 'code' => 'MOH', 'status' => true]),
            Mda::create(['name' => 'Ministry of Works', 'code' => 'MOW', 'status' => true]),
        ];
    }

    private function officer(Mda $mda, string $name = 'MDA Officer'): User
    {
        return User::factory()->create([
            'name' => $name,
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
