@php $contact = $contact ?? null; @endphp
<div class="space-y-5">
    <div class="rounded-lg border border-green-100 bg-green-50/40 p-4 text-sm text-gray-700">
        Enter only information the Office has authorised for the public website. Do not invent addresses, telephone numbers or working hours.
    </div>

    <div>
        <x-cms.label for="address" :required="true">Official address</x-cms.label>
        <x-cms.help>The published office address. City and state should be included in this field if they are to appear on the website. Separate city and state fields are not available yet.</x-cms.help>
        <textarea id="address" name="address" required rows="4"
                  class="cms-input mt-1 {{ $errors->has('address') ? 'is-invalid' : '' }}">{{ old('address', $contact->address ?? '') }}</textarea>
        <x-cms.field-error name="address" />
    </div>

    <div>
        <x-cms.label for="phone" :required="true">Primary telephone</x-cms.label>
        <x-cms.help>The main public telephone number. An alternative telephone field is not available yet.</x-cms.help>
        <input id="phone" name="phone" required maxlength="100" value="{{ old('phone', $contact->phone ?? '') }}"
               class="cms-input mt-1 {{ $errors->has('phone') ? 'is-invalid' : '' }}">
        <x-cms.field-error name="phone" />
    </div>

    <div>
        <x-cms.label for="email" :required="true">Official email</x-cms.label>
        <x-cms.help>The published office email. A separate public enquiries email field is not available yet.</x-cms.help>
        <input id="email" type="email" name="email" required maxlength="255" value="{{ old('email', $contact->email ?? '') }}"
               class="cms-input mt-1 {{ $errors->has('email') ? 'is-invalid' : '' }}">
        <x-cms.field-error name="email" />
    </div>

    <div>
        <x-cms.label for="hours" :required="true">Office hours and working days</x-cms.label>
        <x-cms.help>Record opening time, closing time and working days in this single field (for example Monday to Friday, 8:00am to 4:00pm). Separate opening, closing and working-day fields are not available yet.</x-cms.help>
        <input id="hours" name="hours" required maxlength="500" value="{{ old('hours', $contact->hours ?? '') }}"
               class="cms-input mt-1 {{ $errors->has('hours') ? 'is-invalid' : '' }}">
        <x-cms.field-error name="hours" />
    </div>

    <p class="text-sm text-gray-600">Website, social media and Google Maps links cannot be stored on this record until additional fields are approved.</p>
</div>
