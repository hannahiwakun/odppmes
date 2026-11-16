@extends('layouts.cms')
@section('title', 'Edit office contact')
@section('content')
@include('cms.partials.flash')
<div class="cms-card max-w-3xl p-6">
    <h2 class="text-2xl font-bold text-green-950">Edit office contact</h2>
    <p class="cms-lede">Update the published contact details. Workflow actions remain on the contact page.</p>
    @include('cms.partials.required-note')
    <form method="POST" action="{{ route('cms.office-contact.update', $contact) }}" class="mt-6 space-y-6">
        @csrf
        @method('PUT')
        @include('cms.office-contact.form', ['contact' => $contact])
        @include('cms.partials.form-actions', ['submitLabel' => 'Save changes', 'cancelUrl' => route('cms.office-contact.show')])
    </form>
</div>
@endsection
