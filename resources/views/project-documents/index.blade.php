@extends('layouts.admin')

@section('title', 'Project Documents')

@section('content')

<div class="space-y-5">

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-green-900">{{ $project->project_title }}</h2>
                <p class="mt-1 text-sm text-gray-600">Project code: {{ $project->project_code ?: '-' }}</p>
                <p class="mt-1 text-sm text-gray-500">Project Documents</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('projects.index') }}"
                   class="rounded-lg bg-gray-500 px-5 py-2 text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                    Back to Projects
                </a>
                <a href="{{ route('project-documents.create', $project) }}"
                   class="rounded-lg bg-green-700 px-5 py-2 text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                    Upload Document
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-400 bg-green-100 px-4 py-3 text-green-800" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg border border-red-400 bg-red-100 px-4 py-3 text-red-800" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-green-900">Uploaded Documents</h3>
            <p class="mt-1 text-sm text-gray-500">Documents associated with this project.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-green-700 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Document</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Type</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">File Name</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Uploaded By</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold" scope="col">Date</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold" scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($documents as $document)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4">
                                <div class="font-medium text-gray-900">{{ $document->title }}</div>
                                @if($document->remarks)
                                    <div class="mt-1 text-xs text-gray-500">{{ Str::limit($document->remarks, 60) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800">
                                    {{ $document->document_type }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700">{{ $document->file_name }}</td>
                            <td class="px-4 py-4 text-sm text-gray-700">{{ $document->uploader?->name ?? 'Unknown User' }}</td>
                            <td class="px-4 py-4">
                                <div class="text-sm text-gray-700">{{ $document->created_at->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $document->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    <a href="{{ route('project-documents.download', [$project, $document]) }}"
                                       class="rounded bg-blue-600 px-3 py-1 text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2"
                                       title="Download Document">
                                        Download
                                    </a>

                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isMEO())
                                        <form action="{{ route('project-documents.destroy', [$project, $document]) }}"
                                              method="POST"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    onclick="return confirm('Are you sure you want to delete this document?')"
                                                    class="rounded bg-red-600 px-3 py-1 text-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2"
                                                    title="Delete Document">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <p class="font-medium text-gray-600">No documents have been uploaded.</p>
                                <p class="mt-1 text-sm text-gray-500">Upload project reports, proposals, agreements and other project documents.</p>
                                <a href="{{ route('project-documents.create', $project) }}"
                                   class="mt-4 inline-block rounded-lg bg-green-700 px-4 py-2 text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                                    Upload First Document
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $documents->links() }}
    </div>

</div>

@endsection
