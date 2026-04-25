<x-layout>
    <x-slot:title>Explore Assets</x-slot>

    <!-- Categories / Tags Row (Imgur Style) -->
    <div class="mb-8 overflow-x-auto pb-4 hide-scrollbar">
        <div class="flex gap-4 min-w-max">
            
            @forelse($categories as $category)
                @php
                    $catName = strtoupper($category['name']);
                    $bgImg   = $category['imageUrl'] ?? null;
                @endphp
                <a href="{{ route('home', ['type' => $category['name']]) }}"
                   class="relative overflow-hidden rounded-xl w-32 h-20 group {{ request('type') === $category['name'] ? 'ring-2 ring-indigo-500 ring-offset-2 ring-offset-gray-900' : '' }}">
                    <div class="absolute inset-0 bg-gray-800/80 group-hover:bg-gray-700/80 mix-blend-multiply z-10 transition-colors"></div>
                    @if($bgImg)
                        <img src="{{ $bgImg }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="{{ $catName }}">
                    @else
                        <div class="absolute inset-0 bg-gradient-to-br from-gray-700 to-gray-900"></div>
                    @endif
                    <div class="absolute inset-0 z-20 flex flex-col items-center justify-center text-white p-2 text-center">
                        <span class="font-bold text-sm truncate w-full">{{ $catName }}</span>
                        <span class="text-xs text-white/70">{{ number_format($category['count']) }} Posts</span>
                    </div>
                </a>
            @empty
                <div class="text-gray-500 italic text-sm">No categories available yet.</div>
            @endforelse
            
        </div>
    </div>

    <!-- Feed Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <h2 class="text-xl font-bold text-white uppercase tracking-wider flex items-center gap-2">
                {{ request('type') ? strtoupper(request('type')) . ' Assets' : 'Most Viral' }}
                <svg class="w-5 h-5 text-gray-400 cursor-pointer hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </h2>
            @if(request('type'))
                <a href="{{ route('home') }}" class="text-xs bg-red-500/20 text-red-400 hover:bg-red-500/40 hover:text-red-300 px-3 py-1 rounded-full transition-colors flex items-center gap-1 font-semibold uppercase tracking-wide">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Clear Filter
                </a>
            @endif
        </div>
        <div class="flex gap-4">
            <button class="text-gray-400 hover:text-white uppercase text-sm font-semibold tracking-wider flex items-center gap-1">
                Newest
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div class="flex items-center gap-2 text-gray-500">
                <svg class="w-5 h-5 hover:text-white cursor-pointer" fill="currentColor" viewBox="0 0 20 20"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <svg class="w-5 h-5 hover:text-white cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
            </div>
        </div>
    </div>

    <!-- Error Handling -->
    @if(isset($error))
        <div class="bg-red-500/10 border border-red-500/50 text-red-500 p-4 rounded-xl mb-6">
            Error loading assets: {{ $error }}
        </div>
    @endif

    <!-- Asset Masonry / Grid -->
    <div class="columns-1 sm:columns-2 lg:columns-3 xl:columns-4 gap-4 space-y-4">
        @forelse($assets as $asset)
            <x-asset-card :asset="$asset" />
        @empty
            <div class="col-span-full py-20 text-center text-gray-500 italic">
                No assets found in the database. Go ahead and add some!
            </div>
        @endforelse
    </div>

    <!-- Hidden scrollbar styles for categories row -->
    <style>
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
</x-layout>
