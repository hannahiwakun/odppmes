@php
    $banner = $banner ?? null;
    $types = \App\Cms\CmsHomepageBannerTypes::labels();
@endphp
<div class="space-y-6">
    <section class="rounded-lg border border-green-100 bg-white p-4 sm:p-5">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-green-800">Banner information</h3>
        <div class="mt-4 space-y-5">
            <div>
                <x-cms.label for="title" :required="true">Heading</x-cms.label>
                <input id="title" name="title" required maxlength="255" value="{{ old('title', $banner->title ?? '') }}"
                       class="cms-input {{ $errors->has('title') ? 'is-invalid' : '' }}">
                <x-cms.field-error name="title" />
            </div>
            <div>
                <x-cms.label for="body">Supporting text</x-cms.label>
                <x-cms.help>Optional description shown with the banner. Plain text only. Maximum 5,000 characters.</x-cms.help>
                <textarea id="body" name="body" rows="4" maxlength="5000"
                          class="cms-input mt-1 {{ $errors->has('body') ? 'is-invalid' : '' }}">{{ old('body', $banner->body ?? '') }}</textarea>
                <x-cms.field-error name="body" />
            </div>
        </div>
    </section>

    <section class="rounded-lg border border-green-100 bg-white p-4 sm:p-5">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-green-800">Banner image and preview</h3>
        <p class="mt-1 text-sm text-gray-600">Optional. Stored privately. The public website only displays the image after the banner is published and the image is cleared for public use.</p>
        <div class="mt-4 space-y-4">
            @include('cms.partials.image-upload', [
                'label' => $banner?->image ? 'Replace banner image' : 'Banner image',
                'currentUrl' => $banner?->image ? route('cms.media.preview', $banner->image) : null,
                'currentName' => $banner?->image?->original_name,
            ])
            <div>
                <x-cms.label for="image_alt">Image alternative text</x-cms.label>
                <x-cms.help>Describe the image for people who cannot see it. Recommended whenever an image is attached.</x-cms.help>
                <input id="image_alt" name="image_alt" maxlength="255"
                       value="{{ old('image_alt', $banner->image_alt ?? '') }}"
                       class="cms-input mt-1 {{ $errors->has('image_alt') ? 'is-invalid' : '' }}">
                <x-cms.field-error name="image_alt" />
            </div>
        </div>
    </section>

    <section class="rounded-lg border border-green-100 bg-white p-4 sm:p-5">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-green-800">Button text and destination URL</h3>
        <p class="mt-1 text-sm text-gray-600">Optional. The button is shown on the public website only when a valid http:// or https:// address is supplied.</p>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <x-cms.label for="button_text">Button text</x-cms.label>
                <input id="button_text" name="button_text" maxlength="120"
                       value="{{ old('button_text', $banner->button_text ?? '') }}"
                       class="cms-input {{ $errors->has('button_text') ? 'is-invalid' : '' }}">
                <x-cms.field-error name="button_text" />
            </div>
            <div>
                <x-cms.label for="link_url">Destination URL</x-cms.label>
                <input id="link_url" type="url" name="link_url" maxlength="255"
                       value="{{ old('link_url', $banner->link_url ?? '') }}"
                       placeholder="https://"
                       class="cms-input {{ $errors->has('link_url') ? 'is-invalid' : '' }}">
                <x-cms.field-error name="link_url" />
            </div>
        </div>
    </section>

    <section class="rounded-lg border border-green-100 bg-white p-4 sm:p-5">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-green-800">Banner type</h3>
        <x-cms.help>Hero Banner is the prominent homepage feature. Alert Banner is an announcement strip. Feature Banner is a development highlight card.</x-cms.help>
        <select id="banner_type" name="banner_type" required class="cms-input mt-3 {{ $errors->has('banner_type') ? 'is-invalid' : '' }}">
            @foreach ($types as $value => $label)
                <option value="{{ $value }}" @selected(old('banner_type', $banner->banner_type ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-cms.field-error name="banner_type" />
    </section>

    <section class="rounded-lg border border-green-100 bg-white p-4 sm:p-5">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-green-800">Display order</h3>
        <x-cms.help>Lower numbers appear first among published banners of the same type.</x-cms.help>
        <input id="sort_order" type="number" min="0" max="9999" name="sort_order"
               value="{{ old('sort_order', $banner->sort_order ?? 0) }}"
               class="cms-input mt-3 max-w-xs {{ $errors->has('sort_order') ? 'is-invalid' : '' }}">
        <x-cms.field-error name="sort_order" />
    </section>

    <section class="rounded-lg border border-green-100 bg-green-50/40 p-4 sm:p-5">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-green-800">Publication dates</h3>
        <p class="mt-1 text-sm text-gray-600">Leave blank to show the banner as soon as it is published. Use a start date to delay display and an end date to remove it automatically.</p>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <x-cms.label for="published_from">Visible from</x-cms.label>
                <input id="published_from" type="datetime-local" name="published_from"
                       value="{{ old('published_from', isset($banner->published_from) ? $banner->published_from->format('Y-m-d\\TH:i') : '') }}"
                       class="cms-input {{ $errors->has('published_from') ? 'is-invalid' : '' }}">
                <x-cms.field-error name="published_from" />
            </div>
            <div>
                <x-cms.label for="published_until">Visible until</x-cms.label>
                <input id="published_until" type="datetime-local" name="published_until"
                       value="{{ old('published_until', isset($banner->published_until) ? $banner->published_until->format('Y-m-d\\TH:i') : '') }}"
                       class="cms-input {{ $errors->has('published_until') ? 'is-invalid' : '' }}">
                <x-cms.field-error name="published_until" />
            </div>
        </div>
    </section>
</div>
