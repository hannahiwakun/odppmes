<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_open_the_public_homepage_without_authentication(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Development Partners Management Office', false);
        $response->assertSee('Portal Login', false);
        $response->assertSee('ODPPMES', false);
        $response->assertSee('Coordinating partnerships that deliver development for Ondo State.', false);
        $response->assertSee('images/hero-development.jpg', false);
        $response->assertSee('Illustrative photograph', false);
        $response->assertSee('leading and effective institutional platform', false);
        $response->assertSee(route('login'), false);
        $response->assertDontSee('href="'.url('/register').'"', false);
        $response->assertDontSee('>Register<', false);
        $response->assertDontSee('Executive Dashboard', false);
        $response->assertDontSee('Malaria', false);
    }

    public function test_portal_access_buttons_use_the_existing_login_route(): void
    {
        $html = $this->get(route('home'))->getContent();

        $this->assertSame(0, substr_count($html, 'href="'.url('/register').'"'));
        $this->assertGreaterThanOrEqual(2, substr_count($html, 'href="'.route('login').'"'));
    }

    public function test_guests_cannot_open_the_dashboard_or_project_records(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('projects.index'))->assertRedirect(route('login'));
        $this->get('/register')->assertNotFound();
    }

    public function test_an_authenticated_user_still_reaches_the_dashboard_after_login(): void
    {
        $user = User::factory()->create([
            'role' => 'Viewer',
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Open dashboard', false);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }
}
