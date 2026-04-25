@props([
    'asset',
    'editUrl'   => null,
    'deleteUrl' => null,
    'deleteId'  => null,
])
@php
    $a         = is_array($asset) ? $asset : (array) $asset;
    $fileUrl   = $a['fileUrl']   ?? null;
    $coverUrl  = $a['coverUrl']  ?? null;
    $type      = strtoupper($a['type'] ?? 'FILE');
    $title     = $a['title']     ?? null;
    $createdAt = $a['createdAt'] ?? null;
    $tagsCount = $a['tagsCount'] ?? (($a['charCount'] ?? 0) + ($a['mediaCount'] ?? 0));

    $isVisual   = in_array($type, ['IMG', 'GIF']);
    $isVideo    = in_array($type, ['VIDEO', 'AMV', 'ANIME']);
    $displayUrl = $coverUrl ?: ($isVisual ? $fileUrl : null);

    $hasAdmin = $editUrl || $deleteUrl;
@endphp

<div class="group relative break-inside-avoid bg-gray-900 rounded-lg overflow-hidden shadow-lg border border-gray-800 hover:border-indigo-500/50 transition-all duration-300 hover:shadow-[0_0_20px_rgba(99,102,241,0.15)] hover:-translate-y-1">

    {{-- Visual area --}}
    <a href="{{ $fileUrl ?? '#' }}" target="_blank" class="block relative">

        @if($displayUrl)
            <img src="{{ $displayUrl }}" alt="{{ $title ?? 'Asset' }}"
                 class="w-full h-auto object-cover" loading="lazy">
        @elseif($isVideo)
            <div class="relative w-full aspect-video bg-gray-800 flex items-center justify-center flex-col">
                <div class="w-14 h-14 rounded-full bg-white/10 flex items-center justify-center
                            group-hover:scale-110 group-hover:bg-red-500/20 transition-all duration-300">
                    <svg class="w-7 h-7 text-white group-hover:text-red-400 translate-x-0.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                </div>
            </div>
        @else
            <div class="p-6 bg-gradient-to-br from-gray-800 to-gray-900 flex flex-col items-center justify-center min-h-[140px] group-hover:from-indigo-900/40 group-hover:to-purple-900/40 transition-colors">
                <svg class="w-10 h-10 text-indigo-400 opacity-50 group-hover:opacity-100 group-hover:scale-110 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if($type === 'MUSIC')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    @endif
                </svg>
            </div>
        @endif

        {{-- Type badge --}}
        <div class="absolute top-2 right-2 bg-black/60 backdrop-blur-md px-2 py-0.5 rounded text-[10px] text-white font-bold flex items-center gap-1">
            @if($isVideo)
                <svg class="w-2.5 h-2.5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path d="M8 5v10l7-5-7-5z"/></svg>
            @endif
            {{ $type }}
        </div>

    </a>

    {{-- Admin action buttons (overlaid top-left, visible on hover) --}}
    @if($hasAdmin)
    <div class="absolute top-2 left-2 flex gap-1.5 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
        @if($editUrl)
        <a href="{{ $editUrl }}"
           class="bg-indigo-600/90 hover:bg-indigo-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-lg backdrop-blur-sm transition-colors shadow">
            Edit
        </a>
        @endif
        @if($deleteUrl && $deleteId)
        <button type="button"
                onclick="if(confirm('Delete this asset?')) document.getElementById('{{ $deleteId }}').submit()"
                class="bg-red-600/90 hover:bg-red-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-lg backdrop-blur-sm transition-colors shadow">
            Del
        </button>
        @endif
    </div>
    @endif

    {{-- Metadata --}}
    <a href="{{ $fileUrl ?? '#' }}" target="_blank"
       class="block p-3 border-t border-gray-800 hover:bg-white/3 transition-colors">
        <h3 class="text-white font-medium text-sm line-clamp-2 group-hover:text-indigo-400 transition-colors">
            {{ $title ?? 'Untitled Asset' }}
        </h3>
        <div class="flex items-center justify-between mt-2 text-gray-500 text-xs">
            @if($tagsCount > 0)
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                {{ $tagsCount }}
            </span>
            @else
            <span></span>
            @endif
            @if($createdAt)
            <span class="tabular-nums">{{ \Carbon\Carbon::parse($createdAt)->diffForHumans() }}</span>
            @endif
        </div>
    </a>

    {{-- Delete form (hidden) --}}
    @if($deleteUrl && $deleteId)
    <form id="{{ $deleteId }}" action="{{ $deleteUrl }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
    @endif

</div>
