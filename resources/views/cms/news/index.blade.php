@extends('layouts.cms')

@section('title', 'News and Announcements')

@section('content')
@include('cms.partials.flash')
<div class="cms-card overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-green-100 p-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-green-950">News and Announcements</h2>
            <p class="mt-1 text-sm text-gray-600">Draft, preview, submit and track public news articles. Featured images remain private until they are cleared for public use.</p>
        </div>
        @can('create', App\Models\CmsNewsArticle::class)
            <a href="{{ route('cms.news.create') }}" class="cms-btn cms-btn-primary">New article</a>
        @endcan
    </div>
    @include('cms.partials.filters', [
        'action' => route('cms.news.index'),
        'categoryOptions' => \App\Cms\CmsNewsCategories::all(),
    ])
    <div class="cms-table-wrap overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-green-700 text-white">
                <tr>
                    <th class="px-4 py-3 text-left text-sm">Image</th>
                    <th class="px-4 py-3 text-left text-sm">Title</th>
                    <th class="px-4 py-3 text-left text-sm">Category</th>
                    <th class="px-4 py-3 text-left text-sm">Author</th>
                    <th class="px-4 py-3 text-left text-sm">Date created</th>
                    <th class="px-4 py-3 text-left text-sm">Last updated</th>
                    <th class="px-4 py-3 text-left text-sm">Status</th>
                    <th class="px-4 py-3 text-left text-sm">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($articles as $article)
                    <tr>
                        <td class="px-4 py-3">
                            @if ($article->featuredImage)
                                <img src="{{ route('cms.media.preview', $article->featuredImage) }}" alt="{{ $article->featured_image_alt ?: '' }}" class="h-14 w-16 rounded border border-green-100 bg-green-50 object-cover">
                            @else
                                <span class="inline-flex h-14 w-16 items-center justify-center rounded border border-green-100 bg-green-50 text-[10px] font-medium text-green-800">No image</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm font-medium">
                            <a class="text-green-800 underline" href="{{ route('cms.news.show', $article) }}">{{ $article->title }}</a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $article->categoryLabel() }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $article->createdBy?->name ?? 'Not recorded' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $article->created_at?->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $article->updated_at?->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3">@include('cms.partials.status', ['status' => $article->status])</td>
                        <td class="px-4 py-3">
                            <div class="cms-actions">
                                @can('view', $article)
                                    <a class="cms-btn cms-btn-primary" href="{{ route('cms.news.show', $article) }}">View</a>
                                    <a class="cms-btn cms-btn-secondary" href="{{ route('cms.news.preview', $article) }}">Preview</a>
                                @endcan
                                @can('update', $article)
                                    <a class="cms-btn cms-btn-secondary" href="{{ route('cms.news.edit', $article) }}">Edit</a>
                                @endcan
                                @can('submit', $article)
                                    @if(in_array($article->status, ['draft', 'rejected'], true))
                                        <form method="POST" action="{{ route('cms.workflow.submit', ['type' => 'news', 'id' => $article->id]) }}" onsubmit="return confirm('Submit this content for Super Administrator approval?');">
                                            @csrf
                                            <button type="submit" class="cms-btn cms-btn-primary">{{ $article->status === 'rejected' ? 'Resubmit for approval' : 'Submit for Approval' }}</button>
                                        </form>
                                    @endif
                                @endcan
                                @can('archive', $article)
                                    @if(in_array($article->status, ['approved', 'published'], true))
                                        <form method="POST" action="{{ route('cms.workflow.archive', ['type' => 'news', 'id' => $article->id]) }}" onsubmit="return confirm('Archive this content?');">
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
                        <td colspan="8" class="px-4 py-8 text-center text-gray-600">No news articles match the current filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $articles->links() }}</div>
</div>
@endsection
