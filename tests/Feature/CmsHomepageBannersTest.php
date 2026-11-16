<?php

namespace Tests\Feature;

use App\Cms\CmsHomepageBannerTypes;
use App\Models\CmsHomepageBanner;
use App\Models\CmsMediaAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CmsHomepageBannersTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_website_administrator_can_create_and_edit_a_banner_with_button_text_and_alt(): void
    {
        $admin = $this->websiteAdmin();

        $this->actingAs($admin)
            ->post(route('cms.homepage-banners.store'), [
                'title' => 'State partnership week',
                'body' => 'A public notice about partnership week.',
                'banner_type' => CmsHomepageBanner::TYPE_FEATURE,
                'button_text' => 'Read the notice',
                'link_url' => 'https://example.gov.ng/notice',
                'sort_order' => 3,
                'image_alt' => 'Officials at a partnership briefing',
                'status' => 'published',
            ])
            ->assertRedirect();

        $banner = CmsHomepageBanner::query()->first();
        $this->assertNotNull($banner);
        $this->assertSame('draft', $banner->status);
        $this->assertSame('Read the notice', $banner->button_text);
        $this->assertSame('https://example.gov.ng/notice', $banner->link_url);
        $this->assertSame('Officials at a partnership briefing', $banner->image_alt);
        $this->assertSame(3, $banner->sort_order);
        $this->assertSame($admin->id, $banner->created_by);

        $this->actingAs($admin)
            ->put(route('cms.homepage-banners.update', $banner), [
                'title' => 'State partnership week',
                'body' => 'Updated supporting text.',
                'banner_type' => CmsHomepageBanner::TYPE_ALERT,
                'button_text' => 'View alert',
                'link_url' => 'https://example.gov.ng/alert',
                'sort_order' => 1,
                'image_alt' => 'Updated alternative text',
            ])
            ->assertRedirect();

        $banner = $banner->fresh();
        $this->assertSame('Updated supporting text.', $banner->body);
        $this->assertSame(CmsHomepageBanner::TYPE_ALERT, $banner->banner_type);
        $this->assertSame('View alert', $banner->button_text);
        $this->assertSame('Hero Banner', CmsHomepageBannerTypes::label(CmsHomepageBanner::TYPE_HERO_NOTICE));
        $this->assertSame('Alert Banner', $banner->typeLabel());
        $this->assertSame('Feature Banner', CmsHomepageBannerTypes::label(CmsHomepageBanner::TYPE_FEATURE));
    }

    public function test_featured_image_is_uploaded_replaced_and_not_deleted(): void
    {
        Storage::fake('local');
        $admin = $this->websiteAdmin();

        $this->actingAs($admin)
            ->post(route('cms.homepage-banners.store'), [
                'title' => 'Image banner',
                'banner_type' => CmsHomepageBanner::TYPE_HERO_NOTICE,
                'image_alt' => 'First banner photograph',
                'image' => UploadedFile::fake()->image('first.jpg', 80, 60),
            ])
            ->assertRedirect();

        $banner = CmsHomepageBanner::query()->first();
        $first = CmsMediaAsset::query()->find($banner->image_media_id);
        $this->assertNotNull($first);
        $this->assertFalse($first->is_cleared_for_public);
        Storage::disk('local')->assertExists($first->path);

        $this->actingAs($admin)
            ->put(route('cms.homepage-banners.update', $banner), [
                'title' => 'Image banner',
                'banner_type' => CmsHomepageBanner::TYPE_HERO_NOTICE,
                'image_alt' => 'Replacement banner photograph',
                'image' => UploadedFile::fake()->image('second.jpg', 90, 70),
            ])
            ->assertRedirect();

        $banner = $banner->fresh();
        $second = CmsMediaAsset::query()->find($banner->image_media_id);
        $this->assertNotSame($first->id, $second->id);
        Storage::disk('local')->assertExists($first->path);
        Storage::disk('local')->assertExists($second->path);
    }

    public function test_javascript_and_non_http_links_are_rejected_and_not_rendered(): void
    {
        $admin = $this->websiteAdmin();

        $this->actingAs($admin)
            ->from(route('cms.homepage-banners.create'))
            ->post(route('cms.homepage-banners.store'), [
                'title' => 'Unsafe link banner',
                'banner_type' => CmsHomepageBanner::TYPE_FEATURE,
                'link_url' => 'javascript:alert(1)',
            ])
            ->assertRedirect(route('cms.homepage-banners.create'))
            ->assertSessionHasErrors('link_url');

        $banner = $this->banner($admin, 'published', [
            'title' => 'Stored unsafe link',
            'slug_ignore' => true,
            'banner_type' => CmsHomepageBanner::TYPE_FEATURE,
            'button_text' => 'Do not click',
            'link_url' => 'https://example.gov.ng/safe',
        ]);
        $banner->link_url = 'javascript:alert(1)';
        $banner->save();

        $home = $this->get(route('home'));
        $home->assertOk();
        $home->assertSee('Stored unsafe link', false);
        $home->assertDontSee('javascript:alert(1)', false);
        $home->assertDontSee('Do not click', false);
    }

    public function test_published_banners_render_by_type_with_safe_links_alt_text_and_order(): void
    {
        Storage::fake('local');
        $admin = $this->websiteAdmin();

        $hero = $this->banner($admin, 'published', [
            'title' => 'Published hero banner',
            'body' => 'Hero supporting text.',
            'banner_type' => CmsHomepageBanner::TYPE_HERO_NOTICE,
            'button_text' => 'Open hero page',
            'link_url' => 'https://example.gov.ng/hero',
            'image_alt' => 'Hero photograph of a public briefing',
            'sort_order' => 0,
        ]);
        $this->attachClearedImage($hero, $admin, 'hero.jpg');

        $this->banner($admin, 'published', [
            'title' => 'Second feature card',
            'banner_type' => CmsHomepageBanner::TYPE_FEATURE,
            'sort_order' => 5,
        ]);
        $this->banner($admin, 'published', [
            'title' => 'First feature card',
            'banner_type' => CmsHomepageBanner::TYPE_FEATURE,
            'button_text' => 'Read feature',
            'link_url' => 'https://example.gov.ng/feature',
            'sort_order' => 1,
        ]);
        $this->banner($admin, 'published', [
            'title' => 'Public alert banner',
            'banner_type' => CmsHomepageBanner::TYPE_ALERT,
            'button_text' => 'Alert details',
            'link_url' => 'https://example.gov.ng/alert',
            'sort_order' => 2,
        ]);

        $home = $this->get(route('home'));
        $home->assertOk();
        $home->assertSee('Published hero banner', false);
        $home->assertSee('Hero supporting text.', false);
        $home->assertSee('Open hero page', false);
        $home->assertSee('https://example.gov.ng/hero', false);
        $home->assertSee('rel="noopener noreferrer"', false);
        $home->assertSee('Hero photograph of a public briefing', false);
        $home->assertSee('Public alert banner', false);
        $home->assertSee('Alert details', false);
        $home->assertSee('First feature card', false);
        $home->assertSee('Second feature card', false);
        $home->assertSee('Read feature', false);
        $home->assertDontSee('Coordinating partnerships that deliver development for Ondo State.', false);

        $html = $home->getContent();
        $this->assertLessThan(
            strpos($html, 'Second feature card'),
            strpos($html, 'First feature card')
        );
    }

    public function test_hero_fallback_is_used_when_no_published_hero_exists(): void
    {
        $admin = $this->websiteAdmin();
        $this->banner($admin, 'draft', [
            'title' => 'Draft hero only',
            'banner_type' => CmsHomepageBanner::TYPE_HERO_NOTICE,
        ]);
        $this->banner($admin, 'published', [
            'title' => 'Only a feature',
            'banner_type' => CmsHomepageBanner::TYPE_FEATURE,
        ]);

        $home = $this->get(route('home'));
        $home->assertOk();
        $home->assertSee('Coordinating partnerships that deliver development for Ondo State.', false);
        $home->assertDontSee('Draft hero only', false);
        $home->assertSee('Only a feature', false);
    }

    public function test_unpublished_expired_and_uncleared_images_are_not_public(): void
    {
        Storage::fake('local');
        $admin = $this->websiteAdmin();

        $draft = $this->banner($admin, 'draft', ['title' => 'Draft banner hidden']);
        $expired = $this->banner($admin, 'published', [
            'title' => 'Expired banner hidden',
            'published_until' => now()->subDay(),
        ]);
        $future = $this->banner($admin, 'published', [
            'title' => 'Future banner hidden',
            'published_from' => now()->addDay(),
        ]);
        $approved = $this->banner($admin, 'approved', ['title' => 'Approved unpublished banner']);

        $live = $this->banner($admin, 'published', [
            'title' => 'Live uncleared image banner',
            'banner_type' => CmsHomepageBanner::TYPE_FEATURE,
            'image_alt' => 'Should not appear as a public image',
        ]);
        $this->attachImage($live, $admin, 'secret.jpg', false);

        $home = $this->get(route('home'));
        $home->assertOk();
        $home->assertDontSee('Draft banner hidden', false);
        $home->assertDontSee('Expired banner hidden', false);
        $home->assertDontSee('Future banner hidden', false);
        $home->assertDontSee('Approved unpublished banner', false);
        $home->assertSee('Live uncleared image banner', false);
        $home->assertSee('Image not yet available', false);
        $home->assertDontSee(route('public.media', $live->fresh()->image->uuid), false);
        $this->get(route('public.media', $live->fresh()->image->uuid))->assertNotFound();

        $asset = $live->fresh()->image;
        $asset->is_cleared_for_public = true;
        $asset->save();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('public.media', $asset->uuid), false)
            ->assertSee('Should not appear as a public image', false);
        $this->get(route('public.media', $asset->uuid))->assertOk();
    }

    public function test_listing_shows_type_author_order_and_viewers_are_denied(): void
    {
        $admin = $this->websiteAdmin();
        $this->banner($admin, 'draft', [
            'title' => 'Listing banner',
            'banner_type' => CmsHomepageBanner::TYPE_HERO_NOTICE,
            'button_text' => 'Listing button',
            'link_url' => 'https://example.gov.ng/listing',
            'sort_order' => 4,
        ]);

        $page = $this->actingAs($admin)->get(route('cms.homepage-banners.index'));
        $page->assertOk();
        $page->assertSee('Listing banner', false);
        $page->assertSee('Hero Banner', false);
        $page->assertSee($admin->name, false);
        $page->assertSee('4', false);
        $page->assertSee('View', false);
        $page->assertSee('Edit', false);

        $this->actingAs($admin)
            ->get(route('cms.homepage-banners.show', CmsHomepageBanner::query()->first()))
            ->assertOk()
            ->assertSee('Listing button', false)
            ->assertSee('https://example.gov.ng/listing', false);

        $this->actingAs($this->user('Viewer'))
            ->get(route('cms.homepage-banners.index'))
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
    private function banner(User $owner, string $status, array $overrides = []): CmsHomepageBanner
    {
        unset($overrides['slug_ignore']);
        $banner = CmsHomepageBanner::create(array_merge([
            'title' => 'Banner '.uniqid(),
            'body' => 'Supporting text for the banner.',
            'banner_type' => CmsHomepageBanner::TYPE_FEATURE,
            'sort_order' => 0,
        ], $overrides));
        $banner->created_by = $owner->id;
        $banner->status = $status;
        $banner->published_at = $status === 'published' ? now()->subHour() : null;
        $banner->save();

        return $banner->fresh();
    }

    private function attachImage(CmsHomepageBanner $banner, User $uploader, string $name, bool $cleared): CmsMediaAsset
    {
        $asset = new CmsMediaAsset;
        $asset->uuid = (string) \Illuminate\Support\Str::uuid();
        $asset->disk = 'local';
        $asset->path = 'cms/media/'.$asset->uuid.'.jpg';
        Storage::disk('local')->put($asset->path, $this->jpegBytes());
        $asset->original_name = $name;
        $asset->mime = 'image/jpeg';
        $asset->size_bytes = 100;
        $asset->checksum_sha256 = hash('sha256', 'x');
        $asset->uploaded_by = $uploader->id;
        $asset->is_cleared_for_public = $cleared;
        $asset->save();

        $banner->image_media_id = $asset->id;
        $banner->save();

        return $asset;
    }

    private function attachClearedImage(CmsHomepageBanner $banner, User $uploader, string $name): CmsMediaAsset
    {
        return $this->attachImage($banner, $uploader, $name, true);
    }

    private function jpegBytes(): string
    {
        $image = imagecreatetruecolor(12, 12);
        ob_start();
        imagejpeg($image);
        imagedestroy($image);

        return (string) ob_get_clean();
    }
}
