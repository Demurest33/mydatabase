<x-layout>
    <x-slot:title>{{ $search ? 'Franquicia: ' . $search : 'Neo4j Timeline' }}</x-slot>

    @if($root !== null)
    
    @push('scripts')
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endpush
    
    <x-slot:banner>
        <div class="absolute top-0 inset-x-0 h-[450px] overflow-hidden pointer-events-none" style="z-index: 0;">
            <!-- Background Image -->
            <div class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-60" 
                 style="background-image: url('{{ $root->bannerImage ?: $root->coverImage }}'); mask-image: linear-gradient(to bottom, black 50%, transparent 100%); -webkit-mask-image: linear-gradient(to bottom, black 50%, transparent 100%);">
            </div>
            <!-- Overlay to blend with app dark background -->
            <div class="absolute inset-0 bg-gradient-to-t from-[#0f172a] via-[#0f172a]/80 to-transparent"></div>
        </div>
    </x-slot:banner>

    <div class="relative z-10 pt-20">
        
        <!-- Header Info (Cover + Title/Description) -->
        <div class="flex flex-col md:flex-row gap-8 mb-16">
            
            <!-- Sidebar: Cover Image & Stats -->
            <div class="w-56 shrink-0 mx-auto md:mx-0 flex flex-col gap-6">
                <!-- Cover -->
                <div class="w-full aspect-[2/3] rounded-xl overflow-hidden shadow-[0_10px_40px_rgba(0,0,0,0.5)] border border-gray-700/50 bg-gray-900 group">
                    <img src="{{ $root->coverImage }}" alt="{{ $root->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                </div>
                
                <!-- Extra Stats Sidebar -->
                {{-- <div class="bg-[#151921]/80 backdrop-blur-md rounded-xl p-5 border border-white/5 space-y-4">
                    <div class="flex flex-col">
                        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Format</span>
                        <span class="text-sm text-gray-300 font-medium">{{ $franchiseData['root']['format'] }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Status</span>
                        <span class="text-sm text-gray-300 font-medium">{{ $franchiseData['root']['status'] }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Season</span>
                        <span class="text-sm text-gray-300 font-medium capitalize">{{ strtolower($franchiseData['root']['season']) }} {{ $franchiseData['root']['seasonYear'] }}</span>
                    </div>
                    @if(!empty($franchiseData['root']['averageScore']))
                    <div class="flex flex-col">
                        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Average Score</span>
                        <span class="text-sm text-emerald-400 font-bold flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            {{ $franchiseData['root']['averageScore'] }}%
                        </span>
                    </div>
                    @endif
                </div> --}}
            </div>

            <!-- Main Content: Title, Description, Genres -->
            <div class="flex-1 mt-0 md:mt-2">
                <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-2 drop-shadow-md leading-tight">
                    {{ $root->title }}
                </h1>
                <h2 class="text-lg md:text-xl text-indigo-300/80 mb-6 font-light drop-shadow-sm">
                    {{ $root->native }}
                </h2>

                @if(!empty($root->description))
                    <div class="text-gray-300/90 leading-relaxed text-sm md:text-base mb-8 line-clamp-6 hover:line-clamp-none transition-all duration-500">
                        {!! $root->description !!}
                    </div>
                @endif

                @if(!empty($root->genres))
                    <div class="mt-6 flex flex-wrap gap-2">
                        @foreach($root->genres as $genre)
                            <span class="px-3 py-1 bg-[#151921] text-indigo-300 border border-indigo-500/30 rounded-full text-xs font-bold tracking-wide shadow-sm hover:bg-indigo-900/40 transition-colors cursor-default">{{ $genre }}</span>
                        @endforeach
                    </div>
                @endif

                @if(!empty($root->studios))
                    <p class="mt-6 text-sm text-gray-500">
                        <span class="font-bold uppercase tracking-wider text-xs mr-2">Studios</span>
                        {{ implode(', ', array_map(fn($s) => $s->name, $root->studios)) }}
                    </p>
                @endif
            </div>
            
        </div>

        <!-- LOWER SECTION: TIMELINE AND RELATIONS -->
        <div class="flex flex-col lg:flex-row gap-10">
            
            <div class="flex-1 space-y-12">

                <!-- TIMELINE -->
                @if(count($timeline) > 0)
                <div>
                    <h3 class="text-xl font-bold text-white mb-8 flex items-center gap-3 border-b border-gray-800 pb-4">
                        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Chronological Watch Order
                    </h3>
                    
                    <div class="relative pl-6 md:pl-8 space-y-10 border-l-2 border-gray-800 ml-4 md:ml-6">
                        @foreach($timeline as $item)
                            <div class="relative group">
                                <!-- Dot -->
                                <div class="absolute -left-[31px] md:-left-[39px] top-6 w-3 h-3 md:w-4 md:h-4 rounded-full bg-indigo-500 border-4 border-[#0f172a] shadow-[0_0_10px_rgba(99,102,241,0.6)] group-hover:scale-125 transition-transform z-10"></div>

                                <a href="{{ route('media.show', $item->id) }}" class="bg-[#151921] border border-white/5 rounded-2xl p-4 md:p-6 flex flex-col md:flex-row gap-6 shadow-sm hover:shadow-lg hover:border-indigo-500/30 hover:-translate-y-1 transition-all block">
                                    <div class="w-24 md:w-32 shrink-0 relative rounded-lg overflow-hidden border border-gray-800 aspect-[2/3]">
                                        <img src="{{ $item->coverImage }}" class="w-full h-full object-cover">
                                        <div class="absolute top-0 left-0 bg-indigo-600 text-white text-[9px] font-bold px-2 py-1 uppercase rounded-br-lg">{{ $item->startYear ?? 'TBA' }}</div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start mb-2">
                                            <h4 class="text-lg md:text-xl font-bold text-gray-100">{{ $item->title }}</h4>
                                            <span class="bg-gray-800 text-gray-300 text-xs font-bold px-2.5 py-1 rounded-md ml-2 shrink-0">{{ $item->format }}</span>
                                        </div>
                                        @if(!empty($item->description))
                                            <div class="text-sm text-gray-400 line-clamp-3 mb-4 prose prose-invert max-w-none">{!! $item->description !!}</div>
                                        @endif

                                        <!-- Character Avatars Preview -->
                                        @if(!empty($item->characters))
                                            <div class="flex flex-wrap gap-2 mt-auto">
                                                @foreach(array_slice($item->characters, 0, 8) as $edge)
                                                    <div class="group/char relative">
                                                        <img src="{{ $edge->image ?? '' }}" class="w-8 h-8 rounded-full border border-gray-700 object-cover hover:border-indigo-400 transition-colors cursor-pointer">
                                                        <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 bg-gray-900 text-xs px-2 py-1 rounded opacity-0 invisible group-hover/char:opacity-100 group-hover/char:visible transition-all whitespace-nowrap z-20 pointer-events-none">{{ $edge->name }}</div>
                                                    </div>
                                                @endforeach
                                                @if(count($item->characters) > 8)
                                                    <div class="w-8 h-8 rounded-full border border-gray-700 bg-gray-800 flex items-center justify-center text-[10px] font-bold text-gray-400">
                                                        +{{ count($item->characters) - 8 }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            
                <!-- SOURCE MATERIAL / RELATIONS LIKE ANILIST -->
                @if(count($source) > 0 || count($others) > 0)
                <div class="bg-[#151921]/60 border border-white/5 p-6 rounded-2xl">
                    <h3 class="text-lg font-bold text-gray-200 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                        Related Media
                    </h3>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        @php
                            $relations = array_merge($source, $others);
                        @endphp
                        @foreach($relations as $item)
                            <a href="{{ route('media.show', $item->id) }}" class="group relative rounded-xl overflow-hidden bg-gray-900 border border-gray-800 transition-all hover:border-indigo-500 hover:-translate-y-1 hover:shadow-xl block">
                                @if(in_array($item, $source))
                                    <span class="absolute top-0 inset-x-0 z-10 bg-gradient-to-r from-yellow-600 to-yellow-500 text-black text-[9px] font-black tracking-wider uppercase text-center py-1 border-b border-black/20">Source</span>
                                @else
                                    <span class="absolute top-0 inset-x-0 z-10 bg-gradient-to-r from-gray-700 to-gray-600 text-white text-[9px] font-black tracking-wider uppercase text-center py-1 border-b border-black/20">Alternative</span>
                                @endif
                                
                                <div class="aspect-[2/3] overflow-hidden bg-black">
                                    <img src="{{ $item->coverImage }}" alt="" class="w-full h-full object-cover transition-transform group-hover:scale-105 opacity-90 group-hover:opacity-100 mt-4">
                                </div>
                                <div class="p-3 absolute bottom-0 inset-x-0 bg-gradient-to-t from-gray-950 via-gray-900/90 to-transparent pt-10 pb-2">
                                    <h4 class="text-[11px] font-bold text-gray-300 line-clamp-2 leading-tight group-hover:text-indigo-400">{{ $item->title }}</h4>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

            <!-- RIGHT COLUMN: MAIN CHARACTERS GRID (if we want to show characters of the franchise) -->
            <!-- We can extract main characters from the root media to populate a character grid -->
            @if(!empty($root->characters))
            <div class="w-full lg:w-80 shrink-0">
                <h3 class="text-lg font-bold text-white mb-6 border-b border-gray-800 pb-4">Main Characters</h3>
                <div class="grid grid-cols-2 gap-3">
                    @php
                        // Filter to show mostly ROLE MAIN from the root, or just take first 10
                        $rootChars = collect($root->characters)->sortBy(function($a) {
                            return $a->role === 'MAIN' ? 0 : 1;
                        })->take(12);
                    @endphp
                    @foreach($rootChars as $charEdge)
                        <a href="{{ route('characters.show', $charEdge->id) }}" class="bg-[#151921] border border-white/5 rounded-lg overflow-hidden flex flex-col items-center p-3 hover:border-indigo-500/50 transition-colors group text-center block">
                            <img src="{{ $charEdge->image }}" class="w-16 h-16 rounded-full object-cover mb-3 border-2 border-gray-800 group-hover:border-indigo-500 transition-colors">
                            <h5 class="text-xs font-bold text-gray-200 line-clamp-1 w-full">{{ $charEdge->name }}</h5>
                            <span class="text-[9px] font-black text-indigo-400/80 uppercase mt-1">{{ $charEdge->role }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        @php
            $allCharactersList = [];
            $seenIds = [];

            $checkMedia = function($mediaList) use (&$allCharactersList, &$seenIds) {
                foreach ($mediaList as $item) {
                    if (!empty($item->characters)) {
                        foreach ($item->characters as $edge) {
                            if (!isset($seenIds[$edge->id])) {
                                $seenIds[$edge->id] = true;
                                $allCharactersList[] = $edge;
                            }
                        }
                    }
                }
            };

            $checkMedia($timeline);
            $checkMedia($source);
            $checkMedia($others);

            usort($allCharactersList, function($a, $b) {
                if ($a->role === 'MAIN' && $b->role !== 'MAIN') return -1;
                if ($b->role === 'MAIN' && $a->role !== 'MAIN') return 1;
                return strnatcmp($a->name, $b->name);
            });
        @endphp

        <!-- ALL CHARACTERS SECTION (SEARCHABLE) -->
        @if(count($allCharactersList) > 0)
        <div class="mt-16 bg-[#151921]/40 border border-white/5 rounded-2xl p-8" x-data="{ search: '' }">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8 border-b border-gray-800 pb-6">
                <h3 class="text-2xl font-bold text-white flex items-center gap-3">
                    <svg class="w-7 h-7 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    All Characters ({{ count($allCharactersList) }})
                </h3>
                
                <div class="relative w-full md:w-72">
                    <input x-model="search" type="text" placeholder="Search character..." class="w-full bg-gray-900 border border-gray-700 text-white text-sm rounded-xl pl-10 pr-4 py-2.5 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                    <svg class="w-5 h-5 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-4">
                @foreach($allCharactersList as $char)
                    <a href="{{ route('characters.show', $char->id) }}"
                       x-show="search === '' || '{{ strtolower(addslashes($char->name)) }}'.includes(search.toLowerCase())"
                       class="bg-[#11151d] border border-gray-800 rounded-xl overflow-hidden hover:border-indigo-500 transition-all hover:-translate-y-1 hover:shadow-lg group flex flex-col">
                        <div class="aspect-[3/4] relative overflow-hidden bg-gray-900">
                            <img src="{{ $char->image }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                            @if($char->role === 'MAIN')
                                <div class="absolute top-0 right-0 bg-indigo-600 text-[9px] font-black uppercase px-2 py-0.5 rounded-bl text-white shadow-sm">MAIN</div>
                            @endif
                        </div>
                        <div class="p-3 text-center flex-1 flex flex-col justify-center">
                            <h4 class="text-[11px] font-bold text-gray-200 line-clamp-2 leading-tight group-hover:text-indigo-400 transition-colors">{{ $char->name }}</h4>
                            <span class="text-[9px] font-black text-indigo-400/80 uppercase mt-1 block">{{ $char->role }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

    @elseif($search)
        <!-- Loading or Not Found State -->
        <div class="flex items-center justify-center min-h-[50vh]">
            <div class="text-center bg-[#151921] p-10 rounded-3xl border border-white/5 shadow-xl max-w-md w-full">
                <div class="w-20 h-20 bg-indigo-500/10 rounded-full flex items-center justify-center mx-auto mb-6 text-indigo-400">
                    <svg class="w-10 h-10 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">Searching...</h3>
                <p class="text-gray-400">Looking up franchise data for "{{ $search }}"</p>
            </div>
        </div>
    @else
        <!-- No Search Selection -->
        <div class="flex items-center justify-center min-h-[60vh]">
            <div class="text-center bg-gray-900/50 p-12 rounded-3xl border border-gray-800 max-w-lg">
                <svg class="w-24 h-24 text-gray-700 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                <h3 class="text-xl font-bold text-white mb-4">No Franchise Selected</h3>
                <p class="text-gray-400">Please select a franchise from the catalog to view its comprehensive timeline and related media.</p>
            </div>
        </div>
    @endif

</x-layout>
