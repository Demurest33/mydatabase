<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $character['name'] }} - Detalle</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0b111a;
            --card-bg: #151d29;
            --accent: #f59e0b;
            --text-main: #ffffff;
            --text-dim: #94a3b8;
            --border: rgba(255, 255, 255, 0.08);
            --glass: rgba(255, 255, 255, 0.03);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg); color: var(--text-main);
            margin: 0; padding: 0; min-height: 100vh;
        }

        .hero-banner {
            height: 350px;
            background: linear-gradient(to bottom, transparent, var(--bg)),
                        radial-gradient(circle at top right, rgba(245, 158, 11, 0.15), transparent);
            position: relative; overflow: hidden;
            display: flex; align-items: flex-end; padding: 0 50px 50px;
        }

        .profile-container { display: flex; align-items: center; gap: 40px; z-index: 10; }
        .profile-img { 
            width: 180px; height: 180px; border-radius: 40px; 
            border: 4px solid var(--accent); object-fit: cover;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
        }
        .profile-info h1 { font-size: 3.5rem; margin: 0; font-weight: 800; letter-spacing: -2px; }
        .profile-info p { color: var(--accent); font-weight: 600; text-transform: uppercase; letter-spacing: 2px; }

        .container { max-width: 1200px; margin: 0 auto; padding: 40px 20px 100px; display: grid; grid-template-columns: 2fr 1fr; gap: 40px; }

        .section-title { font-size: 1.5rem; font-weight: 800; margin-bottom: 25px; display: flex; align-items: center; gap: 15px; }
        .section-title::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        /* Media Cards */
        .media-grid { display: grid; gap: 20px; }
        .media-card {
            background: var(--card-bg); border-radius: 20px; border: 1px solid var(--border);
            display: flex; overflow: hidden; align-items: center; padding-right: 20px;
        }
        .media-card img { width: 100px; height: 140px; object-fit: cover; }
        .media-card-info { padding: 20px; flex: 1; }
        .media-card-info h4 { margin: 0 0 5px; font-size: 1.1rem; }
        .media-card-info p { margin: 0; color: var(--text-dim); font-size: 0.85rem; }

        /* Assets Section */
        .asset-container { background: var(--card-bg); border-radius: 24px; border: 1px solid var(--border); padding: 30px; }
        .asset-list { display: grid; gap: 15px; margin-top: 25px; }
        .asset-item {
            background: var(--glass); border: 1px solid var(--border); border-radius: 15px;
            padding: 15px; display: flex; align-items: center; gap: 15px;
            text-decoration: none; color: white; transition: 0.3s;
        }
        .asset-item:hover { background: rgba(255,255,255,0.08); border-color: var(--accent); }
        .asset-icon { 
            width: 45px; height: 45px; background: var(--accent); 
            border-radius: 12px; display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 0.75rem;
        }

        /* Upload Form */
        .upload-section { margin-top: 40px; padding-top: 30px; border-top: 1px dashed var(--border); }
        .upload-btn {
            width: 100%; padding: 15px; border-radius: 15px; border: none;
            background: var(--accent-gradient); color: white; font-weight: 800;
            cursor: pointer; transition: 0.3s; margin-top: 15px;
        }
        .upload-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(245, 158, 11, 0.3); }

        form input[type="file"] {
            width: 100%; background: var(--glass); border: 1px dashed var(--border);
            padding: 20px; border-radius: 15px; color: var(--text-dim); margin-bottom: 15px;
        }
        form input[type="text"] {
            width: 100%; background: var(--glass); border: 1px solid var(--border);
            padding: 15px; border-radius: 12px; color: white; margin-bottom: 15px; outline: none;
        }
    </style>
</head>
<body>

    <div class="hero-banner">
        <div class="profile-container">
            <img src="{{ $character['image'] ?? '' }}" alt="" class="profile-img">
            <div class="profile-info">
                <p>Character Profile</p>
                <h1>{{ $character['name'] }}</h1>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Columna Izquierda: Series -->
        <div class="main-content">
            <h3 class="section-title">Series Relacionadas</h3>
            <div class="media-grid">
                @foreach($medias as $m)
                    <div class="media-card">
                        <img src="{{ $m['coverImage'] ?? '' }}" alt="">
                        <div class="media-card-info">
                            <h4>{{ $m['title'] }}</h4>
                            <p>{{ $m['format'] ?? 'N/A' }} • {{ $m['year'] ?? $m['start_year'] ?? 'TBA' }}</p>
                        </div>
                    </div>
                @endforeach

                @if(empty($medias))
                    <p style="color: var(--text-dim);">No se encontraron series vinculadas a este personaje en Neo4j.</p>
                @endif
            </div>
        </div>

        <!-- Columna Derecha: Assets -->
        <div class="sidebar">
            <div class="asset-container">
                <h3 class="section-title" style="margin-bottom: 5px;">Recursos (Assets)</h3>
                <p style="color: var(--text-dim); font-size: 0.8rem; margin-bottom: 20px;">Archivos vinculados en Neo4j</p>

                <div class="asset-list">
                    @foreach($assets as $item)
                        <a href="{{ asset('storage/assets/' . $item['asset']['filename']) }}" target="_blank" class="asset-item">
                            <div class="asset-icon">{{ $item['asset']['type'] }}</div>
                            <div style="flex: 1">
                                <div style="font-weight: 600; font-size: 0.9rem;">{{ $item['asset']['title'] }}</div>
                                <div style="color: var(--text-dim); font-size: 0.7rem;">Desde {{ $item['storage']['name'] ?? 'Local' }}</div>
                            </div>
                        </a>
                    @endforeach

                    @if(empty($assets))
                        <p style="color: var(--text-dim); text-align: center; padding: 20px;">Sin assets vinculados.</p>
                    @endif
                </div>

                <div class="upload-section">
                    <h4 style="margin-bottom: 15px; font-size: 0.9rem;">Cargar Nuevo Asset</h4>
                    <form action="{{ route('characters.assets.store', $character['id']) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="text" name="title" placeholder="Título del archivo (opcional)">
                        <input type="file" name="file" required>
                        <button type="submit" class="upload-btn">Vincular a Personaje</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('characters.index') }}" style="position: fixed; bottom: 30px; left: 30px; padding: 12px 25px; background: var(--card-bg); border-radius: 30px; text-decoration: none; color: white; border: 1px solid var(--border);">← Volver al Listado</a>

</body>
</html>
