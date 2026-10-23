@props(['status'])

@php
    $style = match (strtolower($status)) {
        'active', 'paid', 'completed' => 'bg-[#e3f5ea] text-[#17744a]',
        'overdue' => 'bg-[#fde8e7] text-[#b42318]',
        'pending', 'upcoming', 'due' => 'bg-[#fff4d6] text-[#946200]',
        default => 'bg-[#eef2ef] text-[#496457]',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex rounded-full px-2.5 py-1 text-xs font-medium {$style}"]) }}>
    {{ $status }}
</span>
