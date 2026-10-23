@props(['name'])

@php
    $paths = [
        'back' => 'M15 18l-6-6 6-6M9 12h12',
        'bell' => 'M18 8a6 6 0 00-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4',
        'borrowers' => 'M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M10 11a4 4 0 100-8 4 4 0 000 8M20 8v6M23 11h-6',
        'contracts' => 'M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8zM14 2v6h6M8 13h8M8 17h8',
        'dashboard' => 'M3 3h8v8H3zM13 3h8v5h-8zM13 10h8v11h-8zM3 13h8v8H3z',
        'download' => 'M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3',
        'dots' => 'M5 12h.01M12 12h.01M19 12h.01',
        'loans' => 'M4 4h16v16H4zM8 8h8M8 12h8M8 16h5',
        'logout' => 'M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9',
        'menu' => 'M4 6h16M4 12h16M4 18h16',
        'plus' => 'M12 5v14M5 12h14',
        'print' => 'M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z',
        'reports' => 'M4 19V5M4 19h17M8 15l4-4 3 2 5-6',
        'search' => 'M11 19a8 8 0 110-16 8 8 0 010 16zM21 21l-4.35-4.35',
        'settings' => 'M12 8a4 4 0 100 8 4 4 0 000-8zM19.4 15a1.7 1.7 0 00.34 1.88l.06.06-1.7 2.94-.08-.02a1.7 1.7 0 00-1.75.82l-.04.07h-3.4l-.04-.07a1.7 1.7 0 00-1.75-.82l-.08.02-1.7-2.94.06-.06A1.7 1.7 0 009.66 15l-.08-.04v-3.4l.08-.04a1.7 1.7 0 00-.34-1.88l-.06-.06 1.7-2.94.08.02a1.7 1.7 0 001.75-.82l.04-.07h3.4l.04.07a1.7 1.7 0 001.75.82l.08-.02 1.7 2.94-.06.06a1.7 1.7 0 00-.34 1.88l.08.04v3.4z',
        'users' => 'M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M10 11a4 4 0 100-8 4 4 0 000 8M20 8v6M23 11h-6',
    ];
    $path = $paths[$name] ?? $paths['dashboard'];
@endphp

<svg {{ $attributes->merge(['viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.8', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round']) }} aria-hidden="true">
    <path d="{{ $path }}" />
</svg>
