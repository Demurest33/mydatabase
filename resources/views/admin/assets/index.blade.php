<x-admin-layout>
    <x-slot:title>Assets</x-slot>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Assets</h1>
            <p class="text-gray-500 text-sm mt-1">{{ count($rows) }} asset(s) encontrados</p>
        </div>
        <a href="{{ route('assets.create') }}"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition-colors shadow-lg shadow-indigo-500/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Asset
        </a>
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

    {{-- Type filter --}}
    <div class="flex flex-wrap gap-2 mb-8">
        <a href="{{ route('admin.assets.index') }}"
           class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors
                  {{ !$type ? 'bg-indigo-500/20 text-indigo-300 ring-1 ring-indigo-500/30' : 'bg-gray-800 text-gray-400 hover:text-white' }}">
            All
        </a>
        @foreach($types as $t)
        <a href="{{ route('admin.assets.index', ['type' => $t]) }}"
           class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors
                  {{ $type === $t ? 'bg-indigo-500/20 text-indigo-300 ring-1 ring-indigo-500/30' : 'bg-gray-800 text-gray-400 hover:text-white' }}">
            {{ $t }}
        </a>
        @endforeach
    </div>

    {{-- Masonry grid --}}
    @if(!empty($rows))
    <div class="columns-1 sm:columns-2 lg:columns-3 xl:columns-4 gap-4 space-y-4">
        @foreach($rows as $asset)
            <x-asset-card
                :asset="$asset"
                :editUrl="route('admin.assets.edit', $asset['id'])"
                :deleteUrl="route('admin.assets.destroy', $asset['id'])"
                :deleteId="'del-'.$asset['id']"
            />
        @endforeach
    </div>
    @else
    <div class="text-center py-24 text-gray-600">
        <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
        </svg>
        <p class="text-sm">No assets found.</p>
    </div>
    @endif

</x-admin-layout>
