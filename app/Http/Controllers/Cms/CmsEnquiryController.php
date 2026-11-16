<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\UpdateCmsEnquiryRequest;
use App\Models\CmsEnquiry;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CmsEnquiryController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CmsEnquiry::class);

        $query = CmsEnquiry::query()->latest('received_at');

        if ($request->filled('status') && in_array($request->string('status')->toString(), [
            CmsEnquiry::STATUS_NEW,
            CmsEnquiry::STATUS_IN_REVIEW,
            CmsEnquiry::STATUS_CLOSED,
            CmsEnquiry::STATUS_SPAM,
        ], true)) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->toString().'%';
            $query->where(function ($inner) use ($term) {
                $inner->where('subject', 'like', $term);
            });
        }

        $enquiries = $query->paginate(15)->withQueryString();

        return view('cms.enquiries.index', compact('enquiries'));
    }

    public function show(CmsEnquiry $enquiry): View
    {
        $this->authorize('view', $enquiry);
        $enquiry->load('assignee');

        return view('cms.enquiries.show', compact('enquiry'));
    }

    public function update(UpdateCmsEnquiryRequest $request, CmsEnquiry $enquiry): RedirectResponse
    {
        $enquiry->status = $request->validated('status');
        $enquiry->internal_notes = $request->validated('internal_notes');
        if ($enquiry->status === CmsEnquiry::STATUS_CLOSED) {
            $enquiry->handled_at = now();
        }
        $enquiry->save();

        return redirect()
            ->route('cms.enquiries.show', $enquiry)
            ->with('success', 'Enquiry updated.');
    }
}
