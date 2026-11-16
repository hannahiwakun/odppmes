@extends('layouts.cms')
@section('title', 'Homepage Banners')
@section('content')
@include('cms.partials.flash')
<div class="cms-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-green-100 p-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-green-950">Homepage Banners</h2>
            <p class="mt-1 text-sm text-gray-600">Draft, submit and publish homepage hero, alert and feature banners. Images remain private until they are cleared for public use.</p>
        </div>
        @can('create', App\Models\CmsHomepageBanner::class)
            <a href="{{ route('cms.homepage-banners.create') }}" class="cms-btn cms-btn-primary">New banner</a>
        @endcan
    </div>
    @include('cms.partials.filters', ['action' => route('cms.homepage-banners.index')])
    <div class="cms-table-wrap overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-green-700 text-white">
                <tr>
                    <th class="px-4 py-3 text-left text-sm">Image</th>
                    <th class="px-4 py-3 text-left text-sm">Title</th>
                    <th class="px-4 py-3 text-left text-sm">Type</th>
                    <th class="px-4 py-3 text-left text-sm">Author</th>
                    <th class="px-4 py-3 text-left text-sm">Order</th>
                    <th class="px-4 py-3 text-left text-sm">Publication</th>
                    <th class="px-4 py-3 text-left text-sm">Status</th>
                    <th class="px-4 py-3 text-left text-sm">Media</th>
                    <th class="px-4 py-3 text-left text-sm">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            @forelse($banners as $banner)
                <tr>
                    <td class="px-4 py-3">
                        @if ($banner->image)
                            <img src="{{ route('cms.media.preview', $banner->image) }}" alt="{{ $banner->imageAltText() }}" class="h-14 w-16 rounded border border-green-100 bg-green-50 object-cover">
                        @else
                            <span class="inline-flex h-14 w-16 items-center justify-center rounded border border-green-100 bg-green-50 text-[10px] font-medium text-green-800">No image</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm font-medium">
                        <a class="text-green-800 underline" href="{{ route('cms.homepage-banners.show', $banner) }}">{{ $banner->title }}</a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $banner->typeLabel() }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $banner->createdBy?->name ?? 'Not recorded' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $banner->sort_order }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $banner->published_from?->format('d M Y H:i') ?? 'On publish' }}
                        —
                        {{ $banner->published_until?->format('d M Y H:i') ?? 'No end' }}
                    </td>
                    <td class="px-4 py-3">@include('cms.partials.status', ['status' => $banner->status])</td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        @if (! $banner->image)
                            No image
                        @elseif ($banner->image->is_cleared_for_public)
                            Cleared
                        @else
                            Not cleared
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="cms-actions">
                            @can('view', $banner)
                                <a class="cms-btn cms-btn-primary" href="{{ route('cms.homepage-banners.show', $banner) }}">View</a>
                            @endcan
                            @can('update', $banner)
                                <a class="cms-btn cms-btn-secondary" href="{{ route('cms.homepage-banners.edit', $banner) }}">Edit</a>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="px-4 py-8 text-center text-gray-600">No banners match the current filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $banners->links() }}</div>
</div>
@endsection
