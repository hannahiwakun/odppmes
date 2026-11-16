@extends('layouts.cms')

@section('title', 'Approval Centre')

@section('content')
@include('cms.partials.flash')
<div class="space-y-6">
    <div class="cms-card p-6">
        <h2 class="text-xl font-bold text-green-950">Submissions waiting for review</h2>
        <p class="cms-lede">Open a record to approve or reject it. Approval does not publish immediately. Rejection requires a reason that the author can act on. Use Publish on the record page only after approval.</p>
    </div>
    @forelse($queues as $queue)
        <div class="cms-card overflow-hidden">
            <div class="border-b border-green-100 px-5 py-3">
                <h3 class="font-semibold text-green-900">{{ $queue['label'] }}</h3>
            </div>
            <div class="cms-table-wrap">
                <table class="min-w-full text-sm">
                    <thead class="bg-green-800 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left">Content type</th>
                            <th class="px-4 py-3 text-left">Title</th>
                            <th class="px-4 py-3 text-left">Author</th>
                            <th class="px-4 py-3 text-left">Submitted</th>
                            <th class="px-4 py-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($queue['items'] as $item)
                            @php
                                $label = $item->title ?? $item->full_name ?? $item->name ?? $item->address ?? ('#'.$item->id);
                                $url = $queue['type'] === 'gallery-items'
                                    ? route('cms.gallery-albums.show', $item->album_id)
                                    : \App\Cms\CmsContentTypes::reviewUrl($queue['type'], (int) $item->id);
                                $submitted = $item->submitted_at ?? $item->updated_at;
                            @endphp
                            <tr>
                                <td class="px-4 py-3 text-gray-700">{{ $queue['label'] }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $label }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $item->createdBy?->name ?? 'Not recorded' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $submitted?->format('d M Y H:i') ?? 'Not recorded' }}</td>
                                <td class="px-4 py-3">
                                    @if($url)
                                        <a href="{{ $url }}" class="cms-btn cms-btn-primary">Review</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-dashed border-green-200 bg-white p-8 text-center text-gray-600">
            There are no submitted items waiting for approval.
        </div>
    @endforelse
</div>
@endsection
