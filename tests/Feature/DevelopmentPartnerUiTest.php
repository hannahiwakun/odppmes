<?php

namespace Tests\Feature;

use App\Models\DevelopmentPartner;
use App\Models\Mda;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevelopmentPartnerUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_page_uses_the_save_label_and_keeps_validation_input(): void
    {
        $admin = $this->user('Super Administrator');

        $page = $this->actingAs($admin)->get(route('development-partners.create'));

        $page->assertOk()
            ->assertSee('Save Development Partner')
            ->assertDontSee('SAVE BUTTON')
            ->assertSee('Partner Identity')
            ->assertSee('Contact Information')
            ->assertSee('Address')
            ->assertSee('for="partner-name"', false)
            ->assertSee('id="partner-name"', false)
            ->assertSee('name="partner_name"', false)
            ->assertSee('focus:ring-green-700', false);

        $this->assertDoesNotMatchRegularExpression('/border-red-500/', $page->getContent());

        $this->actingAs($admin)
            ->from(route('development-partners.create'))
            ->post(route('development-partners.store'), [
                'partner_name' => 'Kept Name',
                'acronym' => 'KN',
                'partner_type' => 'Bilateral',
                'contact_person' => 'Ada Okafor',
                'email' => 'not-an-email',
                'phone' => '08030000000',
                'website' => 'https://kept.example',
                'country' => 'Ghana',
                'address' => 'Akure office',
                'status' => '0',
            ])
            ->assertRedirect(route('development-partners.create'))
            ->assertSessionHasErrors('email');

        $this->actingAs($admin)
            ->get(route('development-partners.create'))
            ->assertOk()
            ->assertSee('value="Kept Name"', false)
            ->assertSee('value="Bilateral" selected', false)
            ->assertSee('value="0" selected', false)
            ->assertSee('value="Ghana"', false)
            ->assertSee('Akure office');
    }

    public function test_listing_and_details_pages_render_partner_information(): void
    {
        $admin = $this->user('Super Administrator');
        $mda = Mda::create([
            'name' => 'Ministry of Health',
            'code' => 'MOHUI',
            'status' => true,
        ]);

        $linked = DevelopmentPartner::create([
            'partner_name' => 'Safe Website Partner',
            'acronym' => 'SWP',
            'partner_type' => 'UN Agency',
            'contact_person' => 'Chioma Ade',
            'email' => 'office@example.org',
            'phone' => '08031111111',
            'website' => 'https://partners.example',
            'country' => 'Nigeria',
            'address' => 'Alagbaka, Akure',
            'status' => true,
        ]);

        Project::create([
            'mda_id' => $mda->id,
            'development_partner_id' => $linked->id,
            'project_title' => 'Clinic Support',
            'sector' => 'Health',
            'lga' => 'Akure South',
            'status' => 'Planning',
            'progress' => 0,
        ]);

        $plain = DevelopmentPartner::create([
            'partner_name' => 'Plain Partner',
            'partner_type' => 'NGO',
            'country' => '',
            'website' => 'javascript:alert(1)',
            'status' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('development-partners.index'))
            ->assertOk()
            ->assertSee('Development Partners')
            ->assertSee('Organisations working with Ondo State on development projects.')
            ->assertSee('Safe Website Partner')
            ->assertSee('SWP')
            ->assertSee('Plain Partner')
            ->assertSee('Active')
            ->assertSee('Inactive')
            ->assertSee('overflow-x-auto', false)
            ->assertSee('bg-green-100', false)
            ->assertSee('bg-gray-100', false)
            ->assertSee("confirm('Delete this Development Partner?')", false);

        $details = $this->actingAs($admin)
            ->get(route('development-partners.show', $linked));

        $details->assertOk()
            ->assertSee('Safe Website Partner')
            ->assertSee('SWP')
            ->assertSee('UN Agency')
            ->assertSee('Partner Identity')
            ->assertSee('Contact Information')
            ->assertSee('Chioma Ade')
            ->assertSee('Alagbaka, Akure')
            ->assertSee('href="https://partners.example"', false)
            ->assertSee('Linked projects: <span class="font-semibold text-gray-900">1</span>', false)
            ->assertSee(route('development-partners.edit', $linked), false)
            ->assertSee(route('development-partners.index'), false);

        $unsafe = $this->actingAs($admin)
            ->get(route('development-partners.show', $plain));

        $unsafe->assertOk()
            ->assertSee('Plain Partner')
            ->assertSee('javascript:alert(1)')
            ->assertDontSee('href="javascript:', false);
    }

    public function test_only_a_super_administrator_can_open_the_module(): void
    {
        $partner = DevelopmentPartner::create([
            'partner_name' => 'Restricted Partner',
            'partner_type' => 'Government',
            'status' => true,
        ]);

        foreach (['M&E Officer', 'MDA Officer', 'Development Partner', 'Viewer'] as $role) {
            $actor = $this->user($role);

            $this->actingAs($actor)->get(route('development-partners.index'))->assertForbidden();
            $this->actingAs($actor)->get(route('development-partners.create'))->assertForbidden();
            $this->actingAs($actor)->get(route('development-partners.show', $partner))->assertForbidden();
            $this->actingAs($actor)->get(route('development-partners.edit', $partner))->assertForbidden();
        }

        $this->actingAs($this->user('Super Administrator'))
            ->get(route('development-partners.index'))
            ->assertOk()
            ->assertSee('Restricted Partner');
    }

    public function test_a_partner_with_linked_projects_cannot_be_deleted(): void
    {
        $admin = $this->user('Super Administrator');
        $mda = Mda::create([
            'name' => 'Ministry of Works',
            'code' => 'MOWUI',
            'status' => true,
        ]);
        $partner = DevelopmentPartner::create([
            'partner_name' => 'Kept Partner',
            'partner_type' => 'Multilateral',
            'status' => true,
        ]);
        $project = Project::create([
            'mda_id' => $mda->id,
            'development_partner_id' => $partner->id,
            'project_title' => 'Road Support',
            'sector' => 'Infrastructure',
            'lga' => 'Ondo West',
            'status' => 'Planning',
            'progress' => 0,
        ]);

        $this->actingAs($admin)
            ->delete(route('development-partners.destroy', $partner))
            ->assertRedirect(route('development-partners.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('development_partners', ['id' => $partner->id]);
        $this->assertSame($partner->id, $project->fresh()->development_partner_id);
    }

    public function test_a_super_administrator_can_register_a_partner(): void
    {
        $admin = $this->user('Super Administrator');

        $this->actingAs($admin)
            ->post(route('development-partners.store'), [
                'partner_name' => 'New Clinic Partner',
                'acronym' => 'NCP',
                'partner_type' => 'NGO',
                'contact_person' => 'Tunde Bello',
                'email' => 'tunde@example.org',
                'phone' => '08032222222',
                'website' => 'https://clinic.example',
                'country' => 'Nigeria',
                'address' => 'Ondo Town',
                'status' => '1',
            ])
            ->assertRedirect(route('development-partners.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('development_partners', [
            'partner_name' => 'New Clinic Partner',
            'partner_type' => 'NGO',
            'email' => 'tunde@example.org',
        ]);

        $this->actingAs($admin)
            ->get(route('development-partners.index'))
            ->assertOk()
            ->assertSee('New Clinic Partner')
            ->assertSee('Development Partner created successfully.');
    }

    private function user(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
        ]);
    }
}
