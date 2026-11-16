<?php

namespace Tests\Feature;

use App\Models\CmsNewsArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsNewsBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_website_administrator_can_create_and_submit_news(): void
    {
        $admin = $this->websiteAdmin();

        $this->actingAs($admin)
            ->post(route('cms.news.store'), [
                'title' => 'DPMO announces clinic programme',
                'excerpt' => 'A short excerpt for the announcement.',
                'body' => str_repeat('The full article body. ', 8),
                'status' => 'published',
                'published_by' => $admin->id,
            ])
            ->assertRedirect();

        $article = CmsNewsArticle::query()->first();
        $this->assertNotNull($article);
        $this->assertSame('draft', $article->status);
        $this->assertSame($admin->id, $article->created_by);
        $this->assertNull($article->published_by);
        $this->assertSame('dpmo-announces-clinic-programme', $article->slug);

        $this->actingAs($admin)
            ->from(route('cms.news.show', $article))
            ->post(route('cms.workflow.submit', ['type' => 'news', 'id' => $article->id]))
            ->assertRedirect();

        $this->assertSame('submitted', $article->fresh()->status);
    }

    public function test_news_validation_and_unique_slugs(): void
    {
        $admin = $this->websiteAdmin();

        $this->actingAs($admin)
            ->from(route('cms.news.create'))
            ->post(route('cms.news.store'), [
                'title' => '',
                'excerpt' => '',
                'body' => 'short',
            ])
            ->assertRedirect(route('cms.news.create'))
            ->assertSessionHasErrors(['title', 'excerpt', 'body']);

        $this->actingAs($admin)
            ->post(route('cms.news.store'), $this->payload(['slug' => 'shared-slug']))
            ->assertRedirect();

        $this->actingAs($admin)
            ->from(route('cms.news.create'))
            ->post(route('cms.news.store'), $this->payload(['slug' => 'shared-slug', 'title' => 'Second article title']))
            ->assertRedirect(route('cms.news.create'))
            ->assertSessionHasErrors('slug');
    }

    public function test_a_website_administrator_cannot_edit_submitted_news(): void
    {
        $admin = $this->websiteAdmin();
        $article = $this->article($admin, 'submitted');

        $this->actingAs($admin)
            ->get(route('cms.news.edit', $article))
            ->assertForbidden();

        $this->actingAs($admin)
            ->put(route('cms.news.update', $article), $this->payload(['title' => 'Changed']))
            ->assertForbidden();
    }

    public function test_a_super_administrator_can_approve_and_publish_news(): void
    {
        $admin = $this->websiteAdmin();
        $super = $this->user('Super Administrator');
        $article = $this->article($admin, 'submitted');

        $this->actingAs($super)
            ->from(route('cms.news.show', $article))
            ->post(route('cms.workflow.approve', ['type' => 'news', 'id' => $article->id]))
            ->assertRedirect();

        $this->actingAs($super)
            ->from(route('cms.news.show', $article))
            ->post(route('cms.workflow.publish', ['type' => 'news', 'id' => $article->id]))
            ->assertRedirect();

        $article = $article->fresh();
        $this->assertSame('published', $article->status);
        $this->assertSame($super->id, $article->reviewed_by);
        $this->assertSame($super->id, $article->published_by);
        $this->assertNotNull($article->published_at);
    }

    public function test_other_roles_cannot_open_news_backend_and_drafts_are_not_public(): void
    {
        $article = $this->article($this->websiteAdmin(), 'draft');

        $this->actingAs($this->user('Viewer'))
            ->get(route('cms.news.index'))
            ->assertForbidden();

        $this->get(route('public.news'))
            ->assertOk()
            ->assertDontSee($article->title, false);
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
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'DPMO news title',
            'excerpt' => 'A valid excerpt for the news article.',
            'body' => str_repeat('The full article body. ', 8),
        ], $overrides);
    }

    private function article(User $owner, string $status): CmsNewsArticle
    {
        $article = CmsNewsArticle::create($this->payload([
            'slug' => 'owned-article-'.uniqid(),
        ]));
        $article->created_by = $owner->id;
        $article->status = $status;
        $article->save();

        return $article->fresh();
    }
}
