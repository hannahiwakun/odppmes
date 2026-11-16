<?php

namespace Tests\Feature;

use App\Cms\CmsNewsHtml;
use App\Models\CmsNewsArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsNewsPhaseATest extends TestCase
{
    use RefreshDatabase;

    public function test_html_body_is_sanitised_on_save_and_unsafe_markup_is_rejected(): void
    {
        $admin = $this->websiteAdmin();

        $this->actingAs($admin)
            ->post(route('cms.news.store'), [
                'title' => 'Sanitised announcement',
                'excerpt' => 'Excerpt for the sanitised announcement.',
                'body' => '<h2>Clinic hours</h2><p onclick="alert(1)">Visit the <a href="https://example.gov.ng">office site</a>.</p><script>alert(1)</script><p><a href="javascript:alert(1)">Bad link</a></p>',
            ])
            ->assertRedirect();

        $article = CmsNewsArticle::query()->first();
        $this->assertNotNull($article);
        $this->assertSame('draft', $article->status);
        $this->assertStringContainsString('<h2>Clinic hours</h2>', $article->body);
        $this->assertStringContainsString('href="https://example.gov.ng"', $article->body);
        $this->assertStringNotContainsString('<script', $article->body);
        $this->assertStringNotContainsString('onclick', $article->body);
        $this->assertStringNotContainsString('javascript:', $article->body);
    }

    public function test_plain_text_articles_remain_compatible_on_cms_and_public_pages(): void
    {
        $admin = $this->websiteAdmin();
        $article = $this->article($admin, 'published', [
            'title' => 'Plain text clinic notice',
            'slug' => 'plain-text-clinic-notice',
            'body' => "Opening hours remain unchanged.\n\nBring a valid identification card.",
        ]);
        $article->published_at = now()->subDay();
        $article->save();

        $this->actingAs($admin)
            ->get(route('cms.news.show', $article))
            ->assertOk()
            ->assertSee('Opening hours remain unchanged.', false)
            ->assertSee('Bring a valid identification card.', false);

        $this->get(route('public.news.show', $article->slug))
            ->assertOk()
            ->assertSee('Opening hours remain unchanged.', false)
            ->assertSee('<p>Opening hours remain unchanged.</p>', false);
    }

    public function test_rich_text_is_displayed_after_sanitisation_on_the_public_article(): void
    {
        $admin = $this->websiteAdmin();
        $article = $this->article($admin, 'published', [
            'title' => 'Formatted clinic notice',
            'slug' => 'formatted-clinic-notice',
            'body' => CmsNewsHtml::sanitize('<h2>Hours</h2><p>The <strong>Akure</strong> desk is open.</p>'),
        ]);
        $article->published_at = now()->subDay();
        $article->save();

        $this->get(route('public.news.show', $article->slug))
            ->assertOk()
            ->assertSee('<h2>Hours</h2>', false)
            ->assertSee('<strong>Akure</strong>', false)
            ->assertDontSee('alert(1)', false)
            ->assertDontSee('onclick=', false);
    }

    public function test_staff_preview_requires_cms_access_and_is_not_a_public_route(): void
    {
        $admin = $this->websiteAdmin();
        $admin->forceFill(['name' => "Ada O'Reilly"])->save();
        $article = $this->article($admin, 'draft', [
            'title' => 'Unpublished preview article',
            'slug' => 'unpublished-preview-article',
        ]);

        $this->get(route('cms.news.preview', $article))->assertRedirect(route('login'));

        $this->actingAs($this->user('Viewer'))
            ->get(route('cms.news.preview', $article))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('cms.news.preview', $article))
            ->assertOk()
            ->assertSee('Staff preview', false)
            ->assertSee('This page is only visible to signed-in CMS users', false)
            ->assertSee('Unpublished preview article', false)
            ->assertSee('A valid excerpt for the news article.', false)
            ->assertSee('By '.$admin->name)
            ->assertSee('Not yet published', false);

        $this->get(route('public.news.show', $article->slug))->assertNotFound();
        $this->get(route('public.news'))
            ->assertOk()
            ->assertDontSee('Unpublished preview article', false);
    }

    public function test_listing_shows_author_dates_and_permitted_actions_only(): void
    {
        $admin = $this->websiteAdmin();
        $this->article($admin, 'draft', [
            'title' => 'Listing draft article',
            'slug' => 'listing-draft-article',
        ]);

        $page = $this->actingAs($admin)->get(route('cms.news.index'));
        $page->assertOk();
        $page->assertSee('Listing draft article', false);
        $page->assertSee($admin->name, false);
        $page->assertSee('Date created', false);
        $page->assertSee('Last updated', false);
        $page->assertSee('Preview', false);
        $page->assertSee('Submit for Approval', false);
        $page->assertDontSee('>Archive<', false);
    }

    public function test_website_administrator_still_cannot_assign_status_or_edit_submitted_news(): void
    {
        $admin = $this->websiteAdmin();

        $this->actingAs($admin)
            ->post(route('cms.news.store'), [
                'title' => 'Status assignment attempt',
                'excerpt' => 'Excerpt for status assignment attempt.',
                'body' => str_repeat('The full article body. ', 8),
                'status' => 'published',
            ])
            ->assertRedirect();

        $this->assertSame('draft', CmsNewsArticle::query()->where('title', 'Status assignment attempt')->value('status'));

        $submitted = $this->article($admin, 'submitted', [
            'title' => 'Already submitted article',
            'slug' => 'already-submitted-article',
        ]);

        $this->actingAs($admin)
            ->get(route('cms.news.edit', $submitted))
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

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function article(User $owner, string $status, array $overrides = []): CmsNewsArticle
    {
        $article = CmsNewsArticle::create(array_merge([
            'slug' => 'phase-a-'.uniqid(),
            'title' => 'Phase A article',
            'excerpt' => 'A valid excerpt for the news article.',
            'body' => str_repeat('The full article body. ', 8),
        ], $overrides));
        $article->created_by = $owner->id;
        $article->status = $status;
        $article->save();

        return $article->fresh();
    }
}
