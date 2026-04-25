<x-layout>
    <x-slot:title>Create Franchise - Backoffice</x-slot>

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Create Franchise</h1>
            <p class="text-gray-400 mt-2">Add a new root franchise to the graph</p>
        </div>
        <a href="{{ route('admin.franchises.index') }}" class="text-indigo-400 hover:text-indigo-300 font-bold text-sm">
            &larr; Back to List
        </a>
    </div>

    <div class="bg-[#151921] border border-gray-800 rounded-2xl p-8 shadow-2xl max-w-2xl">
        @if(session('error'))
            <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-400 px-5 py-4 rounded-xl flex items-center gap-3 shadow-lg">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-400 px-5 py-4 rounded-xl shadow-lg">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.franchises.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Franchise Name <span class="text-emerald-500">*</span></label>
                <input type="text" name="name" required class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors font-medium text-sm" placeholder="e.g. Naruto, Fate, One Piece" value="{{ old('name') }}">
                <p class="text-xs text-gray-500 mt-2">This is the root node name. Keep it exact and capitalized correctly.</p>
            </div>

            <div class="pt-4 border-t border-gray-800 flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl transition-colors shadow-lg shadow-indigo-500/20">
                    Create Franchise
                </button>
            </div>
        </form>
    </div>
</x-layout>
