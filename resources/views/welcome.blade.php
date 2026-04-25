<x-layout>
    <x-slot:title>Explore Assets</x-slot>

    <!-- Categories / Tags Row (Imgur Style) -->
    <div class="mb-8 overflow-x-auto pb-4 hide-scrollbar">
        <div class="flex gap-4 min-w-max">
            
            @php
                $categoryImages = [
                    'ANIME' => 'https://images.unsplash.com/photo-1607604276583-eef5d076ff5f?q=80&w=200&auto=format&fit=crop',
                    'MEMES' => 'https://images.unsplash.com/photo-1541562232579-512a21360020?q=80&w=200&auto=format&fit=crop',
                    'WALLPAPER ENGINE' => 'https://images.unsplash.com/photo-1618336753974-aae8e04506aa?q=80&w=200&auto=format&fit=crop',
                    'ART' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?q=80&w=200&auto=format&fit=crop',
                    'COSPLAY' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?q=80&w=200&auto=format&fit=crop',
                    'MANGA' => 'https://images.unsplash.com/photo-1588497859490-85d1c17db96d?q=80&w=200&auto=format&fit=crop',
                    'DEFAULT' => 'https://images.unsplash.com/photo-1550684848-fac1c5b4e853?q=80&w=200&auto=format&fit=crop'
                ];
                
                $categoryColors = [
                    'ANIME' => 'bg-blue-600/80 group-hover:bg-blue-500/80',
                    'MEMES' => 'bg-purple-600/80 group-hover:bg-purple-500/80',
                    'WALLPAPER ENGINE' => 'bg-emerald-600/80 group-hover:bg-emerald-500/80',
                    'ART' => 'bg-orange-600/80 group-hover:bg-orange-500/80',
                    'COSPLAY' => 'bg-rose-600/80 group-hover:bg-rose-500/80',
                    'MANGA' => 'bg-amber-600/80 group-hover:bg-amber-500/80',
                    'DEFAULT' => 'bg-gray-800/80 group-hover:bg-gray-700/80'
                ];
            @endphp
            
            @forelse($categories as $category)
                @php 
                    $catName = strtoupper($category['name']);
                    $bgImg = $categoryImages[$catName] ?? $categoryImages['DEFAULT'];
                    $colorClass = $categoryColors[$catName] ?? $categoryColors['DEFAULT'];
                @endphp
                <a href="{{ route('home', ['type' => $category['name']]) }}" class="relative overflow-hidden rounded-xl w-32 h-20 group {{ request('type') === $category['name'] ? 'ring-2 ring-indigo-500 ring-offset-2 ring-offset-gray-900' : '' }}">
                    <div class="absolute inset-0 {{ $colorClass }} mix-blend-multiply z-10 transition-colors"></div>
                    <img src="{{ $bgImg }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="{{ $catName }}">
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
            <a href="{{ $asset['fileUrl'] ?? '#' }}" target="_blank" class="block group relative break-inside-avoid bg-gray-900 rounded-lg overflow-hidden shadow-lg border border-gray-800 hover:border-indigo-500/50 transition-all duration-300 hover:shadow-indigo-500/20 hover:-translate-y-1">
                
                @php
                    $isVisual = in_array($asset['type'], ['IMG', 'GIF']);
                    $hasCover = !empty($asset['coverUrl']);
                    $displayUrl = $hasCover ? $asset['coverUrl'] : ($isVisual ? $asset['fileUrl'] : null);
                @endphp

                @if($displayUrl)
                    <!-- Image or Asset with Cover -->
                    <div class="relative">
                        <img src="{{ $displayUrl }}" alt="{{ $asset['title'] ?? 'Asset' }}" class="w-full h-auto object-cover">
                        <div class="absolute top-2 right-2 bg-black/60 backdrop-blur-md px-2 py-0.5 rounded text-xs text-white font-medium flex items-center gap-1">
                            @if($asset['type'] == 'VIDEO' || $asset['type'] == 'AMV')
                                <svg class="w-3 h-3 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path d="M8 5v10l7-5-7-5z"></path></svg>
                            @else
                                <svg class="w-3 h-3 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"></path></svg>
                            @endif
                            {{ strtoupper($asset['type']) }}
                        </div>
                    </div>
                @elseif(in_array($asset['type'], ['VIDEO', 'AMV']))
                    <!-- Video without thumbnail -->
                    <div class="relative w-full aspect-video bg-gray-800 flex items-center justify-center flex-col group-hover:bg-gray-750 transition-colors">
                        <div class="w-16 h-16 rounded-full bg-white/10 flex items-center justify-center group-hover:scale-110 group-hover:bg-red-500/20 transition-all duration-300">
                            <svg class="w-8 h-8 text-white group-hover:text-red-400 translate-x-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"></path></svg>
                        </div>
                        <div class="absolute top-2 right-2 bg-black/60 backdrop-blur-md px-2 py-0.5 rounded text-xs text-white font-medium flex items-center gap-1">
                            <svg class="w-3 h-3 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path d="M8 5v10l7-5-7-5z"></path></svg>
                            {{ strtoupper($asset['type']) }}
                        </div>
                    </div>
                @else
                    <!-- Generic File Type (Audio, Link, Text, etc) without cover -->
                    <div class="p-6 bg-gradient-to-br from-gray-800 to-gray-900 flex flex-col items-center justify-center min-h-[150px] text-center relative group-hover:from-indigo-900/40 group-hover:to-purple-900/40">
                        <svg class="w-12 h-12 text-indigo-400 mb-2 opacity-50 group-hover:scale-110 group-hover:opacity-100 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($asset['type'] == 'MUSIC')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            @endif
                        </svg>
                        <div class="absolute top-2 right-2 bg-black/60 backdrop-blur-md px-2 py-0.5 rounded text-xs text-white font-medium flex items-center gap-1">
                            {{ strtoupper($asset['type']) }}
                        </div>
                    </div>
                @endif

                <!-- Bottom Metadata -->
                <div class="p-4 border-t border-gray-800">
                    <h3 class="text-white font-medium line-clamp-2 group-hover:text-indigo-400 transition-colors" title="{{ $asset['title'] ?? 'Untitled' }}">
                        {{ $asset['title'] ?? 'Untitled Asset' }}
                    </h3>
                    <div class="flex items-center justify-between mt-3 text-gray-500 text-sm">
                        <div class="flex items-center gap-3">
                            <span class="flex items-center gap-1 hover:text-gray-300" title="Tags">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg> 
                                {{ $asset['tagsCount'] ?? 0 }}
                            </span>
                        </div>
                        <span class="text-xs tabular-nums">{{ \Carbon\Carbon::parse($asset['createdAt'] ?? now())->diffForHumans() }}</span>
                    </div>
                </div>
            </a>
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
