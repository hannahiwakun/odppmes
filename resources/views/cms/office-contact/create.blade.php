@extends('layouts.cms')
@section('title', 'Office contact')
@section('content')
@include('cms.partials.flash')
<div class="cms-card max-w-3xl p-6">
    <h2 class="text-2xl font-bold text-green-950">Office contact information</h2>
    <p class="cms-lede">Only one office contact record is used on the public website. Enter authorised information only.</p>
    @include('cms.partials.required-note')
    <form method="POST" action="{{ route('cms.office-contact.store') }}" class="mt-6 space-y-6">
        @csrf
        @include('cms.office-contact.form')
        @include('cms.partials.form-actions', ['submitLabel' => 'Save draft', 'cancelUrl' => route('cms.home')])
    </form>
</div>
@endsection
