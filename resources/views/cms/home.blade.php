@extends('layouts.cms')

@section('title', 'Overview')

@section('content')
@include('cms.partials.flash')
@php
    $user = auth()->user();
    $isWebsiteAdmin = $user->isWebsiteAdmin();
    $pendingHref = $user->isSuperAdmin()
        ? route('cms.approvals.index')
        : route('cms.news.index', ['status' => 'submitted']);
@endphp
<div class="space-y-6">
    <section class="rounded-xl border border-green-100 bg-white p-5 sm:p-6">
        <p class="text-sm font-semibold uppercase tracking-wide text-green-800">Development Partners Management Office</p>
        <h2 class="mt-1 text-2xl font-bold text-green-950">Welcome, {{ $user->name }}</h2>
        <p class="mt-1 text-sm font-medium text-green-900">{{ $user->role }}</p>
        <p class="mt-3 max-w-3xl text-gray-700">
            This workspace manages <span class="font-medium">Public website content</span> only.
            @if($isWebsiteAdmin)
                Draft pages, submit them for Super Administrator review, and handle public enquiries. Approval, publication and user administration are not available on this account.
            @else
                Review submissions in the Approval Centre before publishing. You can also open the operational portal when you need project monitoring tools.
            @endif
        </p>
        <div class="mt-5 flex flex-col gap-3 sm:flex-row">
            @if($isWebsiteAdmin)
                <a href="{{ route('cms.news.create') }}" class="cms-btn cms-btn-primary">Write a news article</a>
                <a href="{{ route('cms.enquiries.index', ['status' => 'new']) }}" class="cms-btn cms-btn-secondary">View new enquiries</a>
            @else
                <a href="{{ route('cms.approvals.index') }}" class="cms-btn cms-btn-primary">Review submissions</a>
                <a href="{{ route('cms.news.create') }}" class="cms-btn cms-btn-secondary">Write a news article</a>
            @endif
        </div>
    </section>

    <section>
        <h3 class="mb-3 text-lg font-semibold text-green-950">Work queue</h3>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <a href="{{ $pendingHref }}" class="cms-card p-5 hover:border-green-700 {{ $stats['pending_review'] > 0 ? 'cms-queue-alert' : '' }}">
                <p class="text-sm font-medium text-gray-700">Pending approval</p>
                <p class="mt-2 text-3xl font-bold text-green-900">{{ $stats['pending_review'] }}</p>
                @if($stats['pending_review'] === 0)
                    <p class="mt-1 text-xs text-gray-500">None yet</p>
                @elseif($isWebsiteAdmin)
                    <p class="mt-1 text-xs text-gray-500">Waiting for Super Administrator review</p>
                @else
                    <p class="mt-1 text-xs font-medium text-green-800">Review these submissions</p>
                @endif
            </a>
            <a href="{{ route('cms.news.index', ['status' => 'rejected']) }}" class="cms-card p-5 hover:border-green-700">
                <p class="text-sm text-gray-600">Rejected articles</p>
                <p class="mt-2 text-3xl font-bold text-green-900">{{ $stats['news_rejected'] }}</p>
                @if($stats['news_rejected'] === 0)
                    <p class="mt-1 text-xs text-gray-500">None yet</p>
                @endif
            </a>
            <a href="{{ route('cms.enquiries.index', ['status' => 'new']) }}" class="cms-card p-5 hover:border-green-700">
                <p class="text-sm text-gray-600">New enquiries</p>
                <p class="mt-2 text-3xl font-bold text-green-900">{{ $stats['enquiries_new'] }}</p>
                @if($stats['enquiries_new'] === 0)
                    <p class="mt-1 text-xs text-gray-500">None yet</p>
                @endif
            </a>
            <a href="{{ route('cms.news.index') }}" class="cms-card p-5 hover:border-green-700">
                <p class="text-sm text-gray-600">Total news articles</p>
                <p class="mt-2 text-3xl font-bold text-green-900">{{ $stats['news_total'] }}</p>
                @if($stats['news_total'] === 0)
                    <p class="mt-1 text-xs text-gray-500">None yet</p>
                @endif
            </a>
        </div>
    </section>

    <section>
        <h3 class="mb-3 text-lg font-semibold text-green-950">Published and draft content</h3>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach([
                ['News', $stats['news_published'], $stats['news_draft'], route('cms.news.index')],
                ['Media Gallery', $stats['albums_published'], $stats['albums_draft'], route('cms.gallery-albums.index')],
                ['Homepage Banners', $stats['banners_published'], $stats['banners_draft'], route('cms.homepage-banners.index')],
                ['Management Team', $stats['profiles_published'], $stats['profiles_draft'], route('cms.management-profiles.index')],
                ['Public Partners', $stats['partners_published'], $stats['partners_draft'], route('cms.public-partners.index')],
                ['Programme Highlights', $stats['highlights_published'], $stats['highlights_draft'], route('cms.programme-highlights.index')],
            ] as [$label, $published, $draft, $href])
                <a href="{{ $href }}" class="cms-card p-5 hover:border-green-700">
                    <p class="text-sm font-medium text-gray-800">{{ $label }}</p>
                    <p class="mt-2 text-sm text-gray-600">Published <span class="font-semibold text-green-900">{{ $published }}</span></p>
                    <p class="text-sm text-gray-600">Draft <span class="font-semibold text-green-900">{{ $draft }}</span></p>
                </a>
            @endforeach
        </div>
    </section>

    <section class="cms-card p-5 sm:p-6">
        <h3 class="text-lg font-semibold text-green-950">Recent CMS activity</h3>
        <p class="cms-lede">Latest workflow changes in this content workspace. Operational project activity is not shown here.</p>
        @if($recentEvents->isEmpty())
            <p class="mt-3 text-gray-600">No workflow events have been recorded yet.</p>
        @else
            <div class="cms-table-wrap mt-4">
                <table class="min-w-full text-sm">
                    <thead class="bg-green-800 text-white">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium">Content</th>
                            <th class="px-3 py-2 text-left font-medium">Change</th>
                            <th class="px-3 py-2 text-left font-medium">By</th>
                            <th class="px-3 py-2 text-left font-medium">When</th>
                            <th class="px-3 py-2 text-left font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($recentEvents as $event)
                            @php
                                $url = $event->content_type === 'gallery-items'
                                    ? null
                                    : \App\Cms\CmsContentTypes::reviewUrl((string) $event->content_type, (int) $event->content_id);
                            @endphp
                            <tr>
                                <td class="px-3 py-2 font-medium text-gray-900">{{ $labels[$event->content_type] ?? $event->content_type }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $event->from_status ?? 'none' }} → {{ $event->to_status }}</td>
                                <td class="px-3 py-2 text-gray-600">{{ $event->actor?->name ?? 'System' }}</td>
                                <td class="px-3 py-2 text-gray-600">{{ $event->created_at?->format('d M Y H:i') }}</td>
                                <td class="px-3 py-2">
                                    @if($url)
                                        <a href="{{ $url }}" class="text-green-800 underline">Open</a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section>
        <h3 class="mb-3 text-lg font-semibold text-green-950">Shortcuts</h3>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <a class="cms-btn cms-btn-secondary w-full" href="{{ route('cms.news.index') }}">News and Announcements</a>
            <a class="cms-btn cms-btn-secondary w-full" href="{{ route('cms.gallery-albums.index') }}">Media Gallery</a>
            <a class="cms-btn cms-btn-secondary w-full" href="{{ route('cms.office-contact.show') }}">Office Contact</a>
            <a class="cms-btn cms-btn-secondary w-full" href="{{ route('cms.enquiries.index') }}">Public Enquiries</a>
        </div>
    </section>
</div>
@endsection
