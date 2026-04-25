<x-admin-layout>
    <x-slot:title>Steam Session</x-slot>

    <div class="max-w-xl mx-auto">

        <div class="flex items-center gap-3 mb-8">
            <a href="{{ route('admin.wallpaper-import') }}"
               class="text-gray-500 hover:text-white transition-colors p-2 rounded-lg hover:bg-white/5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-extrabold text-white">Steam Session</h1>
                <p class="text-gray-500 text-xs mt-0.5">Cookies para acceder a wallpapers que requieren login</p>
            </div>
        </div>

        @if(session('success'))
        <div class="mb-6 bg-emerald-500/10 border border-emerald-500/40 text-emerald-400 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        @if(session('cleared'))
        <div class="mb-6 bg-gray-500/10 border border-gray-500/40 text-gray-400 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Cookies eliminadas.
        </div>
        @endif

        {{-- Status banner --}}
        @php
            $hasLogin   = !empty($steamLoginSecure);
            $hasSession = !empty($sessionId);
            $isActive   = $hasLogin && $hasSession;
            $isPartial  = $hasLogin xor $hasSession;

            function maskValue(string $v): string {
                if (strlen($v) <= 8) return str_repeat('•', strlen($v));
                return substr($v, 0, 6) . str_repeat('•', max(0, strlen($v) - 10)) . substr($v, -4);
            }
        @endphp

        <div class="rounded-2xl border p-4 mb-6 flex items-center gap-4
                    {{ $isActive ? 'bg-emerald-500/8 border-emerald-500/30' : ($isPartial ? 'bg-amber-500/8 border-amber-500/30' : 'bg-gray-800/50 border-gray-700') }}">
            @if($isActive)
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-emerald-400">Sesión configurada</p>
                    <p class="text-xs text-gray-500 mt-0.5">El scraper usará tu cuenta de Steam para acceder a wallpapers privados.</p>
                </div>
            @elseif($isPartial)
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-amber-400">Configuración incompleta</p>
                    <p class="text-xs text-gray-500 mt-0.5">Faltan {{ !$hasLogin ? 'steamLoginSecure' : 'sessionid' }}.</p>
                </div>
            @else
                <div class="w-10 h-10 rounded-xl bg-gray-700/50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-400">Sin sesión</p>
                    <p class="text-xs text-gray-500 mt-0.5">Solo se importarán wallpapers públicos.</p>
                </div>
            @endif
        </div>

        {{-- Current values preview (if saved) --}}
        @if($hasLogin || $hasSession)
        <div class="bg-[#151921] border border-gray-800 rounded-2xl p-4 mb-6 space-y-2.5">
            <p class="text-[10px] font-bold text-gray-600 uppercase tracking-wider">Valores guardados actualmente</p>
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500">steamLoginSecure</span>
                <span class="font-mono text-xs {{ $hasLogin ? 'text-teal-400' : 'text-gray-700' }}">
                    {{ $hasLogin ? maskValue($steamLoginSecure) : '—' }}
                </span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500">sessionid</span>
                <span class="font-mono text-xs {{ $hasSession ? 'text-teal-400' : 'text-gray-700' }}">
                    {{ $hasSession ? maskValue($sessionId) : '—' }}
                </span>
            </div>
        </div>
        @endif

        {{-- Instructions --}}
        <details class="mb-6 group">
            <summary class="text-xs text-gray-500 hover:text-gray-300 cursor-pointer flex items-center gap-1 transition-colors">
                <svg class="w-3.5 h-3.5 group-open:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                ¿Cómo obtengo las cookies?
            </summary>
            <div class="mt-3 bg-blue-500/5 border border-blue-500/20 rounded-xl p-4 text-xs text-gray-400 space-y-2">
                <ol class="space-y-1.5 list-decimal list-inside">
                    <li>Abre <strong class="text-white">steamcommunity.com</strong> e inicia sesión.</li>
                    <li>Abre DevTools (<kbd class="bg-gray-800 text-gray-300 px-1.5 py-0.5 rounded text-[10px]">F12</kbd>) → <strong class="text-white">Application</strong> → <strong class="text-white">Cookies</strong> → <code class="text-blue-300">https://steamcommunity.com</code></li>
                    <li>Busca y copia el valor de <code class="text-teal-300">steamLoginSecure</code>.</li>
                    <li>Busca y copia el valor de <code class="text-teal-300">sessionid</code>.</li>
                </ol>
                <p class="text-amber-400 mt-2">⚠ Equivalen a tu contraseña. Expiran cuando cierras sesión en Steam.</p>
            </div>
        </details>

        {{-- Save form --}}
        <form action="{{ route('admin.steam-settings.save') }}" method="POST"
              class="bg-[#151921] border border-gray-800 rounded-2xl p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                    steamLoginSecure
                </label>
                <input type="password" name="steam_login_secure"
                       placeholder="{{ $hasLogin ? 'Dejar vacío para mantener el actual' : '76561198...||token...' }}"
                       autocomplete="off"
                       class="w-full bg-black/40 border {{ $errors->has('steam_login_secure') ? 'border-red-500' : 'border-gray-700' }} text-white font-mono text-xs rounded-xl px-4 py-3
                              focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-colors placeholder-gray-600">
                @error('steam_login_secure')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                    sessionid
                </label>
                <input type="password" name="session_id"
                       placeholder="{{ $hasSession ? 'Dejar vacío para mantener el actual' : 'abc123def456...' }}"
                       autocomplete="off"
                       class="w-full bg-black/40 border {{ $errors->has('session_id') ? 'border-red-500' : 'border-gray-700' }} text-white font-mono text-xs rounded-xl px-4 py-3
                              focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-colors placeholder-gray-600">
                @error('session_id')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full bg-teal-600 hover:bg-teal-500 text-white font-bold py-3 rounded-xl transition-colors shadow-lg shadow-teal-500/20">
                Guardar cookies
            </button>
        </form>

        {{-- Clear form --}}
        @if($hasLogin || $hasSession)
        <form action="{{ route('admin.steam-settings.clear') }}" method="POST" class="mt-4 text-center">
            @csrf @method('DELETE')
            <button type="submit"
                    onclick="return confirm('¿Eliminar las cookies guardadas?')"
                    class="text-xs text-red-400 hover:text-red-300 transition-colors">
                Eliminar cookies guardadas
            </button>
        </form>
        @endif

    </div>
</x-admin-layout>
