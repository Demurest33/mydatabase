<x-admin-layout>
    <x-slot:title>Auto-tag Characters - Backoffice</x-slot>

    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Auto-tag Characters</h1>
            <p class="text-gray-400 mt-1 text-sm">
                Usa Ollama (local) para detectar tags automáticamente en las imágenes
            </p>
        </div>
        <a href="{{ route('admin.characters.index') }}" class="text-indigo-400 hover:text-indigo-300 font-bold text-sm mt-1">
            &larr; Personajes
        </a>
    </div>

    {{-- ── Status bar ─────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <div class="bg-[#151921] border border-gray-800 rounded-xl p-4">
            <p class="text-[10px] font-bold text-gray-600 uppercase tracking-wider mb-1">Con imagen</p>
            <p class="text-2xl font-extrabold text-white">{{ $totalWithImage }}</p>
        </div>
        <div class="bg-[#151921] border border-gray-800 rounded-xl p-4">
            <p class="text-[10px] font-bold text-gray-600 uppercase tracking-wider mb-1">Sin tags</p>
            <p class="text-2xl font-extrabold text-amber-400">{{ $totalUntagged }}</p>
        </div>
        <div class="bg-[#151921] border border-gray-800 rounded-xl p-4 col-span-2">
            <p class="text-[10px] font-bold text-gray-600 uppercase tracking-wider mb-1">Backend</p>
            @if($backend === 'wd14_local')
                <p class="text-xs font-semibold text-emerald-400">WD14 Local <span class="text-gray-500 font-normal">· tagger_server.py · optimizado para anime</span></p>
                <p class="text-[10px] text-gray-600 mt-0.5">AUTO_TAG_BACKEND=wd14_local · WD14_LOCAL_URL=http://localhost:7860 · WD14_THRESHOLD</p>
            @elseif($backend === 'wd14')
                <p class="text-xs font-semibold text-blue-400">WD14 HF API <span class="text-gray-500 font-normal">· Hugging Face API</span></p>
                <p class="text-[10px] text-gray-600 mt-0.5">AUTO_TAG_BACKEND=wd14 · HF_TOKEN · HF_WD14_MODEL · WD14_THRESHOLD</p>
            @else
                <p class="text-xs font-semibold text-amber-400">Ollama (local) <span class="text-gray-500 font-normal">· modelo generalista · más lento</span></p>
                <p class="text-[10px] text-gray-600 mt-0.5">AUTO_TAG_BACKEND=ollama · OLLAMA_URL · OLLAMA_MODEL · OLLAMA_TIMEOUT</p>
            @endif
        </div>
    </div>

    {{-- ── Setup instructions (collapsible) ─────────────────────────────── --}}
    <details class="mb-6 bg-[#151921] border border-gray-800 rounded-xl overflow-hidden">
        <summary class="px-5 py-3 cursor-pointer select-none list-none flex items-center gap-2 text-sm text-gray-400 hover:text-white transition-colors">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            Configuración de backends
        </summary>
        <div class="px-5 pb-5 pt-3 border-t border-gray-800/60 space-y-5 text-sm text-gray-400">

            <div>
                <p class="text-emerald-400 font-semibold mb-2">✦ WD14 Local — recomendado para anime</p>
                <p class="mb-2">Corre WD14 localmente con un servidor Python. Gratis, sin límites, sin internet.</p>
                <div class="bg-black/40 rounded-lg px-4 py-3 font-mono text-xs space-y-0.5">
                    <p class="text-green-400"># 1. Instalar dependencias Python (una sola vez):</p>
                    <p>pip install dghs-imgutils flask</p>
                    <p class="text-green-400 mt-2"># 2. Iniciar el servidor (descarga el modelo la primera vez, ~700 MB):</p>
                    <p>python tagger_server.py</p>
                    <p class="text-green-400 mt-2"># 3. En .env:</p>
                    <p>AUTO_TAG_BACKEND=wd14_local</p>
                    <p>WD14_LOCAL_URL=http://localhost:7860</p>
                    <p>WD14_THRESHOLD=0.35</p>
                </div>
            </div>

            <div>
                <p class="text-amber-400 font-semibold mb-2">✦ Ollama (local, para fotos reales)</p>
                <div class="bg-black/40 rounded-lg px-4 py-3 font-mono text-xs space-y-0.5">
                    <p class="text-green-400"># En .env:</p>
                    <p>AUTO_TAG_BACKEND=ollama</p>
                    <p>OLLAMA_URL=http://localhost:11434</p>
                    <p>OLLAMA_MODEL=llava-phi3</p>
                    <p>OLLAMA_TIMEOUT=300</p>
                </div>
            </div>

        </div>
    </details>

    {{-- ── Controls ────────────────────────────────────────────────────────── --}}
    <div class="bg-[#151921] border border-gray-800 rounded-xl px-5 py-4 mb-4 flex flex-wrap items-center gap-4">

        {{-- Franchise filter --}}
        <div class="flex items-center gap-2">
            <svg class="w-3.5 h-3.5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
            </svg>
            <select id="franchise-filter"
                    class="bg-black/40 border border-gray-700 text-white rounded-xl px-3 py-2 text-sm
                           focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors max-w-[200px]">
                <option value="">Todas las franquicias</option>
                @foreach($franchises as $f)
                    <option value="{{ $f }}">{{ $f }}</option>
                @endforeach
            </select>
        </div>

        <label class="flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" id="only-untagged" checked
                   class="w-4 h-4 rounded accent-indigo-500 cursor-pointer">
            <span class="text-sm text-gray-300">Solo sin tags</span>
        </label>

        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500">Concurrencia:</span>
            <select id="concurrency"
                    class="bg-black/40 border border-gray-700 text-white rounded-lg px-3 py-1.5 text-sm">
                <option value="1" selected>1 (seguro)</option>
                <option value="2">2</option>
                <option value="3">3</option>
            </select>
        </div>

        <div class="flex-1"></div>

        <button id="btn-stop" type="button"
                class="hidden px-4 py-2 rounded-xl text-sm font-semibold bg-red-600/20 hover:bg-red-600/30
                       text-red-400 border border-red-600/30 transition-colors">
            Detener
        </button>

        <button id="btn-start" type="button"
                class="px-5 py-2 rounded-xl text-sm font-bold bg-indigo-600 hover:bg-indigo-700
                       text-white transition-colors shadow-lg shadow-indigo-500/20">
            Procesar todo
        </button>
    </div>

    {{-- ── Progress ─────────────────────────────────────────────────────────── --}}
    <div id="progress-wrap" class="hidden mb-4 bg-[#151921] border border-gray-800 rounded-xl px-5 py-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-gray-400" id="progress-label">Procesando…</span>
            <span class="text-sm font-bold text-white" id="progress-count">0 / 0</span>
        </div>
        <div class="w-full h-2 bg-gray-800 rounded-full overflow-hidden">
            <div id="progress-bar" class="h-full bg-indigo-500 rounded-full transition-all duration-300" style="width:0%"></div>
        </div>
        <div class="mt-2 flex gap-4 text-xs text-gray-600">
            <span>✓ <span id="stat-ok" class="text-emerald-400">0</span> taggeados</span>
            <span>– <span id="stat-skip" class="text-gray-500">0</span> sin imagen</span>
            <span>✗ <span id="stat-err" class="text-red-400">0</span> errores</span>
        </div>
    </div>

    {{-- ── Character grid ───────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
        @foreach($characters as $char)
        <div class="char-card bg-[#151921] border border-gray-800 rounded-xl overflow-hidden relative
                    transition-all duration-200"
             data-id="{{ $char['id'] }}"
             data-tagged="{{ $char['tagCount'] > 0 ? '1' : '0' }}"
             data-franchise="{{ $char['franchise'] }}">

            {{-- Image --}}
            <div class="aspect-[3/4] bg-gray-900 relative">
                @if(!empty($char['image']))
                    <img src="{{ $char['image'] }}" alt="{{ $char['name'] }}"
                         class="w-full h-full object-cover object-top" loading="lazy">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-700">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                @endif

                {{-- Status overlay --}}
                <div class="status-overlay hidden absolute inset-0 flex items-center justify-center
                            bg-black/60 backdrop-blur-sm">
                    <div class="status-icon text-2xl"></div>
                </div>
            </div>

            {{-- Info --}}
            <div class="p-2">
                <p class="text-white text-xs font-semibold line-clamp-1">{{ $char['name'] }}</p>
                <div class="tags-wrap mt-1 flex flex-wrap gap-0.5 min-h-[1rem]">
                    @foreach($char['tags'] as $tag)
                        <span class="text-[9px] bg-indigo-500/15 text-indigo-300 border border-indigo-500/30
                                     px-1.5 py-0.5 rounded-full">{{ $tag['name'] }}</span>
                    @endforeach
                </div>
            </div>

            {{-- Per-card button --}}
            <button type="button"
                    onclick="processOne(this.closest('.char-card'))"
                    class="absolute top-1.5 right-1.5 w-6 h-6 bg-black/60 hover:bg-indigo-600 rounded-full
                           flex items-center justify-center text-gray-300 hover:text-white transition-all
                           opacity-0 group-hover:opacity-100 card-btn">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </button>
        </div>
        @endforeach
    </div>

    @push('scripts')
    <script>
    (function () {
        const processUrl  = '{{ route("admin.auto-tag.process", ["id" => "__ID__"]) }}';
        const csrfToken   = '{{ csrf_token() }}';

        let running  = false;
        let stopFlag = false;
        let statOk   = 0, statSkip = 0, statErr = 0;

        const btnStart        = document.getElementById('btn-start');
        const btnStop         = document.getElementById('btn-stop');
        const progressWrap    = document.getElementById('progress-wrap');
        const progressBar     = document.getElementById('progress-bar');
        const progressLbl     = document.getElementById('progress-label');
        const progressCnt     = document.getElementById('progress-count');
        const onlyUntagged    = document.getElementById('only-untagged');
        const franchiseFilter = document.getElementById('franchise-filter');

        // Show per-card button on hover
        document.querySelectorAll('.char-card').forEach(card => {
            card.addEventListener('mouseenter', () => card.querySelector('.card-btn')?.classList.remove('opacity-0'));
            card.addEventListener('mouseleave', () => card.querySelector('.card-btn')?.classList.add('opacity-0'));
        });

        function applyFilters() {
            const selectedFranchise = franchiseFilter.value;
            document.querySelectorAll('.char-card').forEach(card => {
                const hideUntagged  = onlyUntagged.checked && card.dataset.tagged === '1';
                const hideFranchise = selectedFranchise && card.dataset.franchise !== selectedFranchise;
                card.style.display  = (hideUntagged || hideFranchise) ? 'none' : '';
            });
        }

        btnStart.addEventListener('click', () => {
            const cards = visibleCards();
            if (!cards.length) return;
            startBatch(cards);
        });

        btnStop.addEventListener('click', () => { stopFlag = true; });

        onlyUntagged.addEventListener('change', applyFilters);
        franchiseFilter.addEventListener('change', applyFilters);

        // Apply filter on load
        applyFilters();

        function visibleCards() {
            return [...document.querySelectorAll('.char-card')]
                .filter(c => c.style.display !== 'none' && c.dataset.tagged !== 'processing');
        }

        async function startBatch(cards) {
            running   = true;
            stopFlag  = false;
            statOk    = statSkip = statErr = 0;
            const total = cards.length;
            let done = 0;

            btnStart.classList.add('hidden');
            btnStop.classList.remove('hidden');
            progressWrap.classList.remove('hidden');
            updateStats();

            const concurrency = parseInt(document.getElementById('concurrency').value) || 1;

            // Process in groups of `concurrency`
            for (let i = 0; i < cards.length; i += concurrency) {
                if (stopFlag) break;
                const batch = cards.slice(i, i + concurrency);
                await Promise.all(batch.map(card => processOne(card)));
                done += batch.length;
                const pct = Math.round((done / total) * 100);
                progressBar.style.width = pct + '%';
                progressCnt.textContent = `${done} / ${total}`;
            }

            progressLbl.textContent = stopFlag ? 'Detenido.' : 'Completado.';
            btnStop.classList.add('hidden');
            btnStart.classList.remove('hidden');
            running = false;
        }

        window.processOne = async function (card) {
            const id = card.dataset.id;
            setCardStatus(card, 'loading');

            try {
                const controller = new AbortController();
                const timer = setTimeout(() => controller.abort(), 360_000); // 6 min
                const res = await fetch(processUrl.replace('__ID__', id), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    signal: controller.signal,
                });
                clearTimeout(timer);
                const data = await res.json();

                if (!res.ok || data.error) {
                    setCardStatus(card, 'error', data.error || 'Error');
                    statErr++;
                } else if (data.skipped) {
                    setCardStatus(card, 'skipped');
                    statSkip++;
                } else {
                    setCardStatus(card, 'done');
                    card.dataset.tagged = '1';
                    renderTags(card, data.tagged || []);
                    statOk++;
                }
            } catch (e) {
                setCardStatus(card, 'error', 'Error de red');
                statErr++;
            }

            updateStats();
        };

        function setCardStatus(card, state, msg = '') {
            const overlay = card.querySelector('.status-overlay');
            const icon    = card.querySelector('.status-icon');
            overlay.classList.remove('hidden');

            const map = {
                loading: { icon: '<svg class="w-8 h-8 text-indigo-400 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>', border: 'border-indigo-500/50' },
                done:    { icon: '<span class="text-emerald-400 text-3xl">✓</span>', border: 'border-emerald-500/50' },
                skipped: { icon: '<span class="text-gray-400 text-xl">—</span>', border: 'border-gray-600' },
                error:   { icon: `<span class="text-red-400 text-xs text-center px-2">${escHtml(msg) || '✗'}</span>`, border: 'border-red-500/50' },
            };

            const s = map[state] || map.error;
            icon.innerHTML = s.icon;
            card.classList.remove('border-indigo-500/50', 'border-emerald-500/50', 'border-gray-600', 'border-red-500/50');
            card.classList.add(s.border);

            if (state !== 'loading') {
                setTimeout(() => overlay.classList.add('hidden'), state === 'done' ? 1500 : 3000);
            }
        }

        function renderTags(card, tags) {
            const wrap = card.querySelector('.tags-wrap');
            if (!wrap) return;
            tags.forEach(t => {
                const chip = document.createElement('span');
                chip.className = 'text-[9px] bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 px-1.5 py-0.5 rounded-full';
                chip.textContent = t.name;
                wrap.appendChild(chip);
            });
        }

        function updateStats() {
            document.getElementById('stat-ok').textContent   = statOk;
            document.getElementById('stat-skip').textContent = statSkip;
            document.getElementById('stat-err').textContent  = statErr;
        }

        function escHtml(s) {
            return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }
    })();
    </script>
    @endpush

</x-admin-layout>
