<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neo4j Franchise Explorer</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0b0e14;
            --card-bg: #151921;
            --accent: #7451f1;
            --accent-gradient: linear-gradient(135deg, #7451f1 0%, #ba61ff 100%);
            --text-main: #ffffff;
            --text-dim: #94a3b8;
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
            min-height: 100vh;
        }

        .hero {
            padding: 80px 20px 60px;
            background: radial-gradient(circle at top right, rgba(116, 81, 241, 0.15), transparent),
                        radial-gradient(circle at bottom left, rgba(186, 97, 255, 0.1), transparent);
            text-align: center;
        }

        h1 { font-size: 3.5rem; font-weight: 800; margin-bottom: 30px; letter-spacing: -2px; }
        h1 span { background: var(--accent-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        .search-container { max-width: 600px; margin: 0 auto; position: relative; }
        
        select {
            width: 100%; padding: 22px 35px; border-radius: 50px;
            border: 1px solid var(--border); background: var(--glass);
            color: white; font-size: 1.1rem; backdrop-filter: blur(20px);
            transition: all 0.4s ease; outline: none; appearance: none;
            cursor: pointer;
        }
        select:focus {
            border-color: var(--accent); box-shadow: 0 0 40px rgba(116, 81, 241, 0.25);
            transform: scale(1.02);
        }

        .content { max-width: 1100px; margin: 0 auto 100px; padding: 0 20px; }
        
        .section-title { 
            font-size: 1.8rem; margin-top: 60px; margin-bottom: 40px; 
            display: flex; align-items: center; gap: 20px; font-weight: 800;
        }
        .section-title::after { content: ''; flex: 1; height: 1px; background: var(--border); }
        .section-title span { color: var(--accent); font-size: 1.2rem; }

        .timeline-container { position: relative; max-width: 900px; margin: 0 auto; padding: 40px 0; }
        .timeline-container::before {
            content: ''; position: absolute; top: 0; left: 120px;
            height: 100%; width: 2px; background: var(--border);
        }

        .timeline-item { position: relative; padding-left: 170px; margin-bottom: 80px; display: flex; opacity: 0; animation: scaleIn 0.5s forwards; }
        @keyframes scaleIn { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

        .timeline-year { 
            position: absolute; left: 0; width: 90px; text-align: right; 
            top: 30px; font-weight: 800; color: var(--accent); font-size: 1.8rem;
        }
        .timeline-node { 
            position: absolute; left: 115px; top: 40px;
            width: 12px; height: 12px; border-radius: 50%;
            background: var(--accent); box-shadow: 0 0 15px var(--accent);
            z-index: 2;
        }

        /* LOCAL SLIDING TABS CARD */
        .timeline-card {
            background: var(--card-bg); border-radius: 20px;
            border: 1px solid var(--border); overflow: hidden;
            display: flex; flex-direction: row; align-items: stretch;
            width: 100%; height: 420px; /* Altura controlada */
            transition: all 0.3s;
        }
        
        .sliding-panel {
            position: relative;
            flex: 1;
            min-width: 60px;
            cursor: pointer;
            transition: all 0.6s cubic-bezier(0.25, 1, 0.5, 1);
            overflow: hidden;
            display: flex;
            flex-direction: row;
        }

        .sliding-panel.active {
            flex: 10;
            cursor: default;
        }

        /* Panel de Info */
        .panel-info { border-right: 1px solid var(--border); background: var(--card-bg); }
        .panel-characters { background: #1a1f29; }

        /* Background for collapsed stage */
        .panel-collapsed-bg {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            opacity: 0.15;
            z-index: 1;
            filter: grayscale(1) blur(2px);
            transition: opacity 0.4s;
        }
        .sliding-panel.active .panel-collapsed-bg { opacity: 0; }

        /* Vertical label */
        .panel-label {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-90deg);
            white-space: nowrap;
            font-weight: 800;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--accent);
            z-index: 5;
            pointer-events: none;
            width: 200px;
            text-align: center;
        }
        .sliding-panel.active .panel-label { opacity: 0; }

        /* Inner Content */
        .panel-content {
            position: relative;
            z-index: 10;
            opacity: 0;
            visibility: hidden;
            width: 100%;
            height: 100%;
            display: flex;
            transition: opacity 0.4s;
        }
        .sliding-panel.active .panel-content {
            opacity: 1;
            visibility: visible;
            transition-delay: 0.3s;
        }

        /* Styles for Info Content */
        .info-cover { width: 220px; flex-shrink: 0; }
        .info-cover img { width: 100%; height: 100%; object-fit: cover; }
        .info-body { padding: 30px; flex: 1; overflow-y: auto; }

        /* Styles for characters content */
        .char-grid { 
            padding: 30px; 
            width: 100%;
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); 
            gap: 20px;
            overflow-y: auto;
        }

        .char-item { text-align: center; }
        .char-item img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent); margin-bottom: 10px; }
        .char-item p { font-size: 0.75rem; font-weight: 700; margin-bottom: 2px; }
        .char-item span { font-size: 0.6rem; color: var(--accent); text-transform: uppercase; font-weight: 800; }

        .status-tag { 
            display: inline-block; padding: 5px 12px; border-radius: 6px; 
            font-size: 0.75rem; font-weight: 800; text-transform: uppercase;
            background: rgba(116, 81, 241, 0.15); color: var(--accent); border: 1px solid rgba(116, 81, 241, 0.3); margin-bottom: 15px;
        }
        
        .timeline-desc {
            font-size: 0.9rem; color: var(--text-dim); margin-top: 10px;
            line-height: 1.5;
        }

        .grid-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 25px; }
        .grid-card {
            background: var(--card-bg); border-radius: 16px; padding: 12px;
            border: 1px solid var(--border); transition: all 0.3s;
            display: flex; flex-direction: column;
        }
        .grid-card:hover { transform: translateY(-8px); border-color: var(--accent); }
        .grid-card img { width: 100%; aspect-ratio: 2/3; object-fit: cover; border-radius: 10px; margin-bottom: 12px; }
        .grid-card h4 { font-size: 0.95rem; font-weight: 600; margin-bottom: 5px; height: 2.7em; overflow: hidden; }
        .grid-meta { font-size: 0.8rem; color: var(--text-dim); display: flex; justify-content: space-between; margin-top: auto; padding-top: 10px; border-top: 1px solid var(--border); }
        
        .tag-source { color: #facc15; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; margin-bottom: 5px; display: block;}
        .tag-other { color: #f472b6; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; margin-bottom: 5px; display: block;}
    </style>
</head>
<body>

    <section class="hero">
        <h1>Neo4j <span>Timeline</span></h1>
        <div class="search-container">
            <form action="{{ route('neo4j.index') }}" method="GET" id="franchise-form">
                <select name="franchise" onchange="document.getElementById('franchise-form').submit()">
                    <option value="">Cargar desde Neo4j...</option>
                    @foreach($franchises ?? [] as $f)
                        <option value="{{ $f }}" {{ $search == $f ? 'selected' : '' }}>{{ $f }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </section>

    <div class="content">
        @if(isset($franchiseData) && !empty($franchiseData['root']))
            
            <div style="text-align: center; margin-bottom: 60px; padding: 40px; background: radial-gradient(circle at center, rgba(116,81,241,0.08), transparent); border-radius: 40px; border: 1px dashed var(--border);">
                <h2 style="font-size: 2.5rem; margin-bottom: 10px;">{{ $franchiseData['root']['title']['romaji'] }}</h2>
                <p style="color: var(--text-dim);">Reconstrucción desde base de datos de grafos.</p>
            </div>

            <!-- Material Original -->
            @if(count($franchiseData['source']) > 0)
            <div class="franchise-section">
                <h3 class="section-title">Material Original</h3>
                <div class="grid-container">
                    @foreach($franchiseData['source'] as $item)
                        <div class="grid-card">
                            <span class="tag-source">{{ $item['format'] }}</span>
                            <img src="{{ $item['coverImage']['large'] }}" alt="">
                            <h4>{{ $item['title']['romaji'] }}</h4>
                            <div class="grid-meta"><span>Lanzamiento</span><b>{{ $item['startDate']['year'] ?? 'TBA' }}</b></div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Vertical Timeline with Internal Sliding Tabs -->
            @if(count($franchiseData['timeline']) > 0)
            <div class="franchise-section">
                <h3 class="section-title">Cronología <span>(Tabs deslizable dentro de cada item)</span></h3>
                <div class="timeline-container">
                    @foreach($franchiseData['timeline'] as $item)
                        <div class="timeline-item">
                            <div class="timeline-year">{{ $item['startDate']['year'] ?? 'TBA' }}</div>
                            <div class="timeline-node"></div>
                            
                            <div class="timeline-card">
                                <!-- PANEL 1: INFO -->
                                <div class="sliding-panel panel-info active">
                                    <div class="panel-collapsed-bg" style="background-image: url('{{ $item['coverImage']['large'] }}')"></div>
                                    <div class="panel-label">Temporada</div>
                                    <div class="panel-content">
                                        <div class="info-cover"><img src="{{ $item['coverImage']['large'] }}" alt=""></div>
                                        <div class="info-body">
                                            <span class="status-tag">{{ $item['format'] }} • {{ $item['status'] }}</span>
                                            <h3 style="font-size: 1.5rem; margin-bottom: 10px;">{{ $item['title']['romaji'] }}</h3>
                                            @if(!empty($item['description']))
                                                <div class="timeline-desc">{!! strip_tags($item['description']) !!}</div>
                                            @endif
                                            <div style="margin-top: 20px; font-size: 0.85rem; color: var(--text-dim);">
                                                @if(!empty($item['studios']['nodes']))
                                                    Estudio: <b style="color: white;">{{ implode(', ', array_column($item['studios']['nodes'], 'name')) }}</b>
                                                @endif
                                                <div style="margin-top: 10px; display: flex; gap: 5px; flex-wrap: wrap;">
                                                    @foreach($item['genres'] ?? [] as $genre)
                                                        <span style="font-size: 0.65rem; padding: 2px 8px; background: rgba(255,255,255,0.05); border-radius: 4px;">{{ $genre }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- PANEL 2: CHARACTERS -->
                                <div class="sliding-panel panel-characters">
                                    <div class="panel-collapsed-bg" style="background-image: url('{{ $item['coverImage']['large'] }}')"></div>
                                    <div class="panel-label">Personajes</div>
                                    <div class="panel-content">
                                        <div class="char-grid">
                                            @foreach($item['characters']['edges'] as $edge)
                                                <div class="char-item">
                                                    <img src="{{ $edge['node']['image']['large'] ?? '' }}" alt="">
                                                    <p>{{ $edge['node']['name']['full'] ?? 'N/A' }}</p>
                                                    <span>{{ $edge['role'] }}</span>
                                                </div>
                                            @endforeach
                                            @if(empty($item['characters']['edges']))
                                                <p style="grid-column: 1/-1; text-align: center; padding-top: 50px; color: var(--text-dim);">No hay personajes sincronizados.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Especiales -->
            @if(count($franchiseData['others']) > 0)
            <div class="franchise-section">
                <h3 class="section-title">Especiales & Spin-offs</h3>
                <div class="grid-container">
                    @foreach($franchiseData['others'] as $item)
                        <div class="grid-card">
                            <span class="tag-other">{{ $item['format'] }}</span>
                            <img src="{{ $item['coverImage']['large'] }}" alt="">
                            <h4>{{ $item['title']['romaji'] }}</h4>
                            <div class="grid-meta"><span>Lanzamiento</span><b>{{ $item['startDate']['year'] ?? 'TBA' }}</b></div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        @elseif($search)
            <div style="text-align: center; padding: 100px 20px; background: var(--card-bg); border-radius: 32px; border: 1px solid var(--border);">
                <p style="font-size: 1.5rem; margin-bottom: 10px; color: white;">Cargando "{{ $search }}"</p>
            </div>
        @else
           <div style="text-align: center; padding: 100px 20px; color: var(--text-dim);">
                <p style="font-size: 1.2rem;">Selecciona una franquicia para ver sus cronología con tabs internas.</p>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.timeline-card').forEach(card => {
                const panels = card.querySelectorAll('.sliding-panel');
                panels.forEach(panel => {
                    panel.addEventListener('click', () => {
                        panels.forEach(p => p.classList.remove('active'));
                        panel.classList.add('active');
                    });
                });
            });
        });
    </script>

</body>
</html>


</body>
</html>

