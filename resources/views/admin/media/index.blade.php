<x-layout>
    <x-slot:title>Media Backoffice</x-slot>

    @php
    $formatColors = [
        'TV'               => 'bg-blue-500/20 text-blue-300 border-blue-500/40',
        'TV_SHORT'         => 'bg-sky-500/20 text-sky-300 border-sky-500/40',
        'MOVIE'            => 'bg-purple-500/20 text-purple-300 border-purple-500/40',
        'SPECIAL'          => 'bg-pink-500/20 text-pink-300 border-pink-500/40',
        'OVA'              => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
        'ONA'              => 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40',
        'MUSIC'            => 'bg-rose-500/20 text-rose-300 border-rose-500/40',
        'MANGA'            => 'bg-amber-500/20 text-amber-300 border-amber-500/40',
        'NOVEL'            => 'bg-orange-500/20 text-orange-300 border-orange-500/40',
        'ONE_SHOT'         => 'bg-teal-500/20 text-teal-300 border-teal-500/40',
    ];
    $defaultColor = 'bg-gray-500/20 text-gray-400 border-gray-500/40';
    @endphp

    {{-- Page header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Media Elements</h1>
            <p class="text-gray-400 mt-1 text-sm">Agrupados por franquicia y formato</p>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.dashboard') }}" class="text-indigo-400 hover:text-indigo-300 font-bold text-sm">
                &larr; Dashboard
            </a>
            <a href="{{ route('admin.media.create') }}" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl font-bold text-sm transition-colors shadow-lg shadow-amber-500/20">
                + Add Media
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
        <aside class="w-52 flex-shrink-0 sticky top-24 self-start max-h-[calc(100vh-8rem)] flex flex-col gap-3 overflow-hidden">

            {{-- Search bar --}}
            <div class="relative">
                <svg class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
                <input
                    id="media-search"
                    type="search"
                    placeholder="Buscar media..."
                    autocomplete="off"
                    class="w-full bg-[#151921] border border-gray-700 focus:border-indigo-500 text-white placeholder-gray-600 text-sm rounded-xl pl-9 pr-4 py-2.5 outline-none transition-colors"
                >
            </div>

            {{-- Franchise nav --}}
            <div class="overflow-y-auto flex-1 pr-1 space-y-0.5 scrollbar-thin"
                 style="scrollbar-color:#374151 transparent; scrollbar-width:thin;">
                @foreach($grouped as $franchise => $formats)
                @php
                    $slug  = Str::slug($franchise);
                    $total = collect($formats)->sum(fn($items) => count($items));
                @endphp
                <a href="#franchise-{{ $slug }}"
                   data-target="franchise-{{ $slug }}"
                   class="sidebar-link flex items-center justify-between px-3 py-2 rounded-lg text-sm text-gray-400 hover:text-white hover:bg-white/5 transition-colors">
                    <span class="truncate leading-tight">{{ $franchise }}</span>
                    <span class="text-xs text-gray-600 ml-2 flex-shrink-0 tabular-nums">{{ $total }}</span>
                </a>
                @endforeach
            </div>
        </aside>

        {{-- ── Main content ── --}}
        <div id="media-content" class="flex-1 min-w-0 space-y-6">

            @forelse($grouped as $franchise => $formats)
            @php
                $slug  = Str::slug($franchise);
                $total = collect($formats)->sum(fn($items) => count($items));
            @endphp

            <details id="franchise-{{ $slug }}"
                     class="franchise-section bg-[#11151d] border border-gray-800 rounded-2xl overflow-hidden group/fr"
                     open>

                {{-- Franchise header --}}
                <summary class="flex items-center gap-3 cursor-pointer select-none list-none px-5 py-4 hover:bg-white/3 transition-colors">
                    <svg class="w-4 h-4 text-gray-500 transition-transform duration-200 group-open/fr:rotate-90 flex-shrink-0"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="text-lg font-bold text-white">{{ $franchise }}</span>
                    <span class="text-xs font-semibold bg-indigo-500/15 text-indigo-300 border border-indigo-500/30 px-2.5 py-0.5 rounded-full">
                        {{ $total }} {{ $total === 1 ? 'entry' : 'entries' }}
                    </span>
                </summary>

                {{-- Format sections --}}
                <div class="px-5 pb-5 space-y-6 border-t border-gray-800/60 pt-5">
                    @foreach($formats as $format => $items)
                    @php $badgeClasses = $formatColors[$format] ?? $defaultColor; @endphp

                    <div class="format-section">
                        {{-- Format label --}}
                        <div class="flex items-center gap-3 mb-3">
                            <span class="text-xs font-bold border px-3 py-1 rounded-full {{ $badgeClasses }}">
                                {{ $format }}
                            </span>
                            <span class="text-gray-700 text-xs">{{ count($items) }} items</span>
                            <div class="flex-1 h-px bg-gray-800/70"></div>
                        </div>

                        {{-- Cards grid --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                            @foreach($items as $media)
                            <div class="media-card bg-[#151921] border border-gray-800 rounded-xl overflow-hidden
                                        hover:border-indigo-500/40 hover:shadow-lg hover:shadow-indigo-500/10
                                        transition-all duration-200 group/card flex flex-col"
                                 data-title="{{ strtolower($media['title'] ?? '') }}">

                                {{-- Cover --}}
                                <div class="aspect-[2/3] bg-gray-900 relative overflow-hidden flex-shrink-0">
                                    @if(!empty($media['coverImage']))
                                        <img src="{{ $media['coverImage'] }}"
                                             alt="{{ $media['title'] }}"
                                             class="w-full h-full object-cover group-hover/card:scale-105 transition-transform duration-300"
                                             loading="lazy">
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center text-gray-700 gap-2">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif

                                    {{-- Format badge --}}
                                    <div class="absolute top-1.5 left-1.5">
                                        <span class="text-[10px] font-bold border px-1.5 py-0.5 rounded backdrop-blur-sm {{ $badgeClasses }}">
                                            {{ $format }}
                                        </span>
                                    </div>

                                    {{-- Status dot --}}
                                    @if(!empty($media['status']))
                                    @php $dotColor = match($media['status']) {
                                        'RELEASING'         => 'bg-green-400',
                                        'FINISHED'          => 'bg-gray-500',
                                        'NOT_YET_RELEASED'  => 'bg-yellow-400',
                                        'CANCELLED'         => 'bg-red-500',
                                        default             => 'bg-gray-600',
                                    }; @endphp
                                    <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full {{ $dotColor }} ring-1 ring-black/60"
                                          title="{{ $media['status'] }}"></span>
                                    @endif
                                </div>

                                {{-- Info --}}
                                <div class="p-2.5 flex flex-col gap-0.5">
                                    <p class="text-white text-xs font-semibold line-clamp-2 leading-snug">
                                        {{ $media['title'] ?? 'Unknown' }}
                                    </p>
                                    @if(!empty($media['start_year']))
                                    <span class="text-gray-600 text-[10px]">{{ $media['start_year'] }}</span>
                                    @endif
                                </div>

                                {{-- Actions (visible on hover) --}}
                                <div class="opacity-0 group-hover/card:opacity-100 transition-opacity duration-150 px-2 pb-2.5 flex gap-1.5">
                                    <a href="{{ route('admin.media.edit', $media['id']) }}"
                                       class="flex-1 text-center text-[10px] font-bold bg-indigo-500/20 hover:bg-indigo-500/40 text-indigo-300 rounded-lg py-1.5 transition-colors">
                                        Editar
                                    </a>
                                    <form action="{{ route('admin.media.destroy', $media['id']) }}" method="POST"
                                          class="flex-1"
                                          data-title="{{ $media['title'] ?? 'this media' }}"
                                          onsubmit="return confirm('¿Eliminar «' + this.dataset.title + '»?')">
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
            <div class="text-center py-20 col-span-full">
                <svg class="w-12 h-12 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M7 4v16M17 4v16M3 8h4m10 0h4M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                </svg>
                <p class="text-gray-500 italic">No media found in the database</p>
            </div>
            @endforelse

            {{-- Empty state when search has no results --}}
            <div id="no-results" class="hidden text-center py-20">
                <svg class="w-12 h-12 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
                <p class="text-gray-500 italic">No media matches your search</p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        const searchInput   = document.getElementById('media-search');
        const noResults     = document.getElementById('no-results');
        const sidebarLinks  = document.querySelectorAll('.sidebar-link');

        // ── Sidebar smooth scroll ──────────────────────────────────────
        sidebarLinks.forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                const target = document.getElementById(link.dataset.target);
                if (!target) return;
                if (target.tagName === 'DETAILS') target.open = true;
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                setActiveLink(link);
            });
        });

        function setActiveLink(active) {
            sidebarLinks.forEach(l => l.classList.remove('text-white', 'bg-white/8'));
            active.classList.add('text-white', 'bg-white/8');
        }

        // ── Highlight sidebar link on scroll ──────────────────────────
        const franchiseSections = document.querySelectorAll('.franchise-section');
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const link = document.querySelector(`.sidebar-link[data-target="${entry.target.id}"]`);
                    if (link) setActiveLink(link);
                }
            });
        }, { rootMargin: '-20% 0px -70% 0px' });

        franchiseSections.forEach(s => observer.observe(s));

        // ── Search / filter ───────────────────────────────────────────
        searchInput.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();

            let anyVisible = false;

            franchiseSections.forEach(franchiseEl => {
                let franchiseHasVisible = false;

                franchiseEl.querySelectorAll('.format-section').forEach(formatEl => {
                    let formatHasVisible = false;

                    formatEl.querySelectorAll('.media-card').forEach(card => {
                        const match = !query || card.dataset.title.includes(query);
                        card.style.display = match ? '' : 'none';
                        if (match) formatHasVisible = true;
                    });

                    formatEl.style.display = formatHasVisible ? '' : 'none';
                    if (formatHasVisible) franchiseHasVisible = true;
                });

                franchiseEl.style.display = franchiseHasVisible ? '' : 'none';
                if (franchiseHasVisible) anyVisible = true;

                // Dim sidebar link when franchise is hidden
                const link = document.querySelector(`.sidebar-link[data-target="${franchiseEl.id}"]`);
                if (link) link.style.opacity = franchiseHasVisible ? '' : '0.3';
            });

            noResults.classList.toggle('hidden', anyVisible || !query);
        });

        // Clear active style on search clear
        searchInput.addEventListener('search', function () {
            if (!this.value) this.dispatchEvent(new Event('input'));
        });
    })();
    </script>
    @endpush

</x-layout>
