<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Cms\Concerns\AppliesCmsListingFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\StoreCmsGalleryAlbumRequest;
use App\Http\Requests\Cms\UpdateCmsGalleryAlbumRequest;
use App\Models\CmsGalleryAlbum;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CmsGalleryAlbumController extends Controller
{
    use AppliesCmsListingFilters;
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CmsGalleryAlbum::class);

        $albums = $this->applyListingFilters(
            CmsGalleryAlbum::query()
                ->with(['createdBy', 'items.media'])
                ->withCount('items'),
            $request,
            ['title', 'slug', 'description']
        )->paginate(15)->withQueryString();

        return view('cms.gallery-albums.index', compact('albums'));
    }

    public function create(): View
    {
        $this->authorize('create', CmsGalleryAlbum::class);

        return view('cms.gallery-albums.create');
    }

    public function store(StoreCmsGalleryAlbumRequest $request): RedirectResponse
    {
        $album = new CmsGalleryAlbum;
        $album->fill($request->contentPayload());
        $album->created_by = $request->user()->id;
        $album->updated_by = $request->user()->id;
        $album->save();

        return redirect()
            ->route('cms.gallery-albums.show', $album)
            ->with('success', 'Gallery album saved as a draft.');
    }

    public function show(CmsGalleryAlbum $album): View
    {
        $this->authorize('view', $album);

        $album->load(['createdBy', 'reviewedBy', 'workflowEvents.actor', 'items.media', 'items.workflowEvents.actor']);

        return view('cms.gallery-albums.show', compact('album'));
    }

    public function edit(CmsGalleryAlbum $album): View
    {
        $this->authorize('update', $album);

        return view('cms.gallery-albums.edit', compact('album'));
    }

    public function update(UpdateCmsGalleryAlbumRequest $request, CmsGalleryAlbum $album): RedirectResponse
    {
        $album->fill($request->contentPayload());
        $album->updated_by = $request->user()->id;
        $album->save();

        return redirect()
            ->route('cms.gallery-albums.show', $album)
            ->with('success', 'Gallery album updated.');
    }

    public function destroy(CmsGalleryAlbum $album): RedirectResponse
    {
        $this->authorize('delete', $album);

        DB::transaction(function () use ($album) {
            $album->items()->each(fn ($item) => $item->delete());
            $album->delete();
        });

        return redirect()
            ->route('cms.gallery-albums.index')
            ->with('success', 'Gallery album removed.');
    }
}
