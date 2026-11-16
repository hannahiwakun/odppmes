@php
    $colours = [
        'draft' => 'bg-gray-100 text-gray-800',
        'submitted' => 'bg-blue-100 text-blue-800',
        'rejected' => 'bg-red-100 text-red-800',
        'approved' => 'bg-amber-100 text-amber-900',
        'published' => 'bg-green-100 text-green-800',
        'archived' => 'bg-slate-200 text-slate-800',
    ];
@endphp
<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $colours[$status] ?? 'bg-gray-100 text-gray-800' }}">
    {{ ucfirst($status) }}
</span>
