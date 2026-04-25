<x-layout>
    <x-slot:title>Characters Backoffice</x-slot>

    @php
    $roleColors = [
        'MAIN'       => 'bg-amber-500/20 text-amber-300 border-amber-500/40',
        'SUPPORTING' => 'bg-blue-500/20 text-blue-300 border-blue-500/40',
        'BACKGROUND' => 'bg-gray-500/20 text-gray-400 border-gray-500/40',
        'UNKNOWN'    => 'bg-gray-500/20 text-gray-400 border-gray-500/40',
    ];
    $defaultRoleColor = 'bg-gray-500/20 text-gray-400 border-gray-500/40';
    @endphp

    {{-- Page header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Characters</h1>
            <p class="text-gray-400 mt-1 text-sm">Agrupados por franquicia y rol</p>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.dashboard') }}" class="text-indigo-400 hover:text-indigo-300 font-bold text-sm">
                &larr; Dashboard
            </a>
            <a href="{{ route('admin.characters.create') }}"
               class="bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-xl font-bold text-sm transition-colors shadow-lg shadow-pink-500/20">
                + Add Character
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 px-5 py-4 rounded-xl flex items-center gap-3 shadow-lg">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="flex gap-6 items-start">

        {{-- ── Sidebar ── --}}
        <aside class="w-56 flex-shrink-0 sticky top-24 self-start max-h-[calc(100vh-8rem)] flex flex-col gap-3 overflow-hidden">

            {{-- Search --}}
            <div class="relative">
                <svg class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
                <input id="char-search" type="search" placeholder="Buscar personaje..."
                       autocomplete="off"
                       class="w-full bg-[#151921] border border-gray-700 focus:border-pink-500 text-white placeholder-gray-600 text-sm rounded-xl pl-9 pr-4 py-2.5 outline-none transition-colors">
            </div>

            {{-- Filter header + select all --}}
            <div class="flex items-center justify-between px-1">
                <span class="text-[10px] font-bold text-gray-600 uppercase tracking-wider">Filtrar por media</span>
                <button id="btn-select-all" type="button"
                        class="text-[10px] text-indigo-400 hover:text-indigo-300 transition-colors font-semibold">
                    Todo
                </button>
            </div>

            {{-- Franchise groups with media checkboxes --}}
            <div class="overflow-y-auto flex-1 space-y-1 pr-0.5"
                 style="scrollbar-color:#374151 transparent; scrollbar-width:thin;">

                @foreach($franchiseMedia as $franchise => $mediaItems)
                @php $slug = Str::slug($franchise); @endphp

                <details class="group/sf">
                    <summary class="flex items-center gap-2 px-2 py-1.5 rounded-lg cursor-pointer select-none list-none
                                    text-gray-400 hover:text-white hover:bg-white/5 transition-colors">
                        <svg class="w-3 h-3 flex-shrink-0 transition-transform duration-150 group-open/sf:rotate-90"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="text-xs font-semibold truncate flex-1">{{ $franchise }}</span>
                        <span class="text-[10px] text-gray-600 flex-shrink-0">{{ count($mediaItems) }}</span>
                    </summary>

                    <div class="mt-1 ml-2 pl-3 border-l border-gray-800 space-y-0.5 pb-1">
                        @foreach($mediaItems as $media)
                        <label class="flex items-center gap-2 px-2 py-1 rounded-md hover:bg-white/5 cursor-pointer group/lbl">
                            <input type="checkbox"
                                   value="{{ $media->id }}"
                                   class="media-filter w-3 h-3 rounded flex-shrink-0 cursor-pointer accent-pink-500"
                                   checked>
                            <span class="text-xs text-gray-500 group-hover/lbl:text-gray-200 truncate transition-colors leading-tight">
                                {{ $media->title }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </details>

                @endforeach
            </div>
        </aside>

        {{-- ── Main content ── --}}
        <div class="flex-1 min-w-0 space-y-6">

            @forelse($grouped as $franchise => $roles)
            @php
                $slug  = Str::slug($franchise);
                $total = collect($roles)->sum(fn($items) => count($items));
            @endphp

            <details id="franchise-{{ $slug }}"
                     class="franchise-section bg-[#11151d] border border-gray-800 rounded-2xl overflow-hidden group/fr"
                     open>

                <summary class="flex items-center gap-3 cursor-pointer select-none list-none px-5 py-4 hover:bg-white/3 transition-colors">
                    <svg class="w-4 h-4 text-gray-500 transition-transform duration-200 group-open/fr:rotate-90 flex-shrink-0"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-lg font-bold text-white">{{ $franchise }}</span>
                    <span class="text-xs font-semibold bg-pink-500/15 text-pink-300 border border-pink-500/30 px-2.5 py-0.5 rounded-full">
                        {{ $total }} {{ $total === 1 ? 'character' : 'characters' }}
                    </span>
                </summary>

                <div class="px-5 pb-5 space-y-6 border-t border-gray-800/60 pt-5">
                    @foreach($roles as $role => $characters)
                    @php $badgeClasses = $roleColors[$role] ?? $defaultRoleColor; @endphp

                    <div class="role-section">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="text-xs font-bold border px-3 py-1 rounded-full {{ $badgeClasses }}">
                                {{ $role }}
                            </span>
                            <span class="text-gray-700 text-xs">{{ count($characters) }} characters</span>
                            <div class="flex-1 h-px bg-gray-800/70"></div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                            @foreach($characters as $char)
                            <div class="char-card bg-[#151921] border border-gray-800 rounded-xl overflow-hidden
                                        hover:border-pink-500/40 hover:shadow-lg hover:shadow-pink-500/10
                                        transition-all duration-200 group/card flex flex-col"
                                 data-name="{{ strtolower($char->name ?? '') }}"
                                 data-media-ids="{{ implode(',', $char->mediaIds ?? []) }}">

                                {{-- Portrait --}}
                                <div class="aspect-[3/4] bg-gray-900 relative overflow-hidden flex-shrink-0">
                                    @if(!empty($char->image))
                                        <img src="{{ $char->image }}"
                                             alt="{{ $char->name }}"
                                             class="w-full h-full object-cover object-top group-hover/card:scale-105 transition-transform duration-300"
                                             loading="lazy">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-700">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    @endif

                                    <div class="absolute top-1.5 left-1.5">
                                        <span class="text-[10px] font-bold border px-1.5 py-0.5 rounded backdrop-blur-sm {{ $badgeClasses }}">
                                            {{ $role }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Info --}}
                                <div class="p-2.5 flex flex-col gap-0.5">
                                    <p class="text-white text-xs font-semibold line-clamp-2 leading-snug">
                                        {{ $char->name ?? 'Unknown' }}
                                    </p>
                                    @if(!empty($char->mediaTitle))
                                    <span class="text-gray-600 text-[10px] truncate">{{ $char->mediaTitle }}</span>
                                    @endif
                                </div>

                                {{-- Actions --}}
                                <div class="opacity-0 group-hover/card:opacity-100 transition-opacity duration-150 px-2 pb-2.5 flex gap-1.5">
                                    <a href="{{ route('admin.characters.edit', $char->id) }}"
                                       class="flex-1 text-center text-[10px] font-bold bg-pink-500/20 hover:bg-pink-500/40 text-pink-300 rounded-lg py-1.5 transition-colors">
                                        Editar
                                    </a>
                                    <form action="{{ route('admin.characters.destroy', $char->id) }}" method="POST"
                                          class="flex-1"
                                          data-name="{{ $char->name ?? 'this character' }}"
                                          onsubmit="return confirm('¿Eliminar «' + this.dataset.name + '»?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-full text-[10px] font-bold bg-red-500/20 hover:bg-red-500/40 text-red-300 rounded-lg py-1.5 transition-colors">
                                            Borrar
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </details>

            @empty
            <div class="text-center py-20">
                <p class="text-gray-500 italic">No characters found in the database</p>
            </div>
            @endforelse

            <div id="no-results" class="hidden text-center py-20">
                <svg class="w-12 h-12 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
                <p class="text-gray-500 italic">No characters match the current filter</p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        const searchInput  = document.getElementById('char-search');
        const noResults    = document.getElementById('no-results');
        const btnSelectAll = document.getElementById('btn-select-all');

        // ── Select all / deselect all toggle ─────────────────────────
        let allSelected = true;
        btnSelectAll.addEventListener('click', () => {
            allSelected = !allSelected;
            document.querySelectorAll('.media-filter').forEach(cb => { cb.checked = allSelected; });
            btnSelectAll.textContent = allSelected ? 'Todo' : 'Ninguno';
            applyFilter();
        });

        // ── Checkbox filter ───────────────────────────────────────────
        document.querySelectorAll('.media-filter').forEach(cb => {
            cb.addEventListener('change', () => {
                // Sync the "Todo/Ninguno" button label
                const all   = document.querySelectorAll('.media-filter');
                const checked = document.querySelectorAll('.media-filter:checked');
                allSelected = all.length === checked.length;
                btnSelectAll.textContent = allSelected ? 'Todo' : (checked.length === 0 ? 'Ninguno' : '···');
                applyFilter();
            });
        });

        // ── Search ────────────────────────────────────────────────────
        searchInput.addEventListener('input', applyFilter);
        searchInput.addEventListener('search', () => {
            if (!searchInput.value) applyFilter();
        });

        // ── Core filter logic ─────────────────────────────────────────
        function applyFilter() {
            const checkedIds = new Set(
                [...document.querySelectorAll('.media-filter:checked')].map(c => c.value)
            );
            const query      = searchInput.value.trim().toLowerCase();
            const noMedia    = checkedIds.size === 0;
            let   anyVisible = false;

            document.querySelectorAll('.franchise-section').forEach(franchiseEl => {
                let franchiseHasVisible = false;

                franchiseEl.querySelectorAll('.role-section').forEach(roleEl => {
                    let roleHasVisible = false;

                    roleEl.querySelectorAll('.char-card').forEach(card => {
                        const ids   = card.dataset.mediaIds.split(',').filter(Boolean);
                        const matchesMedia  = !noMedia && (ids.length === 0 || ids.some(id => checkedIds.has(id)));
                        const matchesSearch = !query || card.dataset.name.includes(query);
                        const visible = matchesMedia && matchesSearch;

                        card.style.display = visible ? '' : 'none';
                        if (visible) roleHasVisible = true;
                    });

                    roleEl.style.display = roleHasVisible ? '' : 'none';
                    if (roleHasVisible) franchiseHasVisible = true;
                });

                franchiseEl.style.display = franchiseHasVisible ? '' : 'none';
                if (franchiseHasVisible) anyVisible = true;
            });

            noResults.classList.toggle('hidden', anyVisible);
        }
    })();
    </script>
    @endpush

</x-layout>
