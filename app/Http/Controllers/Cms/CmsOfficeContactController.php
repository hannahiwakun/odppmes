<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\UpsertCmsOfficeContactRequest;
use App\Models\CmsOfficeContact;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CmsOfficeContactController extends Controller
{
    use AuthorizesRequests;

    public function show(): View
    {
        $this->authorize('viewAny', CmsOfficeContact::class);

        $contact = CmsOfficeContact::query()->first();

        if ($contact === null) {
            $this->authorize('create', CmsOfficeContact::class);

            return view('cms.office-contact.create');
        }

        $this->authorize('view', $contact);
        $contact->load(['createdBy', 'reviewedBy', 'workflowEvents.actor']);

        return view('cms.office-contact.show', compact('contact'));
    }

    public function edit(): View|RedirectResponse
    {
        $contact = CmsOfficeContact::query()->first();

        if ($contact === null) {
            $this->authorize('create', CmsOfficeContact::class);

            return view('cms.office-contact.create');
        }

        $this->authorize('update', $contact);

        return view('cms.office-contact.edit', compact('contact'));
    }

    public function store(UpsertCmsOfficeContactRequest $request): RedirectResponse
    {
        if (CmsOfficeContact::query()->exists()) {
            return redirect()
                ->route('cms.office-contact.show')
                ->with('error', 'Office contact information already exists. Edit the existing record.');
        }

        $contact = new CmsOfficeContact;
        $contact->fill($request->contentPayload());
        $contact->created_by = $request->user()->id;
        $contact->updated_by = $request->user()->id;
        $contact->save();

        return redirect()
            ->route('cms.office-contact.show')
            ->with('success', 'Office contact information saved as a draft.');
    }

    public function update(UpsertCmsOfficeContactRequest $request, CmsOfficeContact $office_contact): RedirectResponse
    {
        $office_contact->fill($request->contentPayload());
        $office_contact->updated_by = $request->user()->id;
        $office_contact->save();

        return redirect()
            ->route('cms.office-contact.show')
            ->with('success', 'Office contact information updated.');
    }
}
