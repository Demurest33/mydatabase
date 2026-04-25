<x-admin-layout>
    <x-slot:title>Edit Media - Backoffice</x-slot>

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Edit Media</h1>
            <p class="text-gray-400 mt-2 truncate max-w-md">{{ $media['title'] ?? 'Unknown' }}</p>
        </div>
        <a href="{{ route('admin.media.index') }}" class="text-indigo-400 hover:text-indigo-300 font-bold text-sm">
            &larr; Back to List
        </a>
    </div>

    <div class="bg-[#151921] border border-gray-800 rounded-2xl p-8 shadow-2xl max-w-3xl">

        @if($errors->any())
            <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-400 px-5 py-4 rounded-xl shadow-lg">
                <ul class="list-disc pl-5 space-y-1 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.media.update', $media['id']) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">
                        Franchise <span class="text-emerald-500">*</span>
                    </label>
                    <select name="franchise_name" required
                            class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors font-medium text-sm">
                        <option value="">-- Select Franchise --</option>
                        @foreach($franchises as $f)
                            <option value="{{ $f }}"
                                {{ old('franchise_name', $franchise) === $f ? 'selected' : '' }}>
                                {{ $f }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Format</label>
                    <select name="format"
                            class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors font-medium text-sm">
                        @foreach(['TV','TV_SHORT','MOVIE','SPECIAL','OVA','ONA','MUSIC','MANGA','NOVEL','ONE_SHOT','VIDEOGAME','MUSIC_VIDEO','ALBUM','SONG'] as $fmt)
                            <option value="{{ $fmt }}"
                                {{ old('format', $media['format'] ?? '') === $fmt ? 'selected' : '' }}>
                                {{ $fmt }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">
                        Romaji Title <span class="text-emerald-500">*</span>
                    </label>
                    <input type="text" name="title" required
                           value="{{ old('title', $media['title'] ?? '') }}"
                           class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors font-medium text-sm"
                           placeholder="e.g. Naruto Shippuden">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Native Title</label>
                    <input type="text" name="native"
                           value="{{ old('native', $media['native'] ?? '') }}"
                           class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors font-medium text-sm"
                           placeholder="e.g. NARUTO -ナルト- 疾風伝">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Status</label>
                    <select name="status"
                            class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors font-medium text-sm">
                        @foreach(['FINISHED','RELEASING','NOT_YET_RELEASED','CANCELLED','HIATUS'] as $s)
                            <option value="{{ $s }}"
                                {{ old('status', $media['status'] ?? 'FINISHED') === $s ? 'selected' : '' }}>
                                {{ $s }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Start Year</label>
                    <input type="number" name="start_year"
                           value="{{ old('start_year', $media['start_year'] ?? '') }}"
                           class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors font-medium text-sm"
                           placeholder="e.g. 2007">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Cover Image URL</label>
                <div class="flex gap-3 items-start">
                    <input type="url" name="coverImage" id="coverImageInput"
                           value="{{ old('coverImage', $media['coverImage'] ?? '') }}"
                           class="flex-1 bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors font-medium text-sm"
                           placeholder="https://...">
                    @if(!empty($media['coverImage']))
                    <img id="coverPreview"
                         src="{{ $media['coverImage'] }}"
                         class="w-14 h-20 object-cover rounded-lg border border-gray-700 flex-shrink-0"
                         alt="cover">
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Description / Synopsis</label>
                <textarea name="description" rows="4"
                          class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors font-medium text-sm"
                          placeholder="Plot summary...">{{ old('description', $media['description'] ?? '') }}</textarea>
            </div>

            <div class="pt-4 border-t border-gray-800 flex items-center justify-between gap-4">
                {{-- Delete button — linked to the form outside via id --}}
                <button type="submit" form="delete-media-form"
                        class="bg-red-600/20 hover:bg-red-600/40 text-red-400 hover:text-red-300 font-bold py-3 px-6 rounded-xl transition-colors border border-red-600/30 text-sm">
                    Eliminar
                </button>

                {{-- Save --}}
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl transition-colors shadow-lg shadow-indigo-500/20">
                    Guardar cambios
                </button>
            </div>
        </form>

        {{-- Delete form lives OUTSIDE the edit form to avoid nesting --}}
        <form id="delete-media-form"
              action="{{ route('admin.media.destroy', $media['id']) }}" method="POST"
              data-title="{{ $media['title'] ?? 'this media' }}"
              onsubmit="return confirm('¿Eliminar «' + this.dataset.title + '»? Esta acción no se puede deshacer.')">
            @csrf
            @method('DELETE')
        </form>
    </div>

    @push('scripts')
    <script>
    // Live cover image preview
    const input   = document.getElementById('coverImageInput');
    const preview = document.getElementById('coverPreview');
    if (input && preview) {
        input.addEventListener('input', () => { preview.src = input.value; });
    }
    </script>
    @endpush

</x-admin-layout>
