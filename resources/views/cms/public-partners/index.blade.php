@extends('layouts.cms')
@section('title', 'Public Partners')
@section('content')
@include('cms.partials.flash')
<div class="cms-card overflow-hidden">
    <div class="flex justify-between gap-4 p-6">
        <div>
            <h2 class="text-2xl font-bold text-green-950">Public Partners</h2>
            <p class="text-sm text-gray-600">These records are for the public website only. They are not operational partner accounts.</p>
        </div>
        @can('create', App\Models\CmsPublicPartner::class)
            <a href="{{ route('cms.public-partners.create') }}" class="cms-btn cms-btn-primary">New public partner</a>
        @endcan
    </div>
    @include('cms.partials.filters', ['action' => route('cms.public-partners.index')])
    <div class="cms-table-wrap">
    <table class="min-w-full">
        <thead class="bg-green-700 text-white"><tr><th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Category</th><th class="px-4 py-3 text-left">Status</th><th></th></tr></thead>
        <tbody class="divide-y">
        @forelse($partners as $partner)
            <tr>
                <td class="px-4 py-3"><a class="text-green-800 underline" href="{{ route('cms.public-partners.show', $partner) }}">{{ $partner->name }}</a></td>
                <td class="px-4 py-3">{{ $partner->category }}</td>
                <td class="px-4 py-3">@include('cms.partials.status', ['status' => $partner->status])</td>
                <td class="px-4 py-3"><a class="cms-btn cms-btn-primary" href="{{ route('cms.public-partners.show', $partner) }}">View</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-600">No public partners match the current filters.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <div class="p-4">{{ $partners->links() }}</div>
</div>
@endsection
