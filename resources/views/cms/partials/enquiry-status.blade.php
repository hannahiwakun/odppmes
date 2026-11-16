@php
    $colours = [
        'new' => 'bg-blue-100 text-blue-900',
        'in_review' => 'bg-amber-100 text-amber-950',
        'closed' => 'bg-green-100 text-green-900',
        'spam' => 'bg-gray-200 text-gray-800',
    ];
    $label = str_replace('_', ' ', (string) $status);
@endphp
<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $colours[$status] ?? 'bg-gray-100 text-gray-800' }}">
    {{ ucfirst($label) }}
</span>
