<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'ODPPMES')
    </title>

    @vite(['resources/css/app.css','resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }

        .admin-shell { min-height: 100vh; }
        .admin-sidebar { width: 18rem; flex-shrink: 0; }
        .admin-main { min-width: 0; }
        .admin-menu-button,
        .admin-sidebar-close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.5rem;
        }
        .admin-menu-button {
            border: 1px solid #166534;
            background: #fff;
            color: #14532d;
        }
        .admin-sidebar-close {
            border: 1px solid #15803d;
            background: transparent;
            color: #fff;
        }
        .admin-overlay { display: none; }

        @media (max-width: 1279px) {
            .admin-sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                z-index: 40;
                transform: translateX(-100%);
                transition: transform 200ms ease;
                overflow-y: auto;
            }
            .admin-sidebar.is-open { transform: translateX(0); }
            .admin-overlay.is-open {
                display: block;
                position: fixed;
                inset: 0;
                z-index: 30;
                background: rgba(0, 0, 0, 0.45);
            }
            .admin-main { width: 100%; }
            .admin-header {
                flex-wrap: wrap;
                gap: 0.75rem;
                padding-left: 1rem;
                padding-right: 1rem;
            }
            .admin-header h1 {
                font-size: 1.5rem;
                line-height: 2rem;
            }
            .admin-content { padding: 1rem; }
            body.sidebar-open { overflow: hidden; }
        }

        @media (min-width: 1280px) {
            .admin-shell { display: flex; }
            .admin-sidebar { position: static; transform: none; }
            .admin-menu-button,
            .admin-sidebar-toolbar,
            .admin-overlay { display: none !important; }
            .admin-main { flex: 1; }
            body.sidebar-open { overflow: auto; }
        }

        @media (prefers-reduced-motion: reduce) {
            .admin-sidebar { transition: none; }
        }

        .admin-menu-button:focus-visible {
            outline: 2px solid #166534;
            outline-offset: 2px;
        }
        .admin-sidebar a:focus-visible,
        .admin-sidebar button:focus-visible {
            outline: 2px solid #fff;
            outline-offset: 2px;
        }
    </style>

</head>


<body
    class="bg-gray-100"
    x-data="{ sidebarOpen: false }"
    :class="{ 'sidebar-open': sidebarOpen }"
    @keydown.escape.window="if (sidebarOpen) { sidebarOpen = false; $nextTick(() => $refs.openMenu.focus()) }"
>


<div
    class="admin-overlay"
    :class="{ 'is-open': sidebarOpen }"
    @click="sidebarOpen = false"
    x-cloak
></div>

<div class="admin-shell">


    <!-- Sidebar -->

    <aside id="admin-sidebar" class="admin-sidebar w-72 bg-green-900 text-white shadow-xl flex flex-col" :class="{ 'is-open': sidebarOpen }" aria-label="Main menu">

        <div class="admin-sidebar-toolbar flex justify-end p-3">
            <button
                type="button"
                class="admin-sidebar-close"
                x-ref="closeMenu"
                @click="sidebarOpen = false"
            >
                <span class="sr-only">Close menu</span>
                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Branding -->

        <div class="p-5 border-b border-green-700 text-center">


            <img src="{{ asset('images/ondo-logo.png') }}"
                class="w-28 h-28 mx-auto bg-white p-2 object-contain"
                 alt="Ondo State Logo">


            <h1 class="text-2xl font-bold mt-3">
                ODPPMES
            </h1>


            <p class="text-xs text-green-200 mt-1 leading-5">
                Development Partners<br>
                Project Monitoring System
            </p>


        </div>




        <!-- Navigation -->

        <nav class="flex-1 px-4 py-6 space-y-2">

            @if(! auth()->user()->isWebsiteAdmin())
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg
               {{ request()->routeIs('dashboard') 
               ? 'bg-green-700' 
               : 'hover:bg-green-800' }}">

                🏠
                <span>{{ auth()->user()->isMdaOfficer() ? 'My MDA Dashboard' : 'Dashboard' }}</span>

            </a>
            @endif

            @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('cms.home') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-green-800">
                <span>CMS Dashboard</span>
            </a>
            @endif



            @if(auth()->user()->isSuperAdmin() || auth()->user()->isMEO())

            <a href="{{ route('mdas.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg
               {{ request()->routeIs('mdas.index') || request()->routeIs('mdas.show') || request()->routeIs('mdas.create') || request()->routeIs('mdas.edit')
               ? 'bg-green-700' 
               : 'hover:bg-green-800' }}">

                🏢
                <span>MDAs</span>

            </a>

            @endif

            @if(auth()->user()->isSuperAdmin())

            <a href="{{ route('development-partners.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg
               {{ request()->routeIs('development-partners.*') 
               ? 'bg-green-700' 
               : 'hover:bg-green-800' }}">

                🤝
                <span>Development Partners</span>

            </a>

            @endif

            @if(auth()->user()->isMdaOfficer())

            <a href="{{ route('mdas.mine') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg
               {{ request()->routeIs('mdas.mine')
               ? 'bg-green-700'
               : 'hover:bg-green-800' }}">

                🏢
                <span>My MDA Profile</span>

            </a>

            @endif




            @if(
                auth()->user()->isSuperAdmin() ||
                auth()->user()->isMEO() ||
                auth()->user()->isMdaOfficer()
            )


            <a href="{{ route('projects.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg
               {{ request()->routeIs('projects.*') 
               ? 'bg-green-700' 
               : 'hover:bg-green-800' }}">

                📁
                <span>{{ auth()->user()->isMdaOfficer() ? 'My Projects' : 'Projects' }}</span>

            </a>



            <a href="{{ route('monitorings.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg
               {{ request()->routeIs('monitorings.*') 
               ? 'bg-green-700' 
               : 'hover:bg-green-800' }}">

                📍
                <span>Monitoring</span>

            </a>


            @endif





            @if(
                auth()->user()->isSuperAdmin() ||
                auth()->user()->isMEO() ||
                auth()->user()->isViewer() ||
                auth()->user()->isMdaOfficer()
            )


            <a href="{{ route('reports.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg
               {{ request()->routeIs('reports.*') 
               ? 'bg-green-700' 
               : 'hover:bg-green-800' }}">

                📊
                <span>{{ auth()->user()->isMdaOfficer() ? 'My Reports' : 'Reports' }}</span>

            </a>

            @endif

            @if(
                auth()->user()->isSuperAdmin() ||
                auth()->user()->isMEO() ||
                auth()->user()->isViewer()
            )

            <a href="{{ route('project.map') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg
               {{ request()->routeIs('project.map') 
               ? 'bg-green-700' 
               : 'hover:bg-green-800' }}">

                🗺️
                <span>Project Map</span>

            </a>


            @endif





            @if(auth()->user()->isSuperAdmin())


            <a href="{{ route('users.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg
               {{ request()->routeIs('users.*') 
               ? 'bg-green-700' 
               : 'hover:bg-green-800' }}">

                👥
                <span>Users</span>

            </a>



            <a href="{{ route('office.circular') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg
               {{ request()->routeIs('office.circular')
               ? 'bg-green-700'
               : 'hover:bg-green-800' }}">

                <span>Circular to MDAs</span>

            </a>

            <a href="{{ route('office.mda-return') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg
               {{ request()->routeIs('office.mda-return')
               ? 'bg-green-700'
               : 'hover:bg-green-800' }}">

                <span>MDA return form</span>

            </a>

            <a href="{{ route('audit.logs') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg
               {{ request()->routeIs('audit.logs') 
               ? 'bg-green-700' 
               : 'hover:bg-green-800' }}">

                📜
                <span>Audit Logs</span>

            </a>


            @endif


        </nav>




        <!-- User Profile -->

        <div class="border-t border-green-700 p-5">


            <p class="font-bold">
                {{ Auth::user()->name }}
            </p>


            <p class="text-sm text-green-200 mb-4">
                {{ Auth::user()->role }}
            </p>

            <a href="{{ route('profile.edit') }}"
               class="mb-3 flex items-center justify-center rounded-lg border border-green-600 px-4 py-2 text-sm hover:bg-green-800">
                Profile
            </a>

            <form method="POST" action="{{ route('logout') }}">

                @csrf


                <button
                    type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 py-2 rounded-lg">

                    Logout

                </button>


            </form>


        </div>


    </aside>





    <!-- Main Content -->


    <main class="admin-main flex-1">


        <header class="admin-header bg-white shadow px-8 py-5 flex justify-between items-center">

            <div class="flex items-center gap-3 min-w-0">

                <button
                    type="button"
                    class="admin-menu-button"
                    x-ref="openMenu"
                    @click="sidebarOpen = true; $nextTick(() => $refs.closeMenu.focus())"
                    aria-controls="admin-sidebar"
                    :aria-expanded="sidebarOpen ? 'true' : 'false'"
                >
                    <span class="sr-only">Open menu</span>
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <h1 class="text-3xl font-bold text-gray-800">

                    @yield('title')

                </h1>

            </div>


            <div class="text-right">

                <p class="font-semibold text-green-800">
                    Ondo State Government
                </p>

                <p class="text-sm text-gray-500">
                    ODPPMES Portal
                </p>

            </div>


        </header>




        <section class="admin-content p-8">

            @yield('content')

        </section>


    </main>



</div>


</body>

</html>