<x-layout>
    <x-slot:title>Characters</x-slot>

    @php
    $roleColors = [
        'MAIN'       => 'bg-amber-500/20 text-amber-300 border-amber-500/40',
        'SUPPORTING' => 'bg-blue-500/20 text-blue-300 border-blue-500/40',
        'BACKGROUND' => 'bg-gray-500/20 text-gray-400 border-gray-500/40',
        'UNKNOWN'    => 'bg-gray-500/20 text-gray-400 border-gray-500/40',
    ];
    $defaultRoleColor = 'bg-gray-500/20 text-gray-400 border-gray-500/40';
    @endphp

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Characters</h1>
            <p class="text-gray-400 mt-1 text-sm">Agrupados por franquicia y rol</p>
        </div>
    </div>

    <div class="flex gap-6 items-start">

        {{-- ── Sidebar ── --}}
        <aside class="w-56 flex-shrink-0 sticky top-24 self-start max-h-[calc(100vh-8rem)] flex flex-col gap-2 overflow-hidden">

            {{-- Search --}}
            <div class="relative">
                <svg class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
                <input id="char-search" type="search" placeholder="Buscar personaje..."
                       autocomplete="off"
                       class="w-full bg-[#151921] border border-gray-700 focus:border-amber-500 text-white placeholder-gray-600 text-sm rounded-xl pl-9 pr-4 py-2.5 outline-none transition-colors">
            </div>

            {{-- Tab switcher --}}
            <div class="flex rounded-xl bg-black/40 border border-gray-800 p-0.5 gap-0.5">
                <button id="tab-btn-media" type="button" onclick="switchTab('media')"
                        class="relative flex-1 py-1.5 text-xs font-bold rounded-lg transition-all
                               bg-[#151921] text-white shadow-sm">
                    Media
                    <span id="tab-dot-media" class="hidden absolute top-1 right-1.5 w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                </button>
                <button id="tab-btn-tags" type="button" onclick="switchTab('tags')"
                        class="relative flex-1 py-1.5 text-xs font-bold rounded-lg transition-all
                               text-gray-500 hover:text-gray-300">
                    Tags
                    <span id="tab-dot-tags" class="hidden absolute top-1 right-1.5 w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                </button>
            </div>

            {{-- ── Panel: Media ── --}}
            <div id="panel-media" class="flex flex-col gap-2 flex-1 min-h-0">
                <div class="flex items-center justify-between px-1">
                    <span class="text-[10px] font-bold text-gray-600 uppercase tracking-wider">Franquicia · Media</span>
                    <button id="btn-select-all" type="button"
                            class="text-[10px] text-indigo-400 hover:text-indigo-300 transition-colors font-semibold">
                        Todo
                    </button>
                </div>
                <div class="overflow-y-auto flex-1 space-y-1 pr-0.5"
                     style="scrollbar-color:#374151 transparent; scrollbar-width:thin;">
                    @foreach($franchiseMedia as $franchise => $mediaItems)
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
                                       class="media-filter w-3 h-3 rounded flex-shrink-0 cursor-pointer accent-amber-500"
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
            </div>

            {{-- ── Panel: Tags ── --}}
            <div id="panel-tags" class="hidden flex-col gap-2 flex-1 min-h-0">
                <div class="flex items-center justify-between px-1 gap-2">
                    <span class="text-[10px] font-bold text-gray-600 uppercase tracking-wider flex-shrink-0">Tags</span>
                    <div class="flex items-center gap-1.5 ml-auto">
                        {{-- AND / OR toggle --}}
                        <div class="flex rounded-lg bg-black/50 border border-gray-800 p-0.5 gap-0.5">
                            <button id="tag-logic-or" type="button" onclick="setTagLogic('or')"
                                    class="px-1.5 py-0.5 rounded text-[9px] font-bold transition-all
                                           bg-[#151921] text-gray-200 shadow-sm">
                                Alguna
                            </button>
                            <button id="tag-logic-and" type="button" onclick="setTagLogic('and')"
                                    class="px-1.5 py-0.5 rounded text-[9px] font-bold transition-all
                                           text-gray-600 hover:text-gray-400">
                                Todas
                            </button>
                        </div>
                        <button id="btn-clear-tags" type="button"
                                class="text-[10px] text-indigo-400 hover:text-indigo-300 transition-colors font-semibold hidden">
                            Limpiar
                        </button>
                    </div>
                </div>
                <div class="overflow-y-auto flex-1 space-y-2 pr-0.5"
                     style="scrollbar-color:#374151 transparent; scrollbar-width:thin;">
                    @if(!empty($allTags))
                        @foreach($allTags as $category => $tagList)
                        <details class="group/tg" open>
                            <summary class="flex items-center gap-2 px-2 py-1.5 rounded-lg cursor-pointer select-none list-none
                                            text-gray-400 hover:text-white hover:bg-white/5 transition-colors">
                                <svg class="w-3 h-3 flex-shrink-0 transition-transform duration-150 group-open/tg:rotate-90"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                                <span class="text-xs font-semibold truncate flex-1">{{ $category }}</span>
                            </summary>
                            <div class="mt-1 ml-2 pl-3 border-l border-gray-800 flex flex-wrap gap-1 pb-1">
                                @foreach($tagList as $tag)
                                <button type="button"
                                        data-tag-id="{{ $tag['id'] }}"
                                        class="tag-filter px-2 py-0.5 rounded-full text-[10px] font-semibold
                                               border border-gray-700 text-gray-500 transition-all cursor-pointer
                                               hover:border-indigo-500/50 hover:text-indigo-300">
                                    {{ $tag['name'] }}
                                </button>
                                @endforeach
                            </div>
                        </details>
                        @endforeach
                    @else
                        <p class="text-gray-600 text-xs px-2 py-4 text-center">No hay tags creados todavía.</p>
                    @endif
                </div>
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
                    <span class="text-xs font-semibold bg-amber-500/15 text-amber-300 border border-amber-500/30 px-2.5 py-0.5 rounded-full">
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
                            <a href="{{ route('characters.show', $char->id) }}"
                               class="char-card bg-[#151921] border border-gray-800 rounded-xl overflow-hidden
                                      hover:border-amber-500/50 hover:shadow-lg hover:shadow-amber-500/10
                                      hover:-translate-y-1 transition-all duration-200 flex flex-col"
                               data-name="{{ strtolower($char->name ?? '') }}"
                               data-media-ids="{{ implode(',', $char->mediaIds ?? []) }}"
                               data-tag-ids="{{ implode(',', $char->tagIds ?? []) }}">

                                {{-- Portrait --}}
                                <div class="aspect-[3/4] bg-gray-900 relative overflow-hidden flex-shrink-0">
                                    @if(!empty($char->image))
                                        <img src="{{ $char->image }}"
                                             alt="{{ $char->name }}"
                                             class="w-full h-full object-cover object-top hover:scale-105 transition-transform duration-300"
                                             loading="lazy">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-700">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    @endif

                                    {{-- Role badge --}}
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
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </details>

            @empty
            <div class="text-center py-20">
                <svg class="w-12 h-12 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <p class="text-gray-500 italic">No characters found</p>
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
    // ── Tab switching ─────────────────────────────────────────────────────────
    function switchTab(tab) {
        const isMedia = tab === 'media';

        const btnMedia  = document.getElementById('tab-btn-media');
        const btnTags   = document.getElementById('tab-btn-tags');
        const panMedia  = document.getElementById('panel-media');
        const panTags   = document.getElementById('panel-tags');

        btnMedia.className = `relative flex-1 py-1.5 text-xs font-bold rounded-lg transition-all ${
            isMedia ? 'bg-[#151921] text-white shadow-sm' : 'text-gray-500 hover:text-gray-300'
        }`;
        btnTags.className = `relative flex-1 py-1.5 text-xs font-bold rounded-lg transition-all ${
            !isMedia ? 'bg-[#151921] text-white shadow-sm' : 'text-gray-500 hover:text-gray-300'
        }`;

        panMedia.classList.toggle('hidden', !isMedia);
        panMedia.classList.toggle('flex', isMedia);
        panTags.classList.toggle('hidden', isMedia);
        panTags.classList.toggle('flex', !isMedia);
    }

    (function () {
        const searchInput  = document.getElementById('char-search');
        const noResults    = document.getElementById('no-results');
        const btnSelectAll = document.getElementById('btn-select-all');
        const btnClearTags = document.getElementById('btn-clear-tags');

        let tagLogic = 'or';

        window.setTagLogic = function (logic) {
            tagLogic = logic;
            const on  = 'px-1.5 py-0.5 rounded text-[9px] font-bold transition-all bg-[#151921] text-gray-200 shadow-sm';
            const off = 'px-1.5 py-0.5 rounded text-[9px] font-bold transition-all text-gray-600 hover:text-gray-400';
            document.getElementById('tag-logic-or').className  = logic === 'or'  ? on : off;
            document.getElementById('tag-logic-and').className = logic === 'and' ? on : off;
            applyFilter();
        };

        let allSelected = true;

        btnSelectAll.addEventListener('click', () => {
            allSelected = !allSelected;
            document.querySelectorAll('.media-filter').forEach(cb => { cb.checked = allSelected; });
            btnSelectAll.textContent = allSelected ? 'Todo' : 'Ninguno';
            applyFilter();
        });

        document.querySelectorAll('.media-filter').forEach(cb => {
            cb.addEventListener('change', () => {
                const all     = document.querySelectorAll('.media-filter');
                const checked = document.querySelectorAll('.media-filter:checked');
                allSelected = all.length === checked.length;
                btnSelectAll.textContent = allSelected ? 'Todo' : (checked.length === 0 ? 'Ninguno' : '···');
                // Show dot on Media tab when not all selected
                document.getElementById('tab-dot-media')?.classList.toggle('hidden', allSelected);
                applyFilter();
            });
        });

        document.querySelectorAll('.tag-filter').forEach(btn => {
            btn.addEventListener('click', () => {
                btn.classList.toggle('active');
                const isActive = btn.classList.contains('active');
                btn.classList.toggle('border-indigo-500', isActive);
                btn.classList.toggle('text-indigo-300', isActive);
                btn.classList.toggle('bg-indigo-500/10', isActive);
                btn.classList.toggle('border-gray-700', !isActive);
                btn.classList.toggle('text-gray-500', !isActive);
                const anyActive = document.querySelectorAll('.tag-filter.active').length > 0;
                btnClearTags?.classList.toggle('hidden', !anyActive);
                // Show dot on Tags tab when any tag active
                document.getElementById('tab-dot-tags')?.classList.toggle('hidden', !anyActive);
                applyFilter();
            });
        });

        btnClearTags?.addEventListener('click', () => {
            document.querySelectorAll('.tag-filter.active').forEach(btn => {
                btn.classList.remove('active', 'border-indigo-500', 'text-indigo-300', 'bg-indigo-500/10');
                btn.classList.add('border-gray-700', 'text-gray-500');
            });
            btnClearTags.classList.add('hidden');
            document.getElementById('tab-dot-tags')?.classList.add('hidden');
            applyFilter();
        });

        searchInput.addEventListener('input', applyFilter);
        searchInput.addEventListener('search', () => { if (!searchInput.value) applyFilter(); });

        function applyFilter() {
            const checkedIds = new Set(
                [...document.querySelectorAll('.media-filter:checked')].map(c => c.value)
            );
            const selectedTags = new Set(
                [...document.querySelectorAll('.tag-filter.active')].map(b => b.dataset.tagId)
            );
            const query   = searchInput.value.trim().toLowerCase();
            const noMedia = checkedIds.size === 0;
            let anyVisible = false;

            document.querySelectorAll('.franchise-section').forEach(franchiseEl => {
                let franchiseHasVisible = false;

                franchiseEl.querySelectorAll('.role-section').forEach(roleEl => {
                    let roleHasVisible = false;

                    roleEl.querySelectorAll('.char-card').forEach(card => {
                        const mediaIds     = card.dataset.mediaIds.split(',').filter(Boolean);
                        const tagIds       = card.dataset.tagIds ? card.dataset.tagIds.split(',').filter(Boolean) : [];
                        const matchesMedia  = !noMedia && (mediaIds.length === 0 || mediaIds.some(id => checkedIds.has(id)));
                        const matchesSearch = !query || card.dataset.name.includes(query);
                        const matchesTags   = selectedTags.size === 0 || (
                            tagLogic === 'and'
                                ? [...selectedTags].every(tid => tagIds.includes(tid))
                                : tagIds.some(tid => selectedTags.has(tid))
                        );
                        const visible = matchesMedia && matchesSearch && matchesTags;
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
    })(); // end filter IIFE
    </script>
    @endpush

</x-layout>
