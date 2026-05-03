<x-admin-layout>
    <x-slot:title>Nuevo Tag - Backoffice</x-slot>

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Nuevos Tags</h1>
            <p class="text-gray-400 mt-1 text-sm">Crea varios tags a la vez</p>
        </div>
        <a href="{{ route('admin.tags.index') }}" class="text-indigo-400 hover:text-indigo-300 font-bold text-sm">
            &larr; Volver
        </a>
    </div>

    @if(session('error'))
        <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-400 px-5 py-3 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.tags.store') }}" method="POST" id="bulk-form">
        @csrf

        {{-- ── Global defaults ────────────────────────────────────────────── --}}
        <div class="bg-[#151921] border border-gray-800 rounded-2xl p-6 mb-4">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Valores por defecto</p>
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Tipo</label>
                    <select id="global-type"
                            class="bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm
                                   focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors min-w-[140px]">
                        <option value="character" selected>Personajes</option>
                        <option value="media">Media</option>
                        <option value="asset">Assets</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Categoría por defecto</label>
                    <div class="flex gap-2">
                        <input type="text" id="global-category"
                               list="cat-suggestions"
                               placeholder="Hair Color, Eye Color…"
                               class="flex-1 bg-black/40 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm
                                      focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                        <button type="button" id="btn-apply-cat"
                                class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-gray-800 hover:bg-gray-700
                                       text-gray-300 hover:text-white border border-gray-700 transition-colors whitespace-nowrap">
                            Aplicar a todas
                        </button>
                    </div>
                </div>
                <div class="flex gap-2 pb-0.5">
                    <button type="button" id="btn-add-row"
                            class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-indigo-500/15 hover:bg-indigo-500/25
                                   text-indigo-300 border border-indigo-500/30 transition-colors">
                        + Añadir fila
                    </button>
                    <button type="button" id="btn-add-5"
                            class="px-4 py-2.5 rounded-xl text-sm font-semibold bg-white/5 hover:bg-white/10
                                   text-gray-400 border border-gray-700 transition-colors">
                        +5 filas
                    </button>
                </div>
            </div>
        </div>

        {{-- ── Tag rows ────────────────────────────────────────────────────── --}}
        <div class="bg-[#151921] border border-gray-800 rounded-2xl overflow-hidden mb-4">

            {{-- Header --}}
            <div class="grid gap-3 px-5 py-3 border-b border-gray-800/60 bg-black/20"
                 style="grid-template-columns: 1fr 1fr 2rem">
                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Nombre</span>
                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Categoría</span>
                <span></span>
            </div>

            {{-- Rows container --}}
            <div id="tag-rows" class="divide-y divide-gray-800/40 px-5"></div>
        </div>

        {{-- ── Submit ──────────────────────────────────────────────────────── --}}
        <div class="flex justify-end">
            <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl
                           transition-colors shadow-lg shadow-indigo-500/20">
                Crear tags
            </button>
        </div>
    </form>

    {{-- Datalist for category suggestions --}}
    <datalist id="cat-suggestions">
        <option value="Hair Color">
        <option value="Hair Length">
        <option value="Eye Color">
        <option value="Skin Tone">
        <option value="Gender">
        <option value="Body Type">
        <option value="Features">
        <option value="Accessories">
        <option value="General">
    </datalist>

    @push('scripts')
    <script>
    (function () {
        const rowsContainer = document.getElementById('tag-rows');
        const globalType    = document.getElementById('global-type');
        const globalCat     = document.getElementById('global-category');
        let idx = 0;

        function currentType()     { return globalType.value; }
        function currentCategory() { return globalCat.value.trim(); }

        function createRow(name = '', category = '') {
            const cat  = category || currentCategory();
            const type = currentType();
            const i    = idx++;

            const row = document.createElement('div');
            row.className = 'tag-row grid gap-3 py-2.5 items-center';
            row.style.gridTemplateColumns = '1fr 1fr 2rem';

            row.innerHTML = `
                <input type="text"
                       name="tags[${i}][name]"
                       value="${esc(name)}"
                       placeholder="Tag name…"
                       class="bg-black/40 border border-gray-700 text-white rounded-xl px-3 py-2 text-sm
                              focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors w-full"
                       autocomplete="off">
                <div class="flex gap-1.5">
                    <input type="text"
                           name="tags[${i}][category]"
                           value="${esc(cat)}"
                           list="cat-suggestions"
                           placeholder="Category…"
                           class="row-cat flex-1 bg-black/40 border border-gray-700 text-white rounded-xl px-3 py-2 text-sm
                                  focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors w-full"
                           autocomplete="off">
                    <input type="hidden" name="tags[${i}][type]" value="${esc(type)}" class="row-type">
                </div>
                <button type="button"
                        onclick="removeRow(this)"
                        class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-600
                               hover:text-red-400 hover:bg-red-500/10 transition-colors text-lg leading-none">
                    ×
                </button>`;

            rowsContainer.appendChild(row);
            return row;
        }

        window.removeRow = function (btn) {
            const rows = rowsContainer.querySelectorAll('.tag-row');
            if (rows.length <= 1) return;
            btn.closest('.tag-row').remove();
        };

        document.getElementById('btn-add-row').addEventListener('click', () => {
            const row = createRow();
            row.querySelector('input[type="text"]').focus();
        });

        document.getElementById('btn-add-5').addEventListener('click', () => {
            for (let i = 0; i < 5; i++) createRow();
        });

        document.getElementById('btn-apply-cat').addEventListener('click', () => {
            const cat = currentCategory();
            rowsContainer.querySelectorAll('.row-cat').forEach(inp => inp.value = cat);
        });

        // Keep hidden type inputs in sync
        globalType.addEventListener('change', () => {
            const t = currentType();
            rowsContainer.querySelectorAll('.row-type').forEach(inp => inp.value = t);
        });

        // Paste handler: split by newline → fill multiple rows
        rowsContainer.addEventListener('paste', function (e) {
            const target = e.target;
            if (!target.name?.includes('[name]')) return;
            const text = (e.clipboardData || window.clipboardData).getData('text');
            const lines = text.split(/\r?\n/).map(l => l.trim()).filter(Boolean);
            if (lines.length <= 1) return;
            e.preventDefault();
            target.value = lines[0];
            for (let i = 1; i < lines.length; i++) createRow(lines[i]);
        });

        function esc(str) {
            return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;');
        }

        // Start with 5 blank rows
        for (let i = 0; i < 5; i++) createRow();
    })();
    </script>
    @endpush

</x-admin-layout>
