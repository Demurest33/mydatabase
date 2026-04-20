@props(['active' => false])

@php
$classes = ($active ?? false)
            ? 'px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 bg-white/10 text-white shadow-sm ring-1 ring-white/20'
            : 'px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 text-gray-400 hover:text-white hover:bg-white/5';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
