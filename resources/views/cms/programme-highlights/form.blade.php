@php $highlight = $highlight ?? null; @endphp
<div class="space-y-5">
    <div class="rounded-lg border border-green-100 bg-green-50/40 p-4 text-sm text-gray-700">
        Programme highlights are public summaries only. Do not copy confidential operational project records, budgets or unpublished performance data into this form.
    </div>

    <div>
        <x-cms.label for="title" :required="true">Title</x-cms.label>
        <input id="title" name="title" required maxlength="255" value="{{ old('title', $highlight->title ?? '') }}"
               class="cms-input {{ $errors->has('title') ? 'is-invalid' : '' }}">
        <x-cms.field-error name="title" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-cms.label for="implementing_mda_name" :required="true">Implementing MDA</x-cms.label>
            <x-cms.help>Public name of the ministry, department or agency implementing the programme. This does not link to an operational MDA account.</x-cms.help>
            <input id="implementing_mda_name" name="implementing_mda_name" required maxlength="255"
                   value="{{ old('implementing_mda_name', $highlight->implementing_mda_name ?? '') }}"
                   class="cms-input mt-1 {{ $errors->has('implementing_mda_name') ? 'is-invalid' : '' }}">
            <x-cms.field-error name="implementing_mda_name" />
        </div>
        <div>
            <x-cms.label for="partner_name" :required="true">Partner name (public)</x-cms.label>
            <x-cms.help>Name shown to the public. This is not an operational development partner login account.</x-cms.help>
            <input id="partner_name" name="partner_name" required maxlength="255"
                   value="{{ old('partner_name', $highlight->partner_name ?? '') }}"
                   class="cms-input mt-1 {{ $errors->has('partner_name') ? 'is-invalid' : '' }}">
            <x-cms.field-error name="partner_name" />
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-cms.label for="sector" :required="true">Sector</x-cms.label>
            <x-cms.help>The public sector or theme of the programme (for example health or education).</x-cms.help>
            <input id="sector" name="sector" required maxlength="100" value="{{ old('sector', $highlight->sector ?? '') }}"
                   class="cms-input mt-1 {{ $errors->has('sector') ? 'is-invalid' : '' }}">
            <x-cms.field-error name="sector" />
        </div>
        <div>
            <x-cms.label for="location" :required="true">Location</x-cms.label>
            <x-cms.help>Where the public may understand the programme to take place (State, local government or community).</x-cms.help>
            <input id="location" name="location" required maxlength="255" value="{{ old('location', $highlight->location ?? '') }}"
                   class="cms-input mt-1 {{ $errors->has('location') ? 'is-invalid' : '' }}">
            <x-cms.field-error name="location" />
        </div>
    </div>

    <div>
        <x-cms.label for="public_status" :required="true">Public status</x-cms.label>
        <x-cms.help>A short public-facing status such as Ongoing or Completed. This is not an operational workflow status.</x-cms.help>
        <input id="public_status" name="public_status" required maxlength="100" value="{{ old('public_status', $highlight->public_status ?? '') }}"
               class="cms-input mt-1 {{ $errors->has('public_status') ? 'is-invalid' : '' }}">
        <x-cms.field-error name="public_status" />
    </div>

    <div>
        <x-cms.label for="summary" :required="true">Public summary</x-cms.label>
        <x-cms.help>A brief description suitable for the website. At least 10 characters.</x-cms.help>
        <textarea id="summary" name="summary" required minlength="10" rows="6"
                  class="cms-input mt-1 {{ $errors->has('summary') ? 'is-invalid' : '' }}">{{ old('summary', $highlight->summary ?? '') }}</textarea>
        <x-cms.field-error name="summary" />
    </div>

    <details class="rounded-lg border border-gray-200 bg-gray-50 p-4">
        <summary class="cursor-pointer text-sm font-semibold text-gray-800">Advanced settings</summary>
        <div class="mt-3">
            <x-cms.label for="slug">Web address (slug)</x-cms.label>
            <x-cms.help>Optional. Leave blank to generate from the title.</x-cms.help>
            <input id="slug" name="slug" value="{{ old('slug', $highlight->slug ?? '') }}" maxlength="191"
                   class="cms-input mt-1 {{ $errors->has('slug') ? 'is-invalid' : '' }}">
            <x-cms.field-error name="slug" />
        </div>
    </details>
</div>
