<?php

namespace Tests\Feature;

use App\Models\CmsGalleryAlbum;
use App\Models\CmsGalleryItem;
use App\Models\CmsManagementProfile;
use App\Models\CmsMediaAsset;
use App\Models\CmsNewsArticle;
use App\Models\CmsOfficeContact;
use App\Models\CmsProgrammeHighlight;
use App\Models\CmsPublicPartner;
use App\Models\DevelopmentPartner;
use App\Models\Mda;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicCmsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_news_is_visible_and_other_statuses_are_hidden(): void
    {
        $published = $this->news('published-notice', 'Published clinic notice', 'published');
        $draft = $this->news('draft-notice', 'Draft clinic notice', 'draft');
        $submitted = $this->news('submitted-notice', 'Submitted clinic notice', 'submitted');
        $rejected = $this->news('rejected-notice', 'Rejected clinic notice', 'rejected');
        $approved = $this->news('approved-notice', 'Approved clinic notice', 'approved');
        $expired = $this->news('expired-notice', 'Expired clinic notice', 'published');
        $expired->published_until = now()->subDay();
        $expired->save();
        $deleted = $this->news('deleted-notice', 'Deleted clinic notice', 'published');
        $deleted->delete();

        $index = $this->get(route('public.news'));
        $index->assertOk();
        $index->assertSee('Published clinic notice', false);
        $index->assertDontSee('Draft clinic notice', false);
        $index->assertDontSee('Submitted clinic notice', false);
        $index->assertDontSee('Rejected clinic notice', false);
        $index->assertDontSee('Approved clinic notice', false);
        $index->assertDontSee('Expired clinic notice', false);
        $index->assertDontSee('Deleted clinic notice', false);

        $this->get(route('public.news.show', $published->slug))
            ->assertOk()
            ->assertSee('Published clinic notice', false);

        $this->get(route('public.news.show', $draft->slug))->assertNotFound();
        $this->get(route('public.news.show', $submitted->slug))->assertNotFound();
        $this->get(route('public.news.show', $rejected->slug))->assertNotFound();
        $this->get(route('public.news.show', $approved->slug))->assertNotFound();
        $this->get(route('public.news.show', $expired->slug))->assertNotFound();
        $this->get(route('public.news.show', $deleted->slug))->assertNotFound();
    }

    public function test_gallery_publication_and_media_rules(): void
    {
        Storage::fake('local');
        $cleared = $this->media(true);
        $uncleared = $this->media(false);
        $publishedAlbum = $this->album('Published album', 'published');
        $draftAlbum = $this->album('Hidden album', 'draft');
        $this->item($publishedAlbum, $cleared, 'published', 'Public caption');
        $this->item($publishedAlbum, $uncleared, 'published', 'Uncleared caption');
        $this->item($draftAlbum, $cleared, 'published', 'Draft album image');

        $deletedAlbum = $this->album('Removed album', 'published');
        $deletedItem = $this->item($deletedAlbum, $cleared, 'published', 'Removed item');
        $deletedAlbum->delete();
        $deletedItem->delete();

        $gallery = $this->get(route('public.gallery'));
        $gallery->assertOk();
        $gallery->assertSee('Published album', false);
        $gallery->assertSee('Public caption', false);
        $gallery->assertSee('View photographs', false);
        $gallery->assertSee('id="album-'.$publishedAlbum->slug.'"', false);
        $gallery->assertSee(route('public.media', $cleared->uuid), false);
        $gallery->assertDontSee('Hidden album', false);
        $gallery->assertDontSee('Uncleared caption', false);
        $gallery->assertDontSee('Removed album', false);
        $gallery->assertDontSee($uncleared->path, false);
        $gallery->assertDontSee(route('public.media', $uncleared->uuid), false);

        $this->get(route('public.media', $cleared->uuid))->assertOk();
        $this->assertNotNull($this->get(route('public.media', $cleared->uuid))->headers->get('X-Content-Type-Options'));
        $this->get(route('public.media', $uncleared->uuid))->assertNotFound();
        $this->get(route('public.media', (string) Str::uuid()))->assertNotFound();
    }

    public function test_management_partners_highlights_and_isolation(): void
    {
        $this->profile('Dr Published', 'published');
        $this->profile('Dr Draft', 'draft');

        $publicPartner = CmsPublicPartner::create([
            'slug' => 'public-fund',
            'name' => 'Published Public Fund',
            'category' => 'Bilateral',
            'summary' => 'A published public partner profile.',
        ]);
        $publicPartner->status = 'published';
        $publicPartner->published_at = now()->subHour();
        $publicPartner->save();

        $internal = DevelopmentPartner::create([
            'partner_name' => 'Secret Internal Partner',
            'partner_type' => 'Bilateral',
        ]);

        $highlight = CmsProgrammeHighlight::create([
            'slug' => 'clinic-highlight',
            'title' => 'Published clinic highlight',
            'implementing_mda_name' => 'Ministry of Health',
            'partner_name' => 'Public partner name',
            'sector' => 'Health',
            'location' => 'Akure',
            'public_status' => 'Ongoing',
            'summary' => 'A public summary with no operational identifiers.',
        ]);
        $highlight->status = 'published';
        $highlight->published_at = now()->subHour();
        $highlight->save();

        $mda = Mda::create([
            'name' => 'Ministry of Works Test',
            'code' => 'MWT1',
            'status' => true,
        ]);
        $project = Project::create([
            'mda_id' => $mda->id,
            'project_title' => 'Confidential Rural Road Budget Line',
            'sector' => 'Infrastructure',
            'lga' => 'Akure North',
            'status' => 'Planning',
            'progress' => 0,
            'budget' => 999999,
        ]);

        $this->get(route('public.management'))
            ->assertOk()
            ->assertSee('Dr Published', false)
            ->assertDontSee('Dr Draft', false);

        $this->get(route('public.partners'))
            ->assertOk()
            ->assertSee('Published Public Fund', false)
            ->assertDontSee($internal->partner_name, false);

        $programmes = $this->get(route('public.projects'));
        $programmes->assertOk();
        $programmes->assertSee('Published clinic highlight', false);
        $programmes->assertDontSee($project->project_title, false);
        $programmes->assertDontSee('999999', false);
        $programmes->assertDontSee('approved_value', false);
    }

    public function test_homepage_and_contact_fallbacks_and_protected_cms_routes(): void
    {
        $home = $this->get(route('home'));
        $home->assertOk();
        $home->assertSee('Coordinating partnerships that deliver development for Ondo State.', false);
        $home->assertSee('Placeholder', false);
        $home->assertDontSee('href="'.route('cms.home').'"', false);

        $contact = $this->get(route('public.contact'));
        $contact->assertOk();
        $contact->assertSee('will be published here when confirmed', false);

        $draftContact = new CmsOfficeContact;
        $draftContact->fill([
            'address' => 'Secret draft address',
            'phone' => '000',
            'email' => 'draft@example.test',
            'hours' => 'Never',
        ]);
        $draftContact->save();

        $this->get(route('public.contact'))
            ->assertOk()
            ->assertDontSee('Secret draft address', false);

        $this->get(route('cms.home'))->assertRedirect(route('login'));
        $this->get(route('cms.news.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['role' => 'Viewer']))
            ->get(route('cms.news.index'))
            ->assertForbidden();
    }

    private function news(string $slug, string $title, string $status): CmsNewsArticle
    {
        $article = CmsNewsArticle::create([
            'slug' => $slug,
            'title' => $title,
            'excerpt' => 'Excerpt for '.$title,
            'body' => str_repeat('Body text. ', 8),
        ]);
        $article->status = $status;
        $article->published_at = $status === 'published' ? now()->subHour() : null;
        $article->save();

        return $article->fresh();
    }

    private function album(string $title, string $status): CmsGalleryAlbum
    {
        $album = CmsGalleryAlbum::create([
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'title' => $title,
            'description' => 'Album description',
            'sort_order' => 0,
        ]);
        $album->status = $status;
        $album->published_at = $status === 'published' ? now()->subHour() : null;
        $album->save();

        return $album->fresh();
    }

    private function item(CmsGalleryAlbum $album, CmsMediaAsset $media, string $status, string $caption): CmsGalleryItem
    {
        $item = new CmsGalleryItem;
        $item->album_id = $album->id;
        $item->media_id = $media->id;
        $item->caption = $caption;
        $item->sort_order = 0;
        $item->status = $status;
        $item->published_at = $status === 'published' ? now()->subHour() : null;
        $item->save();

        return $item->fresh();
    }

    private function media(bool $cleared): CmsMediaAsset
    {
        $asset = new CmsMediaAsset;
        $asset->uuid = (string) Str::uuid();
        $asset->disk = 'local';
        $asset->path = 'cms/media/'.$asset->uuid.'.jpg';
        Storage::disk('local')->put($asset->path, $this->jpegBytes());
        $asset->original_name = 'test.jpg';
        $asset->mime = 'image/jpeg';
        $asset->size_bytes = 100;
        $asset->checksum_sha256 = hash('sha256', 'x');
        $asset->is_cleared_for_public = $cleared;
        $asset->save();

        return $asset;
    }

    private function jpegBytes(): string
    {
        $image = imagecreatetruecolor(12, 12);
        ob_start();
        imagejpeg($image);
        imagedestroy($image);

        return (string) ob_get_clean();
    }

    private function profile(string $name, string $status): CmsManagementProfile
    {
        $profile = CmsManagementProfile::create([
            'slug' => Str::slug($name),
            'full_name' => $name,
            'designation' => 'Director',
            'biography' => str_repeat('Biography text. ', 4),
        ]);
        $profile->status = $status;
        $profile->published_at = $status === 'published' ? now()->subHour() : null;
        $profile->save();

        return $profile->fresh();
    }
}
