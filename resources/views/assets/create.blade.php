<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargar Recurso Centralizado</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0b111a;
            --card-bg: #151d29;
            --accent: #10b981; /* Emerald para la creación global */
            --accent-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --text-main: #ffffff;
            --text-dim: #94a3b8;
            --border: rgba(255, 255, 255, 0.08);
            --glass: rgba(255, 255, 255, 0.03);
        }

        body {
            font-family: 'Outfit', sans-serif; background-color: var(--bg);
            color: var(--text-main); margin: 0; padding: 0; min-height: 100vh;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }

        .container {
            width: 100%; max-width: 800px;
            background: var(--card-bg); border-radius: 30px;
            border: 1px solid var(--border); padding: 50px;
            box-shadow: 0 40px 100px rgba(0,0,0,0.5);
            margin: 40px 0;
        }

        h1 { font-size: 2.5rem; font-weight: 800; margin-top: 0; margin-bottom: 30px; text-align: center; }
        h1 span { background: var(--accent-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        .tabs { display: flex; gap: 15px; margin-bottom: 30px; }
        .tab-btn {
            flex: 1; padding: 15px; border-radius: 15px; font-size: 1rem; font-weight: 700;
            cursor: pointer; border: 1px solid var(--border); background: var(--glass);
            color: var(--text-dim); transition: 0.3s;
        }
        .tab-btn.active { background: var(--accent); color: white; border-color: var(--accent); }

        /* Drag & Drop Zone */
        .drop-zone {
            border: 2px dashed var(--border); border-radius: 20px;
            padding: 60px 20px; text-align: center; background: rgba(0,0,0,0.2);
            transition: 0.3s; cursor: pointer; margin-bottom: 20px;
        }
        .drop-zone.dragover { border-color: var(--accent); background: rgba(16, 185, 129, 0.05); }
        .drop-zone input[type="file"] { display: none; }
        .drop-zone p { font-size: 1.1rem; color: var(--text-dim); margin-bottom: 10px; }
        .drop-zone span { color: var(--accent); font-weight: 700; }

        input[type="text"] {
            width: 100%; padding: 18px 20px; border-radius: 15px;
            background: rgba(0,0,0,0.2); border: 1px solid var(--border);
            color: white; font-size: 1rem; margin-bottom: 20px; outline: none;
            transition: 0.3s;
        }
        input[type="text"]:focus { border-color: var(--accent); }

        /* Character Selector */
        .char-section {
            background: var(--glass); border: 1px solid var(--border);
            border-radius: 20px; padding: 25px; margin-top: 30px;
        }
        
        .filters { display: flex; gap: 15px; margin-bottom: 20px; }
        .filters select, .filters input {
            flex: 1; padding: 12px 15px; border-radius: 12px; font-size: 0.9rem;
            background: rgba(0,0,0,0.3); border: 1px solid var(--border); color: white; outline: none;
        }

        .char-list {
            max-height: 250px; overflow-y: auto; scrollbar-width: thin;
            display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px;
        }

        .char-select-label {
            display: flex; align-items: center; gap: 12px; padding: 10px;
            background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px;
            cursor: pointer; transition: 0.2s;
        }
        .char-select-label:hover { border-color: var(--accent); }
        .char-select-label img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; }
        .char-info { flex: 1; overflow: hidden; }
        .char-info p { margin: 0; font-size: 0.85rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .char-info span { font-size: 0.65rem; color: var(--accent); font-weight: 800; }

        .submit-btn {
            width: 100%; padding: 20px; border-radius: 20px; border: none;
            background: var(--accent-gradient); color: white; font-size: 1.2rem; font-weight: 800;
            cursor: pointer; transition: 0.3s; margin-top: 30px;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
        }
        .submit-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(16, 185, 129, 0.4); }

        .nav-link { position: fixed; top: 30px; left: 30px; color: var(--text-dim); text-decoration: none; font-weight: 600; }
        .nav-link:hover { color: white; }
    </style>
</head>
<body>

    <a href="{{ route('characters.index') }}" class="nav-link">← Volver a Personajes</a>

    <div class="container">
        <h1>Centro de <span>Recursos</span></h1>

        @if(session('success'))
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--accent); color: var(--accent); padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; font-weight: 600;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; font-weight: 600;">
                {{ session('error') }}
            </div>
        @endif

        <div class="tabs">
            <button class="tab-btn active" id="btn-file" onclick="switchTab('file')">Subir Archivo</button>
            <button class="tab-btn" id="btn-url" onclick="switchTab('url')">Vincular Enlace</button>
        </div>

        <form action="{{ route('assets.store') }}" method="POST" enctype="multipart/form-data" id="assetForm">
            @csrf
            
            <input type="text" name="title" placeholder="Título opcional del recurso (ej. Arte Conceptual)">

            <!-- File Upload Area -->
            <div id="file-area">
                <div class="drop-zone" id="drop-zone">
                    <p>Arrastra tu archivo aquí o <span>haz clic para buscar</span></p>
                    <div id="file-name" style="font-size: 0.85rem; margin-top: 10px;">Ningún archivo seleccionado</div>
                    <input type="file" name="file" id="file-input">
                </div>
            </div>

            <!-- URL Area -->
            <div id="url-area" style="display: none;">
                <input type="text" name="url" placeholder="https://ejemplo.com/doc.pdf">
            </div>

            <!-- Progress Bar Area -->
            <div id="progress-container" style="display: none; margin-top: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span id="status-text" style="font-size: 0.85rem; color: var(--text-dim); font-weight: 700;">Subiendo archivo...</span>
                    <span id="percent-text" style="font-size: 0.85rem; color: var(--accent); font-weight: 800;">0%</span>
                </div>
                <div style="width: 100%; height: 10px; background: rgba(0,0,0,0.3); border-radius: 5px; overflow: hidden; border: 1px solid var(--border);">
                    <div id="progress-bar" style="width: 0%; height: 100%; background: var(--accent-gradient); transition: width 0.3s; box-shadow: 0 0 10px rgba(16, 185, 129, 0.4);"></div>
                </div>
            </div>

            <!-- Character Selection -->
            <div class="char-section">
                <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 1.2rem;">¿A quién pertenece este recurso?</h3>
                
                <div class="filters">
                    <select id="franchise-filter">
                        <option value="ALL">Todas las franquicias</option>
                        @foreach($franchises as $f)
                            <option value="{{ $f }}">{{ $f }}</option>
                        @endforeach
                    </select>
                    <input type="text" id="char-search" placeholder="Buscar por nombre...">
                </div>

                <div class="char-list" id="char-list">
                    @foreach($characters as $char)
                        <label class="char-select-label" data-name="{{ strtolower($char['name']) }}" data-franchises="{{ implode(',', $char['franchises']) }}" data-main="{{ $char['isMain'] }}">
                            <input type="checkbox" name="characters[]" value="{{ $char['id'] }}" style="accent-color: var(--accent);">
                            <img src="{{ $char['image'] ?? 'https://via.placeholder.com/40' }}" alt="">
                            <div class="char-info">
                                <p title="{{ $char['name'] }}">{{ $char['name'] }}</p>
                                @if($char['isMain'])
                                    <span>PRINCIPAL</span>
                                @else
                                    <span style="color: var(--text-dim);">SECUNDARIO</span>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="submit-btn" id="submit-btn">Guardar Recurso Global</button>
        </form>
    </div>

    <script>
        // Tab Switching
        function switchTab(type) {
            document.getElementById('file-area').style.display = type === 'file' ? 'block' : 'none';
            document.getElementById('url-area').style.display = type === 'url' ? 'block' : 'none';
            document.getElementById('btn-file').className = 'tab-btn ' + (type === 'file' ? 'active' : '');
            document.getElementById('btn-url').className = 'tab-btn ' + (type === 'url' ? 'active' : '');
        }

        // Drag & Drop
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-input');
        const fileName = document.getElementById('file-name');

        dropZone.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileName.textContent = this.files[0].name;
                fileName.style.color = 'var(--accent)';
            }
        });

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                fileName.textContent = e.dataTransfer.files[0].name;
                fileName.style.color = 'var(--accent)';
            }
        });

        // Filtering
        const searchInput = document.getElementById('char-search');
        const franchiseSelect = document.getElementById('franchise-filter');
        const charLabels = document.querySelectorAll('.char-select-label');

        function filterList() {
            const term = searchInput.value.toLowerCase();
            const franchise = franchiseSelect.value;

            charLabels.forEach(label => {
                const name = label.getAttribute('data-name');
                const franchises = label.getAttribute('data-franchises').split(',');
                
                const matchesSearch = name.includes(term);
                const matchesFranchise = franchise === 'ALL' || franchises.includes(franchise);

                if (matchesSearch && matchesFranchise) {
                    label.style.display = 'flex';
                } else {
                    label.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('input', filterList);
        franchiseSelect.addEventListener('change', filterList);

        // Form submission with Progress tracking
        document.getElementById('assetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const checked = document.querySelectorAll('input[name="characters[]"]:checked');
            if (checked.length === 0) {
                alert('Debes seleccionar al menos un personaje al que pertenecerá este recurso.');
                return;
            }

            const formData = new FormData(form);
            const xhr = new XMLHttpRequest();

            // Mostrar barra de progreso
            const progressContainer = document.getElementById('progress-container');
            const progressBar = document.getElementById('progress-bar');
            const percentText = document.getElementById('percent-text');
            const statusText = document.getElementById('status-text');
            const submitBtn = document.getElementById('submit-btn');

            progressContainer.style.display = 'block';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';
            submitBtn.innerText = 'Subiendo...';

            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percent + '%';
                    percentText.innerText = percent + '%';
                    if (percent === 100) {
                        statusText.innerText = 'Procesando en el servidor (esto puede tardar)...';
                        statusText.style.color = 'var(--accent)';
                    }
                }
            });

            xhr.addEventListener('load', function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    // Éxito: Recargar para ver mensaje o redirigir
                    window.location.reload();
                } else {
                    alert('Error en la subida: ' + xhr.statusText);
                    console.log('Error en la subida: ' + xhr.statusText);
                    console.log(xhr.responseText);
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                }
            });

            xhr.addEventListener('error', function() {
                alert('Error de red al intentar subir el archivo.');
                console.log('Error de red al intentar subir el archivo.')
                console.log(xhr.responseText);
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
            });

            xhr.open('POST', form.action);
            xhr.send(formData);
        });
    </script>
</body>
</html>
