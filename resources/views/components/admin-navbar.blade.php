<nav class="fixed top-0 w-full z-50 bg-gray-950/90 backdrop-blur-xl border-b border-indigo-500/20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <div class="flex-shrink-0">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 group">
                    <div class="w-8 h-8 rounded bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="leading-tight">
                        <span class="block text-sm font-bold text-white group-hover:text-indigo-300 transition-colors">NeoGraph</span>
                        <span class="block text-[10px] font-bold text-indigo-400 uppercase tracking-widest -mt-0.5">Backoffice</span>
                    </div>
                </a>
            </div>

            {{-- Desktop nav --}}
            <div class="hidden md:flex items-center gap-1">
                @php
                    $navItems = [
                        ['route' => 'admin.dashboard',     'pattern' => 'admin.dashboard',    'label' => 'Dashboard'],
                        ['route' => 'admin.franchises.index', 'pattern' => 'admin.franchises.*', 'label' => 'Franchises'],
                        ['route' => 'admin.media.index',   'pattern' => 'admin.media.*',      'label' => 'Media'],
                        ['route' => 'admin.characters.index','pattern'=> 'admin.characters.*', 'label' => 'Characters'],
                        ['route' => 'assets.create',       'pattern' => 'assets.*',           'label' => 'Assets'],
                    ];
                @endphp
                @foreach($navItems as $item)
                @php $active = request()->routeIs($item['pattern']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200
                          {{ $active
                              ? 'bg-indigo-500/15 text-indigo-300 ring-1 ring-indigo-500/30'
                              : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                    {{ $item['label'] }}
                </a>
                @endforeach
            </div>

            {{-- Right: view site + logout --}}
            <div class="hidden md:flex items-center gap-3">
                <a href="{{ url('/') }}"
                   class="flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-300 transition-colors px-3 py-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    View Site
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-bold text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>

            {{-- Mobile menu button --}}
            <div class="flex md:hidden">
                <button type="button"
                        class="text-gray-400 hover:text-white p-2 rounded-md"
                        onclick="document.getElementById('admin-mobile-menu').classList.toggle('hidden')">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div class="md:hidden hidden bg-gray-900/95 backdrop-blur-lg border-b border-indigo-500/20" id="admin-mobile-menu">
        <div class="px-3 pt-2 pb-3 space-y-1">
            @foreach($navItems as $item)
            @php $active = request()->routeIs($item['pattern']); @endphp
            <a href="{{ route($item['route']) }}"
               class="block px-3 py-2 rounded-lg text-sm font-medium transition-colors
                      {{ $active ? 'bg-indigo-500/15 text-indigo-300' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                {{ $item['label'] }}
            </a>
            @endforeach
            <div class="border-t border-gray-800 mt-2 pt-2 flex items-center justify-between px-1">
                <a href="{{ url('/') }}" class="text-xs text-gray-500 hover:text-gray-300 transition-colors px-2 py-1.5">← View Site</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs font-bold text-red-400 hover:text-red-300 px-2 py-1.5 transition-colors">Logout</button>
                </form>
            </div>
        </div>
    </div>
</nav>
