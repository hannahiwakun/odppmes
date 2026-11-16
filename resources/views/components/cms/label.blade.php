@props(['for', 'required' => false])
<label for="{{ $for }}" {{ $attributes->merge(['class' => 'mb-1 block text-sm font-medium text-gray-800']) }}>
    {{ $slot }}
    @if($required)
        <span class="font-semibold text-red-700" aria-hidden="true">*</span>
        <span class="sr-only">(required)</span>
    @endif
</label>
