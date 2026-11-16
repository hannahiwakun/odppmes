@extends('layouts.cms')
@section('title', 'New programme highlight')
@section('content')
@include('cms.partials.flash')
<div class="cms-card max-w-3xl p-6">
    <h2 class="text-2xl font-bold text-green-950">New programme highlight</h2>
    <p class="cms-lede">Publish a public summary of a programme. This is separate from confidential operational project records.</p>
    @include('cms.partials.required-note')
    <form method="POST" action="{{ route('cms.programme-highlights.store') }}" class="mt-6 space-y-6">
        @csrf
        @include('cms.programme-highlights.form')
        @include('cms.partials.form-actions', ['submitLabel' => 'Save draft', 'cancelUrl' => route('cms.programme-highlights.index')])
    </form>
</div>
@endsection
