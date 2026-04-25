<x-admin-layout>
    <x-slot:title>Wallpaper Import</x-slot>

    <div class="max-w-2xl mx-auto">

        <div class="flex items-center gap-3 mb-8">
            <a href="{{ route('admin.dashboard') }}"
               class="text-gray-500 hover:text-white transition-colors p-2 rounded-lg hover:bg-white/5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-extrabold text-white">Bulk Wallpaper Import</h1>
                <p class="text-gray-500 text-xs mt-0.5">Importa todos tus wallpapers de Steam Workshop de una vez</p>
            </div>
        </div>

        @if(session('error'))
        <div class="mb-6 bg-red-500/10 border border-red-500/40 text-red-400 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
        @endif

        @if($batchId)
        {{-- Progress view --}}
        <div class="bg-[#151921] border border-gray-800 rounded-2xl p-6 space-y-5" id="progress-panel">
            <div class="flex items-center justify-between">
                <p class="text-sm font-bold text-white flex items-center gap-2">
                    <span id="status-icon">
                        <svg class="w-4 h-4 text-teal-400 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </span>
                    <span id="status-text">Importando wallpapers...</span>
                </p>
                <span id="count-text" class="text-xs text-gray-500 tabular-nums">0 / 0</span>
            </div>

            {{-- Progress bar --}}
            <div class="w-full bg-gray-800 rounded-full h-3 overflow-hidden">
                <div id="progress-bar"
                     class="h-3 rounded-full bg-gradient-to-r from-teal-500 to-emerald-400 transition-all duration-500"
                     style="width: 0%">
                </div>
            </div>

            <div class="flex items-center justify-between text-xs text-gray-600">
                <span id="failed-text"></span>
                <span id="pct-text">0%</span>
            </div>

            {{-- Issues log --}}
            <div id="log-panel" class="hidden space-y-1 max-h-56 overflow-y-auto
                                        rounded-xl border border-gray-800 bg-black/20 p-3">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Incidencias</p>
                <div id="log-list" class="space-y-1"></div>
            </div>

            <div id="done-actions" class="hidden pt-2 border-t border-gray-800">
                <a href="{{ route('admin.assets.index', ['type' => 'WALLPAPER ENGINE']) }}"
                   class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-500 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Ver assets importados
                </a>
                <a href="{{ route('admin.wallpaper-import') }}"
                   class="ml-3 text-sm text-gray-500 hover:text-white transition-colors">
                    Nueva importación
                </a>
            </div>
        </div>

        <script>
        (function () {
            const batchId   = @json($batchId);
            const url       = `/admin/wallpaper-import/progress/${batchId}`;
            const bar       = document.getElementById('progress-bar');
            const pct       = document.getElementById('pct-text');
            const countEl   = document.getElementById('count-text');
            const statusTxt = document.getElementById('status-text');
            const statusIco = document.getElementById('status-icon');
            const failedEl  = document.getElementById('failed-text');
            const doneEl    = document.getElementById('done-actions');
            const logPanel  = document.getElementById('log-panel');
            const logList   = document.getElementById('log-list');

            let renderedLogs = 0;

            function renderLogs(logs) {
                if (!logs || logs.length === renderedLogs) return;
                const newEntries = logs.slice(renderedLogs);
                newEntries.forEach(entry => {
                    const isSkip   = entry.status === 'skipped';
                    const color    = isSkip ? 'text-amber-400' : 'text-red-400';
                    const icon     = isSkip ? '⚠' : '✕';
                    const steamUrl = `https://steamcommunity.com/sharedfiles/filedetails/?id=${entry.workshop_id}`;
                    const el = document.createElement('div');
                    el.className = `flex items-start gap-2 text-xs ${color}`;
                    el.innerHTML = `<span class="flex-shrink-0 font-bold">${icon}</span>
                        <span><a href="${steamUrl}" target="_blank" class="underline hover:opacity-80">${entry.workshop_id}</a>
                        — ${entry.reason}</span>`;
                    logList.appendChild(el);
                });
                renderedLogs = logs.length;
                logPanel.classList.remove('hidden');
            }

            async function poll() {
                try {
                    const res  = await fetch(url);
                    const data = await res.json();

                    const p = data.progress ?? 0;
                    bar.style.width = p + '%';
                    pct.textContent = p + '%';
                    countEl.textContent = `${data.processed} / ${data.total}`;

                    if (data.failed > 0 || (data.logs && data.logs.some(l => l.status === 'skipped'))) {
                        const skipped = (data.logs || []).filter(l => l.status === 'skipped').length;
                        const parts = [];
                        if (data.failed > 0) parts.push(`${data.failed} fallidos`);
                        if (skipped > 0) parts.push(`${skipped} omitidos`);
                        failedEl.textContent = parts.join(' · ');
                        failedEl.className = 'text-amber-400';
                    }

                    renderLogs(data.logs || []);

                    if (data.finished || data.cancelled) {
                        statusIco.innerHTML = `<svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`;
                        statusTxt.textContent = data.cancelled
                            ? 'Importación cancelada'
                            : `Completo — ${data.processed} procesados`;
                        doneEl.classList.remove('hidden');
                        // Remove ?batch= from URL so next visit shows the form
                        history.replaceState(null, '', location.pathname);
                        return;
                    }
                } catch (e) {}

                setTimeout(poll, 2000);
            }

            poll();
        })();
        </script>

        @else
        {{-- Import form --}}
        <div class="bg-teal-500/5 border border-teal-500/20 rounded-2xl p-5 mb-6 text-sm text-gray-400 space-y-2">
            <p class="font-semibold text-teal-400 text-xs uppercase tracking-wider mb-3">Cómo funciona</p>
            <p>Proporciona la ruta a la carpeta que contiene los IDs de tus wallpapers:</p>
            <code class="block bg-black/40 text-teal-300 text-xs px-3 py-2 rounded-lg mt-2 break-all">
                C:\Program Files (x86)\Steam\steamapps\workshop\content\431960
            </code>
            <p class="text-xs text-gray-500 mt-2">Cada subcarpeta numérica es un Workshop ID. El sistema consultará Steam por cada uno para obtener título y thumbnail.</p>
            <p class="text-xs text-amber-400 mt-1">⚠ Wallpapers ya importados se saltarán automáticamente.</p>
        </div>

        @php $activeMode = old('mode', 'path'); @endphp

        <form action="{{ route('admin.wallpaper-import.store') }}" method="POST"
              class="bg-[#151921] border border-gray-800 rounded-2xl p-6 space-y-5">
            @csrf

            {{-- Mode toggle --}}
            <div class="flex gap-2 bg-black/40 p-1.5 rounded-xl border border-gray-800" id="mode-toggle">
                <label id="btn-path"
                       class="flex-1 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition-all text-center cursor-pointer
                              {{ $activeMode === 'path' ? 'bg-gray-800 text-white shadow-sm' : 'text-gray-500 hover:text-gray-300' }}">
                    <input type="radio" name="mode" value="path" class="hidden"
                           {{ $activeMode === 'path' ? 'checked' : '' }}>
                    📁 Carpeta
                </label>
                <label id="btn-ids"
                       class="flex-1 py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition-all text-center cursor-pointer
                              {{ $activeMode === 'ids' ? 'bg-gray-800 text-white shadow-sm' : 'text-gray-500 hover:text-gray-300' }}">
                    <input type="radio" name="mode" value="ids" class="hidden"
                           {{ $activeMode === 'ids' ? 'checked' : '' }}>
                    🔢 IDs manuales
                </label>
            </div>

            {{-- Path mode --}}
            <div id="section-path" {{ $activeMode === 'ids' ? 'style=display:none' : '' }}>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                    Ruta de la carpeta
                </label>
                <input type="text" name="path"
                       value="{{ old('path', 'C:\Program Files (x86)\Steam\steamapps\workshop\content\431960') }}"
                       class="w-full bg-black/40 border border-gray-700 text-white font-mono text-sm rounded-xl px-4 py-3
                              focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition-colors">
                @error('path')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- IDs mode --}}
            <div id="section-ids" {{ $activeMode === 'path' ? 'style=display:none' : '' }}>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                    IDs de Steam Workshop
                </label>
                <textarea name="ids_list" rows="6"
                          placeholder="899497344, 123456789, 987654321&#10;Separados por comas, espacios o saltos de línea"
                          class="w-full bg-black/40 border border-gray-700 text-white font-mono text-sm rounded-xl px-4 py-3
                                 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none resize-none
                                 transition-colors placeholder-gray-600">{{ old('ids_list') }}</textarea>
                @error('ids_list')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Rate limit config --}}
            <div class="flex items-center gap-4 pt-2 border-t border-gray-800">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">
                        Delay entre requests
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="range" name="delay_seconds" id="delay-range"
                               min="1" max="15" value="{{ old('delay_seconds', 3) }}"
                               class="flex-1 accent-teal-500"
                               oninput="document.getElementById('delay-val').textContent=this.value">
                        <span class="text-sm font-bold text-teal-400 w-16 text-right">
                            <span id="delay-val">{{ old('delay_seconds', 3) }}</span>s
                        </span>
                    </div>
                </div>
                <p class="text-[10px] text-gray-600 max-w-[160px] leading-tight">
                    Pausa entre cada consulta a Steam para evitar bloqueos de IP.
                </p>
            </div>

            <button type="submit"
                    class="w-full bg-teal-600 hover:bg-teal-500 text-white font-bold py-3 rounded-xl transition-colors shadow-lg shadow-teal-500/20 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Iniciar importación
            </button>
        </form>

        <script>
        const ACTIVE   = 'bg-gray-800 text-white shadow-sm';
        const INACTIVE = 'text-gray-500 hover:text-gray-300';

        function setMode(mode) {
            const isPath = mode === 'path';
            document.getElementById('section-path').style.display = isPath ? '' : 'none';
            document.getElementById('section-ids').style.display  = isPath ? 'none' : '';
            document.getElementById('btn-path').classList.toggle(ACTIVE.split(' ')[0], isPath);
            document.getElementById('btn-path').classList.toggle(INACTIVE.split(' ')[0], !isPath);
            document.getElementById('btn-ids').classList.toggle(ACTIVE.split(' ')[0], !isPath);
            document.getElementById('btn-ids').classList.toggle(INACTIVE.split(' ')[0], isPath);
        }

        document.querySelectorAll('[name="mode"]').forEach(radio => {
            radio.addEventListener('change', () => setMode(radio.value));
        });
        </script>

        <p class="text-xs text-gray-600 text-center mt-4">
            Los assets se crean en background. Puedes seguir usando el backoffice mientras se procesan.
        </p>
        <p class="text-xs text-center mt-2">
            <a href="{{ route('admin.steam-settings') }}" class="text-teal-500 hover:text-teal-400 transition-colors">
                ⚙ Configurar cookies de Steam
            </a>
            <span class="text-gray-700 mx-2">·</span>
            <span class="text-gray-600">necesario para wallpapers que requieren login</span>
        </p>
        @endif

    </div>
</x-admin-layout>
