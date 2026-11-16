<?php

namespace Tests\Feature;

use App\Models\DevelopmentPartner;
use App\Models\Mda;
use App\Models\Monitoring;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevelopmentPartnerAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_assigned_partner_sees_only_that_organisations_dashboard_figures(): void
    {
        [$mda, $alpha, $beta] = $this->organisations();
        $own = $this->project($mda, $alpha, 'Alpha Clinic', 2500.50);
        $other = $this->project($mda, $beta, 'Beta Road', 8800.25);
        $partner = $this->partnerUser($alpha, 'Alpha Account');
        $alphaOfficer = User::factory()->create([
            'role' => 'M&E Officer',
            'name' => 'Alpha Officer',
        ]);
        $otherOfficer = User::factory()->create([
            'role' => 'M&E Officer',
            'name' => 'Other Officer',
        ]);
        $this->visit($own, $alphaOfficer, 55);
        $this->visit($other, $otherOfficer, 15);

        $dashboard = $this->actingAs($partner)->get(route('dashboard'));

        $dashboard->assertOk();
        $dashboard->assertSee('Alpha Clinic');
        $dashboard->assertSee('2,500.50');
        $dashboard->assertSee('Alpha Officer');
        $dashboard->assertDontSee('Beta Road');
        $dashboard->assertDontSee('8,800.25');
        $dashboard->assertDontSee('Other Officer');
        $dashboard->assertDontSee('MDAs (statewide)');
        $dashboard->assertDontSee('Development Partners (statewide)');
    }

    public function test_an_unassigned_partner_sees_the_configuration_notice_and_no_project_figures(): void
    {
        [$mda, $alpha, $beta] = $this->organisations();
        $this->project($mda, $alpha, 'Alpha Clinic', 2500.50);
        $this->project($mda, $beta, 'Beta Road', 8800.25);
        $partner = User::factory()->create([
            'role' => 'Development Partner',
            'development_partner_id' => null,
        ]);

        $dashboard = $this->actingAs($partner)->get(route('dashboard'));

        $dashboard->assertOk();
        $dashboard->assertSee('Your development partner access has not been configured. A Super Administrator must assign your organisation before project information is available.');
        $dashboard->assertDontSee('Alpha Clinic');
        $dashboard->assertDontSee('Beta Road');
        $dashboard->assertDontSee('2,500.50');
        $dashboard->assertDontSee('8,800.25');
        $dashboard->assertDontSee('Total Projects');
        $dashboard->assertDontSee('MDAs (statewide)');
        $dashboard->assertDontSee('Development Partners (statewide)');
    }

    public function test_direct_project_and_evidence_addresses_stay_forbidden(): void
    {
        [$mda, $alpha, $beta] = $this->organisations();
        $admin = User::factory()->create(['role' => 'Super Administrator']);
        $own = $this->project($mda, $alpha, 'Alpha Clinic', 2500.50);
        $other = $this->project($mda, $beta, 'Beta Road', 8800.25);
        $partner = $this->partnerUser($alpha, 'Alpha Account');
        $photo = ProjectPhoto::create([
            'project_id' => $other->id,
            'photo' => 'project_photos/beta.jpg',
            'uploaded_by' => $admin->id,
        ]);
        $document = ProjectDocument::create([
            'project_id' => $other->id,
            'title' => 'Beta Plan',
            'document_type' => 'Report',
            'file_name' => 'beta.pdf',
            'file_path' => 'project_documents/beta.pdf',
            'uploaded_by' => $admin->id,
        ]);
        $visit = $this->visit($other, $admin, 15);

        $this->actingAs($partner)->get(route('projects.show', $own))->assertForbidden();
        $this->actingAs($partner)->get(route('projects.show', $other))->assertForbidden();
        $this->actingAs($partner)->get(route('projects.photos.file', [$other, $photo]))->assertForbidden();
        $this->actingAs($partner)->get(route('project-documents.download', [$other, $document]))->assertForbidden();
        $this->actingAs($partner)->get(route('monitorings.show', $visit))->assertForbidden();
        $this->actingAs($partner)->get(route('monitorings.photo', $visit))->assertForbidden();
        $this->actingAs($partner)->get(route('reports.index'))->assertForbidden();
        $this->actingAs($partner)->get(route('reports.pdf'))->assertForbidden();
        $this->actingAs($partner)->get(route('project.map'))->assertForbidden();
    }

    public function test_a_development_partner_role_requires_an_existing_organisation(): void
    {
        $admin = User::factory()->create(['role' => 'Super Administrator']);
        [$mda, $alpha] = $this->organisations();
        unset($mda);

        $this->actingAs($admin)
            ->from(route('users.create'))
            ->post(route('users.store'), [
                'name' => 'Missing Organisation',
                'email' => 'missing-partner@example.com',
                'role' => 'Development Partner',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('users.create'))
            ->assertSessionHasErrors('development_partner_id');

        $this->assertDatabaseMissing('users', ['email' => 'missing-partner@example.com']);

        $this->actingAs($admin)
            ->from(route('users.create'))
            ->post(route('users.store'), [
                'name' => 'Unknown Organisation',
                'email' => 'unknown-partner@example.com',
                'role' => 'Development Partner',
                'development_partner_id' => 999999,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('users.create'))
            ->assertSessionHasErrors('development_partner_id');

        $this->assertDatabaseMissing('users', ['email' => 'unknown-partner@example.com']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Alpha Account',
                'email' => 'alpha-account@example.com',
                'role' => 'Development Partner',
                'development_partner_id' => $alpha->id,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $created = User::where('email', 'alpha-account@example.com')->first();
        $this->assertNotNull($created);
        $this->assertSame($alpha->id, $created->development_partner_id);
        $this->assertNull($created->mda_id);

        $this->actingAs($admin)->get(route('users.index'))->assertSee('Alpha Health Partner');
        $this->actingAs($admin)->get(route('users.show', $created))->assertSee('Alpha Health Partner')->assertSee('None');
    }

    public function test_changing_away_from_the_development_partner_role_clears_the_organisation(): void
    {
        $admin = User::factory()->create(['role' => 'Super Administrator']);
        [, $alpha] = $this->organisations();
        $partner = $this->partnerUser($alpha, 'Alpha Account');

        $this->actingAs($admin)
            ->put(route('users.update', $partner), [
                'name' => 'Alpha Account',
                'email' => $partner->email,
                'role' => 'Viewer',
                'development_partner_id' => $alpha->id,
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $partner->refresh();
        $this->assertSame('Viewer', $partner->role);
        $this->assertNull($partner->development_partner_id);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'Updated',
            'module' => 'User',
        ]);
    }

    public function test_a_super_administrator_still_sees_statewide_dashboard_figures(): void
    {
        [$mda, $alpha, $beta] = $this->organisations();
        $this->project($mda, $alpha, 'Alpha Clinic', 2500.50);
        $this->project($mda, $beta, 'Beta Road', 8800.25);
        $admin = User::factory()->create(['role' => 'Super Administrator']);

        $dashboard = $this->actingAs($admin)->get(route('dashboard'));

        $dashboard->assertOk();
        $dashboard->assertSee('11,300.75');
        $dashboard->assertSee('Alpha Health Partner');
        $dashboard->assertSee('Beta Works Partner');
        $dashboard->assertSee('MDAs (statewide)');
        $dashboard->assertSee('Development Partners (statewide)');
        $dashboard->assertDontSee('Your development partner access has not been configured.');
    }

    /**
     * @return array{0: Mda, 1: DevelopmentPartner, 2: DevelopmentPartner}
     */
    private function organisations(): array
    {
        $mda = Mda::create([
            'name' => 'Ministry of Health',
            'code' => 'MOH',
            'status' => true,
        ]);
        $alpha = DevelopmentPartner::create([
            'partner_name' => 'Alpha Health Partner',
            'partner_type' => 'Multilateral',
            'status' => true,
        ]);
        $beta = DevelopmentPartner::create([
            'partner_name' => 'Beta Works Partner',
            'partner_type' => 'Bilateral',
            'status' => true,
        ]);

        return [$mda, $alpha, $beta];
    }

    private function partnerUser(DevelopmentPartner $partner, string $name): User
    {
        return User::factory()->create([
            'role' => 'Development Partner',
            'name' => $name,
            'development_partner_id' => $partner->id,
        ]);
    }

    private function project(Mda $mda, DevelopmentPartner $partner, string $title, float $budget): Project
    {
        return Project::create([
            'mda_id' => $mda->id,
            'development_partner_id' => $partner->id,
            'project_title' => $title,
            'sector' => 'Health',
            'lga' => 'Akure South',
            'status' => 'Ongoing',
            'progress' => 40,
            'budget' => $budget,
        ]);
    }

    private function visit(Project $project, User $officer, int $progress): Monitoring
    {
        return Monitoring::create([
            'project_id' => $project->id,
            'user_id' => $officer->id,
            'monitoring_date' => '2026-10-17',
            'status' => 'Ongoing',
            'progress_before' => 0,
            'progress_after' => $progress,
        ]);
    }
}
