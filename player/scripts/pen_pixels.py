"""Counts pen-blue pixels in a PNG screenshot, stdlib only. Usage: python3 pen_pixels.py shot.png"""
import struct
import sys
import zlib


def paeth(a, b, c):
    p = a + b - c
    pa, pb, pc = abs(p - a), abs(p - b), abs(p - c)
    return a if pa <= pb and pa <= pc else (b if pb <= pc else c)


def rows(path):
    data = open(path, "rb").read()
    assert data[:8] == b"\x89PNG\r\n\x1a\n", "not a PNG"
    pos, idat = 8, b""
    while pos < len(data):
        length, kind = struct.unpack(">I4s", data[pos:pos + 8])
        chunk = data[pos + 8:pos + 8 + length]
        if kind == b"IHDR":
            width, height, depth, color = struct.unpack(">IIBB", chunk[:10])
            assert depth == 8 and color in (2, 6), "only 8-bit RGB(A) screenshots"
            bpp = 3 if color == 2 else 4
        elif kind == b"IDAT":
            idat += chunk
        pos += 12 + length

    raw, stride = zlib.decompress(idat), width * bpp
    prev = bytearray(stride)
    for y in range(height):
        start = y * (stride + 1)
        kind, line = raw[start], bytearray(raw[start + 1:start + 1 + stride])
        for i in range(stride):  # undo the row filter: 0 none, 1 sub, 2 up, 3 average, 4 paeth
            a = line[i - bpp] if i >= bpp else 0
            b = prev[i]
            c = prev[i - bpp] if i >= bpp else 0
            line[i] = (line[i] + (0, a, b, (a + b) // 2, paeth(a, b, c))[kind]) & 0xFF
        prev = line
        yield line, bpp


blue = 0
for line, bpp in rows(sys.argv[1]):
    for x in range(0, len(line), bpp):
        r, g, b = line[x], line[x + 1], line[x + 2]
        blue += r < 60 and g < 60 and b > 180
print(blue)
