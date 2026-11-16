@extends('layouts.cms')
@section('title', 'New gallery album')
@section('content')
@include('cms.partials.flash')
<div class="cms-card max-w-3xl p-6">
    <h2 class="text-2xl font-bold text-green-950">New gallery album</h2>
    <p class="cms-lede">Create an album first, then upload images from the album page. Images stay private until cleared for public use.</p>
    @include('cms.partials.required-note')
    <form method="POST" action="{{ route('cms.gallery-albums.store') }}" class="mt-6 space-y-6">
        @csrf
        @include('cms.gallery-albums.form')
        @include('cms.partials.form-actions', ['submitLabel' => 'Save draft', 'cancelUrl' => route('cms.gallery-albums.index')])
    </form>
</div>
@endsection
