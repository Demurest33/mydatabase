<x-admin-layout>
    <x-slot:title>Create Album - Backoffice</x-slot>

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Create Album</h1>
            <p class="text-gray-400 mt-2">Crea un álbum seleccionando qué media incluye</p>
        </div>
        <a href="{{ route('admin.albums.index') }}" class="text-indigo-400 hover:text-indigo-300 font-bold text-sm">
            &larr; Back
        </a>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-400 px-5 py-4 rounded-xl shadow-lg">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.albums.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left: album info --}}
            <div class="lg:col-span-1 space-y-5">
                <div class="bg-[#151921] border border-gray-800 rounded-2xl p-6 shadow-xl space-y-5">
                    <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Información del álbum</h2>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nombre <span class="text-violet-400">*</span></label>
                        <input type="text" name="name" required value="{{ old('name') }}"
                               class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors text-sm"
                               placeholder="e.g. Mundial 2026">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Descripción</label>
                        <textarea name="description" rows="3"
                                  class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors text-sm"
                                  placeholder="Descripción opcional del álbum...">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Cover Image URL</label>
                        <input type="url" name="coverImage" value="{{ old('coverImage') }}"
                               class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors text-sm"
                               placeholder="https://...">
                    </div>

                    <div class="pt-4 border-t border-gray-800">
                        <button type="submit"
                                class="w-full bg-violet-600 hover:bg-violet-700 text-white font-bold py-3 px-6 rounded-xl transition-colors shadow-lg shadow-violet-500/20">
                            Crear álbum
                        </button>
                    </div>
                </div>
            </div>

            {{-- Right: media selector --}}
            <div class="lg:col-span-2">
                <div class="bg-[#151921] border border-gray-800 rounded-2xl p-6 shadow-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Media incluida</h2>
                        <div class="flex items-center gap-3">
                            <input type="search" id="media-search" placeholder="Buscar..."
                                   class="bg-black/40 border border-gray-700 text-white placeholder-gray-600 text-xs rounded-lg px-3 py-2 focus:border-violet-500 outline-none w-40 transition-colors">
                            <button type="button" id="btn-select-all"
                                    class="text-xs font-bold text-violet-400 hover:text-violet-300 transition-colors">
                                Todo
                            </button>
                            <button type="button" id="btn-clear-all"
                                    class="text-xs font-bold text-gray-500 hover:text-gray-300 transition-colors">
                                Limpiar
                            </button>
                        </div>
                    </div>

                    <p class="text-xs text-gray-600 mb-4">
                        El álbum incluirá <strong class="text-gray-400">todos los personajes</strong> de las media seleccionadas.
                    </p>

                    <div id="media-list" class="space-y-4 max-h-[60vh] overflow-y-auto pr-1">
                        @foreach($mediaByFranchise as $franchise => $items)
                            <div class="franchise-group" data-franchise="{{ strtolower($franchise) }}">
                                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider px-1 mb-2">
                                    {{ $franchise }}
                                </div>
                                <div class="space-y-1">
                                    @foreach($items as $item)
                                        <label class="media-item flex items-center gap-3 p-2.5 rounded-xl hover:bg-black/30 cursor-pointer transition-colors group"
                                               data-title="{{ strtolower($item['title']) }}">
                                            <input type="checkbox" name="media_ids[]" value="{{ $item['id'] }}"
                                                   {{ in_array($item['id'], old('media_ids', [])) ? 'checked' : '' }}
                                                   class="w-4 h-4 rounded accent-violet-500 cursor-pointer">
                                            @if($item['coverImage'])
                                                <img src="{{ $item['coverImage'] }}" alt=""
                                                     class="w-8 h-8 rounded-lg object-cover flex-shrink-0 opacity-80 group-hover:opacity-100">
                                            @else
                                                <div class="w-8 h-8 rounded-lg bg-gray-800 flex-shrink-0"></div>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="text-sm text-white font-medium truncate">{{ $item['title'] }}</p>
                                                <p class="text-xs text-gray-600">{{ $item['format'] ?? '' }}</p>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-800 flex items-center justify-between text-xs text-gray-600">
                        <span id="selected-count">0 media seleccionados</span>
                    </div>
                </div>
            </div>

        </div>
    </form>

    <script>
    (function () {
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        const countEl    = document.getElementById('selected-count');
        const searchEl   = document.getElementById('media-search');

        function updateCount() {
            const n = document.querySelectorAll('input[type="checkbox"]:checked').length;
            countEl.textContent = n + ' media seleccionado' + (n !== 1 ? 's' : '');
        }

        checkboxes.forEach(cb => cb.addEventListener('change', updateCount));
        updateCount();

        document.getElementById('btn-select-all').addEventListener('click', () => {
            document.querySelectorAll('.media-item:not([hidden]) input').forEach(cb => cb.checked = true);
            updateCount();
        });

        document.getElementById('btn-clear-all').addEventListener('click', () => {
            checkboxes.forEach(cb => cb.checked = false);
            updateCount();
        });

        searchEl.addEventListener('input', () => {
            const q = searchEl.value.toLowerCase();
            document.querySelectorAll('.franchise-group').forEach(group => {
                let anyVisible = false;
                group.querySelectorAll('.media-item').forEach(item => {
                    const matches = item.dataset.title.includes(q) ||
                                    group.dataset.franchise.includes(q);
                    item.hidden = !matches;
                    if (matches) anyVisible = true;
                });
                group.hidden = !anyVisible;
            });
        });
    })();
    </script>
</x-admin-layout>
