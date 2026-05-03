<x-admin-layout>
    <x-slot:title>Tags - Backoffice</x-slot>

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Tags</h1>
            <p class="text-gray-400 mt-1 text-sm">Etiquetas para personajes, media y assets</p>
        </div>
        <a href="{{ route('admin.tags.create') }}"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl transition-colors shadow-lg shadow-indigo-500/20 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo tag
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-emerald-500/10 border border-emerald-500/40 text-emerald-400 px-5 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    @php
        $typeLabels = ['character' => 'Personajes', 'media' => 'Media', 'asset' => 'Assets'];
        $typeColors = [
            'character' => 'text-amber-300 bg-amber-500/10 border-amber-500/30',
            'media'     => 'text-blue-300 bg-blue-500/10 border-blue-500/30',
            'asset'     => 'text-violet-300 bg-violet-500/10 border-violet-500/30',
        ];
    @endphp

    @if(empty($tags))
        <div class="text-center py-20 text-gray-600">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            <p class="text-lg">No hay tags todavía.</p>
            <a href="{{ route('admin.tags.create') }}" class="mt-3 inline-block text-indigo-400 hover:text-indigo-300 text-sm font-semibold">
                Crear el primero →
            </a>
        </div>
    @else
        @foreach($tags as $type => $categories)
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-xs font-bold border px-3 py-1 rounded-full {{ $typeColors[$type] ?? 'text-gray-400 bg-gray-500/10 border-gray-500/30' }}">
                    {{ $typeLabels[$type] ?? strtoupper($type) }}
                </span>
                <div class="flex-1 h-px bg-gray-800"></div>
            </div>

            @foreach($categories as $category => $tagList)
            <div class="mb-4 bg-[#151921] border border-gray-800 rounded-2xl overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-800/60 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-300">{{ $category }}</span>
                    <span class="text-xs text-gray-600">{{ count($tagList) }} {{ count($tagList) === 1 ? 'tag' : 'tags' }}</span>
                </div>
                <div class="divide-y divide-gray-800/60">
                    @foreach($tagList as $tag)
                    <div class="flex items-center justify-between px-5 py-3 hover:bg-white/2 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="text-white text-sm font-medium">{{ $tag->name }}</span>
                            <span class="text-gray-600 text-xs font-mono">{{ $tag->slug }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.tags.edit', $tag->id) }}"
                               class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold px-3 py-1.5 rounded-lg hover:bg-indigo-500/10 transition-all">
                                Editar
                            </a>
                            <form action="{{ route('admin.tags.destroy', $tag->id) }}" method="POST"
                                  onsubmit="return confirm('¿Eliminar tag «{{ $tag->name }}»? Se eliminará de todos los personajes.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-xs text-red-500 hover:text-red-400 font-semibold px-3 py-1.5 rounded-lg hover:bg-red-500/10 transition-all">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endforeach
    @endif

</x-admin-layout>
