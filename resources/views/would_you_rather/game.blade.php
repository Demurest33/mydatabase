<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Would You Rather - Anime</title>
    <style>
        :root {
            --bg: #0b0f19; --surface: #151f2e; --surface-hover: #1c2a3e;
            --primary: #3db4f2; --accent: #c2409f;
            --text: #e5e7eb; --text-muted: #9ca3af;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); padding: 2rem; display: flex; flex-direction: column; align-items: center; min-height: 100vh; overflow-y: auto;}

        h1 { font-size: 2.5rem; text-align: center; margin-bottom: 0.5rem; background: linear-gradient(135deg, var(--primary), var(--accent)); -webkit-background-clip: text; color: transparent; font-weight: 800; }
        .subtitle { text-align: center; color: var(--text-muted); margin-bottom: 3rem; font-size: 1.1rem; }

        .game-area { width: 100%; max-width: 900px; display: flex; flex-direction: column; align-items: center; }
        .options-container { display: flex; gap: 2rem; width: 100%; justify-content: center; flex-wrap: wrap; margin-bottom: 3rem; }

        .option-card {
            background: var(--surface); border-radius: 20px; overflow: hidden; width: 100%; max-width: 320px;
            cursor: pointer; border: 2px solid transparent; transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3); position: relative;
        }
        .option-card:hover { transform: translateY(-10px) scale(1.02); border-color: var(--primary); box-shadow: 0 15px 40px rgba(61, 180, 242, 0.4); }
        .option-card:active { transform: scale(0.98); }

        .option-img-wrapper { width: 100%; height: 400px; overflow: hidden; background: #000; }
        .option-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; opacity: 0.9; }
        .option-card:hover .option-img { transform: scale(1.05); opacity: 1; }

        .option-info { padding: 1.5rem; text-align: center; background: linear-gradient(0deg, var(--surface) 0%, transparent 100%); position: absolute; bottom: 0; width: 100%; backdrop-filter: blur(4px); }
        .option-title { font-weight: bold; font-size: 1.2rem; text-shadow: 0 2px 4px rgba(0,0,0,0.8); }

        .vs-badge { display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 900; color: var(--accent); text-shadow: 0 0 20px rgba(194, 64, 159, 0.6); position: absolute; margin-top: 180px; z-index: 10; pointer-events: none;}

        .btn {
            padding: 1rem 2.5rem; background: rgba(255,255,255,0.05); color: white; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px;
            font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: all 0.3s;
        }
        .btn:hover { background: rgba(239, 68, 68, 0.2); border-color: #ef4444; color: #ef4444; }

        /* Results Area */
        .results-area { width: 100%; max-width: 1000px; display: none; animation: slideUp 0.6s ease; }
        @keyframes slideUp { from {opacity:0; transform: translateY(30px)} to{opacity:1; transform: translateY(0)} }
        
        .results-list { display: flex; flex-direction: column; gap: 2rem; margin-top: 2rem; }
        .result-row { background: var(--surface); border-radius: 20px; padding: 1.5rem; display: flex; align-items: center; justify-content: center; gap: 3rem; border: 1px solid rgba(255,255,255,0.05); }
        
        .result-pair-item { width: 140px; text-align: center; position: relative; }
        .result-pair-item img { width: 100%; height: 200px; object-fit: cover; border-radius: 12px; margin-bottom: 0.5rem; transition: all 0.3s; }
        .result-pair-item span { font-size: 0.9rem; font-weight: 500; display: block; height: 2.5rem; overflow: hidden; }
        
        .result-pair-item.loser { opacity: 0.3; filter: grayscale(1); }
        .result-pair-item.winner img { border: 3px solid var(--primary); box-shadow: 0 0 15px rgba(61,180,242,0.4); }
        .result-pair-item.winner::after { content: "🏆"; position: absolute; top: -10px; right: -10px; font-size: 1.5rem; }

        .result-vs { font-size: 1.2rem; font-weight: 900; color: var(--text-muted); }

        .btn-restart { 
            display: inline-block; padding: 1rem 2.5rem; background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white; border: none; border-radius: 12px; font-weight: bold; font-size: 1.1rem; cursor: pointer; text-decoration: none; margin-top: 3rem; margin-inline: auto;
        }

    </style>
</head>
<body>

    <div id="game-area" class="game-area">
        <h1>Would You Rather</h1>
        <p class="subtitle" id="round-type">Preparando opciones...</p>

        <div class="options-container" style="position: relative;">
            <div class="option-card" onclick="choose(0)">
                <div class="option-img-wrapper">
                    <img id="img0" src="" class="option-img" alt="Option 1">
                </div>
                <div class="option-info">
                    <div class="option-title" id="title0">Cargando...</div>
                </div>
            </div>

            <div class="vs-badge">VS</div>

            <div class="option-card" onclick="choose(1)">
                <div class="option-img-wrapper">
                    <img id="img1" src="" class="option-img" alt="Option 2">
                </div>
                <div class="option-info">
                    <div class="option-title" id="title1">Cargando...</div>
                </div>
            </div>
        </div>

        <button class="btn" style="margin-top: 2rem;" onclick="finishGame()">Terminar y ver resultados</button>
    </div>

    <div id="results-area" class="results-area">
        <h1 style="margin-bottom: 0.5rem;">Tus Decisiones</h1>
        <p class="subtitle" style="margin-bottom: 2rem;">¿A quién elegiste sobre quién?</p>
        
        <div class="results-list" id="results-list">
            <!-- JS injects here -->
        </div>

        <div style="text-align: center; padding: 4rem 0;">
            <a href="{{ route('wyr.index') }}" class="btn-restart">Sincronizar otro perfil o Jugar de nuevo</a>
        </div>
    </div>

    <script>
        let medias = {!! $medias !!};
        let characters = {!! $characters !!};
        let history = []; // Stores objects: { winner, loser, type }
        let currentOptions = [];

        function initGame() {
            if (medias.length < 2 && characters.length < 2) {
                alert("No hay suficientes favoritos sincronizados en Neo4j. Debes esperar a que termine la sincronización.");
                return;
            }
            nextRound();
        }

        function nextRound() {
            // Check if we can still make a pair of at least one type
            const canMedia = medias.length >= 2;
            const canChars = characters.length >= 2;

            if (!canMedia && !canChars) {
                finishGame();
                return;
            }
            
            let poolType = '';
            if (canMedia && canChars) {
                poolType = Math.random() > 0.5 ? 'Media' : 'Character';
            } else if (canMedia) {
                poolType = 'Media';
            } else {
                poolType = 'Character';
            }
            
            let pool = poolType === 'Media' ? medias : characters;
            
            // Pick 2 random from the remaining favorites
            const idx1 = Math.floor(Math.random() * pool.length);
            let item1 = pool.splice(idx1, 1)[0];
            const idx2 = Math.floor(Math.random() * pool.length);
            let item2 = pool.splice(idx2, 1)[0];
            
            currentOptions = [item1, item2];
            
            // Set UI
            document.getElementById('img0').src = item1.image || 'https://via.placeholder.com/300x400/151f2e/3db4f2?text=Sin+Imagen';
            document.getElementById('title0').innerText = item1.title || item1.name;
            
            document.getElementById('img1').src = item2.image || 'https://via.placeholder.com/300x400/151f2e/3db4f2?text=Sin+Imagen';
            document.getElementById('title1').innerText = item2.title || item2.name;
            
            document.getElementById('round-type').innerText = poolType === 'Media' ? '¿Cuál de estos favoritos prefieres?' : '¿Qué personaje te gusta más?';
        }

        function choose(index) {
            const winner = currentOptions[index];
            const loser = currentOptions[1 - index];
            
            history.push({ winner, loser });
            nextRound();
        }

        function finishGame() {
            document.getElementById('game-area').style.display = 'none';
            document.getElementById('results-area').style.display = 'block';
            
            let html = '';
            if(history.length === 0) {
                html = '<p style="text-align: center; color: var(--text-muted);">No completaste ninguna ronda.</p>';
            } else {
                history.forEach(round => {
                    const winName = round.winner.title || round.winner.name;
                    const loseName = round.loser.title || round.loser.name;
                    const winImg = round.winner.image || 'https://via.placeholder.com/150x200/151f2e/3db4f2?text=N/A';
                    const loseImg = round.loser.image || 'https://via.placeholder.com/150x200/151f2e/3db4f2?text=N/A';

                    html += `
                        <div class="result-row">
                            <div class="result-pair-item winner">
                                <img src="${winImg}" alt="${winName}">
                                <span>${winName}</span>
                            </div>
                            <div class="result-vs">SOBRE</div>
                            <div class="result-pair-item loser">
                                <img src="${loseImg}" alt="${loseName}">
                                <span>${loseName}</span>
                            </div>
                        </div>
                    `;
                });
            }
            document.getElementById('results-list').innerHTML = html;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Start!
        initGame();
    </script>
</body>
</html>
