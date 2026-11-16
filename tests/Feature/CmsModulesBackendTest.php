<?php

namespace Tests\Feature;

use App\Models\CmsHomepageBanner;
use App\Models\CmsManagementProfile;
use App\Models\CmsOfficeContact;
use App\Models\CmsProgrammeHighlight;
use App\Models\CmsPublicPartner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsModulesBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_management_profiles_partners_highlights_and_banners_can_be_created_as_drafts(): void
    {
        $admin = $this->websiteAdmin();

        $this->actingAs($admin)->post(route('cms.management-profiles.store'), [
            'full_name' => 'Dr Example',
            'designation' => 'Director',
            'biography' => str_repeat('Biography text. ', 5),
            'status' => 'published',
        ])->assertRedirect();
        $this->assertSame('draft', CmsManagementProfile::query()->first()->status);

        $this->actingAs($admin)->post(route('cms.public-partners.store'), [
            'name' => 'Example Fund',
            'category' => 'Bilateral',
            'summary' => 'A public summary of the partner organisation.',
        ])->assertRedirect();
        $this->assertDatabaseHas('cms_public_partners', ['name' => 'Example Fund', 'status' => 'draft']);
        $this->assertDatabaseMissing('development_partners', ['partner_name' => 'Example Fund']);

        $this->actingAs($admin)->post(route('cms.programme-highlights.store'), [
            'title' => 'Rural clinics',
            'implementing_mda_name' => 'Ministry of Health',
            'partner_name' => 'Example Fund',
            'sector' => 'Health',
            'location' => 'Akure',
            'public_status' => 'Ongoing',
            'summary' => 'A public highlight with no project identifier.',
            'project_id' => 999,
        ])->assertRedirect();
        $highlight = CmsProgrammeHighlight::query()->first();
        $this->assertSame('draft', $highlight->status);
        $this->assertFalse(isset($highlight->project_id));

        $this->actingAs($admin)->post(route('cms.homepage-banners.store'), [
            'title' => 'Welcome notice',
            'banner_type' => CmsHomepageBanner::TYPE_HERO_NOTICE,
            'body' => 'Homepage announcement',
        ])->assertRedirect();
        $this->assertSame('draft', CmsHomepageBanner::query()->first()->status);
    }

    public function test_office_contact_is_a_singleton_and_follows_workflow_permissions(): void
    {
        $admin = $this->websiteAdmin();
        $super = $this->user('Super Administrator');

        $payload = [
            'address' => 'DPMO, Akure',
            'phone' => '08000000000',
            'email' => 'office@example.test',
            'hours' => 'Monday to Friday',
        ];

        $this->actingAs($admin)
            ->post(route('cms.office-contact.store'), $payload)
            ->assertRedirect(route('cms.office-contact.show'));

        $this->actingAs($admin)
            ->post(route('cms.office-contact.store'), $payload)
            ->assertRedirect(route('cms.office-contact.show'))
            ->assertSessionHas('error');

        $this->assertSame(1, CmsOfficeContact::query()->count());

        $contact = CmsOfficeContact::query()->first();
        $this->actingAs($admin)
            ->post(route('cms.workflow.publish', ['type' => 'office-contact', 'id' => $contact->id]))
            ->assertForbidden();

        $this->actingAs($super)
            ->from(route('cms.office-contact.show'))
            ->post(route('cms.workflow.submit', ['type' => 'office-contact', 'id' => $contact->id]))
            ->assertRedirect();
        $this->actingAs($super)
            ->post(route('cms.workflow.approve', ['type' => 'office-contact', 'id' => $contact->id]))
            ->assertRedirect();
        $this->actingAs($super)
            ->post(route('cms.workflow.publish', ['type' => 'office-contact', 'id' => $contact->id]))
            ->assertRedirect();

        $this->assertSame('published', $contact->fresh()->status);
    }

    public function test_website_administrator_cannot_edit_another_users_draft_profile(): void
    {
        $owner = $this->websiteAdmin();
        $other = $this->websiteAdmin();
        $profile = CmsManagementProfile::create([
            'slug' => 'director-one',
            'full_name' => 'Director One',
            'designation' => 'Director',
            'biography' => str_repeat('Biography text. ', 5),
        ]);
        $profile->created_by = $owner->id;
        $profile->save();

        $this->actingAs($other)
            ->get(route('cms.management-profiles.edit', $profile))
            ->assertForbidden();
    }

    public function test_operational_roles_remain_isolated_from_cms_modules(): void
    {
        $this->actingAs($this->user('Development Partner'))
            ->get(route('cms.public-partners.index'))
            ->assertForbidden();

        $this->actingAs($this->user('M&E Officer'))
            ->get(route('cms.programme-highlights.index'))
            ->assertForbidden();
    }

    private function websiteAdmin(): User
    {
        return $this->user('DPMO Website Administrator');
    }

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }
}
