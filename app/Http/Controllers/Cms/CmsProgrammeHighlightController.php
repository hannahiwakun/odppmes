<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Cms\Concerns\AppliesCmsListingFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\StoreCmsProgrammeHighlightRequest;
use App\Http\Requests\Cms\UpdateCmsProgrammeHighlightRequest;
use App\Models\CmsProgrammeHighlight;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CmsProgrammeHighlightController extends Controller
{
    use AppliesCmsListingFilters;
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CmsProgrammeHighlight::class);

        $highlights = $this->applyListingFilters(
            CmsProgrammeHighlight::query(),
            $request,
            ['title', 'slug', 'implementing_mda_name', 'partner_name', 'sector', 'location']
        )->paginate(15)->withQueryString();

        return view('cms.programme-highlights.index', compact('highlights'));
    }

    public function create(): View
    {
        $this->authorize('create', CmsProgrammeHighlight::class);

        return view('cms.programme-highlights.create');
    }

    public function store(StoreCmsProgrammeHighlightRequest $request): RedirectResponse
    {
        $highlight = new CmsProgrammeHighlight;
        $highlight->fill($request->contentPayload());
        $highlight->created_by = $request->user()->id;
        $highlight->updated_by = $request->user()->id;
        $highlight->save();

        return redirect()
            ->route('cms.programme-highlights.show', $highlight)
            ->with('success', 'Programme highlight saved as a draft.');
    }

    public function show(CmsProgrammeHighlight $highlight): View
    {
        $this->authorize('view', $highlight);
        $highlight->load(['createdBy', 'reviewedBy', 'workflowEvents.actor']);

        return view('cms.programme-highlights.show', compact('highlight'));
    }

    public function edit(CmsProgrammeHighlight $highlight): View
    {
        $this->authorize('update', $highlight);

        return view('cms.programme-highlights.edit', compact('highlight'));
    }

    public function update(UpdateCmsProgrammeHighlightRequest $request, CmsProgrammeHighlight $highlight): RedirectResponse
    {
        $highlight->fill($request->contentPayload());
        $highlight->updated_by = $request->user()->id;
        $highlight->save();

        return redirect()
            ->route('cms.programme-highlights.show', $highlight)
            ->with('success', 'Programme highlight updated.');
    }

    public function destroy(CmsProgrammeHighlight $highlight): RedirectResponse
    {
        $this->authorize('delete', $highlight);
        $highlight->delete();

        return redirect()
            ->route('cms.programme-highlights.index')
            ->with('success', 'Programme highlight removed.');
    }
}
