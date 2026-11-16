@extends('layouts.cms')
@section('title', $highlight->title)
@section('content')
@include('cms.partials.flash')
<div class="max-w-4xl space-y-4 rounded-lg border bg-white p-6 shadow">
    <div class="flex justify-between">
        <h2 class="text-2xl font-bold text-green-900">{{ $highlight->title }}</h2>
        @can('update', $highlight)
            <a href="{{ route('cms.programme-highlights.edit', $highlight) }}" class="cms-btn cms-btn-primary">Edit</a>
        @endcan
    </div>
    <p>{{ $highlight->implementing_mda_name }} · {{ $highlight->partner_name }} · {{ $highlight->sector }} · {{ $highlight->location }}</p>
    <p>Public status: {{ $highlight->public_status }}</p>
    <p class="whitespace-pre-wrap">{{ $highlight->summary }}</p>
    @include('cms.partials.workflow-actions', ['content' => $highlight, 'type' => 'programme-highlights'])
</div>
@endsection
