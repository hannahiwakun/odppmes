<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Sign in — DPMO Website and Content Management System</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            .dpmo-auth-shell { min-height: 100vh; background: #f8faf8; }
            .dpmo-auth-card {
                width: 100%;
                max-width: 28rem;
                border: 1px solid #dcfce7;
                background: #fff;
                border-radius: 0.75rem;
            }
            .dpmo-auth-submit {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 2.75rem;
                width: 100%;
                padding: 0.5rem 1.25rem;
                border-radius: 0.5rem;
                background: #15803d;
                color: #fff;
                font-size: 0.875rem;
                font-weight: 600;
            }
            .dpmo-auth-submit:hover { background: #166534; }
            .dpmo-auth-submit:focus-visible,
            .dpmo-auth-link:focus-visible,
            .dpmo-auth-shell input:focus-visible {
                outline: 2px solid #166534;
                outline-offset: 2px;
            }
            .dpmo-auth-link { color: #14532d; text-decoration: underline; }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="dpmo-auth-shell flex flex-col items-center px-4 py-8 sm:justify-center sm:px-6">
            <header class="mb-6 w-full max-w-md text-center">
                <a href="{{ url('/') }}" class="inline-flex flex-col items-center gap-3 rounded-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-800">
                    <img src="{{ asset('images/ondo-logo.png') }}" alt="Ondo State coat of arms" class="h-20 w-20 bg-white object-contain p-1">
                    <span class="sr-only">Ondo State Development Partners Management Office home</span>
                </a>
                <p class="mt-4 text-sm font-semibold uppercase tracking-wide text-green-800">Ondo State</p>
                <h1 class="mt-1 text-xl font-bold leading-snug text-green-950 sm:text-2xl">Ondo State Development Partners Management Office</h1>
                <p class="mt-2 text-sm text-gray-700">Website and Content Management System</p>
            </header>

            <div class="dpmo-auth-card px-5 py-6 sm:px-8">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
