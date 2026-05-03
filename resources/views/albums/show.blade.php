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
            flex: 1; min-width: 0; min-height: 0; max-width: 1420px;
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

        /* Mobile fallback */
        @media (max-width: 760px) {
            html, body { height: auto; overflow: auto; }
            body { display: block; }
            .album-wrap { height: auto; padding: 0 10px 24px; }
            .album-book { flex-direction: column; border-radius: 14px; max-height: none; }
            .page-left { width: 100%; border-right: none; border-bottom: 4px solid rgba(0,0,0,0.5); overflow: visible; }
            .page-right { min-height: 500px; overflow: visible; }
            .sticker-scroll { overflow: visible; }
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
    <div class="album-book" id="albumBook"></div>
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
    return `
        <div class="sticker-empty" style="border-color:${p.c2}38;">
          <div class="se-code" style="color:${p.accent};">#${String(idx + 1).padStart(3,'0')}</div>
          <div class="se-plus" style="color:${p.accent};">+</div>
        </div>`;
}

// ── Place sticker ─────────────────────────────────────────────
function place(char, slot, p, idx, above, m) {
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
render();
</script>
</body>
</html>
