<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_sidebar_has_an_accessible_menu_control_and_keeps_role_links(): void
    {
        $admin = User::factory()->create(['role' => 'Super Administrator']);
        $officer = User::factory()->create(['role' => 'M&E Officer']);
        $ministry = User::factory()->create(['role' => 'MDA Officer']);
        $viewer = User::factory()->create(['role' => 'Viewer']);

        $adminPage = $this->actingAs($admin)->get(route('dashboard'));
        $adminPage->assertOk();
        $adminPage->assertSee('Open menu', false);
        $adminPage->assertSee('Close menu', false);
        $adminPage->assertSee('aria-controls="admin-sidebar"', false);
        $adminPage->assertSee('href="'.route('mdas.index').'"', false);
        $adminPage->assertSee('href="'.route('projects.index').'"', false);
        $adminPage->assertSee('href="'.route('reports.index').'"', false);
        $adminPage->assertSee('href="'.route('audit.logs').'"', false);
        $adminPage->assertSee('href="'.route('cms.home').'"', false);
        $adminPage->assertSee('CMS Dashboard', false);

        $officerPage = $this->actingAs($officer)->get(route('dashboard'));
        $officerPage->assertOk();
        $officerPage->assertSee('href="'.route('projects.index').'"', false);
        $officerPage->assertSee('href="'.route('reports.index').'"', false);
        $officerPage->assertSee('href="'.route('mdas.index').'"', false);
        $officerPage->assertDontSee('href="'.route('users.index').'"', false);
        $officerPage->assertDontSee('href="'.route('audit.logs').'"', false);
        $officerPage->assertDontSee('href="'.route('cms.home').'"', false);

        $ministryPage = $this->actingAs($ministry)->get(route('dashboard'));
        $ministryPage->assertOk();
        $ministryPage->assertSee('My MDA Dashboard', false);
        $ministryPage->assertSee('href="'.route('projects.index').'"', false);
        $ministryPage->assertSee('href="'.route('monitorings.index').'"', false);
        $ministryPage->assertSee('href="'.route('reports.index').'"', false);
        $ministryPage->assertSee('href="'.route('mdas.mine').'"', false);
        $ministryPage->assertDontSee('href="'.route('mdas.index').'"', false);
        $ministryPage->assertDontSee('href="'.route('project.map').'"', false);

        $viewerPage = $this->actingAs($viewer)->get(route('dashboard'));
        $viewerPage->assertOk();
        $viewerPage->assertSee('href="'.route('reports.index').'"', false);
        $viewerPage->assertSee('href="'.route('project.map').'"', false);
        $viewerPage->assertDontSee('href="'.route('projects.index').'"', false);
        $viewerPage->assertDontSee('href="'.route('mdas.index').'"', false);
        $viewerPage->assertDontSee('href="'.route('cms.home').'"', false);
    }
}
