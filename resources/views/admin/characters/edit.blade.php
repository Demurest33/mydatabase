<x-admin-layout>
    <x-slot:title>Edit Character - Backoffice</x-slot>

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Edit Character</h1>
            <p class="text-gray-400 mt-2 truncate max-w-md">{{ $character['name'] ?? 'Unknown' }}</p>
        </div>
        <a href="{{ route('admin.characters.index') }}" class="text-indigo-400 hover:text-indigo-300 font-bold text-sm">
            &larr; Back to List
        </a>
    </div>

    <div class="bg-[#151921] border border-gray-800 rounded-2xl p-8 shadow-2xl max-w-2xl">

        @if($errors->any())
            <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-400 px-5 py-4 rounded-xl shadow-lg">
                <ul class="list-disc pl-5 space-y-1 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.characters.update', $character['id']) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">
                        Media <span class="text-emerald-500">*</span>
                    </label>
                    <select name="media_id" required
                            class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors font-medium text-sm">
                        <option value="">-- Select Media --</option>
                        @foreach($mediaList as $media)
                            <option value="{{ $media['id'] }}"
                                {{ old('media_id', $character['mediaId']) == $media['id'] ? 'selected' : '' }}>
                                {{ $media['title'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">
                        Role <span class="text-emerald-500">*</span>
                    </label>
                    <select name="role" required
                            class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors font-medium text-sm">
                        @foreach(['MAIN', 'SUPPORTING', 'BACKGROUND'] as $r)
                            <option value="{{ $r }}"
                                {{ old('role', $character['role']) === $r ? 'selected' : '' }}>
                                {{ $r }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">
                    Character Name <span class="text-emerald-500">*</span>
                </label>
                <input type="text" name="name" required
                       value="{{ old('name', $character['name'] ?? '') }}"
                       class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors font-medium text-sm"
                       placeholder="e.g. Naruto Uzumaki">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Character Image URL</label>
                <div class="flex gap-3 items-start">
                    <input type="url" name="image" id="imageInput"
                           value="{{ old('image', $character['image'] ?? '') }}"
                           class="flex-1 bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-colors font-medium text-sm"
                           placeholder="https://...">
                    @if(!empty($character['image']))
                    <img id="imagePreview"
                         src="{{ $character['image'] }}"
                         class="w-14 h-20 object-cover object-top rounded-lg border border-gray-700 flex-shrink-0"
                         alt="preview">
                    @endif
                </div>
            </div>

            <div class="pt-4 border-t border-gray-800 flex items-center justify-between gap-4">
                <button type="submit" form="delete-char-form"
                        class="bg-red-600/20 hover:bg-red-600/40 text-red-400 hover:text-red-300 font-bold py-3 px-6 rounded-xl transition-colors border border-red-600/30 text-sm">
                    Eliminar
                </button>
                <button type="submit"
                        class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-8 rounded-xl transition-colors shadow-lg shadow-pink-500/20">
                    Guardar cambios
                </button>
            </div>
        </form>

        {{-- Delete form outside the edit form --}}
        <form id="delete-char-form"
              action="{{ route('admin.characters.destroy', $character['id']) }}" method="POST"
              data-name="{{ $character['name'] ?? 'this character' }}"
              onsubmit="return confirm('¿Eliminar «' + this.dataset.name + '»? Esta acción no se puede deshacer.')">
            @csrf
            @method('DELETE')
        </form>
    </div>

    @push('scripts')
    <script>
    const input   = document.getElementById('imageInput');
    const preview = document.getElementById('imagePreview');
    if (input && preview) {
        input.addEventListener('input', () => { preview.src = input.value; });
    }
    </script>
    @endpush

</x-admin-layout>
