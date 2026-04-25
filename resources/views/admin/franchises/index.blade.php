<x-layout>
    <x-slot:title>Franchises Backoffice</x-slot>

    @php
    $avatarColors = [
        'bg-indigo-600', 'bg-purple-600', 'bg-pink-600',  'bg-rose-600',
        'bg-orange-500', 'bg-amber-500',  'bg-emerald-600','bg-teal-600',
        'bg-cyan-600',   'bg-blue-600',   'bg-violet-600', 'bg-fuchsia-600',
    ];
    @endphp

    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-center gap-4 justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Franchises</h1>
            <p class="text-gray-400 mt-1 text-sm">{{ count($franchises) }} franchises in the graph</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="text-indigo-400 hover:text-indigo-300 font-bold text-sm">
                &larr; Dashboard
            </a>
            <a href="{{ route('admin.franchises.create') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl font-bold text-sm transition-colors shadow-lg shadow-indigo-500/20">
                + Create Franchise
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

    @if(session('error'))
        <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-400 px-5 py-4 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Search --}}
    <div class="relative mb-6 max-w-sm">
        <svg class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
        </svg>
        <input id="franchise-search" type="search" placeholder="Buscar franquicia..."
               autocomplete="off"
               class="w-full bg-[#151921] border border-gray-700 focus:border-indigo-500 text-white placeholder-gray-600 text-sm rounded-xl pl-9 pr-4 py-2.5 outline-none transition-colors">
    </div>

    {{-- Grid --}}
    @if(count($franchises) > 0)
    <div id="franchise-grid"
         class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">

        @foreach($franchises as $f)
        @php
            $initial     = mb_strtoupper(mb_substr($f['name'], 0, 1));
            $colorClass  = $avatarColors[ord($initial) % count($avatarColors)];
        @endphp

        <div class="franchise-card bg-[#151921] border border-gray-800 rounded-2xl p-4
                    hover:border-indigo-500/40 hover:shadow-lg hover:shadow-indigo-500/10
                    transition-all duration-200 group flex flex-col gap-3"
             data-name="{{ strtolower($f['name']) }}">

            {{-- Top: avatar + name --}}
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl {{ $colorClass }} flex items-center justify-center
                            text-white font-extrabold text-lg flex-shrink-0 shadow-md">
                    {{ $initial }}
                </div>
                <span class="text-white font-bold text-sm leading-snug line-clamp-2">
                    {{ $f['name'] }}
                </span>
            </div>

            {{-- Stats --}}
            <div class="flex items-center gap-2 flex-wrap">
                <span class="flex items-center gap-1 text-xs text-gray-500 bg-gray-800/60 px-2 py-1 rounded-lg">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 4v16M17 4v16M3 8h4m10 0h4M3 16h4m10 0h4"/>
                    </svg>
                    {{ $f['mediaCount'] }} media
                </span>
                <span class="flex items-center gap-1 text-xs text-gray-500 bg-gray-800/60 px-2 py-1 rounded-lg">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    {{ $f['characterCount'] }} chars
                </span>
            </div>

            {{-- Actions (visible on hover) --}}
            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-150 flex gap-2 mt-auto pt-1 border-t border-gray-800">
                <a href="{{ route('admin.franchises.edit', $f['name']) }}"
                   class="flex-1 text-center text-xs font-bold bg-indigo-500/20 hover:bg-indigo-500/40 text-indigo-300 rounded-lg py-1.5 transition-colors">
                    Editar
                </a>
                <form action="{{ route('admin.franchises.destroy', $f['name']) }}" method="POST"
                      class="flex-1"
                      data-name="{{ $f['name'] }}"
                      onsubmit="return confirm('¿Eliminar la franquicia «' + this.dataset.name + '»? Se eliminarán todas sus relaciones.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full text-xs font-bold bg-red-500/20 hover:bg-red-500/40 text-red-300 rounded-lg py-1.5 transition-colors">
                        Borrar
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <div id="no-results" class="hidden text-center py-20">
        <svg class="w-12 h-12 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
        </svg>
        <p class="text-gray-500 italic">No franchise matches your search</p>
    </div>

    @else
    <div class="text-center py-20">
        <svg class="w-12 h-12 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        <p class="text-gray-500 italic">No franchises found. Create one to get started.</p>
    </div>
    @endif

    @push('scripts')
    <script>
    (function () {
        const input     = document.getElementById('franchise-search');
        const noResults = document.getElementById('no-results');
        if (!input) return;

        input.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            let any = false;

            document.querySelectorAll('.franchise-card').forEach(card => {
                const match = !q || card.dataset.name.includes(q);
                card.style.display = match ? '' : 'none';
                if (match) any = true;
            });

            if (noResults) noResults.classList.toggle('hidden', any || !q);
        });

        input.addEventListener('search', function () {
            if (!this.value) this.dispatchEvent(new Event('input'));
        });
    })();
    </script>
    @endpush

</x-layout>
