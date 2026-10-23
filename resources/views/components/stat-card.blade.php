@props([
    'label',
    'value',
    'note' => null,
    'tone' => 'green',
])

@php
    $valueColor = match ($tone) {
        'red' => 'text-[#b42318]',
        'muted' => 'text-[#496457]',
        default => 'text-[#0f3d2b]',
    };
@endphp

<section {{ $attributes->merge(['class' => 'rounded-xl border border-[#e3ebe5] bg-white p-5']) }}>
    <p class="text-sm text-[#5b7567]">{{ $label }}</p>
    <p class="mt-2 text-2xl font-semibold {{ $valueColor }}">{{ $value }}</p>
    @if ($note)
        <p class="mt-1 text-xs text-[#5b7567]">{{ $note }}</p>
    @endif
</section>
