<x-layout>
    <x-slot:title>Franchise Catalog</x-slot>

    <!-- Header Section -->
    <div class="mb-10 pt-4">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11v9h-5v-6h-4v6H5v-9M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4a2 2 0 012 2v1m-4 0h14a2 2 0 012 2v14a2 2 0 01-2 2z"></path></svg>
                </div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">Franchise Catalog</h1>
            </div>
            
            <!-- Quick search? Can be added inside the filters -->
        </div>

        <form action="{{ route('franchises.index') }}" method="GET" class="space-y-6" id="filter-form">
            
            <!-- Alphabet Filter Row -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-3 shadow-md flex flex-wrap gap-1.5 justify-center sm:justify-start">
                @php
                    $alphabet = array_merge(['#'], range('A', 'Z'));
                @endphp
                
                @foreach($alphabet as $char)
                    <button type="submit" name="letter" value="{{ $char }}" 
                        class="w-9 h-9 rounded-lg font-bold text-sm transition-all duration-200 {{ $letter === $char ? 'bg-emerald-500 text-white shadow-[0_0_15px_rgba(16,185,129,0.4)]' : 'bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white' }}">
                        {{ $char }}
                    </button>
                @endforeach
                
                <!-- Clear letter button if selected -->
                @if($letter)
                    <button type="submit" name="letter" value="" class="w-9 h-9 rounded-lg font-bold text-sm bg-red-500/20 text-red-400 hover:bg-red-500/40 transition-all" title="Clear letter filter">
                        <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                @endif
            </div>

            <!-- Specific Fitlers (Genre, Sort) -->
            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="flex flex-wrap gap-4 flex-1">
                    <div class="flex-1 min-w-[200px] sm:max-w-xs relative group">
                        <select name="genre" onchange="document.getElementById('filter-form').submit();" class="appearance-none w-full bg-gray-900 border border-gray-800 text-gray-300 font-medium rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500/50 hover:border-gray-700 transition-colors focus:border-emerald-500 cursor-pointer shadow-sm">
                            <option value="">Género: Cualquiera</option>
                            @foreach($allGenres as $g)
                                <option value="{{ $g }}" {{ $genre === $g ? 'selected' : '' }}>{{ $g }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-gray-500 group-hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>

                    <div class="flex-1 min-w-[200px] sm:max-w-xs relative group">
                        <select name="sort" onchange="document.getElementById('filter-form').submit();" class="appearance-none w-full bg-gray-900 border border-gray-800 text-gray-300 font-medium rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500/50 hover:border-gray-700 transition-colors focus:border-emerald-500 cursor-pointer shadow-sm">
                            <option value="default" {{ $sort === 'default' ? 'selected' : '' }}>Ordenar por: A-Z</option>
                            <option value="assets_desc" {{ $sort === 'assets_desc' ? 'selected' : '' }}>Más Recursos (Assets)</option>
                            <option value="chars_desc" {{ $sort === 'chars_desc' ? 'selected' : '' }}>Más Personajes</option>
                        </select>
                        <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-gray-500 group-hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="text-gray-400 text-sm font-semibold tracking-wide bg-gray-900 border border-gray-800 px-5 py-3 rounded-xl shadow-sm">
                    <span class="text-emerald-400 text-lg mr-1">{{ count($franchises) }}</span> Resultados
                </div>
            </div>
            
        </form>
    </div>

    <!-- Error Handling -->
    @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/50 text-red-500 p-4 rounded-xl mb-6 shadow-md shadow-red-500/10">
            {{ session('error') }}
        </div>
    @endif

    <!-- Catalog Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 gap-y-10">
        
        @forelse($franchises as $f)
            <a href="{{ route('franchises.show', $f['name']) }}" class="group flex flex-col relative w-full">
                <!-- Cover Image Container -->
                <div class="relative w-full aspect-[2/3] rounded-2xl overflow-hidden shadow-lg border border-gray-800 bg-gray-900 mb-3 group-hover:border-emerald-500/50 group-hover:shadow-[0_10px_30px_-10px_rgba(16,185,129,0.3)] transition-all duration-300 group-hover:-translate-y-2">
                    
                    @if($f['coverImage'])
                        <img src="{{ $f['coverImage'] }}" alt="{{ $f['name'] }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    @else
                        <!-- No cover fallback -->
                        <div class="absolute inset-0 w-full h-full bg-gradient-to-br from-indigo-900/50 to-purple-900/50 flex items-center justify-center">
                            <svg class="w-16 h-16 text-white/20" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"></path></svg>
                        </div>
                    @endif

                    <!-- Gradient Overlay (Bottom to top) -->
                    <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-[#030712] to-transparent opacity-80 group-hover:opacity-90 transition-opacity"></div>

                    <!-- Top Tags (e.g. Media Format if known) -->
                    @if($f['primaryFormat'])
                        <div class="absolute top-2 left-2 bg-indigo-600/90 backdrop-blur-md text-white text-[10px] font-black tracking-widest px-2.5 py-1 rounded shadow-md uppercase">
                            {{ $f['primaryFormat'] }}
                        </div>
                    @endif

                    <!-- Stats Badges inside cover bottom -->
                    <div class="absolute bottom-2 inset-x-2 flex justify-between items-center text-xs font-semibold text-white/90 px-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300 translate-y-2 group-hover:translate-y-0">
                        <span class="flex items-center gap-1 drop-shadow-md">
                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> 
                            {{ $f['charactersCount'] }}
                        </span>
                        
                        <span class="flex items-center gap-1 drop-shadow-md">
                            <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg> 
                            {{ $f['assetsCount'] }}
                        </span>
                    </div>

                </div>
                
                <!-- Title outside of Cover -->
                <div class="px-1">
                    <h3 class="text-sm font-bold text-gray-200 line-clamp-2 leading-tight group-hover:text-emerald-400 transition-colors">
                        {{ $f['name'] }}
                    </h3>
                </div>
            </a>
        @empty
            <div class="col-span-full py-20 text-center flex flex-col items-center justify-center text-gray-500">
                <svg class="w-20 h-20 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-xl font-medium">No se encontraron franquicias con esos filtros.</p>
                <p class="mt-2 text-sm">Intenta buscar por otra letra o cambiar de género.</p>
            </div>
        @endforelse

    </div>
</x-layout>
