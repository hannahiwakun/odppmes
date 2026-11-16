@extends('layouts.cms')
@section('title', 'New homepage banner')
@section('content')
@include('cms.partials.flash')
<div class="cms-card max-w-3xl p-6">
    <h2 class="text-2xl font-bold text-green-950">New homepage banner</h2>
    <p class="cms-lede">Create a homepage notice or feature. Submit it for approval after saving.</p>
    @include('cms.partials.required-note')
    <form method="POST" action="{{ route('cms.homepage-banners.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @include('cms.homepage-banners.form')
        @include('cms.partials.form-actions', ['submitLabel' => 'Save draft', 'cancelUrl' => route('cms.homepage-banners.index')])
    </form>
</div>
@endsection
