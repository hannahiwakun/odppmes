<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DPMO — Ondo State Development Partners Management Office')</title>
    <meta name="description" content="@yield('meta_description', 'Official public website of the Ondo State Development Partners Management Office (DPMO). ODPPMES is the secure monitoring portal for authorised officers.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --odp-navy: #0b1f3a;
            --odp-navy-soft: #163056;
            --odp-green: #14532d;
            --odp-green-mid: #15803d;
            --odp-ink: #1e293b;
            --odp-muted: #475569;
            --odp-line: #dbe4dc;
            --odp-wash: #f4f7f4;
            --odp-gold: #c5a35a;
        }
        .public-page { background: #fff; color: var(--odp-ink); }
        .public-wrap { width: min(1160px, calc(100% - 1.6rem)); margin-inline: auto; }
        .public-topbar {
            background: linear-gradient(90deg, var(--odp-green) 0%, #0f3d24 55%, var(--odp-navy) 100%);
            color: #ecfdf3;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.04em;
        }
        .public-topbar-inner {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            align-items: center;
            min-height: 2.15rem;
            flex-wrap: wrap;
            padding: 0.4rem 0;
        }
        .public-header {
            position: sticky;
            top: 0;
            z-index: 40;
            background: #fff;
            border-bottom: 3px solid var(--odp-green);
            box-shadow: 0 8px 24px rgb(11 31 58 / 0.06);
        }
        .public-header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            min-height: 4.75rem;
            padding: 0.55rem 0;
        }
        .public-brand {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            text-decoration: none;
            color: inherit;
            min-width: 0;
        }
        .public-brand img {
            width: 4.35rem;
            height: 4.35rem;
            object-fit: contain;
            flex-shrink: 0;
        }
        .public-brand-kicker {
            font-size: 0.68rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--odp-green);
            font-weight: 800;
        }
        .public-brand-name {
            font-size: 1.28rem;
            font-weight: 800;
            color: var(--odp-navy);
            line-height: 1.12;
        }
        .public-brand-sub {
            font-size: 0.8rem;
            color: var(--odp-muted);
            line-height: 1.3;
        }
        .public-header-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }
        .public-header-actions .public-btn-navy {
            white-space: nowrap;
        }
        .public-nav-row {
            border-top: 1px solid var(--odp-line);
            background: var(--odp-wash);
        }
        .public-nav {
            display: none;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.15rem 0.1rem;
            padding: 0.28rem 0;
        }
        .public-nav a.public-link,
        .public-mobile-nav a.public-link {
            color: var(--odp-navy);
            font-size: 0.86rem;
            font-weight: 700;
            text-decoration: none;
            padding: 0.5rem 0.7rem;
            border-radius: 0.35rem;
            border-bottom: 2px solid transparent;
        }
        .public-nav a.public-link:hover {
            background: #fff;
            color: var(--odp-green);
        }
        .public-nav a.public-link[aria-current="page"] {
            background: #fff;
            color: var(--odp-green);
            border-bottom-color: var(--odp-green-mid);
        }
        .public-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.4rem;
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none;
            padding: 0.68rem 1.15rem;
            border: 1px solid transparent;
            line-height: 1.2;
            transition: background-color 0.15s ease, transform 0.15s ease;
        }
        .public-btn:hover { transform: translateY(-1px); }
        .public-btn-primary { background: var(--odp-green-mid); color: #fff !important; border-color: var(--odp-green); }
        .public-btn-primary:hover { background: var(--odp-green); }
        .public-btn-navy { background: var(--odp-navy); color: #fff !important; box-shadow: 0 4px 12px rgb(11 31 58 / 0.22); }
        .public-btn-navy:hover { background: var(--odp-navy-soft); }
        .public-btn-outline { background: #fff; color: var(--odp-navy) !important; border-color: #94a3b8; }
        .public-btn-outline:hover { background: var(--odp-wash); }
        .public-btn-hero {
            background: transparent; color: #fff !important; border-color: #fff;
        }
        .public-btn-hero:hover { background: rgb(255 255 255 / 0.12); }
        .public-navy .public-btn-hero { border-color: #fff; color: #fff !important; }
        .public-menu-toggle {
            display: inline-flex; align-items: center; justify-content: center;
            width: 2.7rem; height: 2.7rem; border: 1px solid var(--odp-navy);
            background: #fff; color: var(--odp-navy); border-radius: 0.35rem;
        }
        .public-mobile-nav { display: none; flex-direction: column; gap: 0.2rem; padding: 0 0 0.9rem; }
        .public-mobile-nav.is-open { display: flex; }
        .public-hero {
            background:
                radial-gradient(circle at 12% 20%, rgb(21 128 61 / 0.35), transparent 42%),
                linear-gradient(118deg, #071526 0%, #0b1f3a 42%, #14532d 100%);
            color: #fff;
            padding: 2.6rem 0 2.7rem;
        }
        .public-hero-grid { display: grid; gap: 1.6rem; align-items: center; }
        .public-eyebrow {
            display: inline-block; font-size: 0.72rem; letter-spacing: 0.14em;
            text-transform: uppercase; font-weight: 800; color: #bbf7d0; margin-bottom: 0.7rem;
        }
        .public-hero h1 {
            font-size: clamp(1.7rem, 3.2vw, 2.45rem);
            line-height: 1.18; font-weight: 800; max-width: 18ch; letter-spacing: -0.02em;
        }
        .public-hero .intro { margin-top: 0.85rem; color: #e2e8f0; font-size: 1.02rem; line-height: 1.65; max-width: 38rem; }
        .public-actions { display: flex; flex-wrap: wrap; gap: 0.7rem; margin-top: 1.25rem; }
        .public-hero-media { margin: 0; position: relative; }
        .public-hero-frame {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            border: 1px solid rgb(255 255 255 / 0.18);
            box-shadow: 0 18px 40px rgb(0 0 0 / 0.28);
            min-height: 18rem;
        }
        .public-hero-frame img {
            width: 100%;
            height: 22rem;
            object-fit: cover;
            display: block;
        }
        .public-hero-frame::after {
            content: "";
            position: absolute; inset: 0;
            background: linear-gradient(180deg, rgb(11 31 58 / 0.12) 35%, rgb(20 83 45 / 0.38) 100%);
            pointer-events: none;
        }
        .public-hero-panel {
            position: absolute;
            left: 0.85rem; right: 0.85rem; bottom: 0.85rem;
            z-index: 1;
            background: rgb(7 21 38 / 0.78);
            border: 1px solid rgb(255 255 255 / 0.16);
            border-radius: 0.75rem;
            padding: 0.9rem 1rem;
            backdrop-filter: blur(6px);
        }
        .public-hero-panel h2 { font-size: 0.82rem; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 0.45rem; color: #bbf7d0; }
        .public-hero-panel li { font-size: 0.86rem; color: #f8fafc; margin: 0.28rem 0; padding-left: 0.85rem; position: relative; }
        .public-hero-panel li::before { content: ""; width: 0.38rem; height: 0.38rem; border-radius: 99px; background: #86efac; position: absolute; left: 0; top: 0.38rem; }
        .public-hero-caption {
            margin-top: 0.55rem;
            font-size: 0.75rem;
            color: #cbd5e1;
            line-height: 1.45;
        }
        .public-hero-frame-cms::after { display: none; }
        .public-banner-hero-image {
            width: 100%; height: 22rem; object-fit: contain; object-position: center;
            background: #0b1f3a; display: block;
        }
        .public-banner-hero-ph { height: 22rem; color: #cbd5e1; }
        .public-banner-alerts { background: #14532d; }
        .public-banner-alert { border-bottom: 1px solid rgb(255 255 255 / 0.14); }
        .public-banner-alert-inner {
            display: flex; flex-wrap: wrap; gap: 0.9rem 1.25rem;
            align-items: center; justify-content: space-between;
            padding: 0.85rem 0;
        }
        .public-banner-kicker {
            font-size: 0.68rem; letter-spacing: 0.12em; text-transform: uppercase;
            font-weight: 800; color: #bbf7d0; margin: 0 0 0.2rem;
        }
        .public-banner-alert-title { margin: 0; color: #fff; font-weight: 800; font-size: 1.02rem; }
        .public-banner-alert-text { margin: 0.25rem 0 0; color: #ecfdf3; font-size: 0.92rem; line-height: 1.5; max-width: 46rem; }
        .public-banner-alert-btn {
            background: #fff; color: #14532d !important; border-color: #fff;
            min-height: 2.75rem; padding: 0.65rem 1.15rem; font-weight: 800;
        }
        .public-banner-alert-btn:hover { background: #ecfdf3; }
        .public-banner-hero-extra {
            display: grid; gap: 1.25rem; align-items: center;
        }
        .public-banner-image {
            width: 100%; height: auto; max-height: 16rem; object-fit: contain;
            object-position: center; background: #e8f0ea; border-radius: 0.5rem;
        }
        .public-banner-image-ph { min-height: 8rem; }
        @media (min-width: 900px) {
            .public-banner-hero-extra { grid-template-columns: minmax(0, 1.1fr) minmax(240px, 0.9fr); }
        }
        @media (max-width: 639px) {
            .public-banner-hero-image, .public-banner-hero-ph { height: 14rem; }
            .public-banner-alert-btn { width: 100%; text-align: center; }
        }
        .public-section { padding: 2.5rem 0; }
        .public-section h2 { font-size: 1.4rem; color: var(--odp-navy); font-weight: 800; }
        .public-section .lede { margin-top: 0.5rem; color: var(--odp-muted); line-height: 1.65; max-width: 46rem; font-size: 0.98rem; }
        .public-wash { background: var(--odp-wash); border-top: 1px solid var(--odp-line); border-bottom: 1px solid var(--odp-line); }
        .public-navy { background: var(--odp-navy); color: #fff; }
        .public-navy h2 { color: #fff; }
        .public-navy .lede { color: #cbd5e1; }
        .public-grid { display: grid; gap: 1rem; margin-top: 1.2rem; }
        .public-card {
            border: 1px solid var(--odp-line); border-radius: 0.85rem; padding: 1.15rem 1.15rem 1.2rem;
            background: #fff; box-shadow: 0 6px 18px rgb(15 23 42 / 0.05);
            transition: box-shadow 0.15s ease, transform 0.15s ease;
        }
        .public-card:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgb(15 23 42 / 0.08); }
        .public-card h3 { color: var(--odp-navy); font-size: 1.02rem; font-weight: 800; }
        .public-card p { margin-top: 0.42rem; color: var(--odp-muted); line-height: 1.58; font-size: 0.94rem; }
        .public-card a { color: var(--odp-green); font-weight: 700; text-decoration: none; }
        .public-card a:hover { text-decoration: underline; }
        .public-icon {
            width: 2.45rem; height: 2.45rem; border-radius: 0.65rem; background: #ecfdf3;
            color: var(--odp-green); display: flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;
            border: 1px solid #bbf7d0;
        }
        .public-num {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 1.7rem; height: 1.35rem; padding: 0 0.35rem; margin-bottom: 0.45rem;
            border-radius: 999px; background: #ecfdf3; color: var(--odp-green);
            font-size: 0.72rem; font-weight: 800; letter-spacing: 0.04em;
            border: 1px solid #bbf7d0;
        }
        .public-media-ph {
            height: 8.5rem; border-radius: 0.55rem; margin: 0.65rem 0 0.85rem;
            background:
                linear-gradient(135deg, #e8f5ec 0%, #dbe7f3 100%);
            border: 1px dashed #94a3b8;
            display: flex; align-items: center; justify-content: center; text-align: center;
            color: var(--odp-muted); font-size: 0.8rem; padding: 0.7rem; line-height: 1.4;
        }
        .public-kicker { font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; font-weight: 800; color: var(--odp-green); margin-bottom: 0.35rem; }
        .public-page-hero { padding: 1.5rem 0 1.35rem; background: var(--odp-wash); border-bottom: 1px solid var(--odp-line); }
        .public-page-hero h1 { font-size: clamp(1.4rem, 2.4vw, 1.85rem); color: var(--odp-navy); font-weight: 800; }
        .public-draft {
            display: inline-block; margin-top: 0.45rem; font-size: 0.72rem; font-weight: 800;
            letter-spacing: 0.04em; text-transform: uppercase; color: #92400e; background: #fef3c7;
            border-radius: 999px; padding: 0.18rem 0.5rem;
        }
        .public-note {
            margin-top: 0.85rem; font-size: 0.86rem; color: #57534e; background: #fffbeb;
            border: 1px solid #fde68a; border-radius: 0.55rem; padding: 0.7rem 0.85rem; line-height: 1.5;
        }
        .public-footer { background: #071526; color: #c9d6e5; padding: 2.1rem 0 1.45rem; border-top: 4px solid var(--odp-green-mid); }
        .public-footer-grid { display: grid; gap: 1.35rem; }
        .public-footer strong, .public-footer h2 { color: #fff; font-size: 0.95rem; letter-spacing: 0.04em; }
        .public-footer p, .public-footer a { font-size: 0.86rem; line-height: 1.6; color: #c9d6e5; }
        .public-footer a { text-decoration: none; }
        .public-footer a:hover { color: #fff; text-decoration: underline; }
        .public-footer ul { margin-top: 0.45rem; }
        .public-footer li { margin: 0.28rem 0; }
        .public-copy { margin-top: 1.25rem; padding-top: 0.95rem; border-top: 1px solid rgb(255 255 255 / 0.12); font-size: 0.8rem; color: #94a3b8; }
        .public-footer-brand { display: flex; gap: 0.75rem; align-items: center; margin-bottom: 0.7rem; }
        .public-footer-brand img { width: 2.6rem; height: 2.6rem; object-fit: contain; background: #fff; border-radius: 0.35rem; padding: 0.15rem; }
        .public-form label { display: block; font-weight: 700; font-size: 0.88rem; margin-bottom: 0.3rem; }
        .public-form input, .public-form textarea, .public-form select {
            width: 100%; border: 1px solid #cbd5e1; border-radius: 0.45rem; padding: 0.55rem 0.7rem; margin-bottom: 0.85rem;
        }
        .public-form input:focus, .public-form textarea:focus { outline: 2px solid var(--odp-green-mid); outline-offset: 1px; }
        .public-error { color: #b91c1c; font-size: 0.82rem; margin: -0.55rem 0 0.7rem; }
        .public-alert { border-radius: 0.5rem; padding: 0.75rem 0.9rem; margin-bottom: 1rem; font-size: 0.9rem; }
        .public-alert-ok { background: #ecfdf3; border: 1px solid #bbf7d0; color: #14532d; }
        .public-alert-bad { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .public-meta { font-size: 0.82rem; color: var(--odp-muted); }
        .public-prose p { margin: 0.7rem 0; line-height: 1.7; color: var(--odp-ink); }
        .public-prose h2 { margin: 1.15rem 0 0.45rem; font-size: 1.35rem; color: var(--odp-green); }
        .public-prose h3 { margin: 1rem 0 0.4rem; font-size: 1.15rem; color: var(--odp-green); }
        .public-prose ul, .public-prose ol { margin: 0.6rem 0 0.6rem 1.3rem; }
        .public-prose a { color: var(--odp-green); text-decoration: underline; }
        .public-news-layout { display: grid; gap: 1.75rem; margin-top: 0.35rem; }
        .public-news-article { max-width: 46rem; min-width: 0; }
        .public-news-article .public-prose { font-size: 1.05rem; }
        .public-news-article .public-prose p { margin: 1rem 0; line-height: 1.8; }
        .public-news-excerpt { margin: 0.85rem 0 0; font-size: 1.05rem; line-height: 1.65; color: var(--odp-ink); }
        .public-news-related h2 { font-size: 1.15rem; color: var(--odp-navy); }
        .news-featured-article {
            display: block; width: 100%; height: auto; max-height: min(28rem, 70vw);
            object-fit: contain; object-position: center; background: #e8f0ea;
            border-radius: 0.5rem;
        }
        .news-featured-card {
            display: block; width: 100%; height: 12rem; object-fit: cover; object-position: center;
            border-radius: 0.5rem; background: #e8f0ea;
        }
        .news-featured-article-ph {
            width: 100%; min-height: 10rem; aspect-ratio: 16 / 9; max-height: 16rem;
        }
        .news-featured-card-ph { width: 100%; height: 12rem; }
        @media (min-width: 960px) {
            .public-news-layout { grid-template-columns: minmax(0, 1fr) 17.5rem; align-items: start; gap: 2.25rem; }
        }
        @media (max-width: 639px) {
            .public-news-article .public-prose { font-size: 1rem; }
            .news-featured-card, .news-featured-card-ph { height: 10rem; }
        }
        .sr-only-hp {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
            pointer-events: none;
        }
        @media (min-width: 900px) {
            .public-nav { display: flex; }
            .public-menu-toggle { display: none; }
            .public-mobile-nav { display: none !important; }
            .public-hero-grid { grid-template-columns: minmax(0, 1.05fr) minmax(280px, 0.95fr); }
            .public-grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .public-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .public-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .public-footer-grid { grid-template-columns: 1.4fr 1fr 1fr 1fr; }
        }
        @media (min-width: 640px) and (max-width: 899px) {
            .public-grid-4, .public-grid-3, .public-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .public-footer-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 899px) {
            .public-nav-row { display: none; }
            .public-hero h1 { max-width: none; }
            .public-hero-frame img { height: 16.5rem; }
            .public-brand img { width: 3.35rem; height: 3.35rem; }
            .public-brand-sub { display: none; }
            .public-header-inner { gap: 0.55rem; min-height: 4.1rem; padding: 0.4rem 0; }
            .public-header-actions .public-btn { padding: 0.5rem 0.8rem; font-size: 0.82rem; }
        }
    </style>
</head>
<body class="public-page font-sans antialiased" x-data="{ open: false }">
@php
    $nav = [
        ['route' => 'home', 'label' => 'Home', 'pattern' => 'home'],
        ['route' => 'public.about', 'label' => 'About Us', 'pattern' => 'public.about'],
        ['route' => 'public.services', 'label' => 'Services', 'pattern' => 'public.services'],
        ['route' => 'public.partners', 'label' => 'Development Partners', 'pattern' => 'public.partners'],
        ['route' => 'public.projects', 'label' => 'Projects & Programmes', 'pattern' => 'public.projects'],
        ['route' => 'public.gallery', 'label' => 'Gallery', 'pattern' => 'public.gallery'],
        ['route' => 'public.management', 'label' => 'Management Team', 'pattern' => 'public.management'],
        ['route' => 'public.news', 'label' => 'News & Updates', 'pattern' => 'public.news*'],
        ['route' => 'public.contact', 'label' => 'Contact Us', 'pattern' => 'public.contact'],
    ];
@endphp
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:bg-white focus:px-3 focus:py-2">Skip to content</a>

    <div class="public-topbar">
        <div class="public-wrap public-topbar-inner">
            <span>Ondo State Government — Official website of the Development Partners Management Office</span>
            <span>Secure portal: ODPPMES</span>
        </div>
    </div>

    <header class="public-header">
        <div class="public-wrap">
            <div class="public-header-inner">
                <a href="{{ route('home') }}" class="public-brand" title="Development Partners Management Office">
                    <img src="{{ asset('images/ondo-logo.png') }}" width="70" height="70" alt="Ondo State Government coat of arms">
                    <span>
                        <span class="public-brand-kicker">Ondo State Government</span>
                        <span class="public-brand-name">DPMO</span>
                        <span class="public-brand-sub">Development Partners Management Office</span>
                    </span>
                </a>

                <div class="public-header-actions">
                    @auth
                        @if(auth()->user()->hasCmsAccess())
                            <a href="{{ route('cms.home') }}" class="public-btn public-btn-outline">Open CMS</a>
                        @else
                            <a href="{{ route('dashboard') }}" class="public-btn public-btn-outline">Open dashboard</a>
                        @endif
                    @endauth
                    <a href="{{ route('login') }}" class="public-btn public-btn-navy">Portal Login</a>
                    <button type="button"
                            class="public-menu-toggle"
                            @click="open = !open"
                            :aria-expanded="open.toString()"
                            aria-controls="public-mobile-nav">
                        <span class="sr-only">Menu</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-6 w-6" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>
            </div>

            <nav id="public-mobile-nav" class="public-mobile-nav" :class="{ 'is-open': open }" aria-label="Mobile">
                @foreach ($nav as $item)
                    <a class="public-link" href="{{ route($item['route']) }}" @click="open = false">{{ $item['label'] }}</a>
                @endforeach
            </nav>
        </div>
        <div class="public-nav-row">
            <div class="public-wrap">
                <nav class="public-nav" aria-label="Primary">
                    @foreach ($nav as $item)
                        <a class="public-link"
                           href="{{ route($item['route']) }}"
                           @if(request()->routeIs($item['pattern'])) aria-current="page" @endif>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </header>

    <main id="main-content">
        @yield('content')
    </main>

    <footer class="public-footer">
        <div class="public-wrap">
            <div class="public-footer-grid">
                <div>
                    <div class="public-footer-brand">
                        <img src="{{ asset('images/ondo-logo.png') }}" width="42" height="42" alt="">
                        <p><strong>Ondo State Government</strong></p>
                    </div>
                    <p>Development Partners Management Office (DPMO)</p>
                    <p>ODPPMES is the authorised monitoring portal for officers issued with an account. Public registration is not available.</p>
                    <p>Official address, telephone and email will be published when confirmed by the Office.</p>
                </div>
                <div>
                    <h2>Website</h2>
                    <ul>
                        <li><a href="{{ route('public.about') }}">About Us</a></li>
                        <li><a href="{{ route('public.services') }}">Services</a></li>
                        <li><a href="{{ route('public.projects') }}">Projects &amp; Programmes</a></li>
                        <li><a href="{{ route('public.news') }}">News &amp; Updates</a></li>
                    </ul>
                </div>
                <div>
                    <h2>Office</h2>
                    <ul>
                        <li><a href="{{ route('public.partners') }}">Development Partners</a></li>
                        <li><a href="{{ route('public.management') }}">Management Team</a></li>
                        <li><a href="{{ route('public.gallery') }}">Gallery</a></li>
                        <li><a href="{{ route('public.contact') }}">Contact Us</a></li>
                    </ul>
                </div>
                <div>
                    <h2>Portal</h2>
                    <ul>
                        <li><a href="{{ route('login') }}">Portal Login</a></li>
                        @auth
                            @if(auth()->user()->hasCmsAccess())
                                <li><a href="{{ route('cms.home') }}">Open CMS</a></li>
                            @else
                                <li><a href="{{ route('dashboard') }}">Open dashboard</a></li>
                            @endif
                        @endauth
                    </ul>
                    <p>For login assistance, contact the administrator who issued your credentials.</p>
                </div>
            </div>
            <p class="public-copy">&copy; {{ date('Y') }} Ondo State Government. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
