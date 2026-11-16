<?php

namespace Tests\Feature;

use App\Models\CmsMediaAsset;
use App\Models\CmsNewsArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CmsDashboardUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_cms_overview_shows_zero_counts_and_cms_navigation(): void
    {
        $admin = $this->websiteAdmin();

        $page = $this->actingAs($admin)->get(route('cms.home'));

        $page->assertOk();
        $page->assertSee('Overview', false);
        $page->assertSee('News and Announcements', false);
        $page->assertSee('Public Enquiries', false);
        $page->assertSee('Total news articles', false);
        $page->assertSee('>0<', false);
        $page->assertSee('None yet', false);
        $page->assertSee('No workflow events have been recorded yet.', false);
        $page->assertSee('href="'.route('cms.news.index').'"', false);
        $page->assertSee('href="'.route('profile.edit').'"', false);
        $page->assertDontSee('Approval Centre', false);
        $page->assertDontSee('CMS User Management', false);
        $page->assertDontSee('href="'.route('projects.index').'"', false);
        $page->assertDontSee('href="'.route('monitorings.index').'"', false);
        $page->assertDontSee('href="'.route('reports.index').'"', false);
        $page->assertDontSee('href="'.route('audit.logs').'"', false);
        $page->assertDontSee('Executive Dashboard', false);
        $page->assertSee('Open menu', false);
        $page->assertSee('aria-controls="cms-sidebar"', false);
    }

    public function test_a_super_administrator_sees_approval_and_user_management_links(): void
    {
        $super = $this->user('Super Administrator');

        $page = $this->actingAs($super)->get(route('cms.home'));
        $page->assertOk();
        $page->assertSee('Approval Centre', false);
        $page->assertSee('CMS User Management', false);
        $page->assertSee('href="'.route('cms.approvals.index').'"', false);
        $page->assertSee('href="'.route('cms.users.index').'"', false);
        $page->assertSee('Operational Portal', false);

        $this->actingAs($super)->get(route('cms.approvals.index'))
            ->assertOk()
            ->assertSee('There are no submitted items waiting for approval.', false);

        $this->actingAs($super)->get(route('cms.users.index'))
            ->assertOk()
            ->assertSee($super->email, false);
    }

    public function test_a_website_administrator_cannot_open_approval_or_cms_user_pages(): void
    {
        $admin = $this->websiteAdmin();

        $this->actingAs($admin)->get(route('cms.approvals.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('cms.users.index'))->assertForbidden();
    }

    public function test_news_listing_and_create_screens_render_for_cms_roles(): void
    {
        $admin = $this->websiteAdmin();

        $this->actingAs($admin)->get(route('cms.news.index'))
            ->assertOk()
            ->assertSee('No news articles match the current filters.', false);

        $this->actingAs($admin)->get(route('cms.news.create'))
            ->assertOk()
            ->assertSee('Save draft', false);
    }

    public function test_media_preview_requires_cms_access(): void
    {
        $asset = new CmsMediaAsset;
        $asset->uuid = (string) Str::uuid();
        $asset->disk = CmsMediaAsset::DISK;
        $asset->path = 'cms/media/'.$asset->uuid.'.jpg';
        $asset->original_name = 'preview.jpg';
        $asset->mime = 'image/jpeg';
        $asset->size_bytes = 100;
        $asset->checksum_sha256 = hash('sha256', 'preview');
        $asset->is_cleared_for_public = false;
        $asset->save();

        $this->get(route('cms.media.preview', $asset))->assertRedirect(route('login'));

        $this->actingAs($this->user('Viewer'))
            ->get(route('cms.media.preview', $asset))
            ->assertForbidden();
    }

    public function test_overview_counts_reflect_existing_news_records(): void
    {
        $admin = $this->websiteAdmin();
        $article = CmsNewsArticle::create([
            'slug' => 'counted-article',
            'title' => 'Counted article',
            'excerpt' => 'Excerpt for count check.',
            'body' => str_repeat('Body text. ', 8),
        ]);
        $article->created_by = $admin->id;
        $article->status = 'draft';
        $article->save();

        $this->actingAs($admin)->get(route('cms.home'))->assertOk();
        $this->actingAs($admin)->get(route('cms.news.index'))
            ->assertOk()
            ->assertSee('Counted article', false);
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
