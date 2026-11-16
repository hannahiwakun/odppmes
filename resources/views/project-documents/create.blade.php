@extends('layouts.admin')

@section('title', 'Upload Project Document')

@section('content')

@php
    $fieldClass = 'w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2';
    $selectedDocumentType = (string) old('document_type', '');
@endphp

<div class="mx-auto max-w-4xl rounded-lg border border-gray-200 bg-white p-6 shadow">

    <h2 class="text-2xl font-bold text-green-900">Upload Project Document</h2>
    <p class="mt-1 mb-6 text-sm text-gray-600">{{ $project->project_title }}</p>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-300 bg-red-50 p-4 text-red-800" role="alert">
            <p class="mb-2 font-semibold">Please correct the following:</p>
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('project-documents.store', $project) }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-5">
            <label for="title" class="mb-2 block font-semibold">
                Document Title <span class="text-red-700" aria-hidden="true">*</span>
                <span class="sr-only">required</span>
            </label>
            <input type="text"
                   id="title"
                   name="title"
                   value="{{ old('title') }}"
                   required
                   class="{{ $fieldClass }}">
        </div>

        <div class="mb-5">
            <label for="document_type" class="mb-2 block font-semibold">
                Document Type <span class="text-red-700" aria-hidden="true">*</span>
                <span class="sr-only">required</span>
            </label>
            <select id="document_type" name="document_type" required class="{{ $fieldClass }}">
                <option value="" @selected($selectedDocumentType === '')>-- Select Document Type --</option>
                <option value="Project Proposal" @selected($selectedDocumentType === 'Project Proposal')>Project Proposal</option>
                <option value="Report" @selected($selectedDocumentType === 'Report')>Report</option>
                <option value="Progress Report" @selected($selectedDocumentType === 'Progress Report')>Progress Report</option>
                <option value="Financial Document" @selected($selectedDocumentType === 'Financial Document')>Financial Document</option>
                <option value="Agreement" @selected($selectedDocumentType === 'Agreement')>Agreement</option>
                <option value="Other" @selected($selectedDocumentType === 'Other')>Other</option>
            </select>
        </div>

        <div class="mb-5">
            <label for="document" class="mb-2 block font-semibold">
                Select Document <span class="text-red-700" aria-hidden="true">*</span>
                <span class="sr-only">required</span>
            </label>
            <input type="file"
                   id="document"
                   name="document"
                   required
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                   class="{{ $fieldClass }}">
            <p class="mt-1 text-sm text-gray-500">
                Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG and PNG.
                Maximum size: 10MB.
            </p>
        </div>

        <div class="mb-6">
            <label for="remarks" class="mb-2 block font-semibold">
                Remarks <span class="text-sm font-normal text-gray-500">Optional</span>
            </label>
            <textarea id="remarks"
                      name="remarks"
                      rows="4"
                      class="{{ $fieldClass }}">{{ old('remarks') }}</textarea>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit"
                    class="rounded-lg bg-green-700 px-5 py-2 font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Upload Document
            </button>
            <a href="{{ route('project-documents.index', $project) }}"
               class="rounded-lg bg-gray-500 px-5 py-2 text-white hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                Cancel
            </a>
        </div>
    </form>

</div>

@endsection
