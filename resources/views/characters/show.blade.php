<x-layout>
    <x-slot:title>{{ $character->name }} — Character</x-slot>

    {{-- ── Blurred hero banner ── --}}
    <x-slot:banner>
        <div class="absolute top-0 inset-x-0 h-[420px] overflow-hidden pointer-events-none" style="z-index:0;">
            <div class="absolute inset-0 bg-cover bg-center scale-110"
                 style="background-image:url('{{ $character->image ?? '' }}');
                        filter:blur(24px);
                        opacity:0.35;
                        mask-image:linear-gradient(to bottom,black 40%,transparent 100%);
                        -webkit-mask-image:linear-gradient(to bottom,black 40%,transparent 100%);">
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#0f172a] via-[#0f172a]/75 to-transparent"></div>
        </div>
    </x-slot:banner>

    <div class="relative z-10">

        {{-- ── Hero: portrait + name ── --}}
        <div class="flex flex-col sm:flex-row gap-8 mb-14 pt-10">

            {{-- Portrait --}}
            <div class="w-44 shrink-0 mx-auto sm:mx-0">
                <div class="w-full aspect-[3/4] rounded-2xl overflow-hidden border border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.6)] bg-gray-900">
                    @if(!empty($character->image))
                        <img src="{{ $character->image }}" alt="{{ $character->name }}"
                             class="w-full h-full object-cover object-top">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-600">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Info --}}
            <div class="flex-1 flex flex-col justify-end pb-2">
                <p class="text-amber-400 text-xs font-bold uppercase tracking-widest mb-2">Character Profile</p>
                <h1 class="text-4xl sm:text-5xl font-extrabold text-white leading-tight mb-4">
                    {{ $character->name }}
                </h1>

                {{-- Media badges --}}
                @if(!empty($medias))
                <div class="flex flex-wrap gap-2 mb-5">
                    @foreach($medias as $m)
                    <a href="{{ route('media.show', $m->id) }}"
                       class="flex items-center gap-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-full pl-1 pr-3 py-1 transition-colors">
                        @if(!empty($m->coverImage))
                        <img src="{{ $m->coverImage }}" class="w-6 h-6 rounded-full object-cover flex-shrink-0">
                        @endif
                        <span class="text-xs font-semibold text-gray-300">{{ $m->title }}</span>
                        @if(!empty($m->format))
                        <span class="text-[10px] text-gray-600">{{ $m->format }}</span>
                        @endif
                    </a>
                    @endforeach
                </div>
                @endif

                {{-- Back link --}}
                <a href="{{ route('characters.index') }}"
                   class="self-start text-xs text-gray-500 hover:text-gray-300 transition-colors flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Characters
                </a>
            </div>
        </div>

        {{-- ── Body: media + assets ── --}}
        <div class="flex flex-col lg:flex-row gap-8">

            {{-- Left: Related media --}}
            <div class="flex-1 min-w-0">
                @if(!empty($medias))
                <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-3 border-b border-gray-800 pb-3">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 4v16M17 4v16M3 8h4m10 0h4M3 16h4m10 0h4"/>
                    </svg>
                    Related Media
                </h2>
                <div class="space-y-3">
                    @foreach($medias as $m)
                    <a href="{{ route('media.show', $m->id) }}"
                       class="flex gap-4 bg-[#151921] border border-gray-800 rounded-2xl overflow-hidden
                              hover:border-amber-500/30 hover:shadow-lg hover:shadow-amber-500/10 hover:-translate-y-0.5
                              transition-all duration-200 group">
                        <div class="w-20 shrink-0 aspect-[2/3] bg-gray-900 overflow-hidden">
                            @if(!empty($m->coverImage))
                            <img src="{{ $m->coverImage }}" alt="{{ $m->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @endif
                        </div>
                        <div class="flex flex-col justify-center py-3 pr-4 flex-1 min-w-0">
                            <h3 class="text-white font-bold text-sm line-clamp-2 leading-snug mb-1">
                                {{ $m->title }}
                            </h3>
                            <div class="flex items-center gap-2 flex-wrap">
                                @if(!empty($m->format))
                                <span class="text-[10px] font-bold bg-gray-800 text-gray-400 px-2 py-0.5 rounded">
                                    {{ $m->format }}
                                </span>
                                @endif
                                @if(!empty($m->startYear))
                                <span class="text-xs text-gray-600">{{ $m->startYear }}</span>
                                @endif
                                @if(!empty($m->status))
                                <span class="text-xs text-gray-600">· {{ $m->status }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
                @endif

                {{-- Session feedback --}}
                @if(session('success'))
                <div class="mt-6 bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 px-4 py-3 rounded-xl flex items-center gap-3 text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="mt-6 bg-red-500/10 border border-red-500/50 text-red-400 px-4 py-3 rounded-xl flex items-center gap-3 text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
                @endif
            </div>

            {{-- Right: Assets + Upload --}}
            <div class="w-full lg:w-80 shrink-0 space-y-4">

                {{-- Assets list --}}
                <div class="bg-[#151921] border border-gray-800 rounded-2xl p-5">
                    <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        Assets
                        <span class="ml-auto text-xs text-gray-600 font-normal">{{ count($assets) }}</span>
                    </h2>

                    @if(!empty($assets))
                    <div class="space-y-2">
                        @foreach($assets as $item)
                        @php
                            $isUrl = !empty($item['asset']['url']);
                            $href  = $isUrl
                                ? $item['asset']['url']
                                : asset('storage/assets/' . ($item['asset']['filename'] ?? ''));
                            $typeColors = [
                                'IMG'   => 'bg-blue-500/20 text-blue-300',
                                'GIF'   => 'bg-purple-500/20 text-purple-300',
                                'VIDEO' => 'bg-rose-500/20 text-rose-300',
                                'MUSIC' => 'bg-emerald-500/20 text-emerald-300',
                                'AMV'   => 'bg-orange-500/20 text-orange-300',
                            ];
                            $typeLabel = $item['asset']['type'] ?? ($isUrl ? 'URL' : 'FILE');
                            $typeClass = $typeColors[$typeLabel] ?? 'bg-gray-700/60 text-gray-400';
                        @endphp
                        <a href="{{ $href }}" target="_blank"
                           class="flex items-center gap-3 p-2.5 rounded-xl bg-white/3 hover:bg-white/8 border border-white/5 hover:border-amber-500/30 transition-all group">
                            <span class="text-[10px] font-bold px-2 py-1 rounded-lg flex-shrink-0 {{ $typeClass }}">
                                {{ $typeLabel }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-gray-300 truncate group-hover:text-white transition-colors">
                                    {{ $item['asset']['title'] ?? 'Untitled' }}
                                </p>
                                <p class="text-[10px] text-gray-600">
                                    {{ $isUrl ? 'External link' : ($item['storage']['name'] ?? 'Local') }}
                                </p>
                            </div>
                            <svg class="w-3.5 h-3.5 text-gray-600 group-hover:text-amber-400 transition-colors flex-shrink-0"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                        @endforeach
                    </div>
                    @else
                    <p class="text-gray-600 text-xs text-center py-4 italic">No assets linked yet</p>
                    @endif
                </div>

                {{-- Upload form --}}
                <div class="bg-[#151921] border border-gray-800 rounded-2xl p-5">
                    <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Upload Asset
                    </h2>

                    <form action="{{ route('characters.assets.store', $character->id) }}"
                          method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf

                        {{-- File / URL toggle --}}
                        <div class="flex gap-2 p-1 bg-gray-900/60 rounded-xl">
                            <button type="button" id="btn-file"
                                    onclick="setMode('file')"
                                    class="flex-1 py-1.5 rounded-lg text-xs font-bold transition-all bg-amber-500 text-white shadow">
                                Archivo
                            </button>
                            <button type="button" id="btn-url"
                                    onclick="setMode('url')"
                                    class="flex-1 py-1.5 rounded-lg text-xs font-bold transition-all text-gray-500 hover:text-gray-300">
                                Enlace
                            </button>
                        </div>

                        {{-- Asset type --}}
                        <select name="asset_type" required
                                class="w-full bg-black/40 border border-gray-700 text-white text-xs rounded-xl px-3 py-2.5 focus:ring-1 focus:ring-amber-500 focus:border-amber-500 outline-none transition-colors">
                            @foreach(['IMG','GIF','VIDEO','AMV','MUSIC','ANIME','MANGA','LIGHT NOVEL','DOUJIN','WALLPAPER ENGINE'] as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>

                        {{-- Title --}}
                        <input type="text" name="title" placeholder="Título (opcional)"
                               class="w-full bg-black/40 border border-gray-700 text-white text-xs rounded-xl px-3 py-2.5 placeholder-gray-600 focus:ring-1 focus:ring-amber-500 focus:border-amber-500 outline-none transition-colors">

                        {{-- File input --}}
                        <div id="input-file">
                            <input type="file" name="file"
                                   class="w-full text-xs text-gray-500 bg-black/30 border border-dashed border-gray-700 rounded-xl px-3 py-3 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-500/20 file:text-amber-300 cursor-pointer">
                        </div>

                        {{-- URL input --}}
                        <div id="input-url" class="hidden">
                            <input type="url" name="url" placeholder="https://..."
                                   class="w-full bg-black/40 border border-gray-700 text-white text-xs rounded-xl px-3 py-2.5 placeholder-gray-600 focus:ring-1 focus:ring-amber-500 focus:border-amber-500 outline-none transition-colors">
                        </div>

                        {{-- Tag other characters --}}
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-wider mb-2">
                                Etiquetar personajes
                            </label>
                            <input type="search" id="tag-search" placeholder="Buscar..."
                                   class="w-full bg-black/40 border border-gray-700 text-white text-xs rounded-xl px-3 py-2 placeholder-gray-600 focus:ring-1 focus:ring-amber-500 outline-none transition-colors mb-2">
                            <div class="max-h-40 overflow-y-auto space-y-0.5 rounded-xl bg-black/20 border border-gray-800 p-2"
                                 style="scrollbar-width:thin;scrollbar-color:#374151 transparent;">
                                @foreach($allCharacters as $char)
                                <label class="char-tag-label flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-white/5 cursor-pointer"
                                       data-name="{{ strtolower($char->name) }}">
                                    <input type="checkbox" name="other_characters[]" value="{{ $char->id }}"
                                           class="w-3 h-3 accent-amber-500 flex-shrink-0">
                                    <img src="{{ $char->image ?? '' }}" alt=""
                                         class="w-5 h-5 rounded-full object-cover border border-gray-700 flex-shrink-0">
                                    <span class="text-xs text-gray-400 truncate flex-1">{{ $char->name }}</span>
                                    @if($char->priority)
                                    <span class="text-[9px] font-black text-amber-500/80 flex-shrink-0">★</span>
                                    @endif
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit"
                                class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm py-2.5 rounded-xl transition-colors shadow-lg shadow-amber-500/20">
                            Guardar Asset
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Related characters ── --}}
        @if(!empty($allCharacters))
        <div class="mt-16">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-3 border-b border-gray-800 pb-4">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Related Characters
                <span class="text-xs text-gray-600 font-normal ml-auto">{{ count($allCharacters) }} total</span>
            </h2>

            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 xl:grid-cols-10 gap-3">
                @foreach(array_slice($allCharacters, 0, 40) as $char)
                <a href="{{ route('characters.show', $char->id) }}"
                   class="flex flex-col items-center text-center group hover:-translate-y-1 transition-transform duration-200">
                    <div class="w-full aspect-square rounded-xl overflow-hidden border border-gray-800 group-hover:border-amber-500/50 transition-colors mb-1.5 bg-gray-900">
                        @if(!empty($char->image))
                        <img src="{{ $char->image }}" alt="{{ $char->name }}"
                             class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-300"
                             loading="lazy">
                        @else
                        <div class="w-full h-full flex items-center justify-center text-gray-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        @endif
                    </div>
                    <p class="text-[10px] font-semibold text-gray-400 group-hover:text-white line-clamp-2 leading-tight transition-colors w-full">
                        {{ $char->name }}
                    </p>
                    @if($char->priority)
                    <span class="text-[8px] font-black text-amber-500/70 mt-0.5">★ FRANCHISE</span>
                    @endif
                </a>
                @endforeach
            </div>

            @if(count($allCharacters) > 40)
            <p class="text-center text-xs text-gray-600 mt-6">
                + {{ count($allCharacters) - 40 }} more characters
            </p>
            @endif
        </div>
        @endif

    </div>

    @push('scripts')
    <script>
    // File / URL mode toggle
    function setMode(mode) {
        const fileEl   = document.getElementById('input-file');
        const urlEl    = document.getElementById('input-url');
        const btnFile  = document.getElementById('btn-file');
        const btnUrl   = document.getElementById('btn-url');
        const isFile   = mode === 'file';

        fileEl.classList.toggle('hidden', !isFile);
        urlEl.classList.toggle('hidden', isFile);

        const active   = 'bg-amber-500 text-white shadow';
        const inactive = 'text-gray-500 hover:text-gray-300';

        btnFile.className = `flex-1 py-1.5 rounded-lg text-xs font-bold transition-all ${isFile ? active : inactive}`;
        btnUrl.className  = `flex-1 py-1.5 rounded-lg text-xs font-bold transition-all ${!isFile ? active : inactive}`;
    }

    // Character tag search
    document.getElementById('tag-search').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.char-tag-label').forEach(label => {
            label.style.display = !q || label.dataset.name.includes(q) ? '' : 'none';
        });
    });
    </script>
    @endpush

</x-layout>
