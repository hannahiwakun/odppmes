@extends('layouts.cms')
@section('title', 'Programme Highlights')
@section('content')
@include('cms.partials.flash')
<div class="cms-card overflow-hidden">
    <div class="flex justify-between gap-4 p-6">
        <div>
            <h2 class="text-2xl font-bold text-green-950">Programme Highlights</h2>
            <p class="text-sm text-gray-600">Public summaries only. These records are not linked to confidential project files.</p>
        </div>
        @can('create', App\Models\CmsProgrammeHighlight::class)
            <a href="{{ route('cms.programme-highlights.create') }}" class="cms-btn cms-btn-primary">New highlight</a>
        @endcan
    </div>
    @include('cms.partials.filters', ['action' => route('cms.programme-highlights.index')])
    <div class="cms-table-wrap">
    <table class="min-w-full">
        <thead class="bg-green-700 text-white"><tr><th class="px-4 py-3 text-left">Title</th><th class="px-4 py-3 text-left">MDA</th><th class="px-4 py-3 text-left">Status</th><th></th></tr></thead>
        <tbody class="divide-y">
        @forelse($highlights as $highlight)
            <tr>
                <td class="px-4 py-3"><a class="text-green-800 underline" href="{{ route('cms.programme-highlights.show', $highlight) }}">{{ $highlight->title }}</a></td>
                <td class="px-4 py-3">{{ $highlight->implementing_mda_name }}</td>
                <td class="px-4 py-3">@include('cms.partials.status', ['status' => $highlight->status])</td>
                <td class="px-4 py-3"><a class="cms-btn cms-btn-primary" href="{{ route('cms.programme-highlights.show', $highlight) }}">View</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-600">No programme highlights match the current filters.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <div class="p-4">{{ $highlights->links() }}</div>
</div>
@endsection
