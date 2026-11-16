@php $profile = $profile ?? null; @endphp
<div class="space-y-5">
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-cms.label for="full_name" :required="true">Full name</x-cms.label>
            <input id="full_name" name="full_name" required maxlength="255" value="{{ old('full_name', $profile->full_name ?? '') }}"
                   class="cms-input {{ $errors->has('full_name') ? 'is-invalid' : '' }}">
            <x-cms.field-error name="full_name" />
        </div>
        <div>
            <x-cms.label for="designation" :required="true">Designation</x-cms.label>
            <input id="designation" name="designation" required maxlength="255" value="{{ old('designation', $profile->designation ?? '') }}"
                   class="cms-input {{ $errors->has('designation') ? 'is-invalid' : '' }}">
            <x-cms.field-error name="designation" />
        </div>
    </div>

    <div>
        <x-cms.label for="biography" :required="true">Biography</x-cms.label>
        <x-cms.help>Public biography shown on the management team page. At least 20 characters.</x-cms.help>
        <textarea id="biography" name="biography" required minlength="20" rows="8"
                  class="cms-input mt-1 min-h-[12rem] leading-relaxed {{ $errors->has('biography') ? 'is-invalid' : '' }}">{{ old('biography', $profile->biography ?? '') }}</textarea>
        <x-cms.field-error name="biography" />
    </div>

    <div>
        <x-cms.label for="responsibilities">Responsibilities</x-cms.label>
        <x-cms.help>Optional. Outline public-facing responsibilities. This is not an operational job description.</x-cms.help>
        <textarea id="responsibilities" name="responsibilities" rows="6"
                  class="cms-input mt-1 {{ $errors->has('responsibilities') ? 'is-invalid' : '' }}">{{ old('responsibilities', $profile->responsibilities ?? '') }}</textarea>
        <x-cms.field-error name="responsibilities" />
    </div>

    <div>
        <x-cms.label for="sort_order">Display order</x-cms.label>
        <x-cms.help>Lower numbers appear first on the public page.</x-cms.help>
        <input id="sort_order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $profile->sort_order ?? 0) }}"
               class="cms-input mt-1 max-w-xs {{ $errors->has('sort_order') ? 'is-invalid' : '' }}">
        <x-cms.field-error name="sort_order" />
    </div>

    @include('cms.partials.image-upload', [
        'label' => 'Photograph',
        'currentUrl' => $profile?->photo ? route('cms.media.preview', $profile->photo) : null,
        'currentName' => $profile?->photo?->original_name,
    ])

    <details class="rounded-lg border border-gray-200 bg-gray-50 p-4">
        <summary class="cursor-pointer text-sm font-semibold text-gray-800">Advanced settings</summary>
        <div class="mt-3">
            <x-cms.label for="slug">Web address (slug)</x-cms.label>
            <x-cms.help>Optional. Leave blank to generate from the full name.</x-cms.help>
            <input id="slug" name="slug" value="{{ old('slug', $profile->slug ?? '') }}" maxlength="191"
                   class="cms-input mt-1 {{ $errors->has('slug') ? 'is-invalid' : '' }}">
            <x-cms.field-error name="slug" />
        </div>
    </details>
</div>
