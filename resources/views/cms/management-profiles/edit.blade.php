@extends('layouts.cms')
@section('title', 'Edit management profile')
@section('content')
@include('cms.partials.flash')
<div class="cms-card max-w-3xl p-6">
    <h2 class="text-2xl font-bold text-green-950">Edit management profile</h2>
    <p class="cms-lede">Update the public profile. Workflow actions remain on the profile page.</p>
    @include('cms.partials.required-note')
    <form method="POST" action="{{ route('cms.management-profiles.update', $profile) }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('PUT')
        @include('cms.management-profiles.form', ['profile' => $profile])
        @include('cms.partials.form-actions', ['submitLabel' => 'Save changes', 'cancelUrl' => route('cms.management-profiles.show', $profile)])
    </form>
</div>
@endsection
