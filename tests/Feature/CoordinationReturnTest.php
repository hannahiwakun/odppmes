<?php

namespace Tests\Feature;

use App\Models\Mda;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoordinationReturnTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_update_stores_the_register_fields_and_keeps_the_original_record(): void
    {
        $admin = User::factory()->create(['role' => 'Super Administrator']);
        $lead = Mda::create(['name' => 'Ministry of Health', 'code' => 'MOH', 'status' => true]);
        $other = Mda::create(['name' => 'Ministry of Agriculture', 'code' => 'MOA', 'status' => true]);

        $project = Project::create([
            'mda_id' => $lead->id,
            'project_title' => 'Rural Health Programme',
            'project_code' => 'RHP-1',
            'sector' => 'Health',
            'lga' => 'Akure South',
            'ward' => 'Ward 2',
            'budget' => 1000,
            'status' => 'Planning',
            'progress' => 10,
            'description' => 'Original description',
        ]);

        $this->actingAs($admin)
            ->put(route('projects.update', $project), [
                'coordination_form' => 1,
                'project_title' => 'Rural Health Programme',
                'project_code' => 'RHP-1',
                'mda_id' => $lead->id,
                'sector' => 'Health',
                'lga' => 'Akure South',
                'ward' => 'Ward 2',
                'budget' => 1000,
                'initiative_status' => 'Under negotiation',
                'progress' => 10,
                'description' => 'Original description',
                'approved_value' => 10000000000,
                'funds_drawn' => 2000000000,
                'counterpart_required' => 500000000,
                'counterpart_paid' => 100000000,
                'funding_currency' => 'NGN',
                'direct_beneficiaries' => '5000 households',
                'beneficiaries_are_estimates' => 1,
                'programme_officer' => 'Ada Okafor',
                'verification_status' => 'Reported',
                'participating_mda_ids' => [$other->id, $lead->id],
            ])
            ->assertRedirect(route('projects.index'));

        $project->refresh();

        $this->assertSame('Rural Health Programme', $project->project_title);
        $this->assertSame('Ward 2', $project->ward);
        $this->assertSame('Original description', $project->description);
        $this->assertSame('Under negotiation', $project->initiative_status);
        $this->assertSame('Planning', $project->status);
        $this->assertEquals(10000000000, (float) $project->approved_value);
        $this->assertEquals(2000000000, (float) $project->funds_drawn);
        $this->assertEquals(400000000, $project->outstandingCounterpart());
        $this->assertTrue($project->beneficiaries_are_estimates);
        $this->assertEquals([$other->id], $project->participatingMdas()->pluck('mdas.id')->all());

        $this->actingAs($admin)
            ->put(route('projects.update', $project), [
                'project_title' => 'Rural Health Programme',
                'mda_id' => $lead->id,
                'sector' => 'Health',
                'lga' => 'Akure South',
                'status' => 'Planning',
                'progress' => 10,
            ])
            ->assertRedirect(route('projects.index'));

        $this->assertEquals(10000000000, (float) $project->fresh()->approved_value);
        $this->assertEquals([$other->id], $project->fresh()->participatingMdas()->pluck('mdas.id')->all());
    }

    public function test_mda_can_store_a_focal_person(): void
    {
        $admin = User::factory()->create(['role' => 'Super Administrator']);
        $mda = Mda::create(['name' => 'Ministry of Works', 'code' => 'MOW', 'status' => true]);

        $this->actingAs($admin)
            ->put(route('mdas.update', $mda), [
                'name' => 'Ministry of Works',
                'code' => 'MOW',
                'email' => 'works@example.com',
                'phone' => '08031112222',
                'contact_person' => 'Records Officer',
                'address' => 'Akure',
                'status' => 1,
                'focal_name' => 'Bola Ade',
                'focal_department' => 'Planning',
                'focal_designation' => 'Director',
                'focal_supervisor' => 'Chief Bola',
                'focal_phone' => '08032223333',
                'focal_email' => 'bola@example.com',
                'alternate_name' => 'Tunde Ali',
            ])
            ->assertRedirect(route('mdas.index'));

        $mda->refresh();
        $this->assertSame('Bola Ade', $mda->focal_name);
        $this->assertSame('bola@example.com', $mda->focal_email);
        $this->assertSame('Tunde Ali', $mda->alternate_name);
        $this->assertSame('Ministry of Works', $mda->name);
    }

    public function test_mda_details_page_shows_department_supervisor_and_alternate_contact(): void
    {
        $admin = User::factory()->create(['role' => 'Super Administrator']);
        $mda = Mda::create([
            'name' => 'Ministry of Health',
            'code' => 'MOH',
            'status' => true,
            'focal_department' => 'Planning Research and Statistics',
            'focal_supervisor' => 'Director Adeyemi',
            'alternate_phone' => '08031234567',
            'alternate_email' => 'alternate@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('mdas.show', $mda))
            ->assertOk()
            ->assertSeeInOrder([
                'Department',
                'Planning Research and Statistics',
                'Supervising director',
                'Director Adeyemi',
                'Alternate contact',
                '08031234567',
                'alternate@example.com',
            ]);

        $blank = Mda::create([
            'name' => 'Ministry of Justice',
            'code' => 'MOJ',
            'status' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('mdas.show', $blank))
            ->assertOk()
            ->assertSeeInOrder([
                'Department',
                '-',
                'Supervising director',
                '-',
                'Alternate contact',
                '-',
            ])
            ->assertDontSee('Planning Research and Statistics')
            ->assertDontSee('Director Adeyemi')
            ->assertDontSee('alternate@example.com');
    }

    public function test_super_administrator_can_open_the_circular_and_form(): void
    {
        $admin = User::factory()->create(['role' => 'Super Administrator']);

        $this->actingAs($admin)->get(route('office.circular'))->assertOk()->assertSee('development coordination and facilitation');
        $this->actingAs($admin)->get(route('office.mda-return'))->assertOk()->assertSee('NIL RETURN');
        $this->actingAs($admin)->get(route('projects.create'))->assertOk()->assertSee('Development register')->assertSee('Total approved value');
        $this->actingAs($admin)->get(route('mdas.create'))->assertOk()->assertSee('Focal person');

        $this->actingAs(User::factory()->create(['role' => 'Viewer']))
            ->get(route('office.circular'))
            ->assertForbidden();
    }

    public function test_a_blank_approved_value_is_stored_as_null(): void
    {
        $admin = User::factory()->create(['role' => 'Super Administrator']);
        $mda = Mda::create(['name' => 'Ministry of Health', 'code' => 'MOH', 'status' => true]);

        $project = Project::create([
            'mda_id' => $mda->id,
            'project_title' => 'Rural Health Programme',
            'sector' => 'Health',
            'lga' => 'Akure South',
            'status' => 'Planning',
            'progress' => 10,
            'approved_value' => 5000,
        ]);

        $this->actingAs($admin)
            ->put(route('projects.update', $project), [
                'coordination_form' => 1,
                'project_title' => 'Rural Health Programme',
                'mda_id' => $mda->id,
                'sector' => 'Health',
                'lga' => 'Akure South',
                'initiative_status' => 'Planning',
                'progress' => 10,
                'approved_value' => '',
            ])
            ->assertRedirect(route('projects.index'));

        $this->assertNull($project->fresh()->approved_value);
    }

    public function test_delayed_initiative_maps_to_ongoing_status(): void
    {
        $admin = User::factory()->create(['role' => 'Super Administrator']);
        $mda = Mda::create(['name' => 'Ministry of Works', 'code' => 'MOW', 'status' => true]);

        $project = Project::create([
            'mda_id' => $mda->id,
            'project_title' => 'Road Programme',
            'sector' => 'Infrastructure',
            'lga' => 'Ondo West',
            'status' => 'Planning',
            'progress' => 5,
        ]);

        $this->actingAs($admin)
            ->put(route('projects.update', $project), [
                'coordination_form' => 1,
                'project_title' => 'Road Programme',
                'mda_id' => $mda->id,
                'sector' => 'Infrastructure',
                'lga' => 'Ondo West',
                'initiative_status' => 'Delayed',
                'progress' => 5,
            ])
            ->assertRedirect(route('projects.index'));

        $project->refresh();

        $this->assertSame('Delayed', $project->initiative_status);
        $this->assertSame('Ongoing', $project->status);
    }
}
