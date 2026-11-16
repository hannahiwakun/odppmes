@extends('layouts.cms')
@section('title', 'Edit programme highlight')
@section('content')
@include('cms.partials.flash')
<div class="cms-card max-w-3xl p-6">
    <h2 class="text-2xl font-bold text-green-950">Edit programme highlight</h2>
    <p class="cms-lede">Update the public summary. Workflow actions remain on the highlight page.</p>
    @include('cms.partials.required-note')
    <form method="POST" action="{{ route('cms.programme-highlights.update', $highlight) }}" class="mt-6 space-y-6">
        @csrf
        @method('PUT')
        @include('cms.programme-highlights.form', ['highlight' => $highlight])
        @include('cms.partials.form-actions', ['submitLabel' => 'Save changes', 'cancelUrl' => route('cms.programme-highlights.show', $highlight)])
    </form>
</div>
@endsection
