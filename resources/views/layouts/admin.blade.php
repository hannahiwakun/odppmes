<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ODPPMES</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

</head>

<body class="bg-gray-100">

<div class="flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-green-900 min-h-screen text-white">

        <div class="p-5 text-2xl font-bold border-b border-green-700">
            ODPPMES
        </div>

        <nav class="mt-5">

            <a href="{{ route('dashboard') }}"
class="block px-5 py-3
{{ request()->routeIs('dashboard') ? 'bg-green-700' : 'hover:bg-green-700' }}">
Dashboard
</a>

            <a href="{{ route('mdas.index') }}"
class="block px-5 py-3
{{ request()->routeIs('mdas.*') ? 'bg-green-700' : 'hover:bg-green-700' }}">
MDAs
</a>

           <a href="{{ route('development-partners.index') }}"
class="block px-5 py-3
{{ request()->routeIs('development-partners.*') ? 'bg-green-700' : 'hover:bg-green-700' }}">
    Development Partners
</a>

            <a href="#" class="block px-5 py-3 hover:bg-green-700">
                Projects
            </a>

            <a href="#" class="block px-5 py-3 hover:bg-green-700">
                Reports
            </a>

        </nav>

    </aside>

    <!-- Main Content -->
    <main class="flex-1">

        <div class="bg-white shadow p-5 flex justify-between">

            <h1 class="text-2xl font-bold">
                @yield('title')
            </h1>

            <div class="flex items-center space-x-4">

    <span class="font-semibold">
        {{ Auth::user()->name }}
    </span>

    <form method="POST" action="{{ route('logout') }}">
        @csrf

        <button
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
            Logout
        </button>

    </form>

</div>

        </div>

        <div class="p-8">

            @yield('content')

        </div>

    </main>

</div>

</body>
</html>