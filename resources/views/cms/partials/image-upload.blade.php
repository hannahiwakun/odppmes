@php
    $inputId = $inputId ?? 'image';
    $label = $label ?? 'Image';
    $required = $required ?? false;
    $currentUrl = $currentUrl ?? null;
    $currentName = $currentName ?? null;
    $maxBytes = \App\Cms\CmsMediaUploader::MAX_BYTES;
@endphp
<div
    class="space-y-2"
    x-data="{
        preview: @js($currentUrl),
        fileName: @js($currentName),
        error: '',
        maxBytes: {{ (int) $maxBytes }},
        onChange(event) {
            const input = event.target;
            const file = input.files?.[0];
            this.error = '';
            this.preview = @js($currentUrl);
            this.fileName = @js($currentName);
            if (!file) {
                return;
            }
            const allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (!allowed.includes(file.type)) {
                this.error = 'Choose a JPEG, PNG or WebP image.';
                input.value = '';
                return;
            }
            if (file.size > this.maxBytes) {
                this.error = 'This file is larger than 5 MB. Choose a smaller JPEG, PNG or WebP image.';
                input.value = '';
                return;
            }
            this.fileName = file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
            this.preview = URL.createObjectURL(file);
        }
    }"
>
    <x-cms.label :for="$inputId" :required="$required">{{ $label }}</x-cms.label>
    <x-cms.help>Accepted formats: JPEG, PNG and WebP. Maximum file size: 5 MB. Images are stored privately and are not shown on the public website until they are cleared for public use.</x-cms.help>
    <input
        id="{{ $inputId }}"
        type="file"
        name="image"
        accept="image/jpeg,image/png,image/webp"
        @if($required) required @endif
        class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-green-700 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white"
        @change="onChange"
        :aria-invalid="error || {{ $errors->has('image') ? 'true' : 'false' }} ? 'true' : 'false'"
        aria-describedby="{{ $inputId }}-client-error"
    >
    <p class="text-sm text-gray-700" x-show="fileName && !error" x-text="'Selected: ' + fileName"></p>
    <p id="{{ $inputId }}-client-error" class="text-sm text-red-700" role="alert" x-show="error" x-text="error"></p>
    <img x-show="preview && !error" x-cloak :src="preview" alt="Image preview" class="mt-2 h-44 w-full max-w-md rounded-lg border border-green-100 bg-green-50 object-contain">
    <x-cms.field-error name="image" />
</div>
