from PIL import Image


def flatten(image):
    """RGB for the model. Transparent pixels become white: convert("RGB") makes them black, and a sprite cut out
    on black scores as nudity (a Friday Night Funkin' sprite: 0.99 on black, 0.53 on white)."""
    if image.mode in ("RGBA", "LA", "PA") or "transparency" in image.info:
        image = image.convert("RGBA")  # before resizing: a resampled colour key would leave dark edges
        image.thumbnail((1024, 1024))  # the model looks at 224 px; spares the canvas copies of huge images
        return Image.alpha_composite(Image.new("RGBA", image.size, "white"), image).convert("RGB")
    return image.convert("RGB")
