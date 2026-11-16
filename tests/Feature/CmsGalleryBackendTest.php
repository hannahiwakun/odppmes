<?php

namespace Tests\Feature;

use App\Models\CmsGalleryAlbum;
use App\Models\CmsMediaAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CmsGalleryBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_website_administrator_can_create_an_album_and_upload_a_private_jpeg(): void
    {
        Storage::fake('local');
        $admin = $this->websiteAdmin();

        $this->actingAs($admin)
            ->post(route('cms.gallery-albums.store'), [
                'title' => 'Field visits',
                'description' => 'Public gallery album',
            ])
            ->assertRedirect();

        $album = CmsGalleryAlbum::query()->first();
        $this->assertSame('draft', $album->status);

        $file = UploadedFile::fake()->image('visit.jpg', 80, 60);

        $this->actingAs($admin)
            ->post(route('cms.gallery-albums.items.store', $album), [
                'image' => $file,
                'caption' => 'A site visit',
            ])
            ->assertRedirect(route('cms.gallery-albums.show', $album));

        $asset = CmsMediaAsset::query()->first();
        $this->assertNotNull($asset);
        $this->assertSame('local', $asset->disk);
        $this->assertStringStartsWith('cms/media/', $asset->path);
        $this->assertSame('visit.jpg', $asset->original_name);
        Storage::disk('local')->assertExists($asset->path);

        $show = $this->actingAs($admin)->get(route('cms.gallery-albums.show', $album));
        $show->assertOk();
        $show->assertSee('visit.jpg', false);
        $show->assertSee('How photographs are published', false);
        $show->assertSee('Maximum file size: 5 MB', false);
        $show->assertSee('This file is larger than 5 MB', false);
        $show->assertDontSee($asset->path, false);

        $listing = $this->actingAs($admin)->get(route('cms.gallery-albums.index'));
        $listing->assertOk();
        $listing->assertSee('Field visits', false);
        $listing->assertSee($admin->name, false);
        $listing->assertSee('Date created', false);
        $listing->assertSee('Submit for Approval', false);
        $listing->assertSee(route('cms.media.preview', $asset), false);
        $listing->assertDontSee($asset->path, false);

        $preview = $this->actingAs($admin)->get(route('cms.media.preview', $asset));
        $preview->assertOk();
        $preview->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_svg_and_html_uploads_are_rejected(): void
    {
        Storage::fake('local');
        $admin = $this->websiteAdmin();
        $album = $this->album($admin);

        $this->actingAs($admin)
            ->from(route('cms.gallery-albums.show', $album))
            ->post(route('cms.gallery-albums.items.store', $album), [
                'image' => UploadedFile::fake()->create('payload.svg', 20, 'image/svg+xml'),
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('image');

        $this->assertSame(0, CmsMediaAsset::query()->count());
    }

    public function test_other_roles_cannot_manage_the_gallery(): void
    {
        $album = $this->album($this->websiteAdmin());

        $this->actingAs($this->user('MDA Officer'))
            ->get(route('cms.gallery-albums.index'))
            ->assertForbidden();

        $this->actingAs($this->user('MDA Officer'))
            ->post(route('cms.gallery-albums.items.store', $album), [])
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

    private function album(User $owner): CmsGalleryAlbum
    {
        $album = CmsGalleryAlbum::create([
            'slug' => 'album-'.uniqid(),
            'title' => 'Album',
            'description' => 'Desc',
            'sort_order' => 0,
        ]);
        $album->created_by = $owner->id;
        $album->save();

        return $album->fresh();
    }
}
