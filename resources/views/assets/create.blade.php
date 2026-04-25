<x-layout>
    <x-slot:title>Graph Node Dashboard</x-slot>

    @push('scripts')
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endpush

    <div class="max-w-7xl mx-auto py-8 px-4" x-data="dashboardForm()">
        <div class="md:flex items-end justify-between mb-8 border-b border-gray-800 pb-6">
            <div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">Graph <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-cyan-400">Dashboard</span></h1>
                <p class="text-gray-400 mt-2 text-sm max-w-2xl">Upload centralized assets and distribute them to the Neo4j timeline. You can attach video chapters directly to Media nodes, or sprite images directly to Character nodes.</p>
            </div>
            <div class="mt-4 md:mt-0 opacity-50 text-xs text-right hidden lg:block">
                Nodes Linked: <b><span x-text="selectedMedia.length + selectedCharacters.length"></span></b>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-8 bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 px-5 py-4 rounded-xl flex items-center gap-3 shadow-lg">
                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-8 bg-red-500/10 border border-red-500/50 text-red-400 px-5 py-4 rounded-xl flex items-center gap-3 shadow-lg">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-8 bg-red-500/10 border border-red-500/50 text-red-400 px-5 py-4 rounded-xl shadow-lg">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('assets.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col lg:flex-row gap-8">
            @csrf

            <!-- LEFT COLUMN: SIDEBAR UPLOAD MANAGER -->
            <div class="w-full lg:w-[420px] shrink-0 space-y-6">
                <!-- Data Settings Card -->
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"></path></svg>
                    </div>

                    <h3 class="text-xl font-bold text-white border-b border-gray-800 pb-4 mb-5 flex items-center gap-2 relative z-10">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        Upload Manager
                    </h3>

                    <div class="space-y-5 relative z-10">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Category Asset Type <span class="text-emerald-500">*</span></label>
                            <select name="asset_type" x-model="assetType" required class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors font-medium text-sm">
                                <option value="ANIME">ANIME (Episode/OVA)</option>
                                <option value="MANGA">MANGA (Chapter/Volume)</option>
                                <option value="LIGHT NOVEL">LIGHT NOVEL (File/Link)</option>
                                <option value="DOUJIN">DOUJIN</option>
                                <option value="VIDEO">VIDEO (Generic Video)</option>
                                <option value="AMV">AMV (Anime Music Video)</option>
                                <option value="MUSIC">MUSIC (Audio Track)</option>
                                <option value="IMG">IMG (Standard Image)</option>
                                <option value="GIF">GIF (Animated Image)</option>
                                <option value="WALLPAPER ENGINE">WALLPAPER ENGINE</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Display Title <span class="text-gray-600 font-normal normal-case">(Optional)</span></label>
                            <input type="text" name="title" placeholder="Leave empty for filename" class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors placeholder-gray-600 text-sm">
                        </div>

                        <!-- Source Type Switcher -->
                        <div class="pt-2">
                            <div class="flex gap-2 mb-4 bg-black/40 p-1.5 rounded-xl border border-gray-800">
                                <button type="button" @click="sourceMode = 'UPLOAD'" 
                                    :class="sourceMode === 'UPLOAD' ? 'bg-gray-800 text-white shadow-sm' : 'text-gray-500 hover:text-gray-300'"
                                    class="flex-1 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition-all">
                                    Local Media
                                </button>
                                <button type="button" @click="sourceMode = 'LINK'" 
                                    :class="sourceMode === 'LINK' ? 'bg-gray-800 text-white shadow-sm' : 'text-gray-500 hover:text-gray-300'"
                                    class="flex-1 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition-all">
                                    Web URL
                                </button>
                            </div>

                            <div x-show="sourceMode === 'UPLOAD'" x-transition class="space-y-2 relative">
                                <input type="file" name="files[]" multiple class="block w-full text-sm text-gray-400 file:mr-4 file:py-3 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-500/10 file:text-emerald-400 hover:file:bg-emerald-500/20 cursor-pointer bg-black/40 border border-gray-800 rounded-xl">
                                <p class="text-[10px] text-gray-500 mt-1 pl-1">You can select multiple files at once. If you provide a base title, they will be sequentially numbered (e.g. Title 01, Title 02).</p>
                            </div>
                            
                            <div x-show="sourceMode === 'LINK'" x-transition x-cloak class="space-y-2">
                                <input type="url" name="url" placeholder="https://example.com/stream.m3u8" class="w-full bg-black/40 border border-gray-800 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 transition-colors placeholder-gray-600 text-sm">
                            </div>
                        </div>

                        <!-- Optional Thumbnail -->
                        <div x-show="!['IMG', 'GIF'].includes(assetType)" x-transition class="pt-4 border-t border-gray-800 mt-6">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Display Thumbnail <span class="text-gray-600 font-normal normal-case">(Optional)</span></label>
                            <input type="file" name="cover_image" accept="image/*" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-gray-800 file:text-gray-300 hover:file:text-white cursor-pointer border border-gray-800 rounded-xl px-2 py-1 bg-black/20">
                            <p class="text-[10px] text-gray-500 mt-2 leading-tight">Add a custom thumbnail cover if you are uploading an Episode, Chapter, or Video.</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button Area (Moves depending on screen size or sits side by side) -->
                <button type="submit" class="w-full group relative overflow-hidden bg-gradient-to-br from-emerald-500 to-cyan-600 text-white font-black text-xl py-5 rounded-2xl shadow-[0_10px_30px_rgba(16,185,129,0.2)] hover:shadow-[0_10px_40px_rgba(16,185,129,0.3)] hover:-translate-y-1 transition-all">
                    <span class="relative z-10 flex items-center justify-center gap-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Deploy to Graph
                    </span>
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-[200%] group-hover:translate-x-[200%] transition-transform duration-1000"></div>
                </button>
            </div>

            <!-- RIGHT COLUMN: GRAPH ASSIGNMENT DASHBOARD -->
            <div class="flex-1 min-w-0">
                <div class="bg-gray-900 border border-gray-800 rounded-2xl shadow-xl flex flex-col h-full min-h-[600px]">
                    
                    <!-- Dashboard Tabs -->
                    <div class="flex border-b border-gray-800 px-6 pt-6">
                        <button type="button" @click="activeTab = 'media'" :class="activeTab === 'media' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-300'" class="flex items-center gap-2 pb-4 px-4 font-bold border-b-2 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path></svg>
                            Link to Media
                        </button>
                        <button type="button" @click="activeTab = 'characters'" :class="activeTab === 'characters' ? 'border-amber-500 text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-300'" class="flex items-center gap-2 pb-4 px-4 font-bold border-b-2 transition-colors -ml-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Link to Character
                        </button>
                    </div>

                    <!-- Filter Area (Shared visual logic for both tabs) -->
                    <div class="px-6 py-5 bg-gray-900/50 flex flex-col sm:flex-row gap-4 border-b border-gray-800">
                        <select x-model="searchFranchise" @change="debouncedFetch" class="w-full sm:w-1/3 bg-black/40 border border-gray-800 text-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="ALL">All Graph Franchises</option>
                            @foreach($franchises as $f)
                                <option value="{{ $f }}">{{ $f }}</option>
                            @endforeach
                        </select>
                        <div class="relative w-full sm:w-2/3">
                            <input type="text" x-model="searchQuery" @input.debounce.500ms="debouncedFetch" placeholder="Search by name, exact ID, etc..." class="w-full bg-black/40 border border-gray-800 text-white rounded-xl pl-10 pr-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-600 text-sm">
                            <svg class="w-4 h-4 text-gray-500 absolute left-4 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>

                    <!-- Selected Items Ribbon (Dynamic) -->
                    <div class="px-6 py-3 bg-[#131720]" x-show="selectedMedia.length > 0 || selectedCharacters.length > 0" x-cloak>
                        <div class="flex flex-wrap gap-2">
                            <!-- Selected Media -->
                            <template x-for="item in selectedMedia" :key="'m_'+item.id">
                                <div class="flex items-center gap-2 bg-indigo-500/10 border border-indigo-500/30 text-indigo-200 py-1 pl-2 pr-2 rounded-lg text-xs font-semibold group">
                                    <input type="hidden" name="media[]" :value="item.id">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path></svg>
                                    <span class="max-w-[120px] truncate" x-text="item.title.romaji || item.title.native"></span>
                                    <button type="button" @click="toggleMedia(item)" class="text-indigo-400/50 hover:text-white hover:bg-red-500 rounded-sm focus:outline-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            </template>
                            
                            <!-- Selected Characters -->
                            <template x-for="char in selectedCharacters" :key="'c_'+char.id">
                                <div class="flex items-center gap-2 bg-amber-500/10 border border-amber-500/30 text-amber-200 py-1 pl-1.5 pr-2 rounded-lg text-xs font-semibold group">
                                    <input type="hidden" name="characters[]" :value="char.id">
                                    <img :src="char.image || 'https://via.placeholder.com/24'" class="w-4 h-4 rounded object-cover">
                                    <span class="max-w-[120px] truncate" x-text="char.name"></span>
                                    <button type="button" @click="toggleCharacter(char)" class="text-amber-400/50 hover:text-white hover:bg-red-500 rounded-sm focus:outline-none">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Search Results Graph Window -->
                    <div class="flex-1 p-6 relative overflow-y-auto max-h-[500px]">
                        
                        <!-- Common Loading State -->
                        <div x-show="loading" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-gray-900/80 backdrop-blur-sm">
                            <svg class="animate-spin h-10 w-10 text-emerald-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-emerald-500 font-bold tracking-widest text-sm uppercase">Querying Graph Engine</span>
                        </div>

                        <!-- TAB: MEDIA GRAPH BINDING -->
                        <div x-show="activeTab === 'media'" class="space-y-6">
                            <div x-show="!loading && searchResultsMedia.length === 0" class="text-center text-gray-500 py-10">
                                <svg class="w-16 h-16 mx-auto mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <p x-text="searchQuery ? 'No media found in graph matching query.' : 'Search to locate Media Nodes for binding.'"></p>
                            </div>

                            <!-- ANIME/TV GROUP -->
                            <div x-show="animeResults.length > 0">
                                <h4 class="text-xs font-black text-indigo-400 mb-3 uppercase tracking-widest border-b border-indigo-500/20 pb-2">Anime & TV</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                                    <template x-for="item in animeResults" :key="item.id">
                                        <label class="flex relative rounded-xl border p-2 cursor-pointer transition-all hover:-translate-y-1 bg-black/20"
                                            :class="isMediaSelected(item.id) ? 'border-indigo-500 shadow-[0_0_15px_rgba(99,102,241,0.2)] bg-indigo-500/10' : 'border-gray-800 hover:border-gray-600'">
                                            
                                            <!-- Absolute Checkbox Checkmark -->
                                            <div class="absolute -top-2 -right-2 rounded-full w-6 h-6 border-2 border-gray-900 bg-gray-800 flex items-center justify-center transition-colors z-10"
                                                 :class="isMediaSelected(item.id) ? 'bg-indigo-500 border-indigo-500' : ''">
                                                <input type="checkbox" :checked="isMediaSelected(item.id)" @change="toggleMedia(item)" class="hidden">
                                                <svg x-show="isMediaSelected(item.id)" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            </div>

                                            <div class="shrink-0 w-12 h-16 rounded overflow-hidden mr-3 bg-gray-900">
                                                <img :src="item.coverImage || (item.image && item.image.large) || 'https://via.placeholder.com/50'" class="w-full h-full object-cover">
                                            </div>
                                            <div class="flex flex-col justify-center min-w-0 pr-2">
                                                <p class="text-[10px] font-black uppercase text-indigo-400 mb-0.5 tracking-wider truncate" x-text="item.format || 'UNKNOWN'"></p>
                                                <h4 class="text-sm font-bold text-gray-200 leading-tight line-clamp-2" x-text="item.title.romaji || item.title.native || item.title"></h4>
                                                <div class="flex content-center gap-1 mt-1">
                                                    <span class="text-[10px] text-gray-500 font-medium" x-text="item.start_year || 'TBA'"></span>
                                                </div>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                            </div>

                            <!-- MANGA/NOVELS GROUP -->
                            <div x-show="mangaResults.length > 0" class="pt-2">
                                <h4 class="text-xs font-black text-amber-500 mb-3 uppercase tracking-widest border-b border-amber-500/20 pb-2">Manga & Novels</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                                    <template x-for="item in mangaResults" :key="item.id">
                                        <label class="flex relative rounded-xl border p-2 cursor-pointer transition-all hover:-translate-y-1 bg-black/20"
                                            :class="isMediaSelected(item.id) ? 'border-amber-500 shadow-[0_0_15px_rgba(245,158,11,0.2)] bg-amber-500/10' : 'border-gray-800 hover:border-gray-600'">
                                            
                                            <!-- Absolute Checkbox Checkmark -->
                                            <div class="absolute -top-2 -right-2 rounded-full w-6 h-6 border-2 border-gray-900 bg-gray-800 flex items-center justify-center transition-colors z-10"
                                                 :class="isMediaSelected(item.id) ? 'bg-amber-500 border-amber-500' : ''">
                                                <input type="checkbox" :checked="isMediaSelected(item.id)" @change="toggleMedia(item)" class="hidden">
                                                <svg x-show="isMediaSelected(item.id)" class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            </div>

                                            <div class="shrink-0 w-12 h-16 rounded overflow-hidden mr-3 bg-gray-900">
                                                <img :src="item.coverImage || (item.image && item.image.large) || 'https://via.placeholder.com/50'" class="w-full h-full object-cover">
                                            </div>
                                            <div class="flex flex-col justify-center min-w-0 pr-2">
                                                <p class="text-[10px] font-black uppercase text-amber-500 mb-0.5 tracking-wider truncate" x-text="item.format || 'UNKNOWN'"></p>
                                                <h4 class="text-sm font-bold text-gray-200 leading-tight line-clamp-2" x-text="item.title.romaji || item.title.native || item.title"></h4>
                                                <div class="flex content-center gap-1 mt-1">
                                                    <span class="text-[10px] text-gray-500 font-medium" x-text="item.start_year || 'TBA'"></span>
                                                </div>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- TAB: CHARACTER GRAPH BINDING -->
                        <div x-show="activeTab === 'characters'" x-cloak class="space-y-4">
                            <div x-show="!loading && searchResultsChars.length === 0" class="text-center text-gray-500 py-10">
                                <svg class="w-16 h-16 mx-auto mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                <p x-text="searchQuery ? 'No character nodes found matching query.' : 'Search to locate Character Nodes for binding.'"></p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                                <template x-for="char in searchResultsChars" :key="char.id">
                                    <label class="flex items-center gap-3 p-2 bg-black/20 rounded-xl border relative cursor-pointer hover:border-gray-600 transition-all hover:-translate-y-1"
                                        :class="isCharSelected(char.id) ? 'border-amber-500 shadow-[0_0_15px_rgba(245,158,11,0.2)] bg-amber-500/10' : 'border-gray-800'">
                                        
                                        <div class="absolute -top-2 -right-2 rounded-full w-6 h-6 border-2 border-gray-900 bg-gray-800 flex items-center justify-center transition-colors z-10"
                                             :class="isCharSelected(char.id) ? 'bg-amber-500 border-amber-500' : ''">
                                            <input type="checkbox" :checked="isCharSelected(char.id)" @change="toggleCharacter(char)" class="hidden">
                                            <svg x-show="isCharSelected(char.id)" class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        </div>

                                        <img :src="char.image || 'https://via.placeholder.com/40'" class="w-10 h-10 rounded-lg object-cover bg-gray-900 border border-gray-800">
                                        <div class="flex-1 min-w-0 pr-3">
                                            <p class="text-sm font-bold text-gray-200 truncate" x-text="char.name"></p>
                                            <div class="flex items-center mt-0.5">
                                                <span class="text-[9px] px-1.5 py-0.5 rounded uppercase font-black" :class="char.isMain ? 'bg-amber-500/20 text-amber-500' : 'bg-gray-800 text-gray-500'" x-text="char.isMain ? 'MAIN' : 'SUPPORT'"></span>
                                            </div>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboardForm', () => ({
                activeTab: 'media', 
                assetType: 'ANIME',
                sourceMode: 'UPLOAD',
                searchQuery: '',
                searchFranchise: 'ALL',
                searchResultsMedia: [],
                searchResultsChars: [],
                selectedMedia: [],
                selectedCharacters: [],
                loading: false,

                get animeResults() {
                    return this.searchResultsMedia
                        .filter(m => m.type === 'ANIME')
                        .sort((a, b) => this.compareDates(a, b));
                },

                get mangaResults() {
                    return this.searchResultsMedia
                        .filter(m => m.type === 'MANGA')
                        .sort((a, b) => this.compareDates(a, b));
                },

                compareDates(a, b) {
                    const yA = a.start_year || 9999;
                    const yB = b.start_year || 9999;
                    if (yA !== yB) return yA - yB;
                    
                    const mA = a.start_month || 12;
                    const mB = b.start_month || 12;
                    if (mA !== mB) return mA - mB;

                    const dA = a.start_day || 31;
                    const dB = b.start_day || 31;
                    return dA - dB;
                },

                init() {
                    this.initWatch();
                    this.fetchMedia();
                },

                // Use a router function for debounced calls based on the active tab
                debouncedFetch() {
                    if (this.activeTab === 'media') {
                        this.fetchMedia();
                    } else {
                        this.fetchCharacters();
                    }
                },

                // Listen for tab changes via Alpine watch to fetch data if needed
                initWatch() {
                    this.$watch('activeTab', value => {
                        this.debouncedFetch();
                    });
                },

                async fetchMedia() {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams();
                        if (this.searchQuery) params.append('search', this.searchQuery);
                        if (this.searchFranchise !== 'ALL') params.append('franchise', this.searchFranchise);

                        const res = await fetch(`/api/media/search?${params.toString()}`);
                        if (!res.ok) throw new Error('Fetch failed');
                        this.searchResultsMedia = await res.json();
                    } catch (error) {
                        console.error("Error fetching media:", error);
                    } finally {
                        this.loading = false;
                    }
                },

                async fetchCharacters() {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams();
                        if (this.searchQuery) params.append('search', this.searchQuery);
                        if (this.searchFranchise !== 'ALL') params.append('franchise', this.searchFranchise);

                        const res = await fetch(`/api/characters/search?${params.toString()}`);
                        if (!res.ok) throw new Error('Fetch failed');
                        this.searchResultsChars = await res.json();
                    } catch (error) {
                        console.error("Error fetching characters:", error);
                    } finally {
                        this.loading = false;
                    }
                },

                isMediaSelected(id) {
                    return this.selectedMedia.some(m => m.id === id);
                },

                isCharSelected(id) {
                    return this.selectedCharacters.some(c => c.id === id);
                },

                toggleMedia(item) {
                    if (this.isMediaSelected(item.id)) {
                        this.selectedMedia = this.selectedMedia.filter(m => m.id !== item.id);
                    } else {
                        this.selectedMedia.push(item);
                    }
                },

                toggleCharacter(char) {
                    if (this.isCharSelected(char.id)) {
                        this.selectedCharacters = this.selectedCharacters.filter(c => c.id !== char.id);
                    } else {
                        this.selectedCharacters.push(char);
                    }
                }
            }));
        });
    </script>
</x-layout>
