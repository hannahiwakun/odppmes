@extends('layouts.cms')
@section('title', 'New management profile')
@section('content')
@include('cms.partials.flash')
<div class="cms-card max-w-3xl p-6">
    <h2 class="text-2xl font-bold text-green-950">New management profile</h2>
    <p class="cms-lede">Create a public profile for the management team. Submit it for approval after saving.</p>
    @include('cms.partials.required-note')
    <form method="POST" action="{{ route('cms.management-profiles.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @include('cms.management-profiles.form')
        @include('cms.partials.form-actions', ['submitLabel' => 'Save draft', 'cancelUrl' => route('cms.management-profiles.index')])
    </form>
</div>
@endsection
