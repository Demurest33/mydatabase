<x-layout>
    <x-slot:title>{{ $media['title'] ?? 'Media Details' }}</x-slot>

    @push('scripts')
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endpush

    @php
        // Media Title Fallbacks
        $romaji = $media['title'] ?? 'N/A'; // SaveAnilist stores title romaji in 'title'
        $native = $media['native'] ?? '';
        $type = $media['type'] ?? 'UNKNOWN';
        $cover = $media['coverImage'] ?? 'https://via.placeholder.com/300x450';
    @endphp

    <div class="max-w-[1400px] mx-auto py-8 px-4" x-data="mediaViewer({{ Js::from($assets) }}, {{ request()->get('asset_id') ? request()->get('asset_id') : 'null' }})">
        
        <div class="mb-6 flex space-x-4">
            <button onclick="window.history.back()" class="text-sm font-bold text-gray-500 hover:text-emerald-400 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Timeline
            </button>
        </div>

        @if(count($assets) > 0 && $type === 'ANIME')
            <!-- ANIME VIEWER LAYOUT (PREMIUM PLAYER) -->
            <div class="flex flex-col lg:flex-row gap-8 items-start">
                
                <!-- MAIN VIDEO COLUMN -->
                <div class="flex-1 w-full flex flex-col min-w-0">
                    
                    <!-- Video Player Frame -->
                    <div class="bg-[#05070a] rounded-xl overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.5)] relative aspect-video border border-gray-800">
                        <template x-if="currentAsset && (currentAsset.type === 'ANIME' || currentAsset.type === 'VIDEO' || currentAsset.type === 'AMV' || currentAsset.fileUrl.endsWith('.mp4') || currentAsset.fileUrl.endsWith('.m3u8'))">
                            <video :src="currentAsset.fileUrl" controls autoplay class="w-full h-full object-contain bg-black"></video>
                        </template>

                        <template x-if="currentAsset && (currentAsset.type === 'IMG' || currentAsset.type === 'GIF')">
                            <img :src="currentAsset.fileUrl" class="w-full h-full object-contain bg-[#05070a]">
                        </template>

                        <!-- Empty State if no assets selected/exist despite count>0 (defensive check) -->
                        <div x-show="!currentAsset" class="absolute inset-0 flex items-center justify-center">
                            <span class="text-gray-500 font-black tracking-widest text-sm uppercase">Select an episode</span>
                        </div>
                    </div>

                    <!-- Player Controls Grid -->
                    <div class="flex items-center justify-between mt-4">
                        <div class="flex gap-2">
                            <button @click="prevAsset()" :disabled="!hasPrev" class="px-5 py-2.5 rounded-lg bg-[#151921] hover:bg-[#1f2533] text-sm font-bold text-gray-300 disabled:opacity-30 disabled:cursor-not-allowed transition-colors border border-gray-800 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                Anterior
                            </button>
                            <button @click="nextAsset()" :disabled="!hasNext" class="px-5 py-2.5 rounded-lg bg-[#151921] hover:bg-[#1f2533] text-sm font-bold text-gray-300 disabled:opacity-30 disabled:cursor-not-allowed transition-colors border border-gray-800 flex items-center gap-2">
                                Siguiente
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </div>
                        
                        <div class="flex gap-2">
                            <button class="w-11 h-11 rounded-lg bg-[#151921] hover:bg-[#1f2533] border border-gray-800 flex items-center justify-center text-gray-500 hover:text-white transition-colors tooltip relative group">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>
                            <a x-bind:href="currentAsset ? currentAsset.fileUrl : '#'" download class="w-11 h-11 rounded-lg bg-[#151921] hover:bg-[#1f2533] border border-gray-800 flex items-center justify-center text-gray-500 hover:text-white transition-colors cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Server Source Tabs (Aesthetic Implementation) -->
                    <div class="flex gap-2 mt-4 bg-[#11151d] border border border-gray-800 rounded-xl p-2.5 items-center flex-wrap shadow-inner">
                        <div class="flex items-center gap-2 px-3 border-r border-gray-700/50 text-gray-400 text-xs font-black mr-2 uppercase tracking-widest">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                            SUB
                        </div>
                        <button class="bg-[#2dd4bf] text-black px-4 py-1.5 rounded-lg text-xs font-black uppercase tracking-wider hover:bg-teal-300 transition-colors">HLS</button>
                        <button class="bg-[#1f2533] text-gray-300 px-4 py-1.5 rounded-lg text-xs font-black uppercase tracking-wider hover:bg-gray-700 transition-colors">Mega</button>
                        <button class="bg-[#1f2533] text-gray-300 px-4 py-1.5 rounded-lg text-xs font-black uppercase tracking-wider hover:bg-gray-700 transition-colors">UPNShare</button>
                        <button class="bg-[#1f2533] text-gray-300 px-4 py-1.5 rounded-lg text-xs font-black uppercase tracking-wider hover:bg-gray-700 transition-colors">MP4Upload</button>
                    </div>

                    <!-- Meta Details -->
                    <div class="mt-8 pb-12 border-b border-gray-800">
                        <h2 class="text-emerald-400 text-lg font-bold mb-1 tracking-wide">{{ $native }}</h2>
                        <h1 class="text-white text-3xl font-extrabold mb-4" x-text="currentAsset ? currentAsset.title : '{{ addslashes($romaji) }}'"></h1>
                        
                        <div class="flex items-center flex-wrap gap-y-2 gap-x-4 text-xs font-bold uppercase tracking-wider text-gray-400 mb-6">
                            <span class="flex items-center gap-1">{{ $media['format'] ?? 'OVA' }}</span>
                            <span>•</span>
                            <span>{{ $media['start_year'] ?? 'TBA' }}</span>
                            <span>•</span>
                            <span>Temporada {{ $media['season'] ?? 'Desconocida' }}</span>
                            <span>•</span>
                            <div class="flex items-center gap-1.5 text-gray-300">
                                En emisión <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2 mb-6">
                            @foreach($genres as $g)
                                <span class="bg-gray-900 border border-gray-800 text-gray-400 opacity-80 text-xs px-3 py-1 rounded-full font-semibold">{{ $g }}</span>
                            @endforeach
                        </div>

                        <div class="text-gray-400 text-sm leading-relaxed max-w-4xl text-justify font-medium">
                            {!! strip_tags($media['description']) !!}
                        </div>
                    </div>

                </div>

                <!-- RIGHT SIDEBAR (EPISODES LIST PANEL) -->
                <div class="w-full lg:w-[320px] shrink-0">
                    <div class="bg-[#151921] rounded-2xl p-6 border border-gray-800 shadow-xl sticky top-24">
                        <p class="text-xs font-bold text-gray-500 mb-1 tracking-widest uppercase">Estás viendo</p>
                        <h3 class="text-lg font-black text-white mb-6 line-clamp-1" x-text="currentAsset ? currentAsset.title : 'Cargando...'"></h3>

                        <!-- Grid de Episodios -->
                        <div class="grid grid-cols-5 gap-2.5 max-h-[600px] overflow-y-auto pr-2 custom-scrollbar">
                            <template x-for="(asset, index) in assets" :key="asset.id">
                                <button @click="setCurrent(index)"
                                    class="h-10 flex items-center justify-center rounded-lg text-sm font-black transition-all relative group"
                                    :class="currentIndex === index 
                                        ? 'bg-transparent text-emerald-400 border-2 border-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.15)] scale-105 z-10' 
                                        : 'bg-[#1e2430] text-gray-400 border border-transparent hover:bg-[#252c3b] hover:text-white hover:border-gray-600'">
                                    
                                    <span x-text="index + 1"></span>
                                    
                                    <!-- Tooltip sobre el número -->
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-max px-2.5 py-1 bg-white text-black font-bold text-[10px] rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none shadow-xl z-50 truncate max-w-[150px]" x-text="asset.title"></div>
                                </button>
                            </template>
                        </div>
                        
                        <style>
                            /* Scrollbar custom para el grid de episodios */
                            .custom-scrollbar::-webkit-scrollbar { width: 4px; }
                            .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
                            .custom-scrollbar::-webkit-scrollbar-thumb { background: #374151; border-radius: 4px; }
                        </style>
                    </div>
                </div>

            </div>

        @else
            <!-- STANDARD ASSETS GRID / MANGA VIEWER / DEFAULT MEDIA VIEW -->
            <div class="bg-[#11151d] border border-gray-800 rounded-2xl p-8 mb-8 shadow-2xl overflow-hidden relative">
                <!-- Background decoration -->
                <div class="absolute inset-0 opacity-10 blur-3xl pointer-events-none">
                    <img src="{{ $cover }}" class="w-full h-full object-cover">
                </div>
                
                <div class="flex flex-col md:flex-row gap-8 items-start relative z-10">
                    <div class="w-48 shrink-0 overflow-hidden rounded-xl border-4 border-gray-900 shadow-2xl">
                        <img src="{{ $cover }}" class="w-full object-cover aspect-[2/3] bg-black">
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-3 text-xs font-black uppercase text-gray-400 mb-3 tracking-widest">
                            <span class="bg-[#1f2533] border border-gray-800 px-2 py-0.5 rounded text-indigo-400">{{ $type }}</span>
                            <span>{{ $media['format'] ?? 'UNKNOWN' }}</span>
                            <span>•</span>
                            <span class="text-white">{{ $media['start_year'] ?? 'TBA' }}</span>
                        </div>
                        <h1 class="text-4xl font-extrabold text-white mb-2 leading-tight">{{ $romaji }}</h1>
                        @if($native) <h2 class="text-xl text-gray-500 font-bold mb-6">{{ $native }}</h2> @endif
                        
                        <div class="flex gap-2 flex-wrap mb-6">
                            @foreach($genres as $g)
                                <span class="bg-[#151921] border border-gray-800 text-gray-400 text-[10px] tracking-widest uppercase px-3 py-1.5 rounded-md font-bold">{{ $g }}</span>
                            @endforeach
                        </div>

                        <div class="text-gray-400 leading-relaxed text-sm font-medium border-l-2 border-indigo-500/30 pl-4 bg-gray-900/30 p-2 rounded-r-lg max-w-3xl">
                            {!! strip_tags($media['description']) !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- List of Assets inside generic layout -->
            <div class="mb-10 block">
                <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-800/60">
                    <h3 class="text-2xl font-bold text-white flex items-center gap-3">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                        Archivos & Recursos Vinculados ({{ count($assets) }})
                    </h3>
                </div>

                @if(count($assets) == 0)
                    <div class="bg-[#151921] border border-gray-800 border-dashed rounded-2xl p-16 text-center shadow-inner">
                        <svg class="w-16 h-16 text-[#1f2533] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <h4 class="text-white text-xl font-bold mb-2">Aún no hay archivos almacenados</h4>
                        <p class="text-gray-500 text-sm mb-6 max-w-md mx-auto">Dirígete al Dashboard Gráfico para subir tomos de manga o imágenes asociadas a esta entrega.</p>
                        <a href="{{ route('assets.create') }}" class="inline-block bg-indigo-500 hover:bg-indigo-400 text-white font-bold text-sm px-6 py-3 rounded-xl transition-all shadow-[0_0_20px_rgba(99,102,241,0.3)] hover:shadow-[0_0_30px_rgba(99,102,241,0.5)]">
                            Launch Graph Dashboard
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-5">
                        @foreach($assets as $asset)
                            @php
                                $assetCover = $asset['coverUrl'] ?? 'https://via.placeholder.com/300x400';
                            @endphp
                            <a href="{{ $asset['fileUrl'] }}" target="_blank" class="group bg-[#151921] border border-gray-800 rounded-xl overflow-hidden hover:border-indigo-500 transition-all hover:-translate-y-1 hover:shadow-[0_10px_20px_rgba(0,0,0,0.5)] relative flex flex-col">
                                <div class="absolute top-2 right-2 bg-black/60 backdrop-blur-md px-2 py-0.5 rounded text-[9px] font-black tracking-widest text-[#2dd4bf] uppercase border border-white/5 z-10">
                                    {{ $asset['type'] }}
                                </div>
                                <div class="aspect-[3/4] overflow-hidden bg-[#0a0c10] relative shrink-0">
                                    <img src="{{ $assetCover }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 opacity-90 group-hover:opacity-100">
                                </div>
                                <div class="p-3 flex-1 flex flex-col justify-between items-start gap-2 bg-[#11151d] border-t border-gray-800/50">
                                    <h4 class="text-xs font-bold text-gray-300 line-clamp-2 leading-snug group-hover:text-white transition-colors" title="{{ $asset['title'] }}">{{ $asset['title'] }}</h4>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

    </div>

    @if(count($assets) > 0 && $type === 'ANIME')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('mediaViewer', (assetsData, initialAssetId) => ({
                    assets: assetsData,
                    currentIndex: 0,

                    init() {
                        if (initialAssetId) {
                            const foundIndex = this.assets.findIndex(a => a.id === initialAssetId);
                            if (foundIndex !== -1) {
                                this.currentIndex = foundIndex;
                            }
                        }
                    },

                    get currentAsset() {
                        if (this.assets.length === 0) return null;
                        return this.assets[this.currentIndex];
                    },

                    get hasNext() {
                        return this.currentIndex < this.assets.length - 1;
                    },

                    get hasPrev() {
                        return this.currentIndex > 0;
                    },

                    nextAsset() {
                        if (this.hasNext) this.currentIndex++;
                    },

                    prevAsset() {
                        if (this.hasPrev) this.currentIndex--;
                    },

                    setCurrent(index) {
                        this.currentIndex = index;
                    }
                }));
            });
        </script>
    @endif
</x-layout>
