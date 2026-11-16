@extends('layouts.admin')

@section('title', 'My MDA Profile')

@section('content')

<div class="mx-auto max-w-3xl rounded-lg border border-amber-200 bg-amber-50 p-8 shadow">
    <h2 class="text-2xl font-bold text-green-900">No MDA assignment</h2>
    <p class="mt-3 text-sm text-gray-700">
        Your account is an MDA Officer account, but a Super Administrator has not assigned a ministry yet.
        Project records stay hidden until that assignment is made.
    </p>
    <a href="{{ route('dashboard') }}"
       class="mt-6 inline-flex rounded-lg bg-green-700 px-5 py-2 text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
        Back to dashboard
    </a>
</div>

@endsection
