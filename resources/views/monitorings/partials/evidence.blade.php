@php
    $canDeleteEvidence = auth()->user()?->isSuperAdmin() || auth()->user()?->isMEO();
    $fieldClass = 'w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2';
@endphp

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow print:hidden">
    <h3 class="text-xl font-bold text-green-900">Monitoring Evidence</h3>
    <p class="mt-1 text-sm text-gray-600">
        Additional photographs (JPEG, PNG or WebP, up to 5 MB) and MP4 video (up to 100 MB) for this visit.
        Files are stored privately and are only shown to authorised officers. Video codec is not inspected beyond the MP4 container type.
    </p>

    <form action="{{ route('monitorings.evidence.store', $monitoring) }}"
          method="POST"
          enctype="multipart/form-data"
          class="mt-5 rounded-lg border border-gray-200 p-4">
        @csrf
        <label for="evidence-files" class="mb-2 block font-semibold">
            Add photographs or video
        </label>
        <input type="file"
               id="evidence-files"
               name="files[]"
               class="{{ $fieldClass }}"
               accept=".jpg,.jpeg,.png,.webp,.mp4,image/jpeg,image/png,image/webp,video/mp4"
               multiple
               required>
        <div id="evidence-captions" class="mt-4 space-y-3"></div>
        <p class="mt-2 text-sm text-gray-500">You can select more than one file. A caption field appears for each selected file.</p>
        <button type="submit"
                class="mt-4 rounded-lg bg-green-700 px-5 py-2 font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
            Upload evidence
        </button>
    </form>

    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($monitoring->evidence as $item)
            <article class="overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                @if($item->isPhoto())
                    <img src="{{ route('monitorings.evidence.file', [$monitoring, $item]) }}"
                         alt="{{ $item->caption ?: $item->original_filename }}"
                         class="h-48 w-full bg-white object-contain">
                @else
                    <video class="h-48 w-full bg-black object-contain"
                           controls
                           preload="metadata"
                           src="{{ route('monitorings.evidence.file', [$monitoring, $item]) }}">
                        Your browser cannot play this video.
                    </video>
                @endif
                <div class="space-y-2 p-4">
                    <p class="font-semibold text-gray-900">{{ $item->caption ?: ($item->isVideo() ? 'Visit video' : 'Visit photograph') }}</p>
                    <p class="text-sm text-gray-500">{{ $item->original_filename }}</p>
                    <p class="text-sm text-gray-500">Uploaded by: {{ $item->uploader->name ?? '-' }}</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('monitorings.evidence.download', [$monitoring, $item]) }}"
                           class="rounded-lg bg-green-700 px-3 py-2 text-sm font-semibold text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-700 focus:ring-offset-2">
                            Download
                        </a>
                        @if($canDeleteEvidence)
                            <form action="{{ route('monitorings.evidence.destroy', [$monitoring, $item]) }}"
                                  method="POST"
                                  onsubmit="return confirm('Delete this evidence file?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="rounded-lg bg-red-700 px-3 py-2 text-sm font-semibold text-white hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-700 focus:ring-offset-2">
                                    Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <p class="text-sm text-gray-500 md:col-span-2 xl:col-span-3">No additional photographs or video have been attached to this visit.</p>
        @endforelse
    </div>
</div>

<script>
    (function () {
        const input = document.getElementById('evidence-files');
        const captions = document.getElementById('evidence-captions');
        if (!input || !captions) {
            return;
        }

        input.addEventListener('change', function () {
            captions.replaceChildren();
            Array.from(input.files).forEach(function (file, index) {
                const wrap = document.createElement('div');
                const label = document.createElement('label');
                label.className = 'mb-2 block font-semibold';
                label.setAttribute('for', 'evidence-caption-' + index);
                label.textContent = 'Caption for ' + file.name + ' (optional)';
                const field = document.createElement('input');
                field.type = 'text';
                field.name = 'captions[]';
                field.id = 'evidence-caption-' + index;
                field.maxLength = 255;
                field.className = @json($fieldClass);
                wrap.appendChild(label);
                wrap.appendChild(field);
                captions.appendChild(wrap);
            });
        });
    })();
</script>
