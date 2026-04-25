<x-admin-layout>
    <x-slot:title>Edit Franchise - Backoffice</x-slot>

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Edit Franchise</h1>
            <p class="text-gray-400 mt-2 truncate max-w-md">{{ $currentName }}</p>
        </div>
        <a href="{{ route('admin.franchises.index') }}" class="text-indigo-400 hover:text-indigo-300 font-bold text-sm">
            &larr; Back to List
        </a>
    </div>

    <div class="bg-[#151921] border border-gray-800 rounded-2xl p-8 shadow-2xl max-w-lg">

        @if($errors->any())
            <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-400 px-5 py-4 rounded-xl shadow-lg">
                <ul class="list-disc pl-5 space-y-1 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.franchises.update', $currentName) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">
                    Franchise Name <span class="text-emerald-500">*</span>
                </label>
                <input type="text" name="name" required
                       value="{{ old('name', $currentName) }}"
                       class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors font-medium text-sm"
                       placeholder="e.g. Naruto">
                <p class="text-xs text-gray-600 mt-2">
                    Renombrar actualiza el nodo en el grafo. Los media y personajes vinculados se mantienen.
                </p>
            </div>

            <div class="pt-4 border-t border-gray-800 flex items-center justify-between gap-4">
                <button type="submit" form="delete-franchise-form"
                        class="bg-red-600/20 hover:bg-red-600/40 text-red-400 hover:text-red-300 font-bold py-3 px-6 rounded-xl transition-colors border border-red-600/30 text-sm">
                    Eliminar
                </button>
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl transition-colors shadow-lg shadow-indigo-500/20">
                    Guardar cambios
                </button>
            </div>
        </form>

        <form id="delete-franchise-form"
              action="{{ route('admin.franchises.destroy', $currentName) }}" method="POST"
              data-name="{{ $currentName }}"
              onsubmit="return confirm('¿Eliminar «' + this.dataset.name + '»? Se eliminarán todas sus relaciones.')">
            @csrf
            @method('DELETE')
        </form>
    </div>

</x-admin-layout>
