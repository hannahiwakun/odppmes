<?php

namespace Tests\Feature;

use App\Models\CmsEnquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_public_navigation_page_opens(): void
    {
        $pages = [
            route('home') => 'Coordinating partnerships that deliver development for Ondo State.',
            route('public.about') => 'About the Development Partners Management Office',
            route('public.services') => 'Development Partnership Coordination',
            route('public.partners') => 'No named official partners have been released',
            route('public.projects') => 'Projects and programmes',
            route('public.gallery') => 'No official gallery files have been supplied',
            route('public.management') => 'Placeholder profile',
            route('public.news') => 'News and updates',
            route('public.contact') => 'Contact us',
        ];

        foreach ($pages as $url => $text) {
            $this->get($url)->assertOk()->assertSee($text, false);
        }

        $article = $this->get(route('public.news.show', 'dpmo-public-website'));
        $article->assertOk();
        $article->assertSee('DPMO publishes an institutional public website', false);
        $article->assertSee('Related news', false);
        $article->assertSee('Share:', false);

        $this->get(route('public.news.show', 'missing-article'))->assertNotFound();
    }

    public function test_public_pages_do_not_expose_portal_records_or_invented_officials(): void
    {
        $home = $this->get(route('home'))->assertOk();
        $home->assertDontSee('Executive Dashboard', false);
        $home->assertDontSee('Rural Road', false);
        $home->assertSee('Placeholder', false);
        $home->assertSee('leading and effective institutional platform', false);

        $this->get(route('public.partners'))
            ->assertSee('Development Stakeholders and Strategic Partners', false)
            ->assertDontSee('World Health Partner', false);

        $this->get(route('public.projects'))
            ->assertSee('not exported from confidential ODPPMES', false)
            ->assertDontSee('approved_value', false);

        $about = $this->get(route('public.about'))->assertOk();
        $about->assertSee('Strategic pillars', false);
        $about->assertSee('Strategic Partnership Development', false);
        $about->assertSee('Knowledge and Information Management', false);
        $about->assertDontSee('Key Stakeholders and Development Partners', false);
        $about->assertSee('Development Stakeholders and Strategic Partners', false);
    }

    public function test_the_contact_form_validates_and_stores_an_enquiry(): void
    {
        Storage::fake('local');

        $this->from(route('public.contact'))
            ->post(route('public.contact.submit'), [
                'name' => 'Ada',
                'email' => 'not-an-email',
                'subject' => 'Hello',
                'message' => 'Too short',
            ])
            ->assertRedirect(route('public.contact'))
            ->assertSessionHasErrors(['email', 'message']);

        $this->from(route('public.contact'))
            ->post(route('public.contact.submit'), [
                'name' => 'Ada Okafor',
                'email' => 'ada@example.com',
                'organisation' => 'Ministry of Health',
                'subject' => 'Partnership enquiry',
                'message' => 'Please advise how an MDA officer should request portal access for monitoring.',
                'website' => 'http://spam.example',
            ])
            ->assertRedirect(route('public.contact'))
            ->assertSessionHasErrors('website');

        $this->from(route('public.contact'))
            ->post(route('public.contact.submit'), [
                'name' => 'Ada Okafor',
                'email' => 'ada@example.com',
                'organisation' => 'Ministry of Health',
                'subject' => 'Partnership enquiry',
                'message' => 'Please advise how an MDA officer should request portal access for monitoring.',
            ])
            ->assertRedirect(route('public.contact'))
            ->assertSessionHas('success');

        $this->assertTrue(Storage::disk('local')->exists('public_enquiries.jsonl'));
        $this->assertFalse(Storage::disk('local')->exists('private/public_enquiries.jsonl'));
        $this->assertSame(1, CmsEnquiry::query()->count());
        $this->assertSame('Ada Okafor', CmsEnquiry::query()->first()->name);
        $this->assertSame('Partnership enquiry', CmsEnquiry::query()->first()->subject);
    }

    public function test_contact_honeypot_does_not_create_an_enquiry(): void
    {
        Storage::fake('local');

        $this->from(route('public.contact'))
            ->post(route('public.contact.submit'), [
                'name' => 'Ada Okafor',
                'email' => 'ada@example.com',
                'subject' => 'Partnership enquiry',
                'message' => 'Please advise how an MDA officer should request portal access for monitoring.',
                'website' => 'http://spam.example',
            ])
            ->assertRedirect(route('public.contact'))
            ->assertSessionHasErrors('website');

        $this->assertSame(0, CmsEnquiry::query()->count());
        $this->assertFalse(Storage::disk('local')->exists('public_enquiries.jsonl'));
    }

    public function test_contact_form_is_rate_limited(): void
    {
        Storage::fake('local');

        $payload = [
            'name' => 'Ada Okafor',
            'email' => 'ada@example.com',
            'subject' => 'Partnership enquiry',
            'message' => 'Please advise how an MDA officer should request portal access for monitoring.',
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('public.contact.submit'), $payload)->assertRedirect(route('public.contact'));
        }

        $this->post(route('public.contact.submit'), $payload)->assertStatus(429);
        $this->assertSame(1, CmsEnquiry::query()->count());
    }

    public function test_portal_permissions_are_unchanged(): void
    {
        $this->get(route('login'))->assertOk();
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('reports.index'))->assertRedirect(route('login'));

        $admin = User::factory()->create(['role' => 'Super Administrator']);
        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('home'))
            ->assertOk()
            ->assertSee('Open CMS', false)
            ->assertDontSee('Open dashboard', false);
    }
}
