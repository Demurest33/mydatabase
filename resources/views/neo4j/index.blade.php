<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neo4j Graph Visualization</title>
    <!-- Incluir Vis.js Network -->
    <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0b0e14;
            --card-bg: #151921;
            --accent: #7451f1;
            --text-main: #ffffff;
            --text-dim: #94a3b8;
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            margin: 0; padding: 0;
            display: flex; flex-direction: column;
            height: 100vh; overflow: hidden;
        }

        .header {
            padding: 20px 40px; background: var(--card-bg);
            border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
        }
        .header h1 { font-size: 1.5rem; font-weight: 800; margin: 0; }
        .header h1 span { color: var(--accent); }

        .controls { display: flex; gap: 15px; align-items: center; }
        select {
            padding: 10px 20px; border-radius: 8px; border: 1px solid var(--border);
            background: var(--bg); color: white; font-family: 'Outfit'; outline: none;
            cursor: pointer; font-size: 1rem;
        }
        button {
            padding: 10px 25px; border-radius: 8px; border: none; font-family: 'Outfit';
            background: linear-gradient(135deg, #7451f1 0%, #ba61ff 100%);
            color: white; font-weight: 600; cursor: pointer; transition: transform 0.2s;
        }
        button:hover { transform: scale(1.05); }

        .legend {
            position: absolute; bottom: 30px; right: 30px;
            background: rgba(21, 25, 33, 0.8); backdrop-filter: blur(10px);
            padding: 20px; border-radius: 12px; border: 1px solid var(--border);
            z-index: 10; font-size: 0.9rem;
        }
        .legend-item { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
        .legend-color { width: 14px; height: 14px; border-radius: 50%; }

        #network { flex-grow: 1; width: 100%; background-color: var(--bg); }
        .vis-tooltip { position: absolute; z-index: 100; pointer-events: none; }
        
        .loading {
            position: absolute; inset: 0; background: rgba(11, 14, 20, 0.8);
            display: flex; justify-content: center; align-items: center;
            z-index: 50; display: none; font-size: 2rem; font-weight: 800; color: var(--accent);
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Neo4j <span>Graph API</span></h1>
        <div class="controls">
            @if(isset($error))
                <span style="color: #ef4444;">{{ $error }}</span>
            @endif
            <select id="franchise-select">
                <option value="">Selecciona una Franquicia</option>
                @foreach($franchises ?? [] as $f)
                    <option value="{{ $f }}">{{ $f }}</option>
                @endforeach
            </select>
            <button onclick="loadGraph()">Dibujar Grafo</button>
        </div>
    </div>

    <div style="position: relative; flex-grow: 1;">
        <div class="loading" id="loader">Extrayendo información de Neo4j...</div>
        <div id="network"></div>
        <div class="legend">
            <h4 style="margin: 0 0 10px 0; font-size: 1rem;">Nodos</h4>
            <div class="legend-item"><div class="legend-color" style="background: #e11d48;"></div> Franquicia</div>
            <div class="legend-item"><div class="legend-color" style="background: #7451f1;"></div> Media (Anime/Manga)</div>
            <div class="legend-item"><div class="legend-color" style="background: #f59e0b;"></div> Personaje</div>
            <div class="legend-item"><div class="legend-color" style="background: #10b981;"></div> Estudio</div>
            <div class="legend-item"><div class="legend-color" style="background: #3b82f6;"></div> Género</div>
        </div>
    </div>

    <script>
        let network = null;

        function renderGraph(nodesData, edgesData) {
            const container = document.getElementById('network');
            const data = {
                nodes: new vis.DataSet(nodesData),
                edges: new vis.DataSet(edgesData)
            };
            const options = {
                physics: {
                    stabilization: false,
                    barnesHut: {
                        gravitationalConstant: -8000,
                        springConstant: 0.001,
                        springLength: 200
                    }
                },
                interaction: { hover: true, tooltipDelay: 100 }
            };

            if (network !== null) { network.destroy(); }
            network = new vis.Network(container, data, options);
        }

        async function loadGraph() {
            const franchise = document.getElementById('franchise-select').value;
            if (!franchise) return alert("Selecciona una franquicia primero.");

            document.getElementById('loader').style.display = 'flex';

            try {
                const response = await fetch(`{{ route('neo4j.data') }}?franchise=${encodeURIComponent(franchise)}`);
                const data = await response.json();

                console.log(data);

                if(data.error) {
                    alert("Error consultando Neo4j: " + data.error);
                } else if(data.nodes.length === 0) {
                    alert("No hay datos en Neo4j para esta franquicia. Asegúrate de ejecutar el comando anime:sync primero.");
                } else {
                    renderGraph(data.nodes, data.edges);
                }
            } catch (err) {
                console.error(err);
                alert("Error de red cargando el grafo.");
            } finally {
                document.getElementById('loader').style.display = 'none';
            }
        }
    </script>
</body>
</html>
