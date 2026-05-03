#!/usr/bin/env python3
"""
Local WD14 tagger server — uses dghs-imgutils, identical to deepghs/wd14_tagging_online.

Setup (one time):
    pip install dghs-imgutils flask

Run:
    python tagger_server.py
"""

import base64, io, sys, inspect
from flask import Flask, request, jsonify
from PIL import Image

try:
    from imgutils.tagging import get_wd14_tags
except ImportError:
    print("ERROR: Install dependencies first:  pip install dghs-imgutils flask")
    sys.exit(1)

# Model name uses the dghs-imgutils convention (NOT the HuggingFace repo name).
# Default is SwinV2_v3. Other options: EVA02_Large_v3, ViT_Large_v3, MOAT_v2
MODEL = "SwinV2_v3"
PORT  = 7860

app = Flask(__name__)

# ── Detect API signature once at startup ─────────────────────────────────────

_SIG        = inspect.signature(get_wd14_tags)
_PARAM_KEYS = list(_SIG.parameters.keys())
print(f"[WD14] Detected params: {_PARAM_KEYS}")

# Determine which threshold kwarg name this version uses
if "general_threshold" in _PARAM_KEYS:
    _THRESH_KWARG = "general_threshold"
elif "general_thresh" in _PARAM_KEYS:
    _THRESH_KWARG = "general_thresh"
else:
    _THRESH_KWARG = "threshold"

# fmt param tells us the return order — use it if available
_FMT_PARAM = "fmt" in _PARAM_KEYS


def _tag_image(image: Image.Image, threshold: float) -> dict:
    kwargs = {
        "model_name":          MODEL,
        _THRESH_KWARG:         threshold,
        "character_threshold": 0.85,
    }
    if "character_threshold" not in _PARAM_KEYS:
        kwargs.pop("character_threshold", None)
    if "character_thresh" in _PARAM_KEYS:
        kwargs["character_thresh"] = 0.85

    # Request explicit return order so we always know rating=0, general=1, character=2
    if _FMT_PARAM:
        kwargs["fmt"] = ("rating", "general", "character")

    result = get_wd14_tags(image, **kwargs)

    RATING_KEYS = {"safe", "questionable", "explicit", "sensitive"}

    if isinstance(result, dict):
        general = result
    elif isinstance(result, (list, tuple)) and len(result) == 3:
        if _FMT_PARAM:
            # We requested ('rating','general','character'), so index 1 is general
            general = result[1]
        else:
            # Heuristic: identify rating dict, pick the larger of the other two
            rating_idx = next(
                (i for i, p in enumerate(result)
                 if isinstance(p, dict) and RATING_KEYS & p.keys()), None
            )
            others = [p for i, p in enumerate(result) if i != rating_idx]
            general = max(others, key=lambda d: len(d) if isinstance(d, dict) else 0)
    else:
        general = {}

    return {k: v for k, v in general.items() if v >= threshold}


# ── Routes ────────────────────────────────────────────────────────────────────

@app.route("/health")
def health():
    return jsonify({"status": "ok", "model": MODEL, "thresh_param": _THRESH_KWARG})


@app.route("/tag", methods=["POST"])
def tag():
    data = request.get_json(silent=True) or {}
    if "image" not in data:
        return jsonify({"error": "Missing 'image' (base64)"}), 400

    try:
        threshold = float(data.get("threshold", 0.35))
        image     = Image.open(io.BytesIO(base64.b64decode(data["image"])))
        general   = _tag_image(image, threshold)

        results = sorted(
            [{"label": tag, "score": round(score, 4)} for tag, score in general.items()],
            key=lambda x: x["score"], reverse=True,
        )
        return jsonify(results)

    except Exception as e:
        return jsonify({"error": str(e)}), 500


# ── Entry point ───────────────────────────────────────────────────────────────

if __name__ == "__main__":
    print(f"[WD14] Warming up model '{MODEL}'…")
    try:
        _tag_image(Image.new("RGB", (64, 64), (255, 255, 255)), threshold=0.99)
        print(f"[WD14] Ready — http://localhost:{PORT}")
        app.run(host="0.0.0.0", port=PORT, debug=False)
    except Exception as e:
        print(f"[WD14] ERROR: {e}")
        sys.exit(1)
