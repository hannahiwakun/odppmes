@extends('layouts.cms')
@section('title', 'Edit gallery album')
@section('content')
@include('cms.partials.flash')
<div class="cms-card max-w-3xl p-6">
    <h2 class="text-2xl font-bold text-green-950">Edit gallery album</h2>
    <p class="cms-lede">Update album details. Images are managed on the album page.</p>
    @include('cms.partials.required-note')
    <form method="POST" action="{{ route('cms.gallery-albums.update', $album) }}" class="mt-6 space-y-6">
        @csrf
        @method('PUT')
        @include('cms.gallery-albums.form', ['album' => $album])
        @include('cms.partials.form-actions', ['submitLabel' => 'Save changes', 'cancelUrl' => route('cms.gallery-albums.show', $album)])
    </form>
</div>
@endsection
