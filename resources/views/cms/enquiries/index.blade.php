@extends('layouts.cms')
@section('title', 'Public Enquiries')
@section('content')
@include('cms.partials.flash')
<div class="cms-card overflow-hidden">
    <div class="p-6">
        <h2 class="text-2xl font-bold text-green-950">Public Enquiries</h2>
        <p class="cms-lede">Inbox for website contact submissions. This is an internal CMS screen. It does not send email replies.</p>
    </div>
    <form method="GET" action="{{ route('cms.enquiries.index') }}" class="flex flex-col gap-3 border-b p-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="q" class="mb-1 block text-sm font-medium">Search subject</label>
            <input id="q" name="q" value="{{ request('q') }}" class="cms-input">
        </div>
        <div>
            <label for="status" class="mb-1 block text-sm font-medium">Status</label>
            <select id="status" name="status" class="cms-input">
                <option value="">All</option>
                @foreach(['new','in_review','closed','spam'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <button class="cms-btn cms-btn-primary">Filter</button>
    </form>
    <div class="cms-table-wrap">
    <table class="min-w-full">
        <thead class="bg-green-700 text-white">
            <tr>
                <th class="px-4 py-3 text-left">Subject</th>
                <th class="px-4 py-3 text-left">Sender</th>
                <th class="px-4 py-3 text-left">Email</th>
                <th class="px-4 py-3 text-left">Date received</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y">
        @forelse($enquiries as $enquiry)
            <tr>
                <td class="px-4 py-3">
                    <a class="font-medium text-green-800 underline" href="{{ route('cms.enquiries.show', $enquiry) }}">{{ $enquiry->subject }}</a>
                </td>
                <td class="px-4 py-3">{{ $enquiry->name }}</td>
                <td class="px-4 py-3">{{ $enquiry->email }}</td>
                <td class="px-4 py-3">{{ $enquiry->received_at?->format('d M Y H:i') }}</td>
                <td class="px-4 py-3">@include('cms.partials.enquiry-status', ['status' => $enquiry->status])</td>
                <td class="px-4 py-3"><a class="cms-btn cms-btn-primary" href="{{ route('cms.enquiries.show', $enquiry) }}">View</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-600">No enquiries match the current filters.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <div class="p-4">{{ $enquiries->links() }}</div>
</div>
@endsection
