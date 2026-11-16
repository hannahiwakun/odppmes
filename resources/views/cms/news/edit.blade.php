@extends('layouts.cms')

@section('title', 'Edit news article')

@section('content')
@include('cms.partials.flash')
<div class="cms-card max-w-5xl p-6">
    <h2 class="text-2xl font-bold text-green-950">Edit news article</h2>
    <p class="cms-lede">Update the draft or returned article. Saving does not change workflow status. Submit, approve and publish remain separate actions.</p>
    @include('cms.partials.required-note')
    <form method="POST" action="{{ route('cms.news.update', $article) }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('cms.news.form', ['article' => $article])
        @include('cms.partials.form-actions', ['submitLabel' => 'Save changes', 'cancelUrl' => route('cms.news.show', $article)])
    </form>
</div>
@endsection
