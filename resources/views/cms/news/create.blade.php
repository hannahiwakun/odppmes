@extends('layouts.cms')

@section('title', 'New news article')

@section('content')
@include('cms.partials.flash')
<div class="cms-card max-w-5xl p-6">
    <h2 class="text-2xl font-bold text-green-950">New news article</h2>
    <p class="cms-lede">Draft a public announcement. Save a draft first. Submit for Super Administrator approval from the article page after you have reviewed the staff preview.</p>
    @include('cms.partials.required-note')
    <form method="POST" action="{{ route('cms.news.store') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @include('cms.news.form')
        @include('cms.partials.form-actions', ['submitLabel' => 'Save draft', 'cancelUrl' => route('cms.news.index')])
    </form>
</div>
@endsection
