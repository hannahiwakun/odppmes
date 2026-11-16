@extends('layouts.cms')
@section('title', 'Edit public partner')
@section('content')
@include('cms.partials.flash')
<div class="cms-card max-w-3xl p-6">
    <h2 class="text-2xl font-bold text-green-950">Edit public partner</h2>
    <p class="cms-lede">Update the public website record. Workflow actions remain on the partner page.</p>
    @include('cms.partials.required-note')
    <form method="POST" action="{{ route('cms.public-partners.update', $partner) }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('PUT')
        @include('cms.public-partners.form', ['partner' => $partner])
        @include('cms.partials.form-actions', ['submitLabel' => 'Save changes', 'cancelUrl' => route('cms.public-partners.show', $partner)])
    </form>
</div>
@endsection
