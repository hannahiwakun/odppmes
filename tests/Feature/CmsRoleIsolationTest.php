<?php

namespace Tests\Feature;

use App\Models\Mda;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CmsRoleIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_website_administrator_can_log_in_and_is_sent_to_cms_from_the_dashboard(): void
    {
        $user = $this->websiteAdmin();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);

        $this->get(route('dashboard'))
            ->assertRedirect(route('cms.home'));

        $cms = $this->get(route('cms.home'));
        $cms->assertOk();
        $cms->assertSee('Public website content', false);
        $cms->assertSee('News and Announcements', false);
        $cms->assertSee('Overview', false);
        $cms->assertSee('href="'.route('profile.edit').'"', false);
        $cms->assertDontSee('Executive Dashboard', false);
        $cms->assertDontSee('Total Projects', false);
        $cms->assertDontSee('href="'.route('projects.index').'"', false);
        $cms->assertDontSee('href="'.route('users.index').'"', false);
        $cms->assertDontSee('href="'.route('audit.logs').'"', false);
    }

    public function test_cms_home_is_limited_to_cms_roles(): void
    {
        $this->get(route('cms.home'))->assertRedirect(route('login'));

        $this->actingAs($this->user('Viewer'))
            ->get(route('cms.home'))
            ->assertForbidden();

        $this->actingAs($this->user('M&E Officer'))
            ->get(route('cms.home'))
            ->assertForbidden();

        $this->actingAs($this->user('MDA Officer'))
            ->get(route('cms.home'))
            ->assertForbidden();

        $this->actingAs($this->user('Super Administrator'))
            ->get(route('cms.home'))
            ->assertOk()
            ->assertSee('Public website content', false);
    }

    public function test_a_website_administrator_is_denied_operational_portal_routes(): void
    {
        $admin = $this->websiteAdmin();
        $project = $this->project();

        $this->actingAs($admin);

        $this->get(route('projects.index'))->assertForbidden();
        $this->get(route('projects.show', $project))->assertForbidden();
        $this->get(route('monitorings.index'))->assertForbidden();
        $this->get(route('project-documents.index', $project))->assertForbidden();
        $this->get(route('reports.index'))->assertForbidden();
        $this->get(route('reports.pdf'))->assertForbidden();
        $this->get(route('users.index'))->assertForbidden();
        $this->get(route('users.create'))->assertForbidden();
        $this->get(route('mdas.index'))->assertForbidden();
        $this->get(route('development-partners.index'))->assertForbidden();
        $this->get(route('project.map'))->assertForbidden();
        $this->get(route('audit.logs'))->assertForbidden();
        $this->get('/documents')->assertNotFound();
    }

    public function test_existing_roles_keep_their_portal_permissions(): void
    {
        $this->actingAs($this->user('Super Administrator'))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Executive Dashboard', false);

        $this->actingAs($this->user('Super Administrator'))
            ->get(route('users.index'))
            ->assertOk();

        $this->actingAs($this->user('Super Administrator'))
            ->get(route('audit.logs'))
            ->assertOk();

        $this->actingAs($this->user('M&E Officer'))
            ->get(route('projects.index'))
            ->assertOk();

        $this->actingAs($this->user('MDA Officer'))
            ->get(route('monitorings.index'))
            ->assertOk();

        $this->actingAs($this->user('Viewer'))
            ->get(route('reports.index'))
            ->assertOk();

        $this->actingAs($this->user('Viewer'))
            ->get(route('projects.index'))
            ->assertForbidden();

        $this->actingAs($this->user('Development Partner'))
            ->get(route('reports.index'))
            ->assertForbidden();
    }

    public function test_a_super_administrator_can_assign_the_website_administrator_role(): void
    {
        $super = $this->user('Super Administrator');

        $this->actingAs($super)
            ->post(route('users.store'), [
                'name' => 'Website Officer',
                'email' => 'website.admin@example.test',
                'role' => 'DPMO Website Administrator',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('users.index'));

        $created = User::where('email', 'website.admin@example.test')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->isWebsiteAdmin());
        $this->assertTrue($created->hasCmsAccess());
        $this->assertTrue(Hash::check('password123', $created->password));
    }

    public function test_the_last_super_administrator_cannot_become_a_website_administrator(): void
    {
        $admin = $this->user('Super Administrator');

        $this->actingAs($admin)
            ->from(route('users.edit', $admin))
            ->put(route('users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'DPMO Website Administrator',
            ])
            ->assertRedirect(route('users.edit', $admin))
            ->assertSessionHasErrors('role');

        $this->assertSame('Super Administrator', $admin->fresh()->role);
    }

    public function test_unknown_roles_do_not_inherit_statewide_project_visibility(): void
    {
        $this->project('Statewide Clinic');

        $unknown = $this->user('Unrecognised Role');
        $websiteAdmin = $this->websiteAdmin();
        $viewer = $this->user('Viewer');
        $super = $this->user('Super Administrator');

        $this->assertSame(0, Project::query()->visibleTo($unknown)->count());
        $this->assertSame(0, Project::query()->visibleTo($websiteAdmin)->count());
        $this->assertSame(1, Project::query()->visibleTo($viewer)->count());
        $this->assertSame(1, Project::query()->visibleTo($super)->count());
        $this->assertFalse($unknown->canAccessProject(Project::query()->first()));
        $this->assertFalse($websiteAdmin->canAccessProject(Project::query()->first()));
        $this->assertTrue($viewer->canAccessProject(Project::query()->first()));
    }

    private function websiteAdmin(): User
    {
        return $this->user('DPMO Website Administrator');
    }

    private function user(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
        ]);
    }

    private function project(string $title = 'Project'): Project
    {
        $mda = Mda::create([
            'name' => 'Ministry '.uniqid(),
            'code' => strtoupper(substr(md5(uniqid('', true)), 0, 8)),
            'status' => true,
        ]);

        return Project::create([
            'mda_id' => $mda->id,
            'project_title' => $title,
            'sector' => 'Education',
            'lga' => 'Akure North',
            'status' => 'Planning',
            'progress' => 0,
        ]);
    }
}
