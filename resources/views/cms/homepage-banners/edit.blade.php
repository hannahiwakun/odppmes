@extends('layouts.cms')
@section('title', 'Edit homepage banner')
@section('content')
@include('cms.partials.flash')
<div class="cms-card max-w-3xl p-6">
    <h2 class="text-2xl font-bold text-green-950">Edit homepage banner</h2>
    <p class="cms-lede">Update the banner. Workflow actions remain on the banner page.</p>
    @include('cms.partials.required-note')
    <form method="POST" action="{{ route('cms.homepage-banners.update', $banner) }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('PUT')
        @include('cms.homepage-banners.form', ['banner' => $banner])
        @include('cms.partials.form-actions', ['submitLabel' => 'Save changes', 'cancelUrl' => route('cms.homepage-banners.show', $banner)])
    </form>
</div>
@endsection
