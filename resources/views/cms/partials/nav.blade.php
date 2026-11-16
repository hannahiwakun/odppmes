@php
    $icon = function (string $name): string {
        $paths = [
            'home' => 'M3 12l9-8 9 8v8a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1v-8z',
            'news' => 'M5 4h14v2H5V4zm0 4h14v12H5V8zm3 3h8v2H8v-2zm0 4h6v2H8v-2z',
            'banner' => 'M4 5h16v4H4V5zm0 6h10v8H4v-8zm12 0h4v8h-4v-8z',
            'gallery' => 'M4 5h16v14H4V5zm2 2v10h12V7H6zm2 6l2.5-3 2 2.5L15 10l3 5H8z',
            'people' => 'M12 12a4 4 0 100-8 4 4 0 000 8zm-7 9a7 7 0 0114 0H5z',
            'partners' => 'M7 10a3 3 0 110-6 3 3 0 010 6zm10 0a3 3 0 110-6 3 3 0 010 6zM7 12c-2.5 0-4.5 1.5-4.5 4v2h9v-2c0-2.5-2-4-4.5-4zm10 0c-1 0-1.9.3-2.6.8 1.5.9 2.6 2.4 2.6 4.2V18h5v-2c0-2.5-2-4-5-4z',
            'highlight' => 'M12 3l2.2 6.6H21l-5.4 4 2.1 6.4L12 16.8 6.3 20l2.1-6.4L3 9.6h6.8L12 3z',
            'office' => 'M5 21V3h8v6h6v12H5zm2-2h2v-2H7v2zm0-4h2v-2H7v2zm0-4h2V9H7v2zm0-4h2V5H7v2zm4 12h2v-2h-2v2zm0-4h2v-2h-2v2zm0-4h2V9h-2v2zm0-4h2V5h-2v2zm4 12h2v-2h-2v2zm0-4h2v-2h-2v2z',
            'enquiry' => 'M4 5h16v12H7l-3 3V5zm4 4h8v2H8V9zm0 4h6v2H8v-2z',
            'approval' => 'M9 12l2 2 4-4 2 2-6 6-4-4 2-2zM4 4h16v16H4V4z',
            'users' => 'M8 11a3 3 0 110-6 3 3 0 010 6zm8 0a3 3 0 110-6 3 3 0 010 6zM3 19c0-2.8 2.2-5 5-5h2c.5 0 1 .1 1.4.2A5 5 0 0116 14h2c2.8 0 5 2.2 5 5v1H3v-1z',
            'leave' => 'M5 4h8v2H7v12h6v2H5V4zm9.5 4.5l5 3.5-5 3.5V13H9v-2h5.5V8.5z',
        ];
        $d = $paths[$name] ?? $paths['home'];

        return '<svg aria-hidden="true" class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="'.$d.'"></path></svg>';
    };

    $link = function (string $match, string $route, string $label, string $iconName) use ($icon) {
        $active = request()->routeIs($match);
        $classes = 'flex min-h-11 items-center gap-3 rounded-lg px-3 py-2.5 text-sm '.($active ? 'bg-green-700 font-semibold ring-2 ring-white/40' : 'hover:bg-green-800');
        $aria = $active ? ' aria-current="page"' : '';
        echo '<a href="'.e(route($route)).'" class="'.$classes.'"'.$aria.'>'.$icon($iconName).'<span>'.e($label).'</span></a>';
    };
@endphp

<div class="cms-nav-group mt-0 space-y-1">
    <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-green-200">Site content</p>
    @php $link('cms.home', 'cms.home', 'Overview', 'home'); @endphp
    @php $link('cms.news.*', 'cms.news.index', 'News and Announcements', 'news'); @endphp
    @php $link('cms.homepage-banners.*', 'cms.homepage-banners.index', 'Homepage Banners', 'banner'); @endphp
    @php $link('cms.gallery-albums.*', 'cms.gallery-albums.index', 'Media Gallery', 'gallery'); @endphp
</div>

<div class="cms-nav-group space-y-1">
    <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-green-200">People and programmes</p>
    @php $link('cms.management-profiles.*', 'cms.management-profiles.index', 'Management Team', 'people'); @endphp
    @php $link('cms.public-partners.*', 'cms.public-partners.index', 'Public Partners', 'partners'); @endphp
    @php $link('cms.programme-highlights.*', 'cms.programme-highlights.index', 'Programme Highlights', 'highlight'); @endphp
</div>

<div class="cms-nav-group space-y-1">
    <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-green-200">Office</p>
    @php $link('cms.office-contact.*', 'cms.office-contact.show', 'Office Contact', 'office'); @endphp
    @php $link('cms.enquiries.*', 'cms.enquiries.index', 'Public Enquiries', 'enquiry'); @endphp
</div>

@if(auth()->user()->isSuperAdmin())
    <div class="cms-nav-group space-y-1">
        <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-green-200">Administration</p>
        @php $link('cms.approvals.*', 'cms.approvals.index', 'Approval Centre', 'approval'); @endphp
        @php $link('cms.users.*', 'cms.users.index', 'CMS User Management', 'users'); @endphp
    </div>

    <div class="cms-nav-group space-y-1">
        <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-green-200">Leave CMS</p>
        <a href="{{ route('dashboard') }}" class="flex min-h-11 items-center gap-3 rounded-lg px-3 py-2.5 text-sm hover:bg-green-800">
            {!! $icon('leave') !!}
            <span>Operational Portal</span>
        </a>
    </div>
@endif
