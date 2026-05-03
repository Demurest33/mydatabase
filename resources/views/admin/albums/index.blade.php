<x-admin-layout>
    <x-slot:title>Albums - Backoffice</x-slot>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Albums</h1>
            <p class="text-gray-400 mt-1 text-sm">Álbumes de personajes por media</p>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.dashboard') }}" class="text-indigo-400 hover:text-indigo-300 font-bold text-sm">
                &larr; Dashboard
            </a>
            <a href="{{ route('admin.albums.create') }}"
               class="bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-xl font-bold text-sm transition-colors shadow-lg shadow-violet-500/20">
                + New Album
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 px-5 py-4 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(count($albums) === 0)
        <div class="text-center py-24 text-gray-600">
            <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <p class="text-lg">No hay álbumes todavía.</p>
            <a href="{{ route('admin.albums.create') }}" class="mt-4 inline-block text-violet-400 hover:text-violet-300 font-semibold">Crear el primero →</a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($albums as $album)
                <div class="bg-[#151921] border border-gray-800 rounded-2xl overflow-hidden hover:border-violet-500/50 transition-colors group">
                    {{-- Cover --}}
                    <div class="h-40 bg-black/40 relative">
                        @if($album->coverImage)
                            <img src="{{ $album->coverImage }}" alt="{{ $album->name }}"
                                 class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                        @endif
                        @if(!$album->isActive)
                            <span class="absolute top-2 right-2 bg-gray-800/90 text-gray-400 text-[10px] font-bold px-2 py-1 rounded-lg uppercase tracking-wider">Inactivo</span>
                        @endif
                    </div>

                    <div class="p-4">
                        <h3 class="font-bold text-white text-sm truncate">{{ $album->name }}</h3>
                        @if($album->description)
                            <p class="text-gray-500 text-xs mt-1 line-clamp-2">{{ $album->description }}</p>
                        @endif
                        <div class="flex items-center gap-3 mt-3 text-xs text-gray-500">
                            <span><span class="text-violet-400 font-bold">{{ $album->mediaCount }}</span> media</span>
                            <span><span class="text-violet-400 font-bold">{{ $album->characterCount }}</span> personajes</span>
                        </div>
                        <div class="mt-4 flex items-center gap-2">
                            <a href="{{ route('albums.show', $album->id) }}" target="_blank"
                               class="flex-1 text-center bg-black/40 hover:bg-violet-600/20 border border-gray-700 hover:border-violet-500/50 text-gray-300 hover:text-violet-300 text-xs font-bold py-2 rounded-lg transition-colors">
                                Ver álbum
                            </a>
                            <a href="{{ route('admin.albums.edit', $album->id) }}"
                               class="flex-1 text-center bg-black/40 hover:bg-amber-600/20 border border-gray-700 hover:border-amber-500/50 text-gray-300 hover:text-amber-300 text-xs font-bold py-2 rounded-lg transition-colors">
                                Editar
                            </a>
                            <form action="{{ route('admin.albums.destroy', $album->id) }}" method="POST"
                                  onsubmit="return confirm('¿Eliminar álbum {{ addslashes($album->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="bg-black/40 hover:bg-red-600/20 border border-gray-700 hover:border-red-500/50 text-gray-500 hover:text-red-400 text-xs font-bold p-2 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-admin-layout>
