@php
    $status = $content->status;
    $user = auth()->user();
@endphp
<div class="mt-6 space-y-4 rounded-xl border border-green-100 bg-emerald-50/50 p-4">
    <h3 class="text-lg font-semibold text-green-950">Publishing workflow</h3>
    <p class="text-sm text-gray-600">Draft → Submitted → Approved → Published. Saving a draft does not submit or publish this record.</p>
    <p class="text-sm text-gray-700">Current status: @include('cms.partials.status', ['status' => $status])</p>

    @if($content->rejected_reason)
        <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
            <p class="font-semibold">Rejection reason</p>
            <p class="mt-1 whitespace-pre-wrap">{{ $content->rejected_reason }}</p>
        </div>
    @endif

    @if(in_array($status, ['draft', 'rejected'], true) && $user->can('submit', $content))
        <form method="POST" action="{{ route('cms.workflow.submit', ['type' => $type, 'id' => $content->id]) }}" onsubmit="return confirm('Submit this content for Super Administrator approval?');">
            @csrf
            <button type="submit" class="cms-btn cms-btn-primary">
                {{ $status === 'rejected' ? 'Resubmit for approval' : 'Submit for approval' }}
            </button>
        </form>
    @endif

    @if($status === 'submitted' && $user->can('approve', $content))
        <div class="flex flex-col gap-4 lg:flex-row">
            <form method="POST" action="{{ route('cms.workflow.approve', ['type' => $type, 'id' => $content->id]) }}" class="space-y-2">
                @csrf
                <p class="text-sm text-gray-600">Approval confirms the content is suitable. It does not publish it immediately.</p>
                <button type="submit" class="cms-btn cms-btn-primary">Approve</button>
            </form>
            <form method="POST" action="{{ route('cms.workflow.reject', ['type' => $type, 'id' => $content->id]) }}" class="min-w-0 flex-1 space-y-2">
                @csrf
                <label for="reject-reason-{{ $content->id }}" class="block text-sm font-medium text-gray-700">Rejection reason <span class="font-semibold text-red-700" aria-hidden="true">*</span><span class="sr-only">(required)</span></label>
                <p class="text-sm text-gray-600">Explain what must be corrected. The author will see this reason and can revise the draft.</p>
                <textarea id="reject-reason-{{ $content->id }}" name="reason" required rows="3"
                          class="cms-input"></textarea>
                <button type="submit" class="cms-btn cms-btn-danger">Reject</button>
            </form>
        </div>
    @elseif($status === 'submitted' && $user->isWebsiteAdmin())
        <p class="text-sm text-gray-600">This item is waiting for Super Administrator review. You cannot approve, reject or publish it.</p>
    @endif

    @if($status === 'approved' && $user->can('publish', $content))
        <form method="POST" action="{{ route('cms.workflow.publish', ['type' => $type, 'id' => $content->id]) }}" onsubmit="return confirm('Publish this content to the public website?');">
            @csrf
            <p class="mb-2 text-sm text-gray-600">Publishing makes this content visible on the public website.</p>
            <button type="submit" class="cms-btn cms-btn-publish">Publish to website</button>
        </form>
    @endif

    @if($status === 'published' && $user->can('unpublish', $content))
        <form method="POST" action="{{ route('cms.workflow.unpublish', ['type' => $type, 'id' => $content->id]) }}">
            @csrf
            <button type="submit" class="cms-btn cms-btn-secondary">Unpublish</button>
        </form>
    @endif

    @if(in_array($status, ['approved', 'published'], true) && $user->can('archive', $content))
        <form method="POST" action="{{ route('cms.workflow.archive', ['type' => $type, 'id' => $content->id]) }}" onsubmit="return confirm('Archive this content?');">
            @csrf
            <button type="submit" class="cms-btn cms-btn-secondary">Archive</button>
        </form>
    @endif

    @if($status === 'archived' && $user->can('restore', $content))
        <form method="POST" action="{{ route('cms.workflow.restore', ['type' => $type, 'id' => $content->id]) }}">
            @csrf
            <button type="submit" class="cms-btn cms-btn-primary">Restore</button>
        </form>
    @endif

    @if($content->relationLoaded('workflowEvents') && $content->workflowEvents->isNotEmpty())
        <div>
            <h4 class="text-sm font-semibold text-gray-800">History</h4>
            <ul class="mt-2 space-y-1 text-sm text-gray-700">
                @foreach($content->workflowEvents as $event)
                    <li>
                        {{ $event->from_status ?? 'none' }} → {{ $event->to_status }}
                        @if($event->actor)
                            by {{ $event->actor->name }}
                        @endif
                        @if($event->created_at)
                            on {{ $event->created_at->format('d M Y H:i') }}
                        @endif
                        @if($event->comment)
                            — {{ $event->comment }}
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
