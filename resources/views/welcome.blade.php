<x-layout>
    <x-slot:title>Explore Assets</x-slot>

    <!-- Categories / Tags Row (Imgur Style) -->
    <div class="mb-8 overflow-x-auto pb-4 hide-scrollbar">
        <div class="flex gap-4 min-w-max">
            <!-- Category Item -->
            <a href="#" class="relative overflow-hidden rounded-xl w-32 h-20 group">
                <div class="absolute inset-0 bg-blue-600/80 mix-blend-multiply z-10 group-hover:bg-blue-500/80 transition-colors"></div>
                <img src="https://images.unsplash.com/photo-1607604276583-eef5d076ff5f?q=80&w=200&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="Anime">
                <div class="absolute inset-0 z-20 flex flex-col items-center justify-center text-white">
                    <span class="font-bold text-sm">Anime</span>
                    <span class="text-xs text-white/70">14k Posts</span>
                </div>
            </a>

            <a href="#" class="relative overflow-hidden rounded-xl w-32 h-20 group">
                <div class="absolute inset-0 bg-purple-600/80 mix-blend-multiply z-10 group-hover:bg-purple-500/80 transition-colors"></div>
                <img src="https://images.unsplash.com/photo-1541562232579-512a21360020?q=80&w=200&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="Memes">
                <div class="absolute inset-0 z-20 flex flex-col items-center justify-center text-white">
                    <span class="font-bold text-sm">Memes</span>
                    <span class="text-xs text-white/70">85k Posts</span>
                </div>
            </a>

            <a href="#" class="relative overflow-hidden rounded-xl w-32 h-20 group">
                <div class="absolute inset-0 bg-emerald-600/80 mix-blend-multiply z-10 group-hover:bg-emerald-500/80 transition-colors"></div>
                <img src="https://images.unsplash.com/photo-1618336753974-aae8e04506aa?q=80&w=200&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="Wallpapers">
                <div class="absolute inset-0 z-20 flex flex-col items-center justify-center text-white">
                    <span class="font-bold text-sm">Wallpapers</span>
                    <span class="text-xs text-white/70">32k Posts</span>
                </div>
            </a>

            <a href="#" class="relative overflow-hidden rounded-xl w-32 h-20 group">
                <div class="absolute inset-0 bg-orange-600/80 mix-blend-multiply z-10 group-hover:bg-orange-500/80 transition-colors"></div>
                <img src="https://images.unsplash.com/photo-1518709268805-4e9042af9f23?q=80&w=200&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="Art">
                <div class="absolute inset-0 z-20 flex flex-col items-center justify-center text-white">
                    <span class="font-bold text-sm">Art</span>
                    <span class="text-xs text-white/70">5k Posts</span>
                </div>
            </a>

            <a href="#" class="relative overflow-hidden rounded-xl w-32 h-20 group">
                <div class="absolute inset-0 bg-rose-600/80 mix-blend-multiply z-10 group-hover:bg-rose-500/80 transition-colors"></div>
                <img src="https://images.unsplash.com/photo-1578632767115-351597cf2477?q=80&w=200&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="Cosplay">
                <div class="absolute inset-0 z-20 flex flex-col items-center justify-center text-white">
                    <span class="font-bold text-sm">Cosplay</span>
                    <span class="text-xs text-white/70">12k Posts</span>
                </div>
            </a>

            <!-- Mocks for categories to demonstrate horizontal scrolling -->
            @foreach(['GIFs', 'Video', 'Manga', 'Lore', 'News'] as $cat)
            <a href="#" class="relative overflow-hidden rounded-xl w-32 h-20 group bg-gray-800 flex items-center justify-center hover:bg-gray-700 transition-colors">
                <span class="font-bold text-sm text-gray-300">{{ $cat }}</span>
            </a>
            @endforeach
        </div>
    </div>

    <!-- Feed Header -->
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-white uppercase tracking-wider flex items-center gap-2">
            Most Viral
            <svg class="w-5 h-5 text-gray-400 cursor-pointer hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </h2>
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

    <!-- Asset Masonry / Grid -->
    <div class="columns-1 sm:columns-2 lg:columns-3 xl:columns-4 gap-4 space-y-4">
        
        <!-- Mock Card 1 (Image) -->
        <a href="#" class="block group relative break-inside-avoid bg-gray-900 rounded-lg overflow-hidden shadow-lg border border-gray-800 hover:border-indigo-500/50 transition-all duration-300 hover:shadow-indigo-500/20 hover:-translate-y-1">
            <div class="relative">
                <img src="https://images.unsplash.com/photo-1621478374422-35206faeddfb?q=80&w=600&auto=format&fit=crop" alt="Mock Asset" class="w-full h-auto object-cover">
                <div class="absolute top-2 right-2 bg-black/60 backdrop-blur-md px-2 py-0.5 rounded text-xs text-white font-medium flex items-center gap-1">
                    <svg class="w-3 h-3 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"></path></svg>
                    IMAGE
                </div>
            </div>
            <div class="p-4">
                <h3 class="text-white font-medium line-clamp-2 group-hover:text-indigo-400 transition-colors">Amazing character concept art found in the wild</h3>
                <div class="flex items-center justify-between mt-3 text-gray-500 text-sm">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center gap-1 hover:text-gray-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg> 1.2k</span>
                        <span class="flex items-center gap-1 hover:text-gray-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg> 45</span>
                    </div>
                    <span class="flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg> 15.3k</span>
                </div>
            </div>
        </a>

        <!-- Mock Card 2 (Video without thumbnail) -->
        <a href="#" class="block group relative break-inside-avoid bg-gray-900 rounded-lg overflow-hidden shadow-lg border border-gray-800 hover:border-indigo-500/50 transition-all duration-300 hover:shadow-indigo-500/20 hover:-translate-y-1">
            <div class="relative w-full aspect-video bg-gray-800 flex items-center justify-center flex-col group-hover:bg-gray-750 transition-colors">
                <div class="w-16 h-16 rounded-full bg-white/10 flex items-center justify-center group-hover:scale-110 group-hover:bg-indigo-500/20 transition-all duration-300">
                    <svg class="w-8 h-8 text-white group-hover:text-indigo-400 translate-x-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"></path></svg>
                </div>
                <div class="absolute top-2 right-2 bg-black/60 backdrop-blur-md px-2 py-0.5 rounded text-xs text-white font-medium flex items-center gap-1">
                    <svg class="w-3 h-3 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path opacity="0.3" d="M2 6a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path><path d="M4 6a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path><path d="M8 8v4l4-2-4-2z"></path></svg>
                    0:45
                </div>
            </div>
            <div class="p-4">
                <h3 class="text-white font-medium line-clamp-2 group-hover:text-indigo-400 transition-colors">Epic fight scene edited to a synthwave track</h3>
                <div class="flex items-center justify-between mt-3 text-gray-500 text-sm">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center gap-1 hover:text-gray-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg> 845</span>
                        <span class="flex items-center gap-1 hover:text-gray-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg> 12</span>
                    </div>
                </div>
            </div>
        </a>

        <!-- Mock Card 3 (Tall Image) -->
        <a href="#" class="block group relative break-inside-avoid bg-gray-900 rounded-lg overflow-hidden shadow-lg border border-gray-800 hover:border-indigo-500/50 transition-all duration-300 hover:shadow-indigo-500/20 hover:-translate-y-1">
            <div class="relative">
                <img src="https://images.unsplash.com/photo-1542451313056-b7c8e626645f?q=80&w=400&h=600&auto=format&fit=crop" alt="Mock Asset" class="w-full h-auto object-cover">
            </div>
            <div class="p-4">
                <h3 class="text-white font-medium line-clamp-2 group-hover:text-indigo-400 transition-colors">Vertical wallpaper for mobile screens</h3>
                <div class="flex items-center justify-between mt-3 text-gray-500 text-sm">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center gap-1 hover:text-gray-300"><svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg> 3.4k</span>
                    </div>
                </div>
            </div>
        </a>

        <!-- Mock Card 4 (Text/Link Asset) -->
        <a href="#" class="block group relative break-inside-avoid bg-gray-900 rounded-lg overflow-hidden shadow-lg border border-gray-800 hover:border-indigo-500/50 transition-all duration-300 hover:shadow-indigo-500/20 hover:-translate-y-1">
            <div class="p-6 bg-gradient-to-br from-gray-800 to-gray-900 flex flex-col items-center justify-center min-h-[200px] text-center">
                <svg class="w-12 h-12 text-indigo-400 mb-4 opacity-50 group-hover:scale-110 group-hover:opacity-100 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                <h4 class="text-white font-bold text-lg leading-tight group-hover:text-indigo-300 transition-colors">Read the latest interview with the creator!</h4>
            </div>
            <div class="p-4 border-t border-gray-800">
                <h3 class="text-gray-400 font-medium text-xs uppercase tracking-widest mb-1">External Article</h3>
                <div class="flex items-center justify-between mt-3 text-gray-500 text-sm">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center gap-1 hover:text-gray-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg> 56</span>
                    </div>
                </div>
            </div>
        </a>

        <!-- Mock Card 5 (Image) -->
        <a href="#" class="block group relative break-inside-avoid bg-gray-900 rounded-lg overflow-hidden shadow-lg border border-gray-800 hover:border-indigo-500/50 transition-all duration-300 hover:shadow-indigo-500/20 hover:-translate-y-1">
            <div class="relative">
                <img src="https://images.unsplash.com/photo-1541562232579-512a21360020?q=80&w=600&auto=format&fit=crop" alt="Mock Asset" class="w-full h-auto object-cover">
            </div>
            <div class="p-4">
                <h3 class="text-white font-medium line-clamp-2 group-hover:text-indigo-400 transition-colors">This frame is absolutely legendary</h3>
                <div class="flex items-center justify-between mt-3 text-gray-500 text-sm">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center gap-1 hover:text-gray-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg> 11k</span>
                    </div>
                </div>
            </div>
        </a>

        <!-- Mock Card 6 (Video without thumbnail) -->
        <a href="#" class="block group relative break-inside-avoid bg-gray-900 rounded-lg overflow-hidden shadow-lg border border-gray-800 hover:border-indigo-500/50 transition-all duration-300 hover:shadow-indigo-500/20 hover:-translate-y-1">
            <div class="relative w-full aspect-[4/3] bg-gradient-to-br from-indigo-900/50 to-purple-900/50 flex flex-col items-center justify-center group-hover:from-indigo-900 group-hover:to-purple-900 transition-colors">
                <div class="w-16 h-16 rounded-full bg-white/10 flex items-center justify-center group-hover:scale-110 group-hover:bg-white/20 transition-all duration-300">
                    <svg class="w-8 h-8 text-white translate-x-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"></path></svg>
                </div>
            </div>
            <div class="p-4">
                <h3 class="text-white font-medium line-clamp-2 group-hover:text-indigo-400 transition-colors">Fan Animation: What if scenario</h3>
                <div class="flex items-center justify-between mt-3 text-gray-500 text-sm">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center gap-1 hover:text-gray-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg> 920</span>
                    </div>
                </div>
            </div>
        </a>

        <!-- Mock Card 7 (Image) -->
        <a href="#" class="block group relative break-inside-avoid bg-gray-900 rounded-lg overflow-hidden shadow-lg border border-gray-800 hover:border-indigo-500/50 transition-all duration-300 hover:shadow-indigo-500/20 hover:-translate-y-1">
            <div class="relative">
                <img src="https://images.unsplash.com/photo-1543852786-1cf6624b9987?q=80&w=400&h=300&auto=format&fit=crop" alt="Cat tax" class="w-full h-auto object-cover">
            </div>
            <div class="p-4">
                <h3 class="text-white font-medium line-clamp-2 group-hover:text-indigo-400 transition-colors">Cat tax because you made it this far down</h3>
                <div class="flex items-center justify-between mt-3 text-gray-500 text-sm">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center gap-1 hover:text-gray-300"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg> 4.5k</span>
                    </div>
                </div>
            </div>
        </a>
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
