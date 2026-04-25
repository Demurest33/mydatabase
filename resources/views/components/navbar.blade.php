<nav class="fixed top-0 w-full z-50 transition-all duration-300 bg-gray-950/80 backdrop-blur-xl border-b border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo area -->
            <div class="flex-shrink-0 flex items-center gap-2">
                <a href="{{ url('/') }}" class="flex items-center gap-2 group">
                    <div class="w-8 h-8 rounded bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 group-hover:shadow-indigo-500/50 transition-all">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                    </div>
                    <span class="font-bold text-xl tracking-tight text-white group-hover:text-transparent group-hover:bg-clip-text group-hover:bg-gradient-to-r group-hover:from-indigo-400 group-hover:to-purple-400 transition-colors">
                        NeoGraph
                    </span>
                </a>
            </div>
            
            <!-- Desktop Menu -->
            <div class="hidden md:flex flex-grow items-center justify-center space-x-1">
                <x-nav-link href="{{ route('anilist.index') }}" :active="request()->routeIs('anilist.*')">Anilist Sync</x-nav-link>
                <x-nav-link href="{{ route('wyr.index') }}" :active="request()->routeIs('wyr.*')">Would You Rather</x-nav-link>
                <x-nav-link href="{{ route('franchises.index') }}" :active="request()->routeIs('franchises.*')">Franchises</x-nav-link>
                <x-nav-link href="{{ route('characters.index') }}" :active="request()->routeIs('characters.*')">Characters</x-nav-link>
            </div>

            <!-- Right Side CTA / Extra -->
            <div class="hidden md:flex items-center gap-4">
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-full text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Backoffice
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-full text-white bg-white/10 hover:bg-white/20 border-white/5 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-indigo-500">
                        Login
                    </a>
                @endauth
            </div>

            <!-- Mobile menu button -->
            <div class="flex md:hidden items-center">
                <button type="button" class="text-gray-400 hover:text-white p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500" aria-controls="mobile-menu" aria-expanded="false" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                    <span class="sr-only">Open main menu</span>
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="md:hidden hidden bg-gray-900/95 backdrop-blur-lg border-b border-white/10" id="mobile-menu">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
            <x-nav-link href="{{ route('anilist.index') }}" :active="request()->routeIs('anilist.*')" class="block">Anilist Sync</x-nav-link>
            <x-nav-link href="{{ route('wyr.index') }}" :active="request()->routeIs('wyr.*')" class="block">Would You Rather</x-nav-link>
            <x-nav-link href="{{ route('franchises.index') }}" :active="request()->routeIs('franchises.*')" class="block">Franchises</x-nav-link>
            <x-nav-link href="{{ route('characters.index') }}" :active="request()->routeIs('characters.*')" class="block">Characters</x-nav-link>
            @auth
                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium text-indigo-400 hover:text-indigo-300 hover:bg-gray-800 transition-colors">Backoffice</a>
            @else
                <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-400 hover:text-white hover:bg-gray-800 transition-colors">Login</a>
            @endauth
        </div>
    </div>
</nav>
