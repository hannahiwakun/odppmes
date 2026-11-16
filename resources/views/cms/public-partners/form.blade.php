@php
    $partner = $partner ?? null;
    $currentCategory = old('category', $partner->category ?? '');
    $categories = \App\Cms\CmsPublicPartnerCategories::formOptions(is_string($currentCategory) ? $currentCategory : null);
@endphp
<div class="space-y-5">
    <div class="rounded-lg border border-green-100 bg-green-50/40 p-4 text-sm text-gray-700">
        <p>These records appear on the public website after approval and publication. They are not operational development partner accounts and do not grant portal access.</p>
        <p class="mt-2">Listing an organisation here does not confirm an official DPMO partnership. Publish only information the Office has authorised.</p>
    </div>

    <div>
        <x-cms.label for="name" :required="true">Organisation name</x-cms.label>
        <input id="name" name="name" required maxlength="255" value="{{ old('name', $partner->name ?? '') }}"
               class="cms-input {{ $errors->has('name') ? 'is-invalid' : '' }}">
        <x-cms.field-error name="name" />
    </div>

    <div>
        <x-cms.label for="category" :required="true">Stakeholder classification</x-cms.label>
        <x-cms.help>Choose the matching DPMO stakeholder group. This is a classification, not confirmation that the organisation is an official partner.</x-cms.help>
        <select id="category" name="category" required class="cms-input mt-1 {{ $errors->has('category') ? 'is-invalid' : '' }}">
            <option value="" @selected($currentCategory === '')>Select a classification</option>
            @foreach($categories as $category)
                <option value="{{ $category }}" @selected($currentCategory === $category)>{{ $category }}</option>
            @endforeach
        </select>
        <x-cms.field-error name="category" />
    </div>

    <div>
        <x-cms.label for="summary" :required="true">Public summary</x-cms.label>
        <x-cms.help>Describe the organisation in language suitable for the public website. At least 10 characters.</x-cms.help>
        <textarea id="summary" name="summary" required minlength="10" rows="6"
                  class="cms-input mt-1 {{ $errors->has('summary') ? 'is-invalid' : '' }}">{{ old('summary', $partner->summary ?? '') }}</textarea>
        <x-cms.field-error name="summary" />
    </div>

    <div>
        <x-cms.label for="website_url">Organisation website</x-cms.label>
        <input id="website_url" type="url" name="website_url" value="{{ old('website_url', $partner->website_url ?? '') }}"
               class="cms-input {{ $errors->has('website_url') ? 'is-invalid' : '' }}" placeholder="https://">
        <x-cms.field-error name="website_url" />
    </div>

    @include('cms.partials.image-upload', [
        'label' => 'Logo',
        'currentUrl' => $partner?->logo ? route('cms.media.preview', $partner->logo) : null,
        'currentName' => $partner?->logo?->original_name,
    ])

    <details class="rounded-lg border border-gray-200 bg-gray-50 p-4">
        <summary class="cursor-pointer text-sm font-semibold text-gray-800">Advanced settings</summary>
        <div class="mt-3">
            <x-cms.label for="slug">Web address (slug)</x-cms.label>
            <x-cms.help>Optional. Leave blank to generate from the organisation name.</x-cms.help>
            <input id="slug" name="slug" value="{{ old('slug', $partner->slug ?? '') }}" maxlength="191"
                   class="cms-input mt-1 {{ $errors->has('slug') ? 'is-invalid' : '' }}">
            <x-cms.field-error name="slug" />
        </div>
    </details>
</div>
