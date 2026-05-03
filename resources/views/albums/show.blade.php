<!DOCTYPE html>
<html lang="es" style="height:100%;overflow:hidden;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $album['name'] }} — Álbum</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%; overflow: hidden;
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
            background: #0e1117; color: #fff;
        }
        body { display: flex; flex-direction: column; }

        /* ── Top bar ── */
        .album-topbar {
            flex-shrink: 0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 22px 0;
            font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.18em;
            color: rgba(255,255,255,0.18);
        }
        .album-topbar a {
            color: rgba(255,255,255,0.3); text-decoration: none; transition: color 0.18s;
            font-size: 10px; letter-spacing: 0.08em;
        }
        .album-topbar a:hover { color: rgba(255,255,255,0.65); }

        /* ── Media navigation ── */
        .media-nav {
            flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            gap: 10px; padding: 8px 22px 10px;
        }
        .nav-arrow {
            width: 32px; height: 32px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.05); border: 1.5px solid rgba(255,255,255,0.08);
            border-radius: 9px; cursor: pointer; color: rgba(255,255,255,0.45);
            transition: all 0.16s; padding: 0;
        }
        .nav-arrow:hover:not(:disabled) { background: rgba(255,255,255,0.09); color: #fff; border-color: rgba(255,255,255,0.18); }
        .nav-arrow:disabled { opacity: 0.2; cursor: default; }

        .nav-center { display: flex; align-items: center; gap: 8px; flex: 1; max-width: 540px; }

        .nav-info { flex: 1; text-align: center; min-width: 0; }
        .nav-title { font-size: 13px; font-weight: 800; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .nav-counter { font-size: 9px; color: rgba(255,255,255,0.28); font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; margin-top: 1px; }

        .search-wrap { position: relative; flex-shrink: 0; }
        .search-input {
            background: rgba(255,255,255,0.05); border: 1.5px solid rgba(255,255,255,0.08);
            border-radius: 9px; color: #fff; font-family: 'Inter', sans-serif;
            font-size: 11px; padding: 6px 11px 6px 28px; outline: none;
            width: 140px; transition: all 0.2s;
        }
        .search-input::placeholder { color: rgba(255,255,255,0.22); }
        .search-input:focus { border-color: rgba(139,92,246,0.45); background: rgba(255,255,255,0.07); width: 200px; }
        .search-icon { position: absolute; left: 8px; top: 50%; transform: translateY(-50%); width: 12px; height: 12px; color: rgba(255,255,255,0.28); pointer-events: none; }

        .search-dropdown {
            position: absolute; top: calc(100% + 5px); left: 0; right: 0;
            background: rgba(8,10,20,0.98); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px; overflow: hidden; z-index: 1000;
            max-height: 320px; overflow-y: auto;
            box-shadow: 0 20px 50px rgba(0,0,0,0.8); backdrop-filter: blur(24px);
            display: none;
        }
        .search-dropdown.open { display: block; }
        .dd-item { display: flex; align-items: center; gap: 8px; padding: 8px 11px; cursor: pointer; transition: background 0.12s; font-size: 11px; font-weight: 600; color: rgba(255,255,255,0.6); }
        .dd-item:hover, .dd-item.is-active { background: rgba(139,92,246,0.14); color: #fff; }
        .dd-thumb { width: 24px; height: 24px; border-radius: 4px; object-fit: cover; flex-shrink: 0; }
        .dd-thumb-ph { width: 24px; height: 24px; border-radius: 4px; background: rgba(255,255,255,0.06); flex-shrink: 0; }

        /* ── Book wrapper ── */
        .album-wrap {
            flex: 1; min-height: 0;
            display: flex; align-items: stretch; justify-content: center;
            padding: 0 18px 14px;
        }
        .album-book {
            flex: 1; min-width: 0; min-height: 0;
            display: flex; border-radius: 3px 18px 18px 3px; overflow: hidden;
            box-shadow: -14px 0 60px rgba(0,0,0,0.95), 0 28px 80px rgba(0,0,0,0.75),
                        inset 0 0 0 1px rgba(255,255,255,0.04);
        }

        /* ── Left page ── */
        .page-left {
            width: 310px; flex-shrink: 0; position: relative;
            display: flex; flex-direction: column;
            padding: 24px 20px 42px;
            border-right: 5px solid rgba(0,0,0,0.6);
            box-shadow: inset -8px 0 22px rgba(0,0,0,0.45);
            overflow: hidden;
            transition: background 0.5s ease;
        }
        .page-left::before {
            content: ''; position: absolute; inset: 0; pointer-events: none; z-index: 1;
            background: repeating-linear-gradient(0deg, transparent, transparent 28px, rgba(0,0,0,0.05) 28px, rgba(0,0,0,0.05) 29px);
        }
        .pl-cover {
            position: absolute; inset: 0; background-size: cover; background-position: center;
            filter: blur(28px) saturate(1.7); opacity: 0; transform: scale(1.12);
            transition: opacity 0.6s ease, background-image 0.4s ease;
        }
        .pl-cover.loaded { opacity: 0.22; }
        .pl-overlay { position: absolute; inset: 0; background: linear-gradient(160deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.1) 50%, rgba(0,0,0,0.6) 100%); }
        .pl-inner { position: relative; z-index: 2; display: flex; flex-direction: column; gap: 14px; flex: 1; }

        /* ── Right page ── */
        .page-right {
            flex: 1; min-width: 0; min-height: 0;
            display: flex; flex-direction: column;
            padding: 20px 24px 42px 18px;
            background: #070a10; overflow: hidden; position: relative;
        }
        .page-right::before {
            content: ''; position: absolute; inset: 0; pointer-events: none;
            background: repeating-linear-gradient(0deg, transparent, transparent 28px, rgba(255,255,255,0.012) 28px, rgba(255,255,255,0.012) 29px);
        }
        .grid-label { font-size: 8.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.16em; opacity: 0.28; flex-shrink: 0; position: relative; z-index: 1; margin-bottom: 2px; }
        .sticker-scroll { flex: 1; min-height: 0; overflow-y: auto; overflow-x: hidden; position: relative; z-index: 1; padding-right: 4px; padding-bottom: 16px; }
        .sticker-scroll::-webkit-scrollbar { width: 4px; }
        .sticker-scroll::-webkit-scrollbar-track { background: transparent; }
        .sticker-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 2px; }
        .sticker-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
            align-content: start;
        }

        /* ── Sticker slot ── */
        .sticker-slot { position: relative; aspect-ratio: 3/4; cursor: pointer; }
        .sticker-slot:hover { z-index: 200; }

        /* Empty */
        .sticker-empty {
            border-radius: 7px; border: 2px dashed rgba(255,255,255,0.12);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 3px; cursor: pointer; transition: all 0.17s;
            background: rgba(0,0,0,0.16); position: relative; overflow: hidden;
            width: 100%; height: 100%;
        }
        .sticker-empty:hover { border-color: rgba(255,255,255,0.38); background: rgba(255,255,255,0.045); transform: scale(1.04); }
        .sticker-empty:hover .se-code { opacity: 0.6; }
        .sticker-empty:hover .se-plus { opacity: 0.85; transform: scale(1.12); }
        .se-code { font-family: 'Bebas Neue'; font-size: 15px; letter-spacing: 0.06em; opacity: 0.28; transition: opacity 0.17s; }
        .se-plus { font-size: 17px; opacity: 0.16; transition: all 0.17s; }
        .sticker-empty::after { content: ''; position: absolute; top: 0; left: -100%; width: 60%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.04), transparent); }
        .sticker-empty:hover::after { animation: shimmer 0.55s ease-out; }

        /* Filled */
        .sticker-filled {
            border-radius: 7px; overflow: hidden; position: relative;
            width: 100%; height: 100%;
            box-shadow: 0 4px 16px rgba(0,0,0,0.65), 0 1px 0 rgba(255,255,255,0.06);
            transition: all 0.22s cubic-bezier(0.34,1.56,0.64,1); cursor: pointer;
        }
        .sticker-filled:hover { transform: scale(1.09) translateY(-5px) rotate(-1deg); box-shadow: 0 16px 38px rgba(0,0,0,0.9), 0 0 0 2px rgba(255,255,255,0.2); }
        .sticker-filled:hover .sf-shine { opacity: 1; }
        .sf-bg { width: 100%; height: 100%; display: flex; flex-direction: column; position: relative; overflow: hidden; }
        .sf-num { font-family: 'Bebas Neue'; font-size: 11px; letter-spacing: 0.06em; padding: 4px 5px 0; position: absolute; top: 0; left: 0; z-index: 3; }
        .sf-photo { flex: 1; overflow: hidden; position: relative; display: flex; align-items: center; justify-content: center; }
        .sf-photo img { width: 100%; height: 100%; object-fit: cover; object-position: top center; }
        .sf-photo-fade { position: absolute; bottom: 0; left: 0; right: 0; height: 52%; background: linear-gradient(to top, rgba(0,0,0,0.82) 0%, transparent 100%); }
        .sf-photo-ph { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 38px; }
        .sf-name { padding: 3px 5px 4px; font-size: 7.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; background: rgba(0,0,0,0.65); color: #fff; position: relative; z-index: 3; flex-shrink: 0; }
        .sf-shine { position: absolute; inset: 0; border-radius: 7px; background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, transparent 50%); opacity: 0; transition: opacity 0.17s; pointer-events: none; z-index: 10; }

        /* Tooltip */
        .sticker-tip {
            position: absolute; width: 160px; background: rgba(6,8,17,0.97);
            border: 1px solid rgba(255,255,255,0.09); border-radius: 13px; padding: 12px;
            pointer-events: none; z-index: 9999; opacity: 0;
            transition: opacity 0.17s, transform 0.17s;
            box-shadow: 0 20px 46px rgba(0,0,0,0.85); backdrop-filter: blur(22px);
        }
        .sticker-slot:hover .sticker-tip { opacity: 1; }
        .tip-above { bottom: calc(100% + 8px); left: 50%; transform: translateX(-50%) translateY(6px) scale(0.93); }
        .tip-below { top: calc(100% + 8px); left: 50%; transform: translateX(-50%) translateY(-6px) scale(0.93); }
        .sticker-slot:hover .tip-above { transform: translateX(-50%) translateY(0) scale(1); }
        .sticker-slot:hover .tip-below { transform: translateX(-50%) translateY(0) scale(1); }

        /* Wireframe */
        .wf-bar { height: 9px; border-radius: 4px; background: rgba(255,255,255,0.06); flex-shrink: 0; }
        .wf-card { border-radius: 10px; padding: 12px; background: rgba(0,0,0,0.22); border: 1px dashed rgba(255,255,255,0.07); }

        /* Page numbers */
        .page-num { position: absolute; bottom: 12px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; opacity: 0.18; z-index: 2; }
        .page-num.l { left: 16px; }
        .page-num.r { right: 16px; }

        /* Animations */
        @keyframes peel-drop {
            0%   { transform: translateY(-70px) rotate(-8deg) scale(0.65); opacity: 0; }
            60%  { transform: translateY(5px) rotate(1.5deg) scale(1.06); opacity: 1; }
            82%  { transform: translateY(-2px) rotate(-0.4deg) scale(0.99); }
            100% { transform: translateY(0) rotate(0deg) scale(1); opacity: 1; }
        }
        @keyframes spark-fly {
            0%   { transform: scale(0); opacity: 1; }
            80%  { opacity: 1; }
            100% { transform: scale(2) rotate(50deg); opacity: 0; }
        }
        @keyframes shimmer {
            0%   { left: -100%; }
            100% { left: 200%; }
        }
        .placing .sticker-filled { animation: peel-drop 0.52s cubic-bezier(0.22,1,0.36,1) forwards; }
        .spark-burst { position: absolute; inset: 0; pointer-events: none; z-index: 999; }
        .spark { position: absolute; border-radius: 50%; animation: spark-fly 0.48s ease-out forwards; }
        .prog-fill { transition: width 0.75s cubic-bezier(0.4,0,0.2,1); }

        /* ── Album+pack flex wrapper ── */
        .album-flex { flex: 1; max-width: 1480px; min-width: 0; display: flex; align-items: stretch; }

        /* ── Pack sidebar ── */
        .pack-area { width: 22px; flex-shrink: 0; position: relative; overflow: visible; display: flex; align-items: center; z-index: 100; }
        .pack-wrap {
            position: absolute; left: -64px;
            width: 86px; height: 124px;
            cursor: pointer;
            transition: left 0.32s cubic-bezier(0.34,1.56,0.64,1), transform 0.25s ease;
            filter: drop-shadow(0 8px 22px rgba(0,0,0,0.75));
        }
        .pack-wrap:hover { left: -4px; transform: translateY(-8px) rotate(-1.5deg); }
        .pack-wrap:hover .pack-glow { opacity: 0.9; }
        .pack-inner { width: 100%; height: 100%; border-radius: 9px; overflow: hidden; position: relative; border: 1.5px solid rgba(255,255,255,0.22); box-shadow: inset 0 1px 0 rgba(255,255,255,0.18); }
        .pack-bg { position: absolute; inset: 0; transition: background 0.5s; }
        .pack-holo { position: absolute; inset: 0; z-index: 1; pointer-events: none; background: conic-gradient(from 0deg,rgba(255,0,128,.18),rgba(255,200,0,.18),rgba(0,255,128,.18),rgba(0,150,255,.18),rgba(200,0,255,.18),rgba(255,0,128,.18)); animation: holo-spin 8s linear infinite; mix-blend-mode: screen; }
        @keyframes holo-spin { 0% { transform: rotate(0deg) scale(2); } 100% { transform: rotate(360deg) scale(2); } }
        .pack-foil-fx { position: absolute; inset: 0; z-index: 2; pointer-events: none; background: linear-gradient(135deg,transparent 0%,rgba(255,255,255,.32) 40%,transparent 55%,rgba(255,255,255,.14) 70%,transparent 100%); background-size: 200% 200%; animation: foil-sweep 3.5s ease-in-out infinite; }
        @keyframes foil-sweep { 0%,100% { background-position: 0% 0%; } 50% { background-position: 100% 100%; } }
        .pack-content { position: absolute; inset: 0; z-index: 3; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; padding-bottom: 10px; }
        .pack-label-txt { font-family: 'Bebas Neue'; font-size: 13px; letter-spacing: 0.1em; text-align: center; line-height: 1.2; text-shadow: 0 1px 4px rgba(0,0,0,0.7); }
        .pack-glow { position: absolute; inset: -10px; border-radius: 16px; pointer-events: none; z-index: -1; animation: glow-pulse 2.5s ease-in-out infinite; transition: opacity 0.3s; }
        @keyframes glow-pulse { 0%,100% { opacity: .45; transform: scale(1); } 50% { opacity: .9; transform: scale(1.07); } }
        .pack-particles { position: absolute; inset: -28px; pointer-events: none; z-index: 5; }
        .p-dot { position: absolute; border-radius: 50%; width: var(--sz); height: var(--sz); left: var(--x); top: var(--y); opacity: 0; animation: p-float var(--dur) ease-in-out infinite var(--del); }
        @keyframes p-float { 0%,100%{opacity:0;transform:translateY(0) scale(0);} 20%{opacity:.9;transform:translateY(-12px) scale(1);} 80%{opacity:.25;transform:translateY(-26px) scale(.55);} }

        /* ── Locked sticker ── */
        .sticker-locked { border: 1.5px solid rgba(255,255,255,0.04) !important; background: rgba(0,0,0,0.3) !important; cursor: default !important; }
        .sticker-locked:hover { transform: none !important; border-color: rgba(255,255,255,0.05) !important; background: rgba(0,0,0,0.3) !important; }
        .sticker-locked .se-code { opacity: 0.09 !important; }
        .lock-svg { width: 13px; height: 13px; color: rgba(255,255,255,0.1); }

        /* ── Pack overlay ── */
        .pack-overlay { position: fixed; inset: 0; z-index: 9000; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.9); backdrop-filter: blur(14px); }
        .pack-modal { position: relative; display: flex; flex-direction: column; align-items: center; gap: 18px; }
        .modal-x { position: absolute; top: -16px; right: -16px; width: 30px; height: 30px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.14); border-radius: 50%; cursor: pointer; color: rgba(255,255,255,0.55); display: flex; align-items: center; justify-content: center; font-size: 16px; line-height: 1; transition: all 0.16s; z-index: 10; }
        .modal-x:hover { background: rgba(255,255,255,0.16); color: #fff; }
        .modal-pack { width: 160px; height: 228px; border-radius: 12px; overflow: hidden; position: relative; cursor: pointer; box-shadow: 0 0 60px rgba(139,92,246,.4), 0 20px 60px rgba(0,0,0,.85); border: 2px solid rgba(255,255,255,0.18); transition: transform 0.2s; }
        .modal-pack:not(.shaking):not(.exploding):hover { transform: scale(1.04) translateY(-4px); }
        @keyframes pack-shake { 0%,100%{transform:rotate(0) translateX(0);} 12%{transform:rotate(-7deg) translateX(-5px);} 25%{transform:rotate(7deg) translateX(5px);} 37%{transform:rotate(-5deg) translateX(-4px);} 50%{transform:rotate(5deg) translateX(4px);} 62%{transform:rotate(-3deg) translateX(-2px);} 75%{transform:rotate(3deg) translateX(2px);} 87%{transform:rotate(-1deg) translateX(-1px);} }
        .modal-pack.shaking { animation: pack-shake .65s cubic-bezier(.36,.07,.19,.97) both; pointer-events: none; }
        @keyframes pack-burst { 0%{transform:scale(1);opacity:1;filter:brightness(1);} 35%{transform:scale(1.25);opacity:1;filter:brightness(5);} 70%{transform:scale(1.7);opacity:0;filter:brightness(2);} 100%{transform:scale(.7);opacity:0;} }
        .modal-pack.exploding { animation: pack-burst .4s ease-out forwards; pointer-events: none; }
        .modal-hint { font-size: 11px; color: rgba(255,255,255,0.32); font-weight: 600; letter-spacing: 0.06em; }

        /* ── Card reveal ── */
        @keyframes card-emerge { 0%{transform:scale(.15) translateY(60px) rotate(-10deg);opacity:0;} 65%{transform:scale(1.07) translateY(-5px) rotate(1deg);opacity:1;} 100%{transform:scale(1) translateY(0) rotate(0);opacity:1;} }
        .reveal-card { width: 200px; height: 280px; border-radius: 12px; overflow: hidden; position: relative; box-shadow: 0 12px 44px rgba(0,0,0,.8), 0 0 0 1.5px rgba(255,255,255,.12); animation: card-emerge .52s cubic-bezier(.22,1,.36,1) forwards; cursor: pointer; }
        .card-badge { position: absolute; bottom: 52px; left: 50%; transform: translateX(-50%); padding: 4px 12px; border-radius: 999px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; white-space: nowrap; box-shadow: 0 2px 10px rgba(0,0,0,.5); z-index: 10; }
        .prog-dots { display: flex; gap: 8px; }
        .prog-dot { width: 7px; height: 7px; border-radius: 50%; background: rgba(255,255,255,.18); transition: all .25s; }
        .prog-dot.done { background: rgba(255,255,255,.5); }
        .prog-dot.active { background: #fff; transform: scale(1.4); }
        .next-btn { padding: 10px 30px; border-radius: 10px; border: none; cursor: pointer; font-family: 'Inter'; font-size: 12px; font-weight: 800; letter-spacing: .07em; transition: all .18s; color: #fff; }
        .next-btn:hover { filter: brightness(1.18); transform: translateY(-1px); }
        .cards-summary { display: flex; gap: 10px; align-items: flex-end; }
        @keyframes sum-emerge { 0%{transform:scale(.3) translateY(30px);opacity:0;} 100%{transform:scale(1) translateY(0);opacity:1;} }
        .summary-card { width: 80px; height: 112px; border-radius: 8px; overflow: hidden; position: relative; box-shadow: 0 4px 16px rgba(0,0,0,.65), 0 0 0 1px rgba(255,255,255,.1); animation: sum-emerge .4s cubic-bezier(.22,1,.36,1) both; }
        .sum-dot { position: absolute; top: 5px; right: 5px; width: 8px; height: 8px; border-radius: 50%; z-index: 5; box-shadow: 0 0 6px currentColor; }
        .close-pack-btn { padding: 10px 30px; border-radius: 10px; cursor: pointer; font-family: 'Inter'; font-size: 12px; font-weight: 700; letter-spacing: .08em; background: rgba(255,255,255,.07); border: 1.5px solid rgba(255,255,255,.13); color: rgba(255,255,255,.65); transition: all .18s; }
        .close-pack-btn:hover { background: rgba(255,255,255,.14); color: #fff; transform: translateY(-1px); }

        /* Mobile fallback */
        @media (max-width: 760px) {
            html, body { height: auto; overflow: auto; }
            body { display: block; }
            .album-wrap { height: auto; padding: 0 10px 24px; }
            .album-book { flex-direction: column; border-radius: 14px; max-height: none; }
            .page-left { width: 100%; border-right: none; border-bottom: 4px solid rgba(0,0,0,0.5); overflow: visible; }
            .page-right { min-height: 500px; overflow: visible; }
            .sticker-scroll { overflow: visible; }
            .pack-area { display: none; }
        }
    </style>
</head>
<body>

@php
$mediaList = [];
foreach ($media as $m) {
    $chars = array_values(array_filter($characters, fn($c) => $c['mediaId'] === $m['id']));
    $mediaList[] = ['id' => $m['id'], 'title' => $m['title'], 'coverImage' => $m['coverImage'] ?? '', 'franchise' => $m['franchise'] ?? '', 'characters' => $chars];
}
@endphp

<div class="album-topbar">
    <a href="{{ route('albums.index') }}">← Álbumes</a>
    <span>{{ strtoupper($album['name']) }}</span>
    <span style="opacity:0">·</span>
</div>

<nav class="media-nav">
    <button class="nav-arrow" id="btnPrev">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </button>
    <div class="nav-center">
        <div class="nav-info">
            <div class="nav-title" id="navTitle">—</div>
            <div class="nav-counter" id="navCounter">—</div>
        </div>
        <div class="search-wrap" id="searchWrap">
            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
            <input type="search" id="searchInput" class="search-input" placeholder="Buscar media..." autocomplete="off">
            <div class="search-dropdown" id="searchDropdown"></div>
        </div>
    </div>
    <button class="nav-arrow" id="btnNext">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </button>
</nav>

<div class="album-wrap">
    <div class="album-flex">
        <div class="album-book" id="albumBook"></div>
        <div class="pack-area">
            <div class="pack-wrap" id="packWrap">
                <div class="pack-particles" id="packParticles"></div>
                <div class="pack-inner">
                    <div class="pack-bg" id="packBg"></div>
                    <div class="pack-holo"></div>
                    <div class="pack-foil-fx"></div>
                    <div class="pack-content">
                        <div class="pack-label-txt" id="packLabel">SOBRE<br>× 5</div>
                    </div>
                </div>
                <div class="pack-glow" id="packGlow"></div>
            </div>
        </div>
    </div>
</div>

<div id="packOverlay" style="display:none;" class="pack-overlay">
    <div id="packModal" class="pack-modal"></div>
</div>

<script>
const MEDIA_LIST = @json($mediaList);
const ALBUM_ID   = {{ $album['id'] }};

// ── Fallback palettes (used before/if color extraction fails) ──
const FALLBACK_PALETTES = [
    { c1:'#5b21b6', c2:'#8b5cf6', accent:'#c084fc', bg:'linear-gradient(150deg,#1a1040 0%,#2d1b69 55%,#0f0826 100%)' },
    { c1:'#065f46', c2:'#10b981', accent:'#6ee7b7', bg:'linear-gradient(150deg,#01200f 0%,#064e3b 55%,#011510 100%)' },
    { c1:'#991b1b', c2:'#f87171', accent:'#fca5a5', bg:'linear-gradient(150deg,#3a0808 0%,#7f1d1d 55%,#250404 100%)' },
    { c1:'#1e3a8a', c2:'#60a5fa', accent:'#93c5fd', bg:'linear-gradient(150deg,#060e2a 0%,#1e3a8a 55%,#030820 100%)' },
    { c1:'#78350f', c2:'#fbbf24', accent:'#fde68a', bg:'linear-gradient(150deg,#2d1500 0%,#78350f 55%,#1a0b00 100%)' },
    { c1:'#0c4a6e', c2:'#22d3ee', accent:'#67e8f9', bg:'linear-gradient(150deg,#021520 0%,#0c4a6e 55%,#010b14 100%)' },
    { c1:'#831843', c2:'#f472b6', accent:'#fbcfe8', bg:'linear-gradient(150deg,#2d0517 0%,#831843 55%,#1a0310 100%)' },
    { c1:'#3f6212', c2:'#a3e635', accent:'#d9f99d', bg:'linear-gradient(150deg,#0f1c04 0%,#3f6212 55%,#090f02 100%)' },
];
function fallbackPal(idx) { return FALLBACK_PALETTES[idx % FALLBACK_PALETTES.length]; }

// ── Color extraction ──────────────────────────────────────────
const colorCache = {};

function hslToHex(h, s, l) {
    s /= 100; l /= 100;
    const k = n => (n + h / 30) % 12;
    const a = s * Math.min(l, 1 - l);
    const f = n => Math.round(255 * (l - a * Math.max(-1, Math.min(k(n) - 3, Math.min(9 - k(n), 1)))));
    return '#' + [f(0), f(8), f(4)].map(v => v.toString(16).padStart(2, '0')).join('');
}

function paletteFromHue(h) {
    return {
        c1:     hslToHex(h, 65, 26),
        c2:     hslToHex(h, 70, 52),
        accent: hslToHex(h, 78, 72),
        bg: `linear-gradient(150deg, ${hslToHex(h,55,7)} 0%, ${hslToHex(h,52,13)} 55%, ${hslToHex(h,48,5)} 100%)`
    };
}

async function extractHue(url) {
    if (!url) return null;
    return new Promise(resolve => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        const tid = setTimeout(() => { img.src = ''; resolve(null); }, 3500);
        img.onload = () => {
            clearTimeout(tid);
            try {
                const cv = document.createElement('canvas');
                cv.width = cv.height = 60;
                const cx = cv.getContext('2d');
                cx.drawImage(img, 0, 0, 60, 60);
                const px = cx.getImageData(0, 0, 60, 60).data;
                const hueWeight = new Float32Array(36);
                for (let i = 0; i < px.length; i += 4) {
                    if (px[i + 3] < 100) continue;
                    const r = px[i] / 255, g = px[i+1] / 255, b = px[i+2] / 255;
                    const max = Math.max(r,g,b), min = Math.min(r,g,b), d = max - min;
                    if (d < 0.12) continue;
                    const l = (max + min) / 2;
                    if (l < 0.1 || l > 0.9) continue;
                    let h;
                    if (max === r)      h = ((g - b) / d + (g < b ? 6 : 0)) / 6;
                    else if (max === g) h = ((b - r) / d + 2) / 6;
                    else               h = ((r - g) / d + 4) / 6;
                    hueWeight[Math.floor(h * 36)] += d * (d / (max + min + 0.001)); // weight by saturation
                }
                const maxW = Math.max(...hueWeight);
                if (maxW < 0.8) { resolve(null); return; }
                resolve(hueWeight.indexOf(maxW) * 10 + 5);
            } catch { resolve(null); }
        };
        img.onerror = () => { clearTimeout(tid); resolve(null); };
        img.src = url;
    });
}

async function getPalette(mediaId, imageUrl, fallbackIdx) {
    if (colorCache[mediaId]) return colorCache[mediaId];
    const hue = await extractHue(imageUrl);
    const p = hue !== null ? paletteFromHue(hue) : fallbackPal(fallbackIdx);
    colorCache[mediaId] = p;
    return p;
}

// Pre-load all palettes in background
MEDIA_LIST.forEach((m, idx) => {
    getPalette(m.id, m.coverImage, idx).then(p => {
        if (MEDIA_LIST[curIdx]?.id === m.id) renderBook();
    });
});

// ── Placed state ──────────────────────────────────────────────
let placed = (() => {
    try {
        const raw = JSON.parse(localStorage.getItem('album_' + ALBUM_ID + '_placed') || '{}');
        const out = {};
        for (const k in raw) out[k] = new Set(raw[k]);
        return out;
    } catch { return {}; }
})();

// ── Owned state (cards obtained from packs) ────────────────────
let owned = (() => {
    try {
        const raw = JSON.parse(localStorage.getItem('album_' + ALBUM_ID + '_owned') || '{}');
        const out = {};
        for (const k in raw) out[k] = new Set(raw[k]);
        // Migrate: already-placed cards are considered owned
        for (const k in placed) {
            if (!out[k]) out[k] = new Set();
            for (const id of placed[k]) out[k].add(id);
        }
        return out;
    } catch { return {}; }
})();

function saveOwned() {
    const out = {};
    for (const k in owned) out[k] = [...owned[k]];
    localStorage.setItem('album_' + ALBUM_ID + '_owned', JSON.stringify(out));
}

function ownedSet(mediaId) {
    const k = String(mediaId);
    if (!owned[k]) owned[k] = new Set();
    return owned[k];
}

function savePlaced() {
    const out = {};
    for (const k in placed) out[k] = [...placed[k]];
    localStorage.setItem('album_' + ALBUM_ID + '_placed', JSON.stringify(out));
}

function placedSet(mediaId) {
    const k = String(mediaId);
    if (!placed[k]) placed[k] = new Set();
    return placed[k];
}

// ── State & navigation ────────────────────────────────────────
let curIdx = 0;

function go(idx) {
    curIdx = Math.max(0, Math.min(MEDIA_LIST.length - 1, idx));
    render();
}

// ── Render ────────────────────────────────────────────────────
function render() {
    updateNav();
    renderBook();
    updatePackStyle();
    initPackParticles();
}

function updateNav() {
    const m = MEDIA_LIST[curIdx];
    document.getElementById('navTitle').textContent   = m ? m.title : '—';
    document.getElementById('navCounter').textContent = (curIdx + 1) + ' / ' + MEDIA_LIST.length;
    document.getElementById('btnPrev').disabled = curIdx === 0;
    document.getElementById('btnNext').disabled = curIdx === MEDIA_LIST.length - 1;
}

function renderBook() {
    if (!MEDIA_LIST.length) {
        document.getElementById('albumBook').innerHTML = '<div style="display:flex;align-items:center;justify-content:center;width:100%;color:rgba(255,255,255,0.18);font-size:13px;">No hay media en este álbum.</div>';
        return;
    }
    const m  = MEDIA_LIST[curIdx];
    const p  = colorCache[m.id] || fallbackPal(curIdx);
    const ps = placedSet(m.id);
    const total = m.characters.length;
    const done  = m.characters.filter(c => ps.has(c.id)).length;
    const pct   = total > 0 ? Math.round(done / total * 100) : 0;

    document.getElementById('albumBook').innerHTML = buildLeftPage(m, p, total, done, pct) + buildRightShell(m);
    applyLeftCover(m.coverImage);
    renderGrid(m, p, ps);
}

function applyLeftCover(url) {
    const el = document.querySelector('.pl-cover');
    if (!el) return;
    if (url) {
        el.style.backgroundImage = `url('${esc(url)}')`;
        el.classList.add('loaded');
    } else {
        el.classList.remove('loaded');
    }
}

// ── Left page ─────────────────────────────────────────────────
function buildLeftPage(m, p, total, done, pct) {
    const thumb = m.coverImage
        ? `<div style="width:58px;height:78px;border-radius:7px;overflow:hidden;margin-bottom:12px;box-shadow:0 4px 18px rgba(0,0,0,0.65);border:2px solid rgba(255,255,255,0.1);">
             <img src="${esc(m.coverImage)}" style="width:100%;height:100%;object-fit:cover;">
           </div>`
        : '';

    const wfData = [[68,42],[80,34],[50,58],[74,38]];

    return `
    <div class="page-left" style="background:${p.bg};">
      <div class="pl-cover"></div>
      <div class="pl-overlay"></div>
      <div class="pl-inner">

        <div>
          ${thumb}
          <div style="font-family:'Bebas Neue';font-size:38px;line-height:1;color:${p.accent};text-shadow:0 2px 12px rgba(0,0,0,0.65);letter-spacing:0.03em;word-break:break-word;">${esc(m.title).toUpperCase()}</div>
          ${m.franchise ? `<div style="font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.14em;opacity:0.42;margin-top:3px;">${esc(m.franchise)}</div>` : ''}
          <div style="height:2px;width:42px;border-radius:999px;background:${p.accent};margin-top:10px;opacity:0.75;"></div>
        </div>

        <div>
          <div style="display:flex;justify-content:space-between;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;opacity:0.5;margin-bottom:6px;">
            <span>Progreso</span>
            <span style="color:${p.accent};">${done}/${total}</span>
          </div>
          <div style="height:5px;background:rgba(255,255,255,0.09);border-radius:999px;overflow:hidden;">
            <div class="prog-fill" style="height:100%;width:${pct}%;background:${p.accent};border-radius:999px;"></div>
          </div>
        </div>

        <div class="wf-card">
          <div style="font-size:8px;font-weight:800;text-transform:uppercase;letter-spacing:0.14em;opacity:0.25;margin-bottom:9px;">Detalles · Próximamente</div>
          <div style="display:flex;flex-direction:column;gap:7px;">
            ${wfData.map(([a,b]) => `<div style="display:flex;align-items:center;justify-content:space-between;gap:8px;"><div class="wf-bar" style="width:${a}px;"></div><div class="wf-bar" style="width:${b}px;opacity:0.38;"></div></div>`).join('')}
          </div>
        </div>

        <div style="margin-top:auto;">
          <div style="background:rgba(0,0,0,0.28);border:1px solid rgba(255,255,255,0.06);border-radius:11px;padding:12px;">
            <div style="font-size:8px;font-weight:800;text-transform:uppercase;letter-spacing:0.12em;opacity:0.32;margin-bottom:5px;">Personajes</div>
            <div style="font-family:'Bebas Neue';font-size:40px;color:${p.accent};line-height:1;">${total}</div>
            <div style="font-size:10px;opacity:0.32;margin-top:2px;">${done} colocados · ${total - done} pendientes</div>
          </div>
        </div>

      </div>
      <div class="page-num l" style="color:${p.accent};">#${m.id} · ${total} stickers</div>
    </div>`;
}

// ── Right page shell ──────────────────────────────────────────
function buildRightShell(m) {
    return `
    <div class="page-right">
      <div class="grid-label">Plantilla oficial · ${esc(m.title).toUpperCase()}</div>
      <div class="sticker-scroll">
        <div id="stickerGrid" class="sticker-grid"></div>
      </div>
      <div class="page-num r">${curIdx + 1} · ${MEDIA_LIST.length}</div>
    </div>`;
}

// ── Sticker grid ──────────────────────────────────────────────
function renderGrid(m, p, ps) {
    const grid = document.getElementById('stickerGrid');
    if (!grid) return;
    const chars = m.characters;
    if (!chars.length) {
        grid.style.display = 'flex';
        grid.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:180px;color:rgba(255,255,255,0.16);font-size:13px;width:100%;">Sin personajes</div>';
        return;
    }
    grid.style.display = '';
    grid.innerHTML = '';
    chars.forEach((char, idx) => {
        const above = idx >= Math.ceil(chars.length / 2);
        const slot  = document.createElement('div');
        slot.className = 'sticker-slot';
        if (ps.has(char.id)) {
            slot.innerHTML = buildFilled(char, p, idx, above);
        } else {
            slot.innerHTML = buildEmpty(char, p, idx);
            slot.querySelector('.sticker-empty').addEventListener('click', () => place(char, slot, p, idx, above, m));
        }
        grid.appendChild(slot);
    });
}

function buildFilled(char, p, idx, above) {
    const photo = char.image
        ? `<img src="${esc(char.image)}" alt="${esc(char.name)}"><div class="sf-photo-fade"></div>`
        : `<div class="sf-photo-ph">👤</div>`;
    const ttClass = above ? 'tip-above' : 'tip-below';
    const ttImg = char.image
        ? `<div style="width:46px;height:60px;border-radius:6px;overflow:hidden;margin:0 auto 8px;"><img src="${esc(char.image)}" style="width:100%;height:100%;object-fit:cover;object-position:top;"></div>`
        : `<div style="font-size:28px;text-align:center;margin-bottom:8px;">👤</div>`;

    return `
        <div class="sticker-filled">
          <div class="sf-bg" style="background:${p.bg};">
            <div class="sf-num" style="color:${p.accent};">${idx + 1}</div>
            <div class="sf-photo">${photo}</div>
            <div class="sf-name">${esc(char.name)}</div>
          </div>
          <div class="sf-shine"></div>
        </div>
        <div class="sticker-tip ${ttClass}">
          ${ttImg}
          <div style="font-size:12px;font-weight:800;text-align:center;color:#fff;line-height:1.25;">${esc(char.name)}</div>
          ${char.role ? `<div style="font-size:9px;font-weight:700;text-align:center;display:block;margin:4px auto 0;padding:2px 8px;border-radius:999px;background:${p.c1}44;color:${p.accent};">${char.role}</div>` : ''}
          <div style="margin-top:8px;padding-top:7px;border-top:1px solid rgba(255,255,255,0.06);font-size:10px;color:rgba(255,255,255,0.36);text-align:center;">${esc(char.mediaTitle || '')}</div>
        </div>`;
}

function buildEmpty(char, p, idx) {
    const m = MEDIA_LIST[curIdx];
    const isOwned = ownedSet(m.id).has(char.id);
    if (isOwned) {
        return `
            <div class="sticker-empty" style="border-color:${p.c2}38;">
              <div class="se-code" style="color:${p.accent};">#${String(idx + 1).padStart(3,'0')}</div>
              <div class="se-plus" style="color:${p.accent};">+</div>
            </div>`;
    }
    return `
        <div class="sticker-empty sticker-locked">
          <div class="se-code">#${String(idx + 1).padStart(3,'0')}</div>
          <svg class="lock-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
        </div>`;
}

// ── Place sticker ─────────────────────────────────────────────
function place(char, slot, p, idx, above, m) {
    if (!ownedSet(m.id).has(char.id)) return;
    if (slot.classList.contains('placing')) return;
    slot.classList.add('placing');
    slot.innerHTML = buildFilled(char, p, idx, above) + sparks(p);
    setTimeout(() => {
        slot.classList.remove('placing');
        placedSet(m.id).add(char.id);
        savePlaced();
        refreshProgress(m, p);
    }, 570);
}

function sparks(p) {
    const cols = [p.accent, p.c2, '#fff', p.c1];
    let html = '';
    for (let i = 0; i < 10; i++) {
        const angle = (i / 10) * 360;
        const dist  = 22 + Math.random() * 26;
        const x = 50 + Math.cos(angle * Math.PI / 180) * dist;
        const y = 50 + Math.sin(angle * Math.PI / 180) * dist;
        const delay = Math.random() * 0.13;
        const sz = 3 + Math.random() * 5;
        html += `<div class="spark" style="left:${x}%;top:${y}%;background:${cols[i%cols.length]};width:${sz}px;height:${sz}px;animation-delay:${delay}s;"></div>`;
    }
    return `<div class="spark-burst">${html}</div>`;
}

function refreshProgress(m, p) {
    const ps = placedSet(m.id);
    const total = m.characters.length;
    const done  = m.characters.filter(c => ps.has(c.id)).length;
    const pct   = total > 0 ? Math.round(done / total * 100) : 0;
    const fill  = document.querySelector('.prog-fill');
    if (fill) fill.style.width = pct + '%';
    const prog = document.querySelector('[data-prog]');
    if (prog) prog.textContent = done + '/' + total;
}

// ── Pack system ───────────────────────────────────────────────
let packState = 'idle'; // idle | ready | shaking | exploding | revealing | done
let drawnCards = [];
let revealIdx  = 0;

function updatePackStyle() {
    const m = MEDIA_LIST[curIdx];
    if (!m) return;
    const p = colorCache[m.id] || fallbackPal(curIdx);
    const bg = document.getElementById('packBg');
    if (bg) bg.style.background = p.bg;
    const glow = document.getElementById('packGlow');
    if (glow) glow.style.background = `radial-gradient(ellipse at center, ${p.c2} 0%, transparent 70%)`;
    const label = document.getElementById('packLabel');
    if (label) label.style.color = p.accent;
}

function initPackParticles() {
    const wrap = document.getElementById('packParticles');
    if (!wrap) return;
    wrap.innerHTML = '';
    const m = MEDIA_LIST[curIdx];
    const p = colorCache[m?.id] || fallbackPal(curIdx);
    for (let i = 0; i < 14; i++) {
        const d = document.createElement('span');
        d.className = 'p-dot';
        const sz = (2 + Math.random() * 4).toFixed(1) + 'px';
        d.style.cssText = `--sz:${sz};--x:${(Math.random()*110-5).toFixed(0)}%;--y:${(Math.random()*110-5).toFixed(0)}%;--dur:${(1.6+Math.random()*2.2).toFixed(1)}s;--del:${(Math.random()*2).toFixed(1)}s;background:${i%3===0?p.accent:i%3===1?p.c2:'rgba(255,255,255,0.7)'};`;
        wrap.appendChild(d);
    }
}

// Open overlay
function openPackOverlay() {
    if (packState !== 'idle') return;
    const m = MEDIA_LIST[curIdx];
    if (!m?.characters.length) return;
    packState = 'ready';
    const overlay = document.getElementById('packOverlay');
    overlay.style.display = 'flex';
    renderPackModal();
}

function closePackOverlay() {
    document.getElementById('packOverlay').style.display = 'none';
    packState = 'idle';
    renderBook();
}

function renderPackModal() {
    const m  = MEDIA_LIST[curIdx];
    const p  = colorCache[m.id] || fallbackPal(curIdx);
    const modal = document.getElementById('packModal');

    if (packState === 'ready') {
        modal.innerHTML = `
            <button class="modal-x" id="mClose">×</button>
            <div class="modal-pack" id="modalPack">
                <div style="position:absolute;inset:0;background:${p.bg};"></div>
                <div class="pack-holo"></div>
                <div class="pack-foil-fx"></div>
                <div style="position:absolute;inset:0;z-index:3;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;">
                    <div style="font-size:42px;">📦</div>
                    <div style="font-family:'Bebas Neue';font-size:22px;letter-spacing:.1em;color:${p.accent};">× 5 CARTAS</div>
                </div>
            </div>
            <p class="modal-hint">Haz clic para abrir</p>`;
        document.getElementById('modalPack').addEventListener('click', startOpening);
        document.getElementById('mClose').addEventListener('click', closePackOverlay);

    } else if (packState === 'revealing') {
        const char = drawnCards[revealIdx];
        const BADGES = {
            new:    { text:'★ Nueva',    bg:'rgba(4,120,87,0.85)',   color:'#6ee7b7' },
            repeat: { text:'↩ Repetida', bg:'rgba(17,24,39,0.9)',    color:'#9ca3af' },
            album:  { text:'✓ En álbum', bg:'rgba(120,53,15,0.85)',  color:'#fbbf24' },
        };
        const badge = BADGES[char.badgeType] || BADGES.repeat;
        const dots  = Array.from({length:5}, (_,i) =>
            `<div class="prog-dot ${i<revealIdx?'done':i===revealIdx?'active':''}"></div>`).join('');

        modal.innerHTML = `
            <button class="modal-x" id="mClose">×</button>
            <div class="prog-dots">${dots}</div>
            <div class="reveal-card" id="revealCard">
                <div style="position:absolute;inset:0;background:${p.bg};"></div>
                <div class="pack-holo" style="z-index:1;"></div>
                <div class="pack-foil-fx" style="z-index:2;"></div>
                ${char.image
                    ? `<img src="${esc(char.image)}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:top;z-index:3;">`
                    : `<div style="position:absolute;inset:0;z-index:3;display:flex;align-items:center;justify-content:center;font-size:64px;">👤</div>`}
                <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.88) 0%,rgba(0,0,0,.1) 55%,transparent 100%);z-index:4;"></div>
                <div style="position:absolute;top:7px;left:9px;font-family:'Bebas Neue';font-size:14px;color:${p.accent};z-index:6;">#${String(revealIdx+1).padStart(2,'0')}</div>
                <div style="position:absolute;bottom:0;left:0;right:0;padding:10px 10px 14px;z-index:6;">
                    <div style="font-size:15px;font-weight:800;color:#fff;line-height:1.2;">${esc(char.name)}</div>
                    <div style="font-size:10px;color:rgba(255,255,255,.4);margin-top:2px;">${esc(char.mediaTitle||m.title)}</div>
                </div>
                <div class="card-badge" style="background:${badge.bg};color:${badge.color};">${badge.text}</div>
            </div>
            <button class="next-btn" id="nextBtn" style="background:${p.c1};">
                ${revealIdx < 4 ? 'SIGUIENTE →' : 'VER RESULTADO'}
            </button>
            <p style="font-size:10px;color:rgba(255,255,255,.18);margin-top:-10px;">o haz clic en la carta</p>`;
        document.getElementById('mClose').addEventListener('click', closePackOverlay);
        document.getElementById('nextBtn').addEventListener('click', advanceReveal);
        document.getElementById('revealCard').addEventListener('click', advanceReveal);

    } else if (packState === 'done') {
        const DOT_COLORS = { new:'#34d399', repeat:'#6b7280', album:'#fbbf24' };
        const cardsHtml = drawnCards.map((c, i) => `
            <div class="summary-card" style="background:${p.bg};animation-delay:${i*.09}s;">
                <div class="pack-foil-fx" style="z-index:2;"></div>
                ${c.image ? `<img src="${esc(c.image)}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:top;z-index:1;">` : ''}
                <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.8) 0%,transparent 60%);z-index:3;"></div>
                <div style="position:absolute;bottom:0;left:0;right:0;padding:5px;z-index:4;text-align:center;font-size:8.5px;font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(c.name)}</div>
                <div class="sum-dot" style="background:${DOT_COLORS[c.badgeType]};color:${DOT_COLORS[c.badgeType]};"></div>
            </div>`).join('');

        modal.innerHTML = `
            <button class="modal-x" id="mClose">×</button>
            <p style="font-size:16px;font-weight:800;color:#fff;letter-spacing:.04em;">¡Sobre abierto!</p>
            <div class="cards-summary">${cardsHtml}</div>
            <div style="display:flex;gap:14px;font-size:10px;color:rgba(255,255,255,.32);">
                <span style="color:#34d399;">★</span> Nueva &nbsp;
                <span style="color:#6b7280;">●</span> Repetida &nbsp;
                <span style="color:#fbbf24;">✓</span> En álbum
            </div>
            <button class="close-pack-btn" id="closePack">CERRAR Y COLOCAR</button>`;
        document.getElementById('mClose').addEventListener('click', closePackOverlay);
        document.getElementById('closePack').addEventListener('click', closePackOverlay);
    }
}

function startOpening() {
    if (packState !== 'ready') return;
    packState = 'shaking';
    const packEl = document.getElementById('modalPack');
    packEl.classList.add('shaking');
    setTimeout(() => {
        packEl.classList.remove('shaking');
        packEl.classList.add('exploding');
        // Draw and classify cards
        const m = MEDIA_LIST[curIdx];
        const os = ownedSet(m.id);
        const ps = placedSet(m.id);
        const preOwned = new Set(os);
        const thisPackIds = new Set();
        drawnCards = Array.from({length: 5}, () => {
            const char = {...m.characters[Math.floor(Math.random() * m.characters.length)]};
            if (ps.has(char.id)) char.badgeType = 'album';
            else if (preOwned.has(char.id) || thisPackIds.has(char.id)) char.badgeType = 'repeat';
            else char.badgeType = 'new';
            thisPackIds.add(char.id);
            os.add(char.id);
            return char;
        });
        saveOwned();
        revealIdx = 0;
        setTimeout(() => {
            packState = 'revealing';
            renderPackModal();
        }, 380);
    }, 680);
}

function advanceReveal() {
    revealIdx++;
    if (revealIdx >= 5) { packState = 'done'; }
    renderPackModal();
}

// ── Search dropdown ───────────────────────────────────────────
function renderDropdown(q) {
    const dd = document.getElementById('searchDropdown');
    const lq = q.trim().toLowerCase();
    const filtered = MEDIA_LIST.filter(m => !lq || m.title.toLowerCase().includes(lq) || m.franchise.toLowerCase().includes(lq));
    if (!filtered.length) {
        dd.innerHTML = '<div style="padding:12px;font-size:11px;color:rgba(255,255,255,0.28);text-align:center;">Sin resultados</div>';
    } else {
        dd.innerHTML = filtered.map(m => {
            const i = MEDIA_LIST.indexOf(m);
            return `<div class="dd-item ${i === curIdx ? 'is-active' : ''}" data-idx="${i}">
                ${m.coverImage ? `<img class="dd-thumb" src="${esc(m.coverImage)}" alt="">` : `<div class="dd-thumb-ph"></div>`}
                <div><div style="font-weight:700;">${esc(m.title)}</div>${m.franchise ? `<div style="font-size:10px;opacity:0.38;">${esc(m.franchise)}</div>` : ''}</div>
            </div>`;
        }).join('');
        dd.querySelectorAll('.dd-item').forEach(el => el.addEventListener('click', () => {
            go(+el.dataset.idx);
            document.getElementById('searchInput').value = '';
            dd.classList.remove('open');
        }));
    }
    dd.classList.add('open');
}

// ── Utility ───────────────────────────────────────────────────
function esc(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Events ────────────────────────────────────────────────────
document.getElementById('btnPrev').addEventListener('click', () => go(curIdx - 1));
document.getElementById('btnNext').addEventListener('click', () => go(curIdx + 1));

const si = document.getElementById('searchInput');
si.addEventListener('input', () => renderDropdown(si.value));
si.addEventListener('focus', () => renderDropdown(si.value));
document.addEventListener('click', e => {
    if (!document.getElementById('searchWrap').contains(e.target))
        document.getElementById('searchDropdown').classList.remove('open');
});
document.addEventListener('keydown', e => {
    if (document.activeElement === si) return;
    if (e.key === 'ArrowLeft')  go(curIdx - 1);
    if (e.key === 'ArrowRight') go(curIdx + 1);
});

// ── Init ──────────────────────────────────────────────────────
document.getElementById('packWrap').addEventListener('click', openPackOverlay);
render();
</script>
</body>
</html>
