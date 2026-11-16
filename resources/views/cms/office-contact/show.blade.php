@extends('layouts.cms')
@section('title', 'Office Contact')
@section('content')
@include('cms.partials.flash')
<div class="cms-card max-w-4xl space-y-4 p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-green-950">Office contact information</h2>
            <p class="cms-lede">Public contact details shown after this record is approved and published.</p>
        </div>
        @can('update', $contact)
            <a href="{{ route('cms.office-contact.edit') }}" class="cms-btn cms-btn-primary">Edit</a>
        @endcan
    </div>
    <dl class="grid gap-4 text-sm sm:grid-cols-2">
        <div class="sm:col-span-2">
            <dt class="font-semibold text-gray-800">Official address</dt>
            <dd class="whitespace-pre-wrap">{{ $contact->address }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-gray-800">Primary telephone</dt>
            <dd>{{ $contact->phone }}</dd>
        </div>
        <div>
            <dt class="font-semibold text-gray-800">Official email</dt>
            <dd>{{ $contact->email }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="font-semibold text-gray-800">Office hours and working days</dt>
            <dd>{{ $contact->hours }}</dd>
        </div>
    </dl>
    @include('cms.partials.workflow-actions', ['content' => $contact, 'type' => 'office-contact'])
</div>
@endsection
