<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explorador de Personajes</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0b111a;
            --card-bg: #151d29;
            --accent: #f59e0b; /* Amber para personajes */
            --accent-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --text-main: #ffffff;
            --text-dim: #94a3b8;
            --border: rgba(255, 255, 255, 0.08);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            margin: 0; padding: 0; min-height: 100vh;
        }

        .header {
            padding: 60px 20px;
            text-align: center;
            background: radial-gradient(circle at top right, rgba(245, 158, 11, 0.1), transparent);
        }

        h1 { font-size: 3rem; font-weight: 800; margin-bottom: 20px; }
        h1 span { background: var(--accent-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        .search-bar { max-width: 500px; margin: 0 auto 50px; }
        .search-bar input {
            width: 100%; padding: 18px 25px; border-radius: 40px;
            background: var(--card-bg); border: 1px solid var(--border);
            color: white; font-size: 1.1rem; outline: none; transition: 0.3s;
        }
        .search-bar input:focus { border-color: var(--accent); box-shadow: 0 0 20px rgba(245, 158, 11, 0.2); }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px 100px; }

        .char-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 25px;
        }

        .char-card {
            background: var(--card-bg); border-radius: 20px;
            padding: 20px; text-align: center; border: 1px solid var(--border);
            transition: 0.3s; text-decoration: none; color: inherit;
        }
        .char-card:hover { transform: translateY(-10px); border-color: var(--accent); }
        .char-card img {
            width: 110px; height: 110px; border-radius: 50%;
            object-fit: cover; border: 3px solid var(--accent); margin-bottom: 15px;
        }
        .char-card h3 { font-size: 1.1rem; margin-bottom: 5px; }
        .char-card p { font-size: 0.8rem; color: var(--text-dim); }

        .nav-link { 
            position: fixed; bottom: 30px; left: 30px; 
            padding: 12px 25px; background: rgba(255,255,255,0.05); 
            border-radius: 30px; text-decoration: none; color: white;
            border: 1px solid var(--border); backdrop-filter: blur(10px);
        }
    </style>
</head>
<body>

    <section class="header">
        <h1>Explora <span>Personajes</span></h1>
        <div class="search-bar">
            <form action="{{ route('characters.index') }}" method="GET">
                <input type="text" name="search" value="{{ $search }}" placeholder="Busca un personaje sincronizado...">
            </form>
        </div>
    </section>

    <div class="container">
        <div class="char-grid">
            @foreach($characters as $char)
                <a href="{{ route('characters.show', $char['id']) }}" class="char-card">
                    <img src="{{ $char['image'] ?? 'https://via.placeholder.com/150' }}" alt="">
                    <h3>{{ $char['name'] }}</h3>
                    <p>ID: {{ $char['id'] }}</p>
                </a>
            @endforeach

            @if(empty($characters))
                <div style="grid-column: 1/-1; text-align: center; padding: 100px;">
                    <p style="color: var(--text-dim);">No hay personajes que coincidan con la búsqueda.</p>
                </div>
            @endif
        </div>
    </div>

    <a href="{{ route('neo4j.index') }}" class="nav-link">← Volver al Timeline</a>

</body>
</html>
