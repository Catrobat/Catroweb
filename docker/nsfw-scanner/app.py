import io
from flask import Flask, request, jsonify
from PIL import Image, UnidentifiedImageError
from transformers import pipeline

app = Flask(__name__)
app.config["MAX_CONTENT_LENGTH"] = 10 * 1024 * 1024  # 10 MB
classifier = pipeline("image-classification", model="Falconsai/nsfw_image_detection")


@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok"})


@app.route("/scan", methods=["POST"])
def scan():
    if not request.data:
        return jsonify({"error": "No image data provided"}), 400

    try:
        image = Image.open(io.BytesIO(request.data)).convert("RGB")
    except (UnidentifiedImageError, OSError, ValueError):
        return jsonify({"error": "Invalid image data"}), 400

    results = classifier(image)

    # Find the "nsfw" and "normal" scores
    scores = {r["label"]: r["score"] for r in results}
    nsfw_score = scores.get("nsfw", 0.0)
    safe_score = scores.get("normal", 0.0)

    return jsonify(
        {
            "safe": nsfw_score < 0.5,
            "nsfw_score": round(nsfw_score, 4),
            "safe_score": round(safe_score, 4),
            "label": "nsfw" if nsfw_score >= 0.5 else "safe",
        }
    )


if __name__ == "__main__":
    raise RuntimeError(
        "Do not run with Flask's built-in server in production. "
        "Start this app with gunicorn instead."
    )
