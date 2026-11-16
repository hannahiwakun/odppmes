<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DPMO CMS') — ODPPMES</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .cms-shell { min-height: 100vh; }
        .cms-sidebar { width: 18rem; flex-shrink: 0; }
        .cms-main { min-width: 0; }
        .cms-menu-button,
        .cms-sidebar-close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.5rem;
        }
        .cms-menu-button {
            border: 1px solid #166534;
            background: #fff;
            color: #14532d;
        }
        .cms-sidebar-close {
            border: 1px solid #15803d;
            background: transparent;
            color: #fff;
        }
        .cms-overlay { display: none; }
        @media (max-width: 1279px) {
            .cms-sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                z-index: 40;
                transform: translateX(-100%);
                transition: transform 200ms ease;
                overflow-y: auto;
            }
            .cms-sidebar.is-open { transform: translateX(0); }
            .cms-overlay.is-open {
                display: block;
                position: fixed;
                inset: 0;
                z-index: 30;
                background: rgba(0, 0, 0, 0.45);
            }
            .cms-main { width: 100%; }
            .cms-header {
                flex-wrap: wrap;
                gap: 0.75rem;
                padding-left: 1rem;
                padding-right: 1rem;
            }
            .cms-header h1 { font-size: 1.35rem; line-height: 1.75rem; }
            .cms-content { padding: 1rem; }
            body.cms-sidebar-open { overflow: hidden; }
        }
        @media (min-width: 1280px) {
            .cms-shell { display: flex; }
            .cms-sidebar { position: static; transform: none; }
            .cms-menu-button,
            .cms-sidebar-toolbar,
            .cms-overlay { display: none !important; }
            .cms-main { flex: 1; }
            body.cms-sidebar-open { overflow: auto; }
        }
        @media (max-width: 639px) {
            .cms-table-wrap { margin-left: -0.25rem; margin-right: -0.25rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .cms-sidebar { transition: none; }
        }
        .cms-card {
            border: 1px solid #dcfce7;
            background: #fff;
            border-radius: 0.75rem;
        }
        .cms-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.75rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            text-align: center;
        }
        .cms-btn-primary { background: #15803d; color: #fff; }
        .cms-btn-primary:hover { background: #166534; }
        .cms-btn-secondary {
            background: #fff;
            color: #14532d;
            border: 1px solid #166534;
        }
        .cms-btn-secondary:hover { background: #f0fdf4; }
        .cms-btn-danger { background: #b91c1c; color: #fff; }
        .cms-btn-danger:hover { background: #991b1b; }
        .cms-btn-publish { background: #14532d; color: #fff; }
        .cms-btn-publish:hover { background: #052e16; }
        .cms-btn:focus-visible,
        .cms-content a:focus-visible,
        .cms-content button:focus-visible {
            outline: 2px solid #166534;
            outline-offset: 2px;
        }
        .cms-menu-button:focus-visible {
            outline: 2px solid #166534;
            outline-offset: 2px;
        }
        .cms-sidebar a:focus-visible,
        .cms-sidebar button:focus-visible {
            outline: 2px solid #fff;
            outline-offset: 2px;
        }
        .cms-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .cms-input {
            width: 100%;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
        }
        .cms-input:focus {
            border-color: #15803d;
            --tw-ring-color: #15803d;
        }
        .cms-input.is-invalid { border-color: #b91c1c; }
        .cms-lede { margin-top: 0.25rem; font-size: 0.875rem; color: #4b5563; }
        .cms-queue-alert { border-left: 4px solid #15803d; }
        .cms-nav-group { margin-top: 1rem; padding-top: 0.25rem; }
        .cms-editor-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            margin-bottom: 0.5rem;
        }
        .cms-editor-toolbar button {
            min-height: 2.25rem;
            padding: 0.25rem 0.65rem;
            border: 1px solid #166534;
            background: #fff;
            color: #14532d;
            border-radius: 0.375rem;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .cms-editor-toolbar button:hover,
        .cms-editor-toolbar button:focus-visible {
            background: #f0fdf4;
        }
        .cms-editor-surface {
            min-height: 16rem;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background: #fff;
        }
        .cms-editor-surface:focus {
            outline: 2px solid #166534;
            outline-offset: 2px;
            border-color: #15803d;
        }
        .cms-news-body p { margin: 0.7rem 0; line-height: 1.7; }
        .cms-news-body h2 { margin: 1.1rem 0 0.5rem; font-size: 1.35rem; font-weight: 700; color: #14532d; }
        .cms-news-body h3 { margin: 0.9rem 0 0.4rem; font-size: 1.15rem; font-weight: 700; color: #14532d; }
        .cms-news-body ul, .cms-news-body ol { margin: 0.6rem 0 0.6rem 1.25rem; }
        .cms-news-body a { color: #166534; text-decoration: underline; }
        .cms-news-preview { max-width: 46rem; }
        .cms-news-preview .cms-news-body p { margin: 1rem 0; line-height: 1.8; }
        .cms-news-featured {
            display: block; width: 100%; height: auto; max-height: min(28rem, 70vw);
            object-fit: contain; object-position: center; background: #ecfdf5;
            border: 1px solid #dcfce7; border-radius: 0.5rem;
        }
        .cms-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    </style>
</head>
<body
    class="bg-emerald-50/60"
    x-data="{ sidebarOpen: false, profileOpen: false }"
    :class="{ 'cms-sidebar-open': sidebarOpen }"
    @keydown.escape.window="sidebarOpen = false; profileOpen = false"
>
<div class="cms-overlay" :class="{ 'is-open': sidebarOpen }" @click="sidebarOpen = false" x-cloak></div>
<div class="cms-shell">
    <aside id="cms-sidebar" class="cms-sidebar flex w-72 flex-col bg-green-900 text-white shadow-xl" :class="{ 'is-open': sidebarOpen }" aria-label="CMS menu">
        <div class="cms-sidebar-toolbar flex justify-end p-3">
            <button type="button" class="cms-sidebar-close" x-ref="closeMenu" @click="sidebarOpen = false">
                <span class="sr-only">Close menu</span>
                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="border-b border-green-700 p-5 text-center">
            <img src="{{ asset('images/ondo-logo.png') }}" class="mx-auto h-20 w-20 bg-white object-contain p-1.5" alt="Ondo State Logo">
            <p class="mt-3 text-lg font-bold">DPMO CMS</p>
            <p class="mt-1 text-xs leading-5 text-green-200">Ondo State public website<br>content workspace</p>
        </div>
        <nav class="flex-1 space-y-1 px-3 py-5">
            @include('cms.partials.nav')
        </nav>
        <div class="border-t border-green-700 p-4">
            <p class="font-semibold">{{ auth()->user()->name }}</p>
            <p class="mb-3 text-sm text-green-200">{{ auth()->user()->role }}</p>
            <a href="{{ route('profile.edit') }}" class="mb-2 flex items-center justify-center rounded-lg border border-green-600 px-4 py-2 text-sm hover:bg-green-800">Profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-lg bg-red-600 py-2 text-sm hover:bg-red-700">Logout</button>
            </form>
        </div>
    </aside>
    <main class="cms-main flex-1">
        <header class="cms-header flex items-center justify-between bg-white px-8 py-4 shadow">
            <div class="flex min-w-0 items-center gap-3">
                <button
                    type="button"
                    class="cms-menu-button"
                    x-ref="openMenu"
                    @click="sidebarOpen = true; $nextTick(() => $refs.closeMenu.focus())"
                    aria-controls="cms-sidebar"
                    :aria-expanded="sidebarOpen ? 'true' : 'false'"
                >
                    <span class="sr-only">Open menu</span>
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wide text-green-800">DPMO website CMS</p>
                    <h1 class="truncate text-2xl font-bold text-green-950">@yield('title')</h1>
                </div>
            </div>
            <div class="relative" @click.outside="profileOpen = false">
                <button type="button" class="rounded-lg border border-green-200 px-3 py-2 text-left text-sm text-green-900 hover:bg-green-50" @click="profileOpen = !profileOpen" :aria-expanded="profileOpen ? 'true' : 'false'">
                    <span class="block font-semibold">{{ auth()->user()->name }}</span>
                    <span class="block text-xs text-gray-500">Account</span>
                </button>
                <div x-show="profileOpen" x-cloak class="absolute right-0 z-20 mt-2 w-48 rounded-lg border border-green-100 bg-white py-1 shadow-lg">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-green-50">Profile</a>
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-green-50">Operational Portal</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-red-700 hover:bg-red-50">Logout</button>
                    </form>
                </div>
            </div>
        </header>
        <section class="cms-content p-6 sm:p-8">
            @yield('content')
        </section>
    </main>
</div>
</body>
</html>
