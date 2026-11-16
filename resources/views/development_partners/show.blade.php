@extends('layouts.admin')

@section('title', 'Development Partner')

@section('content')

@php
    $websiteValue = trim((string) ($development_partner->website ?? ''));
    $websiteHref = null;

    if ($websiteValue !== '' && filter_var($websiteValue, FILTER_VALIDATE_URL)) {
        $scheme = strtolower((string) parse_url($websiteValue, PHP_URL_SCHEME));

        if (in_array($scheme, ['http', 'https'], true)) {
            $websiteHref = $websiteValue;
        }
    }
@endphp

<div class="mx-auto max-w-4xl space-y-6">

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-green-900">{{ $development_partner->partner_name }}</h2>
                @if(filled($development_partner->acronym))
                    <p class="mt-1 text-gray-600">{{ $development_partner->acronym }}</p>
                @endif
                <p class="mt-2 text-sm text-gray-700">{{ $development_partner->partner_type }}</p>
            </div>

            <div class="flex flex-col items-start gap-2 sm:items-end">
                @if($development_partner->status)
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">Active</span>
                @else
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700">Inactive</span>
                @endif
                <p class="text-sm text-gray-600">Linked projects: <span class="font-semibold text-gray-900">{{ $development_partner->projects_count }}</span></p>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Partner Identity</h3>
        <dl class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <dt class="text-sm text-gray-500">Acronym</dt>
                <dd class="font-semibold">{{ filled($development_partner->acronym) ? $development_partner->acronym : '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Type</dt>
                <dd class="font-semibold">{{ $development_partner->partner_type }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Country</dt>
                <dd class="font-semibold">{{ filled($development_partner->country) ? $development_partner->country : '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Status</dt>
                <dd class="font-semibold">{{ $development_partner->status ? 'Active' : 'Inactive' }}</dd>
            </div>
        </dl>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Contact Information</h3>
        <dl class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <dt class="text-sm text-gray-500">Contact Person</dt>
                <dd class="font-semibold">{{ filled($development_partner->contact_person) ? $development_partner->contact_person : '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Email</dt>
                <dd class="font-semibold">{{ filled($development_partner->email) ? $development_partner->email : '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Phone</dt>
                <dd class="font-semibold">{{ filled($development_partner->phone) ? $development_partner->phone : '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Website</dt>
                <dd class="font-semibold break-all">
                    @if($websiteHref)
                        <a href="{{ $websiteHref }}" class="text-green-800 underline" rel="noopener noreferrer">{{ $websiteValue }}</a>
                    @elseif($websiteValue !== '')
                        {{ $websiteValue }}
                    @else
                        -
                    @endif
                </dd>
            </div>
        </dl>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-green-900">Address</h3>
        <dl>
            <dt class="text-sm text-gray-500">Address</dt>
            <dd class="font-semibold">{{ filled($development_partner->address) ? $development_partner->address : '-' }}</dd>
        </dl>
    </div>

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('development-partners.edit', $development_partner) }}"
           class="rounded-lg bg-blue-600 px-5 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
            Edit
        </a>
        <a href="{{ route('development-partners.index') }}"
           class="rounded-lg bg-gray-500 px-5 py-2 text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
            Back
        </a>
    </div>

</div>

@endsection
