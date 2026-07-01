@extends('layouts.admin')

@section('title', 'Add Monitoring')

@section('content')

<div class="max-w-5xl mx-auto bg-white shadow rounded-lg p-6">

    <h2 class="text-2xl font-bold mb-6">
        New Project Monitoring
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

    <form action="{{ route('monitorings.store') }}" method="POST" enctype="multipart/form-data">

        @csrf

        <div class="grid grid-cols-2 gap-6">

            <div>
                <label class="block font-semibold mb-2">Project</label>

                <select name="project_id"
                        class="w-full border rounded px-4 py-2"
                        required>

                    <option value="">Select Project</option>

                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">
                            {{ $project->project_title }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div>
                <label class="block font-semibold mb-2">Monitoring Date</label>

                <input
                    type="date"
                    name="monitoring_date"
                    value="{{ date('Y-m-d') }}"
                    class="w-full border rounded px-4 py-2"
                    required>
            </div>

            <div>
                <label class="block font-semibold mb-2">Progress Before (%)</label>

                <input
                    type="number"
                    name="progress_before"
                    min="0"
                    max="100"
                    value="0"
                    class="w-full border rounded px-4 py-2">
            </div>

            <div>
                <label class="block font-semibold mb-2">Progress After (%)</label>

                <input
                    type="number"
                    name="progress_after"
                    min="0"
                    max="100"
                    value="0"
                    class="w-full border rounded px-4 py-2">
            </div>

            <div>
                <label class="block font-semibold mb-2">Status</label>

                <select
                    name="status"
                    class="w-full border rounded px-4 py-2">

                    <option>Planning</option>
                    <option>Ongoing</option>
                    <option>Completed</option>
                    <option>Suspended</option>
                    <option>Cancelled</option>

                </select>
            </div>

        </div>

        <div class="mt-6">

            <label class="block font-semibold mb-2">Findings</label>

            <textarea
                name="findings"
                rows="4"
                class="w-full border rounded px-4 py-2"></textarea>

        </div>

        <div class="mt-6">

            <label class="block font-semibold mb-2">Challenges</label>

            <textarea
                name="challenges"
                rows="3"
                class="w-full border rounded px-4 py-2"></textarea>

        </div>

        <div class="mt-6">

            <label class="block font-semibold mb-2">Recommendations</label>

            <textarea
                name="recommendations"
                rows="3"
                class="w-full border rounded px-4 py-2"></textarea>

        </div>
<div class="mt-6">

    <label class="block font-semibold mb-2">
        Monitoring Photo
    </label>

    <input
        type="file"
        name="photo"
        accept="image/*"
        class="w-full border rounded px-4 py-2">

    <p class="text-sm text-gray-500 mt-1">
        Upload a photo taken during the monitoring visit.
    </p>

</div>
        <div class="mt-8 flex gap-3">

            <button
                type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded border border-blue-800">

                Save Monitoring

            </button>

            <a
                href="{{ route('monitorings.index') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">

                Cancel

            </a>

        </div>

    </form>

</div>

@endsection