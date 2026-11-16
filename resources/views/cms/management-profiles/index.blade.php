@extends('layouts.cms')
@section('title', 'Management Team')
@section('content')
@include('cms.partials.flash')
<div class="cms-card overflow-hidden">
    <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-2xl font-bold text-green-950">Management Team</h2>
        @can('create', App\Models\CmsManagementProfile::class)
            <a href="{{ route('cms.management-profiles.create') }}" class="cms-btn cms-btn-primary">New profile</a>
        @endcan
    </div>
    @include('cms.partials.filters', ['action' => route('cms.management-profiles.index')])
    <div class="cms-table-wrap">
    <table class="min-w-full">
        <thead class="bg-green-700 text-white"><tr><th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Designation</th><th class="px-4 py-3 text-left">Status</th><th></th></tr></thead>
        <tbody class="divide-y">
        @forelse($profiles as $profile)
            <tr>
                <td class="px-4 py-3"><a class="text-green-800 underline" href="{{ route('cms.management-profiles.show', $profile) }}">{{ $profile->full_name }}</a></td>
                <td class="px-4 py-3">{{ $profile->designation }}</td>
                <td class="px-4 py-3">@include('cms.partials.status', ['status' => $profile->status])</td>
                <td class="px-4 py-3"><a class="cms-btn cms-btn-primary" href="{{ route('cms.management-profiles.show', $profile) }}">View</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-600">No management profiles match the current filters.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <div class="p-4">{{ $profiles->links() }}</div>
</div>
@endsection
