<?php

namespace App\Http\Controllers\Cms;

use App\Cms\CmsMediaUploader;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\StoreCmsGalleryItemRequest;
use App\Http\Requests\Cms\UpdateCmsGalleryItemRequest;
use App\Models\CmsGalleryAlbum;
use App\Models\CmsGalleryItem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CmsGalleryItemController extends Controller
{
    use AuthorizesRequests;

    public function store(
        StoreCmsGalleryItemRequest $request,
        CmsGalleryAlbum $album,
        CmsMediaUploader $uploader,
    ): RedirectResponse {
        $this->authorize('view', $album);

        $file = $request->file('image');
        if (! $file instanceof UploadedFile) {
            return back()->withErrors(['image' => 'An image is required.']);
        }

        DB::transaction(function () use ($request, $album, $uploader, $file) {
            $media = $uploader->store($file, $request->user());

            $item = new CmsGalleryItem;
            $item->album_id = $album->id;
            $item->media_id = $media->id;
            $item->caption = $request->input('caption');
            $item->sort_order = (int) $request->input('sort_order', 0);
            $item->created_by = $request->user()->id;
            $item->updated_by = $request->user()->id;
            $item->save();
        });

        return redirect()
            ->route('cms.gallery-albums.show', $album)
            ->with('success', 'Gallery item added as a draft.');
    }

    public function update(UpdateCmsGalleryItemRequest $request, CmsGalleryAlbum $album, CmsGalleryItem $item): RedirectResponse
    {
        abort_unless((int) $item->album_id === (int) $album->id, 404);

        $item->fill($request->contentPayload());
        $item->updated_by = $request->user()->id;
        $item->save();

        return redirect()
            ->route('cms.gallery-albums.show', $album)
            ->with('success', 'Gallery item updated.');
    }

    public function destroy(CmsGalleryAlbum $album, CmsGalleryItem $item): RedirectResponse
    {
        abort_unless((int) $item->album_id === (int) $album->id, 404);
        $this->authorize('delete', $item);
        $item->delete();

        return redirect()
            ->route('cms.gallery-albums.show', $album)
            ->with('success', 'Gallery item removed.');
    }
}
