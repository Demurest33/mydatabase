<x-admin-layout>
    <x-slot:title>Edit Asset</x-slot>

    <div class="max-w-xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-8">
            <a href="{{ route('admin.assets.index') }}"
               class="text-gray-500 hover:text-white transition-colors p-2 rounded-lg hover:bg-white/5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-extrabold text-white">Edit Asset</h1>
                <p class="text-gray-500 text-xs mt-0.5 truncate max-w-xs">{{ $asset['title'] ?? $asset['id'] }}</p>
            </div>
        </div>

        @if(session('error'))
        <div class="mb-6 bg-red-500/10 border border-red-500/40 text-red-400 px-4 py-3 rounded-xl text-sm">{{ session('error') }}</div>
        @endif

        <form action="{{ route('admin.assets.update', $asset['id']) }}" method="POST"
              enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Current file / URL (read-only info) --}}
            <div class="bg-[#151921] border border-gray-800 rounded-2xl p-5">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Source</p>
                @if(!empty($asset['filename']))
                    <p class="text-sm text-gray-300 font-mono break-all">{{ $asset['filename'] }}</p>
                    <p class="text-[10px] text-gray-600 mt-1">Local file — cannot be changed here</p>
                @elseif(!empty($asset['url']))
                    <a href="{{ $asset['url'] }}" target="_blank"
                       class="text-sm text-indigo-400 hover:text-indigo-300 break-all transition-colors">
                        {{ $asset['url'] }}
                    </a>
                    <p class="text-[10px] text-gray-600 mt-1">External URL — cannot be changed here</p>
                @endif
            </div>

            {{-- Editable fields --}}
            <div class="bg-[#151921] border border-gray-800 rounded-2xl p-5 space-y-5">

                {{-- Title --}}
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Title</label>
                    <input type="text" name="title" value="{{ old('title', $asset['title'] ?? '') }}"
                           placeholder="Untitled"
                           class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm
                                  focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none placeholder-gray-600 transition-colors">
                </div>

                {{-- Type --}}
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Type</label>
                    <select name="type" required
                            class="w-full bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm
                                   focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-colors">
                        @foreach($types as $t)
                        <option value="{{ $t }}" {{ old('type', $asset['type'] ?? '') === $t ? 'selected' : '' }}>
                            {{ $t }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Thumbnail --}}
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                        Thumbnail
                    </label>

                    @if(!empty($asset['coverUrl']))
                    <div class="mb-3 flex items-start gap-4">
                        <img src="{{ $asset['coverUrl'] }}" alt="current cover"
                             class="w-24 aspect-video object-cover rounded-xl border border-gray-700">
                        <p class="text-xs text-gray-500 mt-1">Current thumbnail. Upload a new one to replace it.</p>
                    </div>
                    @endif

                    <input type="file" name="cover_image" accept="image/*"
                           class="block w-full text-sm text-gray-400
                                  file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0
                                  file:text-xs file:font-bold file:bg-indigo-500/10 file:text-indigo-400
                                  hover:file:bg-indigo-500/20 cursor-pointer
                                  bg-black/30 border border-dashed border-gray-700 rounded-xl px-3 py-2.5 transition-colors">
                    <p class="text-[10px] text-gray-600 mt-2">Recommended: 16:9 or 4:3, JPG/PNG/WebP</p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3">
                <button type="submit"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition-colors shadow-lg shadow-indigo-500/20">
                    Save Changes
                </button>
                <a href="{{ route('admin.assets.index') }}"
                   class="px-5 py-3 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-colors font-bold text-sm">
                    Cancel
                </a>
            </div>
        </form>

        {{-- Delete --}}
        <div class="mt-8 pt-6 border-t border-gray-800">
            <p class="text-xs text-gray-600 mb-3">Danger zone — this also deletes the file from storage.</p>
            <form action="{{ route('admin.assets.destroy', $asset['id']) }}" method="POST" id="delete-asset-form">
                @csrf
                @method('DELETE')
            </form>
            <button type="button"
                    onclick="if(confirm('Delete this asset permanently?')) document.getElementById('delete-asset-form').submit()"
                    class="text-sm font-bold text-red-400 hover:text-red-300 bg-red-500/10 hover:bg-red-500/20 px-4 py-2.5 rounded-xl transition-colors">
                Delete Asset
            </button>
        </div>

    </div>
</x-admin-layout>
