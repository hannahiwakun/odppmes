<?php

namespace App\Http\Controllers;

use App\Cms\CmsContentTypes;
use App\Cms\CmsWorkflowManager;
use App\Http\Requests\CmsRejectContentRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CmsWorkflowController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly CmsWorkflowManager $workflow)
    {
    }

    public function submit(Request $request, string $type, int $id): RedirectResponse
    {
        $content = $this->content($type, $id);
        $this->authorize('submit', $content);
        $this->workflow->submit($content, $request->user());

        return back()->with('success', 'Content submitted for approval.');
    }

    public function approve(Request $request, string $type, int $id): RedirectResponse
    {
        $content = $this->content($type, $id);
        $this->authorize('approve', $content);
        $this->workflow->approve($content, $request->user());

        return back()->with('success', 'Content approved.');
    }

    public function reject(CmsRejectContentRequest $request, string $type, int $id): RedirectResponse
    {
        $content = $this->content($type, $id);
        $this->authorize('reject', $content);
        $this->workflow->reject($content, $request->user(), $request->validated('reason'));

        return back()->with('success', 'Content rejected.');
    }

    public function publish(Request $request, string $type, int $id): RedirectResponse
    {
        $content = $this->content($type, $id);
        $this->authorize('publish', $content);
        $this->workflow->publish($content, $request->user());

        return back()->with('success', 'Content published.');
    }

    public function unpublish(Request $request, string $type, int $id): RedirectResponse
    {
        $content = $this->content($type, $id);
        $this->authorize('unpublish', $content);
        $this->workflow->unpublish($content, $request->user());

        return back()->with('success', 'Content unpublished.');
    }

    public function archive(Request $request, string $type, int $id): RedirectResponse
    {
        $content = $this->content($type, $id);
        $this->authorize('archive', $content);
        $this->workflow->archive($content, $request->user());

        return back()->with('success', 'Content archived.');
    }

    public function restore(Request $request, string $type, int $id): RedirectResponse
    {
        $content = $this->content($type, $id);
        $this->authorize('restore', $content);
        $this->workflow->restore($content, $request->user());

        return back()->with('success', 'Content restored.');
    }

    private function content(string $type, int $id): Model
    {
        $class = CmsContentTypes::modelClass($type);

        return $class::query()->findOrFail($id);
    }
}
