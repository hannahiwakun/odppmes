@extends('layouts.admin')

@section('title','Edit Project')

@section('content')

<div class="max-w-6xl mx-auto bg-white shadow rounded-lg p-6">

    <h2 class="text-2xl font-bold mb-6">
        Edit Project
    </h2>

    @if ($errors->any())

        <div class="bg-red-100 border border-red-400 text-red-700 p-4 rounded mb-5">

            <ul class="list-disc pl-5">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <form action="{{ route('projects.update', $project->id) }}" method="POST">

        @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-6">

            <div>
                <label class="block font-semibold mb-2">
                    Project Title
                </label>

                <input
                    type="text"
                    name="project_title"
                    value="{{ old('project_title', $project->project_title) }}"
                    class="w-full border rounded px-4 py-2"
                    required>
            </div>

            <div>
                <label class="block font-semibold mb-2">
                    Project Code
                </label>

                <input
                    type="text"
                    name="project_code"
                    value="{{ old('project_code', $project->project_code) }}"
                    class="w-full border rounded px-4 py-2">
            </div>

            <div>
                <label class="block font-semibold mb-2">
                    MDA
                </label>

                <select
                    name="mda_id"
                    class="w-full border rounded px-4 py-2"
                    required>

                    @foreach($mdas as $mda)

                        <option
                            value="{{ $mda->id }}"
                            {{ $project->mda_id == $mda->id ? 'selected' : '' }}>

                            {{ $mda->name }}

                        </option>

                    @endforeach

                </select>
            </div>

            <div>
                <label class="block font-semibold mb-2">
                    Development Partner
                </label>

                <select
                    name="development_partner_id"
                    class="w-full border rounded px-4 py-2">

                    <option value="">None</option>

                    @foreach($developmentPartners as $partner)

                        <option
                            value="{{ $partner->id }}"
                            {{ $project->development_partner_id == $partner->id ? 'selected' : '' }}>

                            {{ $partner->partner_name }}

                        </option>

                    @endforeach

                </select>
            </div>

            <div>
                <label class="block font-semibold mb-2">
                    Sector
                </label>

                <input
                    type="text"
                    name="sector"
                    value="{{ old('sector', $project->sector) }}"
                    class="w-full border rounded px-4 py-2">
            </div>

            <div>
                <label class="block font-semibold mb-2">
                    LGA
                </label>

                <input
                    type="text"
                    name="lga"
                    value="{{ old('lga', $project->lga) }}"
                    class="w-full border rounded px-4 py-2">
            </div>

            <div>
                <label class="block font-semibold mb-2">
                    Status
                </label>

                <select
                    name="status"
                    class="w-full border rounded px-4 py-2">

                    <option value="Planning" {{ $project->status=='Planning' ? 'selected' : '' }}>Planning</option>
                    <option value="Ongoing" {{ $project->status=='Ongoing' ? 'selected' : '' }}>Ongoing</option>
                    <option value="Completed" {{ $project->status=='Completed' ? 'selected' : '' }}>Completed</option>
                    <option value="Suspended" {{ $project->status=='Suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="Cancelled" {{ $project->status=='Cancelled' ? 'selected' : '' }}>Cancelled</option>

                </select>
            </div>

            <div>
                <label class="block font-semibold mb-2">
                    Progress (%)
                </label>

                <input
                    type="number"
                    name="progress"
                    min="0"
                    max="100"
                    value="{{ old('progress', $project->progress) }}"
                    class="w-full border rounded px-4 py-2">
            </div>

        </div>

        <div class="mt-8 flex gap-3">

            <button
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded border border-blue-800">

                Update Project

            </button>

            <a
                href="{{ route('projects.index') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">

                Cancel

            </a>

        </div>

    </form>

</div>

@endsection