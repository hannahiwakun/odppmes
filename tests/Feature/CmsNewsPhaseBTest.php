<?php

namespace Tests\Feature;

use App\Cms\CmsNewsCategories;
use App\Cms\CmsNewsHtml;
use App\Models\CmsMediaAsset;
use App\Models\CmsNewsArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CmsNewsPhaseBTest extends TestCase
{
    use RefreshDatabase;

    public function test_featured_image_is_uploaded_associated_and_replaced_without_deleting_the_previous_file(): void
    {
        Storage::fake('local');
        $admin = $this->websiteAdmin();

        $this->actingAs($admin)
            ->post(route('cms.news.store'), $this->payload([
                'title' => 'Clinic image article',
                'category' => 'Events',
                'featured_image_alt' => 'Officials at a clinic opening',
                'image' => UploadedFile::fake()->image('first.jpg', 80, 60),
            ]))
            ->assertRedirect();

        $article = CmsNewsArticle::query()->first();
        $this->assertNotNull($article);
        $this->assertSame('Events', $article->category);
        $this->assertSame('Officials at a clinic opening', $article->featured_image_alt);
        $this->assertNotNull($article->featured_image_media_id);
        $this->assertSame('draft', $article->status);

        $first = CmsMediaAsset::query()->find($article->featured_image_media_id);
        $this->assertNotNull($first);
        $this->assertFalse($first->is_cleared_for_public);
        $this->assertSame('local', $first->disk);
        $this->assertStringStartsWith('cms/media/', $first->path);
        Storage::disk('local')->assertExists($first->path);
        $this->assertStringNotContainsString(public_path(), Storage::disk('local')->path($first->path));

        $this->actingAs($admin)
            ->get(route('cms.news.edit', $article))
            ->assertOk()
            ->assertSee('first.jpg', false)
            ->assertSee('Officials at a clinic opening', false)
            ->assertSee('Events', false);

        $this->actingAs($admin)
            ->put(route('cms.news.update', $article), $this->payload([
                'title' => 'Clinic image article',
                'category' => 'Press Releases',
                'featured_image_alt' => 'Replacement photograph of the clinic',
                'image' => UploadedFile::fake()->image('second.jpg', 90, 70),
            ]))
            ->assertRedirect();

        $article = $article->fresh();
        $this->assertSame('Press Releases', $article->category);
        $this->assertNotSame($first->id, $article->featured_image_media_id);
        $second = CmsMediaAsset::query()->find($article->featured_image_media_id);
        $this->assertNotNull($second);
        Storage::disk('local')->assertExists($first->path);
        Storage::disk('local')->assertExists($second->path);
        $this->assertSame(2, CmsMediaAsset::query()->count());
    }

    public function test_cleared_featured_image_is_public_only_when_the_article_is_published(): void
    {
        Storage::fake('local');
        $admin = $this->websiteAdmin();
        $article = $this->article($admin, 'draft', [
            'title' => 'Public image article',
            'slug' => 'public-image-article',
            'category' => 'Government News',
        ]);

        $this->actingAs($admin)
            ->put(route('cms.news.update', $article), $this->payload([
                'title' => 'Public image article',
                'category' => 'Government News',
                'featured_image_alt' => 'Governor speaking at a briefing',
                'image' => UploadedFile::fake()->image('briefing.jpg', 80, 60),
            ]))
            ->assertRedirect();

        $article = $article->fresh();
        $asset = $article->featuredImage;
        $this->assertNotNull($asset);

        $this->get(route('public.news.show', $article->slug))->assertNotFound();
        $this->get(route('public.media', $asset->uuid))->assertNotFound();

        $article->status = 'published';
        $article->published_at = now()->subHour();
        $article->save();

        $index = $this->get(route('public.news'));
        $index->assertOk();
        $index->assertSee('Public image article', false);
        $index->assertSee('Government News', false);
        $index->assertSee('Featured image not yet available', false);
        $index->assertDontSee(route('public.media', $asset->uuid), false);
        $this->get(route('public.media', $asset->uuid))->assertNotFound();

        $asset->is_cleared_for_public = true;
        $asset->save();

        $this->get(route('public.news'))
            ->assertOk()
            ->assertSee(route('public.media', $asset->uuid), false)
            ->assertSee('Governor speaking at a briefing', false);

        $publishedDate = $article->fresh()->published_at->format('d F Y');
        $publicArticle = $this->get(route('public.news.show', $article->slug));
        $publicArticle->assertOk();
        $publicArticle->assertSee('Government News', false);
        $publicArticle->assertSee($publishedDate, false);
        $publicArticle->assertSee('A valid excerpt for the news article.', false);
        $publicArticle->assertSee('By '.$admin->name, false);
        $publicArticle->assertSee(route('public.media', $asset->uuid), false);

        $this->get(route('public.media', $asset->uuid))->assertOk();
    }

    public function test_category_is_validated_persisted_displayed_and_filterable(): void
    {
        $admin = $this->websiteAdmin();

        $this->actingAs($admin)
            ->from(route('cms.news.create'))
            ->post(route('cms.news.store'), $this->payload(['category' => 'Confidential']))
            ->assertRedirect(route('cms.news.create'))
            ->assertSessionHasErrors('category');

        $this->actingAs($admin)
            ->post(route('cms.news.store'), $this->payload([
                'title' => 'Policy briefing',
                'category' => 'Policies and Programmes',
            ]))
            ->assertRedirect();

        $policy = CmsNewsArticle::query()->where('title', 'Policy briefing')->first();
        $this->assertSame('Policies and Programmes', $policy->category);

        $events = $this->article($admin, 'draft', [
            'title' => 'Town hall event',
            'slug' => 'town-hall-event',
            'category' => 'Events',
        ]);

        $listing = $this->actingAs($admin)->get(route('cms.news.index', ['category' => 'Events']));
        $listing->assertOk();
        $listing->assertSee('Town hall event', false);
        $listing->assertDontSee('Policy briefing', false);

        $policy->status = 'published';
        $policy->published_at = now()->subHour();
        $policy->save();
        $events->status = 'published';
        $events->published_at = now()->subHour();
        $events->save();

        $this->get(route('public.news', ['category' => 'Policies and Programmes']))
            ->assertOk()
            ->assertSee('Policy briefing', false)
            ->assertDontSee('Town hall event', false);
    }

    public function test_existing_articles_without_images_and_plain_or_rich_text_remain_valid(): void
    {
        $admin = $this->websiteAdmin();
        $plain = $this->article($admin, 'published', [
            'title' => 'Historic plain notice',
            'slug' => 'historic-plain-notice',
            'body' => "Opening hours remain unchanged.\n\nBring identification.",
        ]);
        $plain->published_at = now()->subDay();
        $plain->save();

        $rich = $this->article($admin, 'published', [
            'title' => 'Historic formatted notice',
            'slug' => 'historic-formatted-notice',
            'body' => CmsNewsHtml::sanitize('<h2>Hours</h2><p>The <strong>Akure</strong> desk is open.</p>'),
            'category' => 'Announcements',
        ]);
        $rich->published_at = now()->subDay();
        $rich->save();

        $this->assertNull($plain->featured_image_media_id);
        $this->assertSame('Announcements', $plain->fresh()->categoryLabel());

        $this->get(route('public.news.show', $plain->slug))
            ->assertOk()
            ->assertSee('Featured image not yet available', false)
            ->assertSee('Opening hours remain unchanged.', false);

        $this->get(route('public.news.show', $rich->slug))
            ->assertOk()
            ->assertSee('<h2>Hours</h2>', false)
            ->assertSee('<strong>Akure</strong>', false);

        $this->actingAs($admin)
            ->get(route('cms.news.index'))
            ->assertOk()
            ->assertSee('Historic plain notice', false)
            ->assertSee('No image', false);
    }

    public function test_website_administrator_cannot_bypass_workflow_and_viewers_cannot_manage_news(): void
    {
        $admin = $this->websiteAdmin();
        $this->actingAs($admin)
            ->post(route('cms.news.store'), $this->payload([
                'title' => 'Workflow guard article',
                'status' => 'published',
                'category' => 'Announcements',
            ]))
            ->assertRedirect();

        $this->assertSame('draft', CmsNewsArticle::query()->where('title', 'Workflow guard article')->value('status'));

        $this->actingAs($this->user('Viewer'))
            ->get(route('cms.news.index'))
            ->assertForbidden();

        $draft = CmsNewsArticle::query()->where('title', 'Workflow guard article')->first();
        $this->get(route('public.news'))
            ->assertOk()
            ->assertDontSee('Workflow guard article', false);
        $this->get(route('public.news.show', $draft->slug))->assertNotFound();
    }

    public function test_known_news_categories_are_extensible_strings(): void
    {
        $this->assertContains('Government News', CmsNewsCategories::all());
        $this->assertContains('Press Releases', CmsNewsCategories::all());
        $this->assertTrue(CmsNewsCategories::isValid('Projects and Development'));
        $this->assertFalse(CmsNewsCategories::isValid('Confidential'));
        $this->assertSame('Announcements', CmsNewsCategories::display(null));
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
            'category' => 'Announcements',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function article(User $owner, string $status, array $overrides = []): CmsNewsArticle
    {
        $article = CmsNewsArticle::create(array_merge([
            'slug' => 'phase-b-'.uniqid(),
            'title' => 'Phase B article',
            'excerpt' => 'A valid excerpt for the news article.',
            'body' => str_repeat('The full article body. ', 8),
            'category' => 'Announcements',
        ], $overrides));
        $article->created_by = $owner->id;
        $article->status = $status;
        $article->save();

        return $article->fresh();
    }
}
