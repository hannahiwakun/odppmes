@extends('layouts.cms')
@section('title', 'Enquiry')
@section('content')
@include('cms.partials.flash')
<div class="cms-card max-w-4xl space-y-6 p-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-green-950">{{ $enquiry->subject }}</h2>
            <p class="cms-lede">Received {{ $enquiry->received_at?->format('d M Y H:i') ?? 'date not recorded' }}</p>
        </div>
        @include('cms.partials.enquiry-status', ['status' => $enquiry->status])
    </div>

    <dl class="grid gap-4 text-sm sm:grid-cols-2">
        <div>
            <dt class="font-semibold text-gray-800">Sender</dt>
            <dd>{{ $enquiry->name }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-gray-800">Email</dt>
            <dd>{{ $enquiry->email }}</dd>
        </div>
        @if($enquiry->organisation)
            <div>
                <dt class="font-semibold text-gray-800">Organisation</dt>
                <dd>{{ $enquiry->organisation }}</dd>
            </div>
        @endif
        <div>
            <dt class="font-semibold text-gray-800">Status</dt>
            <dd>@include('cms.partials.enquiry-status', ['status' => $enquiry->status])</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="font-semibold text-gray-800">Message from the public</dt>
            <dd class="mt-1 whitespace-pre-wrap rounded-lg border border-green-100 bg-green-50/40 p-3 text-gray-800">{{ $enquiry->message }}</dd>
        </div>
    </dl>

    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">
        <p class="font-semibold text-gray-900">Internal notes are not shown to the public</p>
        <p class="mt-1">Notes saved here stay in the CMS. This screen does not send email. Use authorised office processes if a reply is required.</p>
        @if($enquiry->internal_notes)
            <p class="mt-3 whitespace-pre-wrap text-gray-800">{{ $enquiry->internal_notes }}</p>
        @else
            <p class="mt-3 text-gray-600">No internal notes have been recorded.</p>
        @endif
    </div>

    @can('update', $enquiry)
        <form method="POST" action="{{ route('cms.enquiries.update', $enquiry) }}" class="space-y-3 border-t pt-4">
            @csrf
            @method('PATCH')
            <x-cms.label for="status">Status</x-cms.label>
            <select id="status" name="status" class="cms-input max-w-xs">
                @foreach(['new','in_review','closed','spam'] as $status)
                    <option value="{{ $status }}" @selected($enquiry->status === $status)>{{ str_replace('_', ' ', $status) }}</option>
                @endforeach
            </select>
            <x-cms.label for="internal_notes">Internal notes</x-cms.label>
            <x-cms.help>Staff only. These notes are never published on the public website.</x-cms.help>
            <textarea id="internal_notes" name="internal_notes" rows="4" class="cms-input mt-1">{{ old('internal_notes', $enquiry->internal_notes) }}</textarea>
            <button class="cms-btn cms-btn-primary">Update enquiry</button>
        </form>
    @endcan

    <a href="{{ route('cms.enquiries.index') }}" class="cms-btn cms-btn-secondary">Return to inbox</a>
</div>
@endsection
