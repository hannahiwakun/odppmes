<?php

namespace App\Http\Controllers\Cms;

use App\Cms\CmsMediaUploader;
use App\Http\Controllers\Cms\Concerns\AppliesCmsListingFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\StoreCmsPublicPartnerRequest;
use App\Http\Requests\Cms\UpdateCmsPublicPartnerRequest;
use App\Models\CmsPublicPartner;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class CmsPublicPartnerController extends Controller
{
    use AppliesCmsListingFilters;
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CmsPublicPartner::class);

        $partners = $this->applyListingFilters(
            CmsPublicPartner::query(),
            $request,
            ['name', 'slug', 'category', 'summary']
        )->paginate(15)->withQueryString();

        return view('cms.public-partners.index', compact('partners'));
    }

    public function create(): View
    {
        $this->authorize('create', CmsPublicPartner::class);

        return view('cms.public-partners.create');
    }

    public function store(StoreCmsPublicPartnerRequest $request, CmsMediaUploader $uploader): RedirectResponse
    {
        $partner = new CmsPublicPartner;
        $partner->fill($request->contentPayload());
        $partner->created_by = $request->user()->id;
        $partner->updated_by = $request->user()->id;
        if ($request->file('image') instanceof UploadedFile) {
            $partner->logo_media_id = $uploader->store($request->file('image'), $request->user())->id;
        }
        $partner->save();

        return redirect()
            ->route('cms.public-partners.show', $partner)
            ->with('success', 'Public partner saved as a draft.');
    }

    public function show(CmsPublicPartner $partner): View
    {
        $this->authorize('view', $partner);
        $partner->load(['createdBy', 'reviewedBy', 'logo', 'workflowEvents.actor']);

        return view('cms.public-partners.show', compact('partner'));
    }

    public function edit(CmsPublicPartner $partner): View
    {
        $this->authorize('update', $partner);
        $partner->load('logo');

        return view('cms.public-partners.edit', compact('partner'));
    }

    public function update(UpdateCmsPublicPartnerRequest $request, CmsPublicPartner $partner, CmsMediaUploader $uploader): RedirectResponse
    {
        $partner->fill($request->contentPayload());
        $partner->updated_by = $request->user()->id;
        if ($request->file('image') instanceof UploadedFile) {
            $partner->logo_media_id = $uploader->store($request->file('image'), $request->user())->id;
        }
        $partner->save();

        return redirect()
            ->route('cms.public-partners.show', $partner)
            ->with('success', 'Public partner updated.');
    }

    public function destroy(CmsPublicPartner $partner): RedirectResponse
    {
        $this->authorize('delete', $partner);
        $partner->delete();

        return redirect()
            ->route('cms.public-partners.index')
            ->with('success', 'Public partner removed.');
    }
}
