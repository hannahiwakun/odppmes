<?php

namespace App\Http\Controllers\Cms;

use App\Cms\CmsMediaUploader;
use App\Http\Controllers\Cms\Concerns\AppliesCmsListingFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\StoreCmsHomepageBannerRequest;
use App\Http\Requests\Cms\UpdateCmsHomepageBannerRequest;
use App\Models\CmsHomepageBanner;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class CmsHomepageBannerController extends Controller
{
    use AppliesCmsListingFilters;
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CmsHomepageBanner::class);

        $banners = $this->applyListingFilters(
            CmsHomepageBanner::query()->with(['createdBy', 'image']),
            $request,
            ['title', 'body', 'banner_type', 'button_text', 'link_url']
        )->paginate(15)->withQueryString();

        return view('cms.homepage-banners.index', compact('banners'));
    }

    public function create(): View
    {
        $this->authorize('create', CmsHomepageBanner::class);

        return view('cms.homepage-banners.create');
    }

    public function store(StoreCmsHomepageBannerRequest $request, CmsMediaUploader $uploader): RedirectResponse
    {
        $banner = new CmsHomepageBanner;
        $banner->fill($request->contentPayload());
        $banner->created_by = $request->user()->id;
        $banner->updated_by = $request->user()->id;
        $this->attachImage($request->file('image'), $request->user(), $uploader, $banner);
        $banner->save();

        return redirect()
            ->route('cms.homepage-banners.show', $banner)
            ->with('success', 'Homepage banner saved as a draft.');
    }

    public function show(CmsHomepageBanner $banner): View
    {
        $this->authorize('view', $banner);
        $banner->load(['createdBy', 'reviewedBy', 'image', 'workflowEvents.actor']);

        return view('cms.homepage-banners.show', compact('banner'));
    }

    public function edit(CmsHomepageBanner $banner): View
    {
        $this->authorize('update', $banner);
        $banner->load('image');

        return view('cms.homepage-banners.edit', compact('banner'));
    }

    public function update(UpdateCmsHomepageBannerRequest $request, CmsHomepageBanner $banner, CmsMediaUploader $uploader): RedirectResponse
    {
        $banner->fill($request->contentPayload());
        $banner->updated_by = $request->user()->id;
        $this->attachImage($request->file('image'), $request->user(), $uploader, $banner);
        $banner->save();

        return redirect()
            ->route('cms.homepage-banners.show', $banner)
            ->with('success', 'Homepage banner updated.');
    }

    public function destroy(CmsHomepageBanner $banner): RedirectResponse
    {
        $this->authorize('delete', $banner);
        $banner->delete();

        return redirect()
            ->route('cms.homepage-banners.index')
            ->with('success', 'Homepage banner removed.');
    }

    private function attachImage(?UploadedFile $file, User $user, CmsMediaUploader $uploader, CmsHomepageBanner $banner): void
    {
        if (! $file instanceof UploadedFile) {
            return;
        }

        $banner->image_media_id = $uploader->store($file, $user)->id;
    }
}
