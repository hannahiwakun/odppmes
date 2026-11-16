@extends('layouts.cms')
@section('title', 'New public partner')
@section('content')
@include('cms.partials.flash')
<div class="cms-card max-w-3xl p-6">
    <h2 class="text-2xl font-bold text-green-950">New public partner</h2>
    <p class="cms-lede">Add a public website record for a stakeholder organisation. This does not create an operational partner account.</p>
    @include('cms.partials.required-note')
    <form method="POST" action="{{ route('cms.public-partners.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @include('cms.public-partners.form')
        @include('cms.partials.form-actions', ['submitLabel' => 'Save draft', 'cancelUrl' => route('cms.public-partners.index')])
    </form>
</div>
@endsection
