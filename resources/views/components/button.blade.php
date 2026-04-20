@props(['type' => 'button', 'variant' => 'primary', 'size' => 'md', 'href' => null])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium rounded-xl transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-950 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm';

$variants = [
    'primary' => 'bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white border border-transparent shadow-indigo-500/25 hover:shadow-indigo-500/40 focus:ring-indigo-500',
    'secondary' => 'bg-white/5 hover:bg-white/10 text-white border border-white/10 hover:border-white/20 focus:ring-gray-400',
    'danger' => 'bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 focus:ring-red-500 hover:border-red-500/40',
];

$sizes = [
    'sm' => 'px-3 py-1.5 text-xs',
    'md' => 'px-5 py-2.5 text-sm',
    'lg' => 'px-6 py-3 text-base',
];

$classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
