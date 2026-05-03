<x-admin-layout>
    <x-slot:title>Editar Tag - Backoffice</x-slot>

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Editar Tag</h1>
            <p class="text-gray-400 mt-1 text-sm">{{ $tag->name }}</p>
        </div>
        <a href="{{ route('admin.tags.index') }}" class="text-indigo-400 hover:text-indigo-300 font-bold text-sm">
            &larr; Volver
        </a>
    </div>

    <div class="bg-[#151921] border border-gray-800 rounded-2xl p-8 shadow-2xl max-w-xl">

        @if($errors->any())
            <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-400 px-5 py-4 rounded-xl text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.tags.update', $tag->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">
                    Nombre <span class="text-emerald-500">*</span>
                </label>
                <input type="text" name="name" required
                       value="{{ old('name', $tag->name) }}"
                       class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3
                              focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-sm">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">
                    Tipo <span class="text-emerald-500">*</span>
                </label>
                <select name="type" required
                        class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3
                               focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-sm">
                    <option value="character" {{ old('type', $tag->type) === 'character' ? 'selected' : '' }}>Personajes</option>
                    <option value="media"     {{ old('type', $tag->type) === 'media'     ? 'selected' : '' }}>Media</option>
                    <option value="asset"     {{ old('type', $tag->type) === 'asset'     ? 'selected' : '' }}>Assets</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">
                    Categoría <span class="text-emerald-500">*</span>
                </label>
                <input type="text" name="category" required
                       value="{{ old('category', $tag->category) }}"
                       list="category-suggestions"
                       class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3
                              focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-sm">
                <datalist id="category-suggestions">
                    <option value="Cabello">
                    <option value="Ojos">
                    <option value="Complexión">
                    <option value="Estatura">
                    <option value="Rasgos">
                    <option value="Ropa">
                    <option value="General">
                </datalist>
            </div>

            <div class="pt-4 border-t border-gray-800 flex items-center justify-between gap-4">
                <button type="submit" form="del-form"
                        class="bg-red-600/20 hover:bg-red-600/40 text-red-400 font-bold py-3 px-6 rounded-xl
                               transition-colors border border-red-600/30 text-sm">
                    Eliminar
                </button>
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl
                               transition-colors shadow-lg shadow-indigo-500/20">
                    Guardar cambios
                </button>
            </div>
        </form>

        <form id="del-form"
              action="{{ route('admin.tags.destroy', $tag->id) }}" method="POST"
              onsubmit="return confirm('¿Eliminar «{{ $tag->name }}»? Se eliminará de todos los personajes.')">
            @csrf
            @method('DELETE')
        </form>
    </div>

</x-admin-layout>
