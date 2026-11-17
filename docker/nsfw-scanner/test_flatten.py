"""python3 test_flatten.py (needs only Pillow)."""
from PIL import Image

from flatten import flatten

transparent = Image.new("RGBA", (2, 1), (0, 0, 0, 0))
transparent.putpixel((1, 0), (200, 10, 10, 255))
flat = flatten(transparent)
assert [flat.getpixel((0, 0)), flat.getpixel((1, 0))] == [(255, 255, 255), (200, 10, 10)]

palette = Image.new("P", (1, 1), 0)
palette.info["transparency"] = 0
assert flatten(palette).getpixel((0, 0)) == (255, 255, 255)

opaque = Image.new("RGB", (1, 1), (0, 0, 0))
assert flatten(opaque).getpixel((0, 0)) == (0, 0, 0), "opaque images stay as they are"

print("ok")
