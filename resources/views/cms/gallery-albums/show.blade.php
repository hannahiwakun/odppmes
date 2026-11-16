@extends('layouts.cms')
@section('title', $album->title)
@section('content')
@include('cms.partials.flash')
<div class="max-w-5xl space-y-6">
    <div class="cms-card p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-green-800">Media Gallery album</p>
                <h2 class="text-2xl font-bold text-green-950">{{ $album->title }}</h2>
                <p class="cms-lede">{{ $album->description }}</p>
                <p class="mt-2">@include('cms.partials.status', ['status' => $album->status])</p>
            </div>
            <div class="cms-actions">
                <a href="{{ route('cms.gallery-albums.index') }}" class="cms-btn cms-btn-secondary">Return to albums</a>
                @can('update', $album)
                    <a href="{{ route('cms.gallery-albums.edit', $album) }}" class="cms-btn cms-btn-primary">Edit album</a>
                @endcan
            </div>
        </div>
        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
            <div>
                <dt class="font-semibold text-gray-800">Author</dt>
                <dd>{{ $album->createdBy?->name ?? 'Not recorded' }}</dd>
            </div>
            <div>
                <dt class="font-semibold text-gray-800">Last updated</dt>
                <dd>{{ $album->updated_at?->format('d M Y H:i') }}</dd>
            </div>
        </dl>
    </div>

    <div class="rounded-xl border border-green-100 bg-green-50/50 p-4 text-sm text-gray-700">
        <p class="font-semibold text-green-950">How photographs are published</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            <li>Upload JPEG, PNG or WebP files of 5 MB or smaller. Files are stored privately on the CMS disk.</li>
            <li><span class="font-medium">Album publication</span> makes the album eligible to appear on the public gallery.</li>
            <li><span class="font-medium">Photograph publication</span> is a separate workflow for each image.</li>
            <li><span class="font-medium">Public clearance</span> is a Super Administrator action. Until a file is cleared, it cannot be served on the public website, even if the album is published.</li>
            <li>The public gallery shows an image only when the album is published, the photograph is published, and the file is cleared for public use.</li>
        </ul>
    </div>

    <div class="cms-card p-6">
        <h3 class="text-lg font-semibold text-green-950">Photographs</h3>
        <p class="cms-lede">Captions and display order can be updated for records you are allowed to edit. Removing an image does not delete the stored file.</p>
        <ul class="mt-4 grid gap-4 sm:grid-cols-2">
            @forelse($album->items as $item)
                <li class="rounded-xl border border-green-100 p-3">
                    @if($item->media)
                        <img src="{{ route('cms.media.preview', $item->media) }}" alt="{{ $item->caption ?: $item->media->original_name }}" class="mb-3 h-48 w-full rounded-lg bg-green-50 object-cover">
                        <p class="text-sm font-medium text-gray-900">{{ $item->media->original_name }}</p>
                        <p class="text-xs text-gray-500">{{ $item->media->mime }} · {{ $item->media->width }}×{{ $item->media->height }}</p>
                        @include('cms.partials.clear-media', ['asset' => $item->media])
                    @endif
                    <p class="mt-2 text-sm text-gray-800">
                        <span class="font-semibold">Caption:</span>
                        {{ $item->caption ?: 'No caption' }}
                    </p>
                    <p class="mt-1 text-sm text-gray-700">
                        <span class="font-semibold">Display order:</span> {{ $item->sort_order }}
                    </p>
                    <p class="mt-2">@include('cms.partials.status', ['status' => $item->status])</p>
                    @can('update', $item)
                        <form method="POST" action="{{ route('cms.gallery-albums.items.update', [$album, $item]) }}" class="mt-3 flex flex-col gap-2">
                            @csrf
                            @method('PUT')
                            <x-cms.label :for="'caption-'.$item->id">Caption</x-cms.label>
                            <input id="caption-{{ $item->id }}" name="caption" value="{{ $item->caption }}" maxlength="500" class="cms-input text-sm" placeholder="Caption shown with the photograph">
                            <x-cms.label :for="'sort-'.$item->id">Display order</x-cms.label>
                            <input id="sort-{{ $item->id }}" type="number" name="sort_order" value="{{ $item->sort_order }}" class="cms-input max-w-xs text-sm">
                            <button class="cms-btn cms-btn-primary">Update photograph</button>
                        </form>
                    @endcan
                    @can('delete', $item)
                        <form method="POST" action="{{ route('cms.gallery-albums.items.destroy', [$album, $item]) }}" class="mt-2" onsubmit="return confirm('Remove this image from the album?');">
                            @csrf
                            @method('DELETE')
                            <button class="cms-btn cms-btn-danger">Remove image</button>
                        </form>
                    @endcan
                    <details class="mt-3">
                        <summary class="cursor-pointer text-sm font-medium text-green-800">Photograph workflow</summary>
                        @include('cms.partials.workflow-actions', ['content' => $item, 'type' => 'gallery-items'])
                    </details>
                </li>
            @empty
                <li class="rounded-xl border border-dashed border-green-200 p-6 text-gray-600 sm:col-span-2">This album has no photographs yet. Use the upload form below. Accepted formats are JPEG, PNG and WebP, up to 5 MB each.</li>
            @endforelse
        </ul>
    </div>

    @can('create', App\Models\CmsGalleryItem::class)
        <form method="POST" action="{{ route('cms.gallery-albums.items.store', $album) }}" enctype="multipart/form-data" class="cms-card space-y-4 p-6" x-data="{ uploading: false }" @submit="if (uploading) { $event.preventDefault(); } else { uploading = true; }">
            @csrf
            <h3 class="font-semibold text-green-950">Upload a photograph</h3>
            <p class="text-sm text-gray-600">Choose one image at a time. The file is stored privately. It will not appear on the public website until this photograph is published and Super Administrator clearance is given.</p>
            @include('cms.partials.image-upload', ['label' => 'Image file', 'required' => true])
            <div>
                <x-cms.label for="caption">Caption</x-cms.label>
                <input id="caption" name="caption" maxlength="500" class="cms-input" placeholder="Optional caption shown with the image">
            </div>
            <div>
                <x-cms.label for="item_sort_order">Display order</x-cms.label>
                <x-cms.help>Lower numbers appear first in the album.</x-cms.help>
                <input id="item_sort_order" type="number" name="sort_order" value="0" class="cms-input mt-1 max-w-xs">
            </div>
            <button class="cms-btn cms-btn-primary disabled:opacity-60" :disabled="uploading">
                <span x-show="!uploading">Upload image</span>
                <span x-cloak x-show="uploading">Uploading…</span>
            </button>
        </form>
    @endcan

    @include('cms.partials.workflow-actions', ['content' => $album, 'type' => 'gallery-albums'])
</div>
@endsection
