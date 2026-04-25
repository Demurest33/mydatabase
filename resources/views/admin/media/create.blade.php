<x-layout>
    <x-slot:title>Create Media - Backoffice</x-slot>

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Create Media</h1>
            <p class="text-gray-400 mt-2">Add a new Anime, Manga, or Light Novel entry</p>
        </div>
        <a href="{{ route('admin.media.index') }}" class="text-indigo-400 hover:text-indigo-300 font-bold text-sm">
            &larr; Back to List
        </a>
    </div>

    <div class="bg-[#151921] border border-gray-800 rounded-2xl p-8 shadow-2xl max-w-3xl">
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

        <form action="{{ route('admin.media.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Franchise <span class="text-emerald-500">*</span></label>
                    <select name="franchise_name" required class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors font-medium text-sm">
                        <option value="">-- Select Franchise --</option>
                        @foreach($franchises as $franchise)
                            <option value="{{ $franchise }}" {{ old('franchise_name') == $franchise ? 'selected' : '' }}>{{ $franchise }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Format</label>
                    <select name="format" class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors font-medium text-sm">
                        <option value="TV">TV Anime</option>
                        <option value="MOVIE">Movie</option>
                        <option value="OVA">OVA / ONA</option>
                        <option value="MANGA">Manga</option>
                        <option value="NOVEL">Light Novel</option>
                        <option value="ONE_SHOT">One Shot</option>
                        <option value="VIDEOGAME">Videogame</option>
                        <option value="MUSIC_VIDEO">Music Video</option>
                        <option value="ALBUM">Album</option>
                        <option value="SONG">Song</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Romaji Title <span class="text-emerald-500">*</span></label>
                    <input type="text" name="title" required class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors font-medium text-sm" placeholder="e.g. Naruto Shippuden" value="{{ old('title') }}">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Native Title</label>
                    <input type="text" name="native" class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors font-medium text-sm" placeholder="e.g. NARUTO -ナルト- 疾風伝" value="{{ old('native') }}">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Status</label>
                    <select name="status" class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors font-medium text-sm">
                        <option value="FINISHED">Finished</option>
                        <option value="RELEASING">Releasing</option>
                        <option value="NOT_YET_RELEASED">Not Yet Released</option>
                        <option value="CANCELLED">Cancelled</option>
                        <option value="HIATUS">Hiatus</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Start Year</label>
                    <input type="number" name="start_year" class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors font-medium text-sm" placeholder="e.g. 2007" value="{{ old('start_year') }}">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Cover Image URL</label>
                <input type="url" name="coverImage" class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors font-medium text-sm" placeholder="https://..." value="{{ old('coverImage') }}">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-2">Description / Synopsis</label>
                <textarea name="description" rows="4" class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors font-medium text-sm" placeholder="Plot summary...">{{ old('description') }}</textarea>
            </div>

            <div class="pt-4 border-t border-gray-800 flex justify-end">
                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-8 rounded-xl transition-colors shadow-lg shadow-amber-500/20">
                    Create Media
                </button>
            </div>
        </form>
    </div>
</x-layout>
