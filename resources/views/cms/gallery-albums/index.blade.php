@extends('layouts.cms')
@section('title', 'Media Gallery')
@section('content')
@include('cms.partials.flash')
<div class="cms-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-green-100 p-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-green-950">Media Gallery</h2>
            <p class="mt-1 text-sm text-gray-600">Manage public website albums. Files remain in private storage until a photograph is published and cleared for public use.</p>
        </div>
        @can('create', App\Models\CmsGalleryAlbum::class)
            <a href="{{ route('cms.gallery-albums.create') }}" class="cms-btn cms-btn-primary">New album</a>
        @endcan
    </div>
    @include('cms.partials.filters', ['action' => route('cms.gallery-albums.index')])
    <div class="cms-table-wrap">
    <table class="min-w-full">
        <thead class="bg-green-700 text-white">
            <tr>
                <th class="px-4 py-3 text-left text-sm">Album</th>
                <th class="px-4 py-3 text-left text-sm">Items</th>
                <th class="px-4 py-3 text-left text-sm">Author</th>
                <th class="px-4 py-3 text-left text-sm">Date created</th>
                <th class="px-4 py-3 text-left text-sm">Last updated</th>
                <th class="px-4 py-3 text-left text-sm">Status</th>
                <th class="px-4 py-3 text-left text-sm">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($albums as $album)
                @php
                    $thumb = $album->items->first(fn ($item) => $item->media);
                @endphp
                <tr>
                    <td class="px-4 py-3">
                        <div class="flex items-start gap-3">
                            @if($thumb?->media)
                                <img src="{{ route('cms.media.preview', $thumb->media) }}" alt="" class="h-14 w-16 shrink-0 rounded border border-green-100 bg-green-50 object-cover">
                            @else
                                <span class="flex h-14 w-16 shrink-0 items-center justify-center rounded border border-dashed border-green-200 bg-green-50 text-[10px] font-semibold uppercase tracking-wide text-green-800">No image</span>
                            @endif
                            <div class="min-w-0">
                                <a class="font-medium text-green-800 underline" href="{{ route('cms.gallery-albums.show', $album) }}">{{ $album->title }}</a>
                                @if($album->description)
                                    <p class="mt-1 line-clamp-2 text-xs text-gray-600">{{ $album->description }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $album->items_count }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $album->createdBy?->name ?? 'Not recorded' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $album->created_at?->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $album->updated_at?->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3">@include('cms.partials.status', ['status' => $album->status])</td>
                    <td class="px-4 py-3">
                        <div class="cms-actions">
                            @can('view', $album)
                                <a class="cms-btn cms-btn-primary" href="{{ route('cms.gallery-albums.show', $album) }}">View</a>
                            @endcan
                            @can('update', $album)
                                <a class="cms-btn cms-btn-secondary" href="{{ route('cms.gallery-albums.edit', $album) }}">Edit</a>
                            @endcan
                            @can('submit', $album)
                                @if(in_array($album->status, ['draft', 'rejected'], true))
                                    <form method="POST" action="{{ route('cms.workflow.submit', ['type' => 'gallery-albums', 'id' => $album->id]) }}" onsubmit="return confirm('Submit this content for Super Administrator approval?');">
                                        @csrf
                                        <button type="submit" class="cms-btn cms-btn-primary">{{ $album->status === 'rejected' ? 'Resubmit for approval' : 'Submit for Approval' }}</button>
                                    </form>
                                @endif
                            @endcan
                            @can('archive', $album)
                                @if(in_array($album->status, ['approved', 'published'], true))
                                    <form method="POST" action="{{ route('cms.workflow.archive', ['type' => 'gallery-albums', 'id' => $album->id]) }}" onsubmit="return confirm('Archive this content?');">
                                        @csrf
                                        <button type="submit" class="cms-btn cms-btn-secondary">Archive</button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center">
                        <p class="font-medium text-gray-800">No albums match the current filters.</p>
                        <p class="mt-1 text-sm text-gray-600">Create an album, then upload JPEG, PNG or WebP photographs from the album page.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="p-4">{{ $albums->links() }}</div>
</div>
@endsection
