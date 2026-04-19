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

        .content { max-width: 1200px; margin: 0 auto 100px; padding: 0 20px; }
        
        .section-title { 
            font-size: 1.8rem; margin-top: 60px; margin-bottom: 40px; 
            display: flex; align-items: center; gap: 20px; font-weight: 800;
        }
        .section-title::after { content: ''; flex: 1; height: 1px; background: var(--border); }
        .section-title span { color: var(--accent); font-size: 1.2rem; }

        /* Accordion Container */
        .timeline-accordion {
            display: flex;
            gap: 15px;
            min-height: 600px;
            margin-bottom: 80px;
            align-items: stretch;
            padding: 20px 0;
        }

        .timeline-item {
            position: relative;
            flex: 1;
            min-width: 70px;
            cursor: pointer;
            transition: all 0.7s cubic-bezier(0.25, 1, 0.5, 1);
            border-radius: 30px;
            overflow: hidden;
            border: 1px solid var(--border);
            background: var(--card-bg);
            display: flex;
            flex-direction: column;
        }

        /* ACTIVE STATE */
        .timeline-item.active {
            flex: 15;
            cursor: default;
            border-color: var(--accent);
            box-shadow: 0 30px 60px rgba(0,0,0,0.6);
        }

        /* BACKGROUND FOR COLLAPSED STATE */
        .collapsed-bg {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            z-index: 1;
            transition: opacity 0.5s, filter 0.5s;
        }
        .timeline-item.active .collapsed-bg {
            opacity: 0.08;
            filter: blur(20px);
        }

        /* COLLAPSED TITLE (Vertical) */
        .collapsed-title {
            position: absolute;
            bottom: 50px;
            left: 50%;
            transform: translateX(-50%) rotate(-90deg);
            white-space: nowrap;
            font-weight: 800;
            font-size: 1.2rem;
            color: white;
            z-index: 10;
            pointer-events: none;
            transition: opacity 0.3s;
            text-transform: uppercase;
            letter-spacing: 2px;
            width: 500px;
            text-align: center;
        }
        .timeline-item.active .collapsed-title {
            opacity: 0;
        }

        /* ITEM CONTENT (Visible only when expanded) */
        .item-content {
            position: relative;
            z-index: 20;
            display: flex;
            height: 100%;
            width: 100%;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s;
            overflow: hidden;
        }
        .timeline-item.active .item-content {
            opacity: 1;
            visibility: visible;
            transition-delay: 0.3s;
        }

        .timeline-cover { width: 320px; flex-shrink: 0; position: relative; }
        .timeline-cover img { width: 100%; height: 100%; object-fit: cover; }

        .timeline-info { padding: 40px; flex: 1; overflow-y: auto; display: flex; flex-direction: column; scrollbar-width: thin; }
        .timeline-info::-webkit-scrollbar { width: 6px; }
        .timeline-info::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }
        
        .timeline-info h3 { font-size: 2rem; font-weight: 800; margin-bottom: 15px; line-height: 1.1; display: block; }
        
        .status-tag { 
            display: inline-block; padding: 6px 15px; border-radius: 8px; 
            font-size: 0.8rem; font-weight: 800; text-transform: uppercase;
            background: var(--accent); color: white; margin-bottom: 15px;
        }
        
        .timeline-desc {
            font-size: 1rem; color: var(--text-dim); margin-top: 15px;
            line-height: 1.6; padding-right: 20px;
        }

        .stat-group { display: flex; gap: 30px; font-size: 0.95rem; color: var(--text-dim); margin-top: 25px; flex-wrap: wrap; }
        .stat-group b { color: white; margin-left: 5px; }

        .characters-list { margin-top: 40px; padding-top: 30px; border-top: 1px dashed var(--border); }
        .characters-list h4 { font-size: 0.85rem; color: var(--text-dim); margin-bottom: 20px; text-transform: uppercase; letter-spacing: 2px; font-weight: 800; }
        .character-scroll { display: flex; gap: 20px; overflow-x: auto; padding-bottom: 20px; }
        .character-scroll::-webkit-scrollbar { height: 8px; }
        .character-scroll::-webkit-scrollbar-thumb { background-color: var(--accent); border-radius: 10px; }
        
        .char-card { min-width: 80px; text-align: center; flex-shrink: 0; }
        .char-card img { width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 3px solid transparent; margin-bottom: 10px; transition: transform 0.3s; }
        .char-card.main img { border-color: var(--accent); }
        .char-card:hover img { transform: scale(1.1); }
        .char-card p { font-size: 0.75rem; color: var(--text-main); font-weight: 600; }
        .char-role { font-size: 0.6rem; color: var(--accent); text-transform: uppercase; font-weight: 800; }

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
        <h1>Neo4j <span>Franchise</span></h1>
        <div class="search-container">
            <form action="{{ route('neo4j.index') }}" method="GET" id="franchise-form">
                <select name="franchise" onchange="document.getElementById('franchise-form').submit()">
                    <option value="">Selecciona una franquicia en Neo4j...</option>
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
                <p style="color: var(--text-dim);">Reconstrucción dinámica desde tu base de datos de grafos.</p>
            </div>

            <!-- Material Original -->
            @if(count($franchiseData['source']) > 0)
            <div class="franchise-section">
                <h3 class="section-title">Material Original <span>(Manga / Novela)</span></h3>
                <div class="grid-container">
                    @foreach($franchiseData['source'] as $item)
                        <div class="grid-card">
                            <span class="tag-source">{{ $item['format'] }}</span>
                            <img src="{{ $item['coverImage']['large'] }}" alt="">
                            <h4>{{ $item['title']['romaji'] }}</h4>
                            <div class="grid-meta">
                                <span>Lanzamiento</span>
                                <b>{{ $item['startDate']['year'] ?? 'TBA' }}</b>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Sliding Timeline Accordion -->
            @if(count($franchiseData['timeline']) > 0)
            <div class="franchise-section">
                <h3 class="section-title">Cronología Deslizable <span>(Haz clic para expandir)</span></h3>
                <div class="timeline-accordion">
                    @foreach($franchiseData['timeline'] as $index => $item)
                        <div class="timeline-item {{ $index === 0 ? 'active' : '' }}">
                            <!-- Background for closed state -->
                            <div class="collapsed-bg" style="background-image: url('{{ $item['coverImage']['large'] }}')"></div>
                            
                            <!-- Vertical title for closed state -->
                            <div class="collapsed-title">
                                {{ $item['startDate']['year'] ?? 'TBA' }} — {{ $item['title']['romaji'] }}
                            </div>

                            <!-- Full Content for active state -->
                            <div class="item-content">
                                <div class="timeline-cover">
                                    <img src="{{ $item['coverImage']['large'] }}" alt="">
                                </div>
                                <div class="timeline-info">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                        <span class="status-tag">{{ $item['format'] }} • {{ $item['status'] }}</span>
                                        <div style="font-size: 2rem; font-weight: 800; color: var(--accent); opacity: 0.5;">{{ $item['startDate']['year'] }}</div>
                                    </div>
                                    <h3>{{ $item['title']['romaji'] }}</h3>
                                    
                                    @if(!empty($item['description']))
                                        <div class="timeline-desc">
                                            {!! strip_tags($item['description']) !!}
                                        </div>
                                    @endif
                                    
                                    <div class="stat-group">
                                        @if($item['averageScore'])
                                            <div>Puntuación: <b>{{ $item['averageScore'] }}%</b></div>
                                        @endif
                                        @if(!empty($item['studios']['nodes']))
                                            <div>Estudio: <b>{{ implode(', ', array_column($item['studios']['nodes'], 'name')) }}</b></div>
                                        @endif
                                    </div>

                                    <div style="display: flex; gap: 8px; margin-top: 15px; flex-wrap: wrap;">
                                    @foreach($item['genres'] ?? [] as $genre)
                                        <span style="font-size: 0.75rem; padding: 4px 12px; background: rgba(116, 81, 241, 0.1); border: 1px solid rgba(116, 81, 241, 0.2); border-radius: 6px; color: #a5b4fc;">{{ $genre }}</span>
                                    @endforeach
                                    </div>

                                    @if(!empty($item['characters']['edges']))
                                    <div class="characters-list">
                                        <h4>Cast Sincronizado</h4>
                                        <div class="character-scroll">
                                            @foreach($item['characters']['edges'] as $edge)
                                                <div class="char-card {{ strtolower($edge['role']) }}">
                                                    <img src="{{ $edge['node']['image']['large'] ?? '' }}" alt="">
                                                    <p>{{ $edge['node']['name']['full'] ?? 'N/A' }}</p>
                                                    <span class="char-role">{{ $edge['role'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Otros / Spin-offs -->
            @if(count($franchiseData['others']) > 0)
            <div class="franchise-section">
                <h3 class="section-title">Especiales & Spin-offs</h3>
                <div class="grid-container">
                    @foreach($franchiseData['others'] as $item)
                        <div class="grid-card">
                            <span class="tag-other">{{ $item['format'] }}</span>
                            <img src="{{ $item['coverImage']['large'] }}" alt="">
                            <h4>{{ $item['title']['romaji'] }}</h4>
                            <div class="grid-meta">
                                <span>Lanzamiento</span>
                                <b>{{ $item['startDate']['year'] ?? 'TBA' }}</b>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        @elseif($search)
            <div style="text-align: center; padding: 100px 20px; background: var(--card-bg); border-radius: 32px; border: 1px solid var(--border);">
                <p style="font-size: 1.5rem; margin-bottom: 10px; color: white;">Franquicia "{{ $search }}" disponible</p>
                <p style="color: var(--text-dim);">Haz clic en el selector superior para cargar los datos sincronizados.</p>
            </div>
        @else
           <div style="text-align: center; padding: 100px 20px; color: var(--text-dim);">
                <p style="font-size: 1.2rem;">Selecciona una franquicia sincronizada en Neo4j para ver su cronología deslizable.</p>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const items = document.querySelectorAll('.timeline-item');
            items.forEach(item => {
                item.addEventListener('click', () => {
                    items.forEach(i => i.classList.remove('active'));
                    item.classList.add('active');
                });
            });
        });
    </script>

</body>
</html>

