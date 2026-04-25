<x-admin-layout>
    <x-slot:title>Admin Dashboard</x-slot>

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Backoffice Dashboard</h1>
            <p class="text-gray-400 mt-2">Manage the Neo4j Graph database entities</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Asset Management -->
        <a href="{{ route('assets.create') }}" class="bg-[#151921] border border-gray-800 rounded-2xl p-6 hover:border-indigo-500 transition-all hover:-translate-y-1 group relative overflow-hidden">
            <div class="w-12 h-12 bg-indigo-500/10 rounded-xl flex items-center justify-center text-indigo-400 mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Assets Graph DB</h3>
            <p class="text-sm text-gray-500">Upload images, videos and link them to Neo4j nodes.</p>
        </a>

        <!-- Franchises -->
        <a href="{{ route('admin.franchises.index') }}" class="bg-[#151921] border border-gray-800 rounded-2xl p-6 hover:border-emerald-500 transition-all hover:-translate-y-1 group relative overflow-hidden">
            <div class="w-12 h-12 bg-emerald-500/10 rounded-xl flex items-center justify-center text-emerald-400 mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Franchises</h3>
            <p class="text-sm text-gray-500">Manage root franchise nodes and collections.</p>
        </a>

        <!-- Media -->
        <a href="{{ route('admin.media.index') }}" class="bg-[#151921] border border-gray-800 rounded-2xl p-6 hover:border-amber-500 transition-all hover:-translate-y-1 group relative overflow-hidden">
            <div class="w-12 h-12 bg-amber-500/10 rounded-xl flex items-center justify-center text-amber-400 mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Media Items</h3>
            <p class="text-sm text-gray-500">Manage Anime, Manga, Light Novels entries.</p>
        </a>

        <!-- Characters -->
        <a href="{{ route('admin.characters.index') }}" class="bg-[#151921] border border-gray-800 rounded-2xl p-6 hover:border-pink-500 transition-all hover:-translate-y-1 group relative overflow-hidden">
            <div class="w-12 h-12 bg-pink-500/10 rounded-xl flex items-center justify-center text-pink-400 mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Characters</h3>
            <p class="text-sm text-gray-500">Manage database characters and their associations.</p>
        </a>

        <!-- Wallpaper Bulk Import -->
        <a href="{{ route('admin.wallpaper-import') }}" class="bg-[#151921] border border-gray-800 rounded-2xl p-6 hover:border-teal-500 transition-all hover:-translate-y-1 group relative overflow-hidden">
            <div class="w-12 h-12 bg-teal-500/10 rounded-xl flex items-center justify-center text-teal-400 mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Wallpaper Import</h3>
            <p class="text-sm text-gray-500">Importa todos tus wallpapers de Steam Workshop en bulk.</p>
        </a>
    </div>
</x-admin-layout>
