<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AniList Franchise Explorer</title>
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
        .search-container input {
            width: 100%; padding: 22px 35px; border-radius: 50px;
            border: 1px solid var(--border); background: var(--glass);
            color: white; font-size: 1.1rem; backdrop-filter: blur(20px);
            transition: all 0.4s ease; outline: none;
        }
        .search-container input:focus {
            border-color: var(--accent); box-shadow: 0 0 40px rgba(116, 81, 241, 0.25);
            transform: scale(1.02);
        }

        .content { max-width: 1100px; margin: 0 auto 100px; padding: 0 20px; }
        
        /* Roots & Grids */
        .section-title { 
            font-size: 1.8rem; margin-top: 60px; margin-bottom: 40px; 
            display: flex; align-items: center; gap: 20px; font-weight: 800;
        }
        .section-title::after { content: ''; flex: 1; height: 1px; background: var(--border); }
        .section-title span { color: var(--accent); font-size: 1.2rem; }

        /* VTimeline Styles */
        .timeline-container { position: relative; max-width: 800px; margin: 0 auto; padding: 40px 0; }
        .timeline-container::before {
            content: ''; position: absolute; top: 0; left: 120px;
            height: 100%; width: 2px; background: var(--border);
        }
        .timeline-item { position: relative; padding-left: 170px; margin-bottom: 60px; display: flex; opacity: 0; animation: scaleIn 0.5s forwards; }
        .timeline-item:nth-child(even) { animation-delay: 0.1s; }
        @keyframes scaleIn { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

        .timeline-year { 
            position: absolute; left: 0; width: 90px; text-align: right; 
            top: 25px; font-weight: 800; color: var(--accent); font-size: 1.5rem;
        }
        .timeline-node { 
            position: absolute; left: 115px; top: 32px;
            width: 12px; height: 12px; border-radius: 50%;
            background: var(--accent); box-shadow: 0 0 15px var(--accent);
            z-index: 2;
        }

        .timeline-card {
            background: var(--card-bg); border-radius: 20px;
            border: 1px solid var(--border); overflow: hidden;
            display: flex; flex-direction: row; align-items: stretch;
            width: 100%; transition: all 0.3s;
        }
        .timeline-card:hover { transform: translateY(-5px); border-color: var(--accent); box-shadow: 0 20px 40px rgba(0,0,0,0.3); }

        .timeline-cover { width: 140px; flex-shrink: 0; position: relative; }
        .timeline-cover img { width: 100%; height: 100%; object-fit: cover; }
        .timeline-cover::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(to right, transparent, var(--card-bg));
        }

        .timeline-info { padding: 25px; flex-grow: 1; }
        .timeline-info h3 { font-size: 1.4rem; font-weight: 700; margin-bottom: 8px; line-height: 1.2; }
        .status-tag { 
            display: inline-block; padding: 4px 10px; border-radius: 6px; 
            font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
            background: rgba(255,255,255,0.05); color: var(--text-dim); margin-bottom: 10px;
            border: 1px solid var(--border);
        }
        .timeline-desc {
            font-size: 0.9rem; color: var(--text-dim); margin-top: 10px;
            display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;
            overflow: hidden; line-height: 1.5;
        }
        .stat-group { display: flex; gap: 20px; font-size: 0.85rem; color: var(--text-dim); margin-top: 15px; }
        .stat-group b { color: white; margin-left: 5px; }

        /* Generic Grid (for Source & Others) */
        .grid-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 25px; }
        .grid-card {
            background: var(--card-bg); border-radius: 16px; padding: 12px;
            border: 1px solid var(--border); transition: all 0.3s;
            display: flex; flex-direction: column;
        }
        .grid-card:hover { transform: translateY(-8px); border-color: var(--accent); background: rgba(255,255,255,0.04); }
        .grid-card img { width: 100%; aspect-ratio: 2/3; object-fit: cover; border-radius: 10px; margin-bottom: 12px; }
        .grid-card h4 { font-size: 0.95rem; font-weight: 600; margin-bottom: 5px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 2.7em; }
        .grid-meta { font-size: 0.8rem; color: var(--text-dim); display: flex; justify-content: space-between; margin-top: auto; padding-top: 10px; border-top: 1px solid var(--border); }
        
        .tag-source { color: #facc15; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; margin-bottom: 5px; display: block;}
        .tag-other { color: #f472b6; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; margin-bottom: 5px; display: block;}

        @media (max-width: 768px) {
            .timeline-item { padding-left: 70px; flex-direction: column; }
            .timeline-container::before { left: 40px; }
            .timeline-node { left: 35px; top: 10px; }
            .timeline-year { top: -25px; left: 60px; text-align: left; width: auto; font-size: 1.1rem; }
            .timeline-card { flex-direction: column; }
            .timeline-cover { width: 100%; height: 200px; }
            .timeline-cover::after { background: linear-gradient(to top, var(--card-bg), transparent); }
        }
    </style>
</head>
<body>

    <section class="hero">
        <h1>Franchise <span>Timeline</span></h1>
        <div class="search-container">
            <form action="{{ route('anilist.index') }}" method="GET">
                <input type="text" name="search" placeholder="Escribe el nombre de tu serie..." value="{{ $search }}" autocomplete="off">
            </form>
        </div>
    </section>

    <div class="content">
        @if(isset($franchiseData) && !empty($franchiseData['root']))
            
            <div style="text-align: center; margin-bottom: 60px; padding: 40px; background: radial-gradient(circle at center, rgba(116,81,241,0.08), transparent); border-radius: 40px; border: 1px dashed var(--border);">
                <h2 style="font-size: 2.5rem; margin-bottom: 10px;">{{ $franchiseData['root']['title']['romaji'] }}</h2>
                <p style="color: var(--text-dim);">Franquicia completa mapeada y ordenada cronológicamente.</p>
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

            <!-- Timeline Principal -->
            @if(count($franchiseData['timeline']) > 0)
            <div class="franchise-section">
                <h3 class="section-title">Cronología de la Serie <span>(Watch Order)</span></h3>
                <div class="timeline-container">
                    @foreach($franchiseData['timeline'] as $item)
                        <div class="timeline-item">
                            <div class="timeline-year">{{ $item['startDate']['year'] ?? 'TBA' }}</div>
                            <div class="timeline-node"></div>
                            <div class="timeline-card">
                                <div class="timeline-cover">
                                    <img src="{{ $item['coverImage']['large'] }}" alt="">
                                </div>
                                <div class="timeline-info">
                                    <span class="status-tag">{{ $item['format'] }} • {{ $item['status'] }}</span>
                                    <h3>{{ $item['title']['romaji'] }}</h3>
                                    
                                    @if(!empty($item['description']))
                                        <div class="timeline-desc">
                                            {!! strip_tags($item['description']) !!}
                                        </div>
                                    @endif
                                    
                                    <div class="stat-group">
                                        @if(isset($item['season']) && isset($item['seasonYear']))
                                            <div>Temporada: <b>{{ $item['season'] }} {{ $item['seasonYear'] }}</b></div>
                                        @endif
                                        @if(isset($item['averageScore']))
                                            <div>Puntuación: <b>{{ $item['averageScore'] }}%</b></div>
                                        @endif
                                    </div>
                                    <div style="display: flex; gap: 8px; margin-top: 15px; flex-wrap: wrap;">
                                    @foreach($item['genres'] ?? [] as $genre)
                                        <span style="font-size: 0.7rem; padding: 3px 8px; background: rgba(255,255,255,0.05); border-radius: 4px; color: var(--text-dim);">{{ $genre }}</span>
                                    @endforeach
                                    </div>
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
                <h3 class="section-title">Multiverso / Spin-offs <span>(Fuera de canon o paralelos)</span></h3>
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
                <p style="font-size: 1.5rem; margin-bottom: 10px; color: white;">No pudimos mapear la franquicia de "{{ $search }}"</p>
                <p style="color: var(--text-dim);">Intenta con un nombre exacto (Ej: "Fate Zero" o "Naruto").</p>
            </div>
        @else
           <div style="text-align: center; padding: 100px 20px; color: var(--text-dim);">
                <p style="font-size: 1.2rem;">Utiliza la barra superior para generar el hilo cronológico.</p>
            </div>
        @endif
    </div>

</body>
</html>
