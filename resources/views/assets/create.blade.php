<x-layout>
    <x-slot:title>Add New Asset</x-slot>

    <!-- Alpine requires defer, but Vite might have it. Let's make sure it's loaded if not via CDN -->
    @push('scripts')
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endpush

    <div class="max-w-4xl mx-auto py-10 px-4">
        <h1 class="text-4xl font-extrabold text-white text-center mb-2">
            Add Centralized <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-cyan-400">Asset</span>
        </h1>
        <p class="text-gray-400 text-center mb-10">Upload or link an external file and tag the characters</p>

        @if(session('success'))
            <div class="mb-6 bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 px-4 py-3 rounded-xl flex items-center gap-3 shadow-lg">
                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-400 px-4 py-3 rounded-xl flex items-center gap-3 shadow-lg">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-400 px-4 py-3 rounded-xl shadow-lg">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('assets.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8" x-data="assetForm()">
            @csrf

            <!-- Asset Info Container -->
            <div class="glass-panel rounded-2xl p-6 sm:p-8 space-y-6 shadow-2xl">
                <h3 class="text-xl font-bold text-white border-b border-white/10 pb-4 mb-4">Core Metadata</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Asset Type <span class="text-red-400">*</span></label>
                        <select name="asset_type" x-model="assetType" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                            <option value="IMG">IMG (Image)</option>
                            <option value="GIF">GIF (Animated Image)</option>
                            <option value="VIDEO">VIDEO (Generic Video)</option>
                            <option value="AMV">AMV (Anime Music Video)</option>
                            <option value="MUSIC">MUSIC (Audio Track)</option>
                            <option value="ANIME">ANIME (Episode/OVA)</option>
                            <option value="MANGA">MANGA (Chapter/Volume)</option>
                            <option value="LIGHT NOVEL">LIGHT NOVEL</option>
                            <option value="DOUJIN">DOUJIN</option>
                            <option value="WALLPAPER ENGINE">WALLPAPER ENGINE</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Title (Optional)</label>
                        <input type="text" name="title" placeholder="Leave empty to use filename" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors placeholder-gray-600">
                    </div>
                </div>

                <!-- Main File / Link Switcher -->
                <div class="p-6 bg-gray-900/50 rounded-xl border border-gray-800">
                    <div class="flex gap-4 mb-6">
                        <template x-for="mode in ['UPLOAD', 'LINK']">
                            <button type="button" @click="sourceMode = mode" 
                                :class="sourceMode === mode ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/50' : 'bg-gray-800 text-gray-400 border-gray-700 hover:bg-gray-700'"
                                class="flex-1 py-2 rounded-lg border font-medium transition-all text-sm">
                                <span x-text="mode === 'UPLOAD' ? 'Upload Local File' : 'External Link'"></span>
                            </button>
                        </template>
                    </div>

                    <div x-show="sourceMode === 'UPLOAD'" x-transition class="space-y-2">
                        <label class="block text-sm font-medium text-gray-300">File <span class="text-red-400">*</span></label>
                        <input type="file" name="file" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-gray-800 file:text-white hover:file:bg-gray-700 cursor-pointer">
                    </div>
                    <div x-show="sourceMode === 'LINK'" x-transition x-cloak class="space-y-2">
                        <label class="block text-sm font-medium text-gray-300">URL <span class="text-red-400">*</span></label>
                        <input type="url" name="url" placeholder="https://example.com/file.mp4" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors placeholder-gray-600">
                    </div>
                </div>

                <!-- Cover Image (Optional, hidden for purely visual static image types) -->
                <div x-show="!['IMG', 'GIF'].includes(assetType)" x-transition class="p-6 bg-indigo-900/10 rounded-xl border border-indigo-500/20">
                    <div class="flex items-start gap-4">
                        <div class="bg-indigo-500/20 p-3 rounded-xl border border-indigo-500/30">
                            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-indigo-200 mb-1">Cover/Thumbnail Image (Optional)</label>
                            <p class="text-xs text-indigo-300/70 mb-3">Since this is not a standard image, adding a cover gives it a beautiful representation in the feed.</p>
                            <input type="file" name="cover_image" accept="image/*" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-500/20 file:text-indigo-300 hover:file:bg-indigo-500/30 cursor-pointer">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Character Selection Container with Search & Selected chips -->
            <div class="glass-panel rounded-2xl p-6 sm:p-8 shadow-2xl">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <h3 class="text-xl font-bold text-white">Tag Characters</h3>
                    <div class="text-xs font-semibold px-3 py-1 bg-emerald-500/20 text-emerald-400 rounded-full border border-emerald-500/30">
                        <span x-text="selectedCharacters.length"></span> Selected
                    </div>
                </div>

                <!-- Selected Characters Chips -->
                <div class="flex flex-wrap gap-2 mb-6" x-show="selectedCharacters.length > 0">
                    <template x-for="char in selectedCharacters" :key="char.id">
                        <div class="flex items-center gap-2 bg-gray-800 border border-gray-700 py-1.5 pl-2 pr-3 rounded-lg shadow-sm group">
                            <input type="hidden" name="characters[]" :value="char.id">
                            <img :src="char.image || 'https://via.placeholder.com/24'" class="w-6 h-6 rounded-md object-cover">
                            <span class="text-sm font-medium text-gray-200" x-text="char.name"></span>
                            <button type="button" @click="toggleCharacter(char)" class="text-gray-500 hover:text-red-400 ml-1 focus:outline-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Search Tools -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div class="sm:col-span-1">
                        <select x-model="searchFranchise" @change="fetchCharacters" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                            <option value="ALL">All Franchises</option>
                            @foreach($franchises as $f)
                                <option value="{{ $f }}">{{ $f }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2 relative">
                        <input type="text" x-model="searchQuery" @input.debounce.500ms="fetchCharacters" placeholder="Search character by name..." class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl pl-12 pr-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 placeholder-gray-600 text-sm">
                        <svg class="w-5 h-5 text-gray-500 absolute left-4 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>

                <!-- Results -->
                <div class="relative min-h-[250px] bg-gray-900/50 rounded-xl p-4 border border-gray-800 overflow-y-auto max-h-[350px]">
                    <div x-show="loading" class="absolute inset-0 z-10 flex items-center justify-center bg-gray-900/80 backdrop-blur-sm rounded-xl">
                        <svg class="animate-spin h-8 w-8 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    <div x-show="!loading && searchResults.length === 0" class="text-center text-gray-500 py-10">
                        <p x-text="searchQuery ? 'No characters found matching query.' : 'Search or filter to load characters.'"></p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <template x-for="char in searchResults" :key="char.id">
                            <label class="flex items-center gap-3 p-3 bg-gray-800 rounded-xl border cursor-pointer hover:bg-gray-750 transition-colors"
                                :class="isSelected(char.id) ? 'border-emerald-500/50 bg-emerald-500/5' : 'border-gray-700'">
                                <input type="checkbox" :checked="isSelected(char.id)" @change="toggleCharacter(char)" class="w-4 h-4 rounded appearance-none checked:bg-emerald-500 border-gray-600 bg-gray-900 focus:ring-emerald-500 focus:ring-offset-gray-800">
                                <img :src="char.image || 'https://via.placeholder.com/40'" class="w-10 h-10 rounded-lg object-cover bg-gray-900">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-white truncate" x-text="char.name"></p>
                                    <p class="text-xs text-emerald-400 font-semibold truncate" x-show="char.isMain">MAIN</p>
                                    <p class="text-xs text-gray-500 truncate" x-show="!char.isMain">SECONDARY</p>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-emerald-500 to-cyan-600 hover:from-emerald-400 hover:to-cyan-500 text-white font-bold text-lg py-4 rounded-2xl shadow-[0_0_20px_rgba(16,185,129,0.3)] hover:shadow-[0_0_30px_rgba(16,185,129,0.5)] transition-all">
                Upload & Create Asset
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('assetForm', () => ({
                assetType: 'IMG',
                sourceMode: 'UPLOAD',
                searchQuery: '',
                searchFranchise: 'ALL',
                searchResults: [],
                selectedCharacters: [],
                loading: false,

                init() {
                    // Cargar unos iniciales al entrar
                    this.fetchCharacters();
                },

                async fetchCharacters() {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams();
                        if (this.searchQuery) params.append('search', this.searchQuery);
                        if (this.searchFranchise !== 'ALL') params.append('franchise', this.searchFranchise);

                        const res = await fetch(`/api/characters/search?${params.toString()}`);
                        if (!res.ok) throw new Error('Fetch failed');
                        this.searchResults = await res.json();
                    } catch (error) {
                        console.error("Error fetching characters:", error);
                    } finally {
                        this.loading = false;
                    }
                },

                isSelected(id) {
                    return this.selectedCharacters.some(c => c.id === id);
                },

                toggleCharacter(char) {
                    if (this.isSelected(char.id)) {
                        this.selectedCharacters = this.selectedCharacters.filter(c => c.id !== char.id);
                    } else {
                        this.selectedCharacters.push(char);
                    }
                }
            }));
        });
    </script>
</x-layout>
