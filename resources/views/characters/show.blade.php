<x-layout>
    <x-slot:title>{{ $character->name }} — Character</x-slot>

    {{-- Blurred hero banner --}}
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
        <div class="flex flex-col sm:flex-row gap-8 mb-10 pt-10">

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

        {{-- ── Tabs ── --}}
        <div class="border-b border-gray-800 mb-8">
            <nav class="-mb-px flex gap-1">
                @php
                    $tabs = [
                        ['id' => 'media',      'label' => 'Media',      'count' => count($medias)],
                        ['id' => 'assets',     'label' => 'Assets',     'count' => count($assets)],
                        ['id' => 'characters', 'label' => 'Characters', 'count' => count($allCharacters)],
                    ];
                @endphp
                @foreach($tabs as $tab)
                <button data-tab="{{ $tab['id'] }}"
                        onclick="switchTab('{{ $tab['id'] }}')"
                        class="tab-btn flex items-center gap-2 px-5 py-3 text-sm font-semibold border-b-2 transition-all
                               {{ $loop->first ? 'border-amber-500 text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-300' }}">
                    {{ $tab['label'] }}
                    <span class="text-[10px] font-bold bg-gray-800 text-gray-500 rounded-full px-2 py-0.5 min-w-[1.5rem] text-center">
                        {{ $tab['count'] }}
                    </span>
                </button>
                @endforeach
            </nav>
        </div>

        {{-- ── Tab: Media ── --}}
        <div id="panel-media" class="tab-panel">
            @if(!empty($medias))
            <div class="space-y-3 max-w-2xl">
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
                        <h3 class="text-white font-bold text-sm line-clamp-2 leading-snug mb-1">{{ $m->title }}</h3>
                        <div class="flex items-center gap-2 flex-wrap">
                            @if(!empty($m->format))
                            <span class="text-[10px] font-bold bg-gray-800 text-gray-400 px-2 py-0.5 rounded">{{ $m->format }}</span>
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
            @else
            <p class="text-gray-600 text-sm italic py-12 text-center">No media linked to this character.</p>
            @endif
        </div>

        {{-- ── Tab: Assets ── --}}
        <div id="panel-assets" class="tab-panel hidden">
            @if(!empty($assets))
            @php
                $typeColors = [
                    'IMG'              => ['pill' => 'bg-sky-500/20 text-sky-300',     'ring' => 'group-hover:border-sky-500/40'],
                    'GIF'              => ['pill' => 'bg-violet-500/20 text-violet-300','ring' => 'group-hover:border-violet-500/40'],
                    'AMV'              => ['pill' => 'bg-rose-500/20 text-rose-300',    'ring' => 'group-hover:border-rose-500/40'],
                    'MUSIC'            => ['pill' => 'bg-emerald-500/20 text-emerald-300','ring'=> 'group-hover:border-emerald-500/40'],
                    'ANIME'            => ['pill' => 'bg-blue-500/20 text-blue-300',    'ring' => 'group-hover:border-blue-500/40'],
                    'MANGA'            => ['pill' => 'bg-orange-500/20 text-orange-300','ring' => 'group-hover:border-orange-500/40'],
                    'LIGHT NOVEL'      => ['pill' => 'bg-yellow-500/20 text-yellow-300','ring' => 'group-hover:border-yellow-500/40'],
                    'DOUJIN'           => ['pill' => 'bg-pink-500/20 text-pink-300',    'ring' => 'group-hover:border-pink-500/40'],
                    'WALLPAPER ENGINE' => ['pill' => 'bg-teal-500/20 text-teal-300',    'ring' => 'group-hover:border-teal-500/40'],
                ];
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                @foreach($assets as $asset)
                @php
                    $colors   = $typeColors[$asset->type] ?? ['pill' => 'bg-gray-700/60 text-gray-400', 'ring' => 'group-hover:border-gray-600'];
                    $isImage  = in_array($asset->type, ['IMG', 'GIF']) && $asset->fileUrl;
                    $hasCover = !empty($asset->coverUrl);
                @endphp
                <a href="{{ $asset->fileUrl }}" target="_blank"
                   class="group flex flex-col bg-[#151921] border border-gray-800 rounded-2xl overflow-hidden
                          hover:-translate-y-1 hover:shadow-xl transition-all duration-200 {{ $colors['ring'] }}">

                    {{-- Thumbnail --}}
                    <div class="aspect-[4/3] bg-gray-900 overflow-hidden relative">
                        @if($hasCover)
                            <img src="{{ $asset->coverUrl }}" alt="{{ $asset->title ?? $asset->type }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 loading="lazy">
                        @elseif($isImage)
                            <img src="{{ $asset->fileUrl }}" alt="{{ $asset->title ?? '' }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 loading="lazy">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center gap-2 text-gray-700">
                                @if($asset->type === 'MUSIC')
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                    </svg>
                                @elseif(in_array($asset->type, ['ANIME', 'AMV']))
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @else
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                @endif
                            </div>
                        @endif

                        {{-- Type badge overlay --}}
                        <span class="absolute top-2 left-2 text-[10px] font-bold px-2 py-0.5 rounded-lg {{ $colors['pill'] }}">
                            {{ $asset->type }}
                        </span>

                        {{-- External link icon --}}
                        <span class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-4 h-4 text-white drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </span>
                    </div>

                    {{-- Info --}}
                    <div class="p-3">
                        <p class="text-xs font-semibold text-gray-300 group-hover:text-white truncate transition-colors">
                            {{ $asset->title ?? 'Untitled' }}
                        </p>
                        @if($asset->storageName)
                        <p class="text-[10px] text-gray-600 mt-0.5 truncate">{{ $asset->storageName }}</p>
                        @elseif($asset->url)
                        <p class="text-[10px] text-gray-600 mt-0.5">External link</p>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <p class="text-gray-600 text-sm italic py-12 text-center">No assets linked to this character.</p>
            @endif
        </div>

        {{-- ── Tab: Characters ── --}}
        <div id="panel-characters" class="tab-panel hidden">
            @if(!empty($allCharacters))
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 xl:grid-cols-10 gap-3">
                @foreach($allCharacters as $char)
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
                </a>
                @endforeach
            </div>
            @else
            <p class="text-gray-600 text-sm italic py-12 text-center">No related characters found.</p>
            @endif
        </div>

    </div>

    @push('scripts')
    <script>
    function switchTab(name) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('border-amber-500', 'text-amber-400');
            b.classList.add('border-transparent', 'text-gray-500');
        });
        document.getElementById('panel-' + name).classList.remove('hidden');
        const btn = document.querySelector('[data-tab="' + name + '"]');
        btn.classList.remove('border-transparent', 'text-gray-500');
        btn.classList.add('border-amber-500', 'text-amber-400');
    }
    </script>
    @endpush

</x-layout>
