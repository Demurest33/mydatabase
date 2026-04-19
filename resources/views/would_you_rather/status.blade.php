<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sincronización - Would You Rather</title>
    <style>
        :root {
            --bg: #0b0f19; --surface: #151f2e; --surface-hover: #1c2a3e;
            --primary: #3db4f2; --accent: #c2409f;
            --success: #10b981; --warning: #f59e0b;
            --text: #e5e7eb; --text-muted: #9ca3af;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: var(--bg); color: var(--text); padding: 2rem; }
        
        .header { text-align: center; margin-bottom: 3rem; animation: slideDown 0.5s ease; }
        @keyframes slideDown { from {opacity:0; transform: translateY(-20px)} to{opacity:1; transform: translateY(0)} }
        
        .avatar { width: 80px; height: 80px; border-radius: 50%; border: 3px solid var(--primary); margin-bottom: 1rem; box-shadow: 0 0 20px rgba(61,180,242,0.5); object-fit: cover;}
        h1 { font-size: 2rem; margin-bottom:0.5rem;}
        .text-muted { color: var(--text-muted); }

        .dashboard {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; max-width: 1000px; margin: 0 auto;
        }

        .card {
            background: var(--surface); border-radius: 16px; padding: 1.5rem;
            border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: fadeIn 0.8s ease backwards;
        }
        .card:nth-child(2) { animation-delay: 0.1s; }
        @keyframes fadeIn { from {opacity:0; transform: scale(0.95)} to{opacity:1; transform: scale(1)} }

        .card h2 { font-size: 1.3rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem;}
        
        .stat { display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: rgba(0,0,0,0.2); border-radius: 8px; margin-bottom: 0.5rem; }
        .stat-val { font-size: 1.5rem; font-weight: bold; color: var(--primary); }

        .list { list-style: none; margin-top: 1rem; max-height: 300px; overflow-y: auto; padding-right: 0.5rem; }
        .list::-webkit-scrollbar { width: 6px; }
        .list::-webkit-scrollbar-track { background: rgba(0,0,0,0.1); border-radius: 3px; }
        .list::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 3px; }
        
        .item { display: flex; align-items: center; gap: 1rem; padding: 0.75rem; border-radius: 8px; background: rgba(255,255,255,0.02); margin-bottom: 0.5rem; transition: background 0.2s; }
        .item:hover { background: rgba(255,255,255,0.05); }
        .item img { width: 40px; height: 40px; border-radius: 6px; object-fit: cover; }
        .item-name { font-size: 0.95rem; font-weight: 500; }
        
        .badge-queued { background: rgba(245, 158, 11, 0.2); color: var(--warning); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: bold; margin-left: auto; }

        .actions { text-align: center; margin-top: 3rem; }
        .btn {
            display: inline-block; padding: 1rem 2rem; background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white; text-decoration: none; border-radius: 12px; font-weight: bold; font-size: 1.1rem;
            transition: all 0.3s; box-shadow: 0 4px 15px rgba(194, 64, 159, 0.4);
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(194, 64, 159, 0.6); }

        .empty-state { text-align: center; padding: 2rem; color: var(--success); font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ $user['avatar']['large'] ?? '' }}" alt="Avatar" class="avatar">
        <h1>Hola, {{ $user['name'] }}</h1>
        <p class="text-muted">Hemos revisado tu perfil de AniList.</p>
    </div>

    <div class="dashboard">
        <!-- Resumen -->
        <div class="card">
            <h2>📊 Estado Neo4j</h2>
            <div class="stat">
                <span>Media sincronizados</span>
                <span class="stat-val" style="color: var(--success);">{{ $existingMediaCount }}</span>
            </div>
            <div class="stat">
                <span>Personajes sincronizados</span>
                <span class="stat-val" style="color: var(--success);">{{ $existingCharactersCount }}</span>
            </div>
            <div class="stat">
                <span>Nuevas Franquicias a procesar</span>
                <span class="stat-val" style="color: var(--warning);">{{ count($missingMedia) }}</span>
            </div>
            <div class="stat">
                <span>Personajes pendientes</span>
                <span class="stat-val" style="color: var(--warning);">{{ count($missingCharacters) }}</span>
            </div>
            <p class="text-muted" style="margin-top: 1rem; font-size: 0.85rem; line-height: 1.4;">
                Los personajes pendientes usualmente se importan junto a su respectiva franquicia automáticamente en segundo plano.
            </p>
        </div>

        <!-- Trabajos creados -->
        <div class="card">
            <h2>⚙️ Colas de Sincronización</h2>
            @if(count($queuedJobs) > 0)
                <div class="progress-container" style="margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; font-size: 0.9rem;">
                        <span>Progreso de importación</span>
                        <span id="progress-text" style="color: var(--primary); font-weight: bold;">0 / {{ count($queuedJobs) }} completados</span>
                    </div>
                    <div style="background: rgba(255,255,255,0.1); border-radius: 8px; height: 16px; width: 100%; overflow: hidden; position: relative;">
                        <div id="progress-bar" style="background: linear-gradient(90deg, var(--primary), var(--accent)); height: 100%; width: 0%; transition: width 0.5s ease; box-shadow: 0 0 10px rgba(61, 180, 242, 0.5);"></div>
                    </div>
                </div>
                
                <div id="rate-limit-warning" style="display: none; background: rgba(245, 158, 11, 0.1); border: 1px dashed var(--warning); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; color: var(--warning); animation: pulse 2s infinite;">
                    ⏳ <b>Rate Limit Activo:</b> La API de AniList pide descanso. Se reanudará automáticamente en <span id="rate-limit-seconds" style="font-weight: 800; font-size: 1.1rem;">0</span> segundos...
                </div>

                <p class="text-muted" style="margin-bottom: 1rem; font-size: 0.9rem;">Estas franquicias fueron enviadas al procesador de background <b>(SyncAnimeJob)</b>.</p>
                <ul class="list">
                    @foreach($queuedJobs as $job)
                    <li class="item">
                        <div class="item-name">{{ $job }}</div>
                        <span class="badge-queued">QUEUED</span>
                    </li>
                    @endforeach
                </ul>
            @else
                <div class="empty-state">
                    ✨ ¡Todo parece estar sincronizado! No hay franquicias nuevas.
                </div>
            @endif
        </div>
    </div>

    <div class="actions">
        <!-- disabled btn initally if sync is not complete to prevent broken game expectations maybe? Or just leave it open -->
        <a href="{{ route('wyr.game') }}" class="btn">Empezar Would You Rather</a>
    </div>

    <script>
        const batchId = '{{ isset($batchId) ? $batchId : '' }}';
        if (batchId) {
            function checkProgress() {
                fetch(`/would-you-rather/progress/${batchId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.total > 0) {
                            const done = data.done;
                            const percent = ((done + data.error) / data.total) * 100;
                            
                            document.getElementById('progress-bar').style.width = percent + '%';
                            
                            if (data.error > 0) {
                                document.getElementById('progress-text').innerText = `${done} / ${data.total} completados (${data.error} errores)`;
                            } else {
                                document.getElementById('progress-text').innerText = `${done} / ${data.total} completados`;
                            }
                            
                            if (data.resumeIn > 0) {
                                document.getElementById('rate-limit-warning').style.display = 'block';
                                document.getElementById('rate-limit-seconds').innerText = data.resumeIn;
                            } else {
                                document.getElementById('rate-limit-warning').style.display = 'none';
                            }
                            
                            // Stop if all done
                            if (done + data.error >= data.total) {
                                document.getElementById('progress-text').innerText = `¡Completado! Todo sincronizado.`;
                                document.getElementById('rate-limit-warning').style.display = 'none';
                                return; // Stop polling
                            }
                        }
                        setTimeout(checkProgress, 2000);
                    })
                    .catch(err => {
                        console.error('Error fetching progress:', err);
                        setTimeout(checkProgress, 5000);
                    });
            }
            checkProgress();
        }
    </script>
</body>
</html>
