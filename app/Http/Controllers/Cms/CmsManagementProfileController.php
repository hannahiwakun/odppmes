<?php

namespace App\Http\Controllers\Cms;

use App\Cms\CmsMediaUploader;
use App\Http\Controllers\Cms\Concerns\AppliesCmsListingFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\StoreCmsManagementProfileRequest;
use App\Http\Requests\Cms\UpdateCmsManagementProfileRequest;
use App\Models\CmsManagementProfile;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class CmsManagementProfileController extends Controller
{
    use AppliesCmsListingFilters;
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CmsManagementProfile::class);

        $profiles = $this->applyListingFilters(
            CmsManagementProfile::query(),
            $request,
            ['full_name', 'slug', 'designation']
        )->paginate(15)->withQueryString();

        return view('cms.management-profiles.index', compact('profiles'));
    }

    public function create(): View
    {
        $this->authorize('create', CmsManagementProfile::class);

        return view('cms.management-profiles.create');
    }

    public function store(StoreCmsManagementProfileRequest $request, CmsMediaUploader $uploader): RedirectResponse
    {
        $profile = new CmsManagementProfile;
        $profile->fill($request->contentPayload());
        $profile->created_by = $request->user()->id;
        $profile->updated_by = $request->user()->id;
        $this->attachPhoto($request->file('image'), $request->user(), $uploader, $profile);
        $profile->save();

        return redirect()
            ->route('cms.management-profiles.show', $profile)
            ->with('success', 'Management profile saved as a draft.');
    }

    public function show(CmsManagementProfile $profile): View
    {
        $this->authorize('view', $profile);
        $profile->load(['createdBy', 'reviewedBy', 'photo', 'workflowEvents.actor']);

        return view('cms.management-profiles.show', compact('profile'));
    }

    public function edit(CmsManagementProfile $profile): View
    {
        $this->authorize('update', $profile);
        $profile->load('photo');

        return view('cms.management-profiles.edit', compact('profile'));
    }

    public function update(UpdateCmsManagementProfileRequest $request, CmsManagementProfile $profile, CmsMediaUploader $uploader): RedirectResponse
    {
        $profile->fill($request->contentPayload());
        $profile->updated_by = $request->user()->id;
        $this->attachPhoto($request->file('image'), $request->user(), $uploader, $profile);
        $profile->save();

        return redirect()
            ->route('cms.management-profiles.show', $profile)
            ->with('success', 'Management profile updated.');
    }

    public function destroy(CmsManagementProfile $profile): RedirectResponse
    {
        $this->authorize('delete', $profile);
        $profile->delete();

        return redirect()
            ->route('cms.management-profiles.index')
            ->with('success', 'Management profile removed.');
    }

    private function attachPhoto(mixed $file, $user, CmsMediaUploader $uploader, CmsManagementProfile $profile): void
    {
        if ($file instanceof UploadedFile) {
            $profile->photo_media_id = $uploader->store($file, $user)->id;
        }
    }
}
