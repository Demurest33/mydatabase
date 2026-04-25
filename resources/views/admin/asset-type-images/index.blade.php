<x-admin-layout>
    <x-slot:title>Asset Type Images</x-slot>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Asset Type Images</h1>
            <p class="text-gray-500 text-sm mt-1">Imagen de portada para cada categoría en el filtro del home.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-emerald-500/10 border border-emerald-500/40 text-emerald-400 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-6 bg-red-500/10 border border-red-500/40 text-red-400 px-4 py-3 rounded-xl text-sm">{{ session('error') }}</div>
    @endif

    @if(empty($types))
        <div class="text-center py-24 text-gray-600 text-sm italic">No hay tipos de asset en Neo4j todavía.</div>
    @else
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5">
        @foreach($types as $cat)
            @php
                $typeName = $cat['name'];
                $record   = $images->get($typeName);
                $imgUrl   = $record?->image_url;
            @endphp

            <div class="bg-gray-900/60 border border-white/5 rounded-2xl overflow-hidden flex flex-col">

                {{-- Preview --}}
                <div class="relative h-36 bg-gray-800 flex items-center justify-center overflow-hidden">
                    @if($imgUrl)
                        <img src="{{ $imgUrl }}" class="absolute inset-0 w-full h-full object-cover" alt="{{ $typeName }}">
                        <div class="absolute inset-0 bg-black/40"></div>
                    @else
                        <svg class="w-10 h-10 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    @endif
                    <span class="relative z-10 font-extrabold text-white text-sm tracking-wide drop-shadow-lg">{{ $typeName }}</span>
                </div>

                {{-- Upload form --}}
                <div class="p-3 flex flex-col gap-2 flex-1">
                    <p class="text-gray-500 text-xs">{{ number_format($cat['count']) }} posts</p>

                    <form action="{{ route('admin.asset-type-images.upsert', $typeName) }}"
                          method="POST" enctype="multipart/form-data" class="flex flex-col gap-2 flex-1 justify-end">
                        @csrf
                        <label class="flex items-center gap-2 cursor-pointer bg-gray-800 hover:bg-gray-700 border border-white/5 rounded-lg px-3 py-2 transition-colors">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            <span class="text-xs text-gray-400 truncate" id="label-{{ Str::slug($typeName) }}">
                                {{ $imgUrl ? 'Reemplazar imagen' : 'Subir imagen' }}
                            </span>
                            <input type="file" name="image" accept="image/*" class="hidden"
                                   onchange="document.getElementById('label-{{ Str::slug($typeName) }}').textContent = this.files[0]?.name ?? ''; this.closest('form').querySelector('button[type=submit]').classList.remove('opacity-0')">
                        </label>
                        <button type="submit"
                                class="opacity-0 w-full bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-3 py-2 rounded-lg transition-all">
                            Guardar
                        </button>
                    </form>

                    @if($imgUrl)
                    <form action="{{ route('admin.asset-type-images.destroy', $typeName) }}"
                          method="POST" onsubmit="return confirm('¿Eliminar imagen de {{ $typeName }}?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="w-full text-red-500 hover:text-red-400 text-xs font-semibold py-1 transition-colors">
                            Quitar imagen
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    @endif

</x-admin-layout>
