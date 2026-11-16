@php $album = $album ?? null; @endphp
<div class="space-y-5">
    <div>
        <x-cms.label for="title" :required="true">Album title</x-cms.label>
        <input id="title" name="title" required maxlength="255" value="{{ old('title', $album->title ?? '') }}"
               class="cms-input {{ $errors->has('title') ? 'is-invalid' : '' }}">
        <x-cms.field-error name="title" />
    </div>
    <div>
        <x-cms.label for="description">Description</x-cms.label>
        <textarea id="description" name="description" rows="4"
                  class="cms-input {{ $errors->has('description') ? 'is-invalid' : '' }}">{{ old('description', $album->description ?? '') }}</textarea>
        <x-cms.field-error name="description" />
    </div>
    <div>
        <x-cms.label for="sort_order">Display order</x-cms.label>
        <input id="sort_order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $album->sort_order ?? 0) }}"
               class="cms-input max-w-xs {{ $errors->has('sort_order') ? 'is-invalid' : '' }}">
        <x-cms.field-error name="sort_order" />
    </div>
    <details class="rounded-lg border border-gray-200 bg-gray-50 p-4">
        <summary class="cursor-pointer text-sm font-semibold text-gray-800">Advanced settings</summary>
        <div class="mt-3">
            <x-cms.label for="slug">Web address (slug)</x-cms.label>
            <x-cms.help>Optional. Leave blank to generate from the title.</x-cms.help>
            <input id="slug" name="slug" maxlength="191" value="{{ old('slug', $album->slug ?? '') }}"
                   class="cms-input mt-1 {{ $errors->has('slug') ? 'is-invalid' : '' }}">
            <x-cms.field-error name="slug" />
        </div>
    </details>
</div>
