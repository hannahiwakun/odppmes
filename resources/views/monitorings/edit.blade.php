@extends('layouts.admin')

@section('title', 'Edit Monitoring')

@section('content')

<div class="max-w-5xl mx-auto bg-white shadow rounded-lg p-6">

    <h2 class="text-2xl font-bold mb-6">
        Edit Monitoring Record
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

   <form action="{{ route('monitorings.update', $monitoring->id) }}" method="POST" enctype="multipart/form-data">

        @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-6">

            <div>
                <label class="block font-semibold mb-2">Project</label>

                <select
                    name="project_id"
                    class="w-full border rounded px-4 py-2"
                    required>

                    @foreach($projects as $project)

                        <option value="{{ $project->id }}"
                            {{ $monitoring->project_id == $project->id ? 'selected' : '' }}>

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
                    value="{{ $monitoring->monitoring_date }}"
                    class="w-full border rounded px-4 py-2"
                    required>
            </div>

            <div>
                <label class="block font-semibold mb-2">Progress Before (%)</label>

                <input
                    type="number"
                    name="progress_before"
                    value="{{ $monitoring->progress_before }}"
                    min="0"
                    max="100"
                    class="w-full border rounded px-4 py-2">
            </div>

            <div>
                <label class="block font-semibold mb-2">Progress After (%)</label>

                <input
                    type="number"
                    name="progress_after"
                    value="{{ $monitoring->progress_after }}"
                    min="0"
                    max="100"
                    class="w-full border rounded px-4 py-2">
            </div>

            <div>
                <label class="block font-semibold mb-2">Status</label>

                <select
                    name="status"
                    class="w-full border rounded px-4 py-2">

                    <option value="Planning" {{ $monitoring->status=='Planning' ? 'selected' : '' }}>Planning</option>
                    <option value="Ongoing" {{ $monitoring->status=='Ongoing' ? 'selected' : '' }}>Ongoing</option>
                    <option value="Completed" {{ $monitoring->status=='Completed' ? 'selected' : '' }}>Completed</option>
                    <option value="Suspended" {{ $monitoring->status=='Suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="Cancelled" {{ $monitoring->status=='Cancelled' ? 'selected' : '' }}>Cancelled</option>

                </select>
            </div>

        </div>

        <div class="mt-6">

            <label class="block font-semibold mb-2">Findings</label>

            <textarea
                name="findings"
                rows="4"
                class="w-full border rounded px-4 py-2">{{ $monitoring->findings }}</textarea>

        </div>

        <div class="mt-6">

            <label class="block font-semibold mb-2">Challenges</label>

            <textarea
                name="challenges"
                rows="3"
                class="w-full border rounded px-4 py-2">{{ $monitoring->challenges }}</textarea>

        </div>

        <div class="mt-6">

            <label class="block font-semibold mb-2">Recommendations</label>

            <textarea
                name="recommendations"
                rows="3"
                class="w-full border rounded px-4 py-2">{{ $monitoring->recommendations }}</textarea>

        </div>
<div class="mt-6">

    <label class="block font-semibold mb-2">
        Monitoring Photo
    </label>

    @if($monitoring->photo)

        <img
            src="{{ asset('storage/'.$monitoring->photo) }}"
            width="200"
            class="rounded mb-3">

    @endif

    <input
        type="file"
        name="photo"
        accept="image/*"
        class="w-full border rounded px-4 py-2">

</div>
        <div class="mt-8 flex gap-3">

            <button
    type="submit"
    class="bg-blue-600 text-white px-8 py-3 rounded border-2 border-black">

    UPDATE MONITORING

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