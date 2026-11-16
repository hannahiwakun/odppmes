<?php

namespace App\Http\Controllers\Cms;

use App\Cms\CmsContentTypes;
use App\Cms\CmsWorkflowStatus;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class CmsApprovalCentreController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $queues = [];

        foreach (CmsContentTypes::MAP as $type => $class) {
            $items = $class::query()
                ->with('createdBy')
                ->where('status', CmsWorkflowStatus::SUBMITTED)
                ->latest()
                ->limit(50)
                ->get();

            if ($items->isEmpty()) {
                continue;
            }

            $queues[] = [
                'type' => $type,
                'label' => CmsContentTypes::labels()[$type] ?? $type,
                'items' => $items,
            ];
        }

        return view('cms.approvals.index', compact('queues'));
    }
}
