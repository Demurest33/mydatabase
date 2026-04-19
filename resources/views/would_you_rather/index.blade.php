<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Would You Rather - AniList</title>
    <style>
        :root {
            --bg: #0b0f19;
            --surface: #151f2e;
            --primary: #3db4f2;
            --primary-hover: #1e9ce1;
            --accent: #c2409f;
            --text: #e5e7eb;
            --text-muted: #9ca3af;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
        }
        .bg-shapes {
            position: absolute; width: 100vw; height: 100vh; z-index: -1; overflow: hidden;
            pointer-events: none;
        }
        .shape {
            position: absolute; border-radius: 50%; filter: blur(100px);
            opacity: 0.5; animation: float 10s infinite alternate linear;
        }
        .shape1 { width: 500px; height: 500px; background: rgba(61, 180, 242, 0.2); top: -200px; left: -100px; }
        .shape2 { width: 600px; height: 600px; background: rgba(194, 64, 159, 0.2); bottom: -200px; right: -100px; }
        
        @keyframes float { 0% { transform: translate(0, 0) scale(1); } 100% { transform: translate(50px, 50px) scale(1.1); } }

        .container {
            background: rgba(21, 31, 46, 0.8);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 3rem;
            border-radius: 20px;
            text-align: center;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp { 0% { opacity: 0; transform: translateY(40px); } 100% { opacity: 1; transform: translateY(0); } }

        h1 {
            font-size: 2.5rem; margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text; color: transparent;
            font-weight: 800; letter-spacing: -0.05em;
        }
        p { color: var(--text-muted); margin-bottom: 2rem; font-size: 1.1rem; }
        
        .form-group { text-align: left; margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-size: 0.9rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; }
        input {
            width: 100%; padding: 1rem 1.2rem;
            background: rgba(11, 15, 25, 0.5);
            border: 1px solid rgba(255,255,255,0.1);
            color: white; border-radius: 12px;
            font-size: 1.1rem; transition: all 0.3s ease;
        }
        input:focus { outline: none; border-color: var(--primary); background: rgba(11, 15, 25, 0.8); box-shadow: 0 0 0 3px rgba(61, 180, 242, 0.2); }
        
        button {
            width: 100%; padding: 1rem;
            background: linear-gradient(135deg, var(--primary), #2982ff);
            color: white; border: none; border-radius: 12px;
            font-size: 1.1rem; font-weight: 600; cursor: pointer;
            transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(61, 180, 242, 0.4);
            display: flex; justify-content: center; align-items: center; gap: 0.5rem;
        }
        button:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(61, 180, 242, 0.6); }
        button:active { transform: translateY(0); }

        .error {
            background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem;
            border: 1px solid rgba(239, 68, 68, 0.2); font-size: 0.95rem;
        }
    </style>
</head>
<body>
    <div class="bg-shapes">
        <div class="shape shape1"></div>
        <div class="shape shape2"></div>
    </div>
    <div class="container">
        <h1>Would You Rather</h1>
        <p>Prepara tu DB Neo4j con tus favoritos de AniList.</p>
        
        @if(session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        <form action="{{ route('wyr.fetch') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="username">AniList Username</label>
                <input type="text" id="username" name="username" placeholder="p. ej. Demurest" required autofocus autocomplete="off">
            </div>
            <button type="submit">
                Sincronizar y Jugar
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
            </button>
        </form>
    </div>
</body>
</html>
