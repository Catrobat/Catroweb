"""Builds player/fixtures/touching-color-maximize.catrobat from Catroid's catrobatTests/touchesColor/testTouchesColorRotate.catrobat.
Usage: python3 make_touching_color_fixture.py <testTouchesColorRotate.catrobat> <out.catrobat>"""
import struct, sys, zipfile, zlib
src, out = sys.argv[1], sys.argv[2]
x = zipfile.ZipFile(src).read("code.xml").decode()
scene = "Scene 1"
WIDTH, HEIGHT, FLOOR = 1080, 2075, 300  # the base project's screen; the floor's top is at y -737
ids = iter(range(0xd0, 0x100))


def png(width, height, row):
    """A plain RGBA PNG without Catroid's collision metadata, like the costumes of converted Scratch projects."""
    def chunk(kind, data):
        return struct.pack(">I", len(data)) + kind + data + struct.pack(">I", zlib.crc32(kind + data))
    raw = b"".join(b"\0" + row(y) for y in range(height))
    return (b"\x89PNG\r\n\x1a\n" + chunk(b"IHDR", struct.pack(">IIBBBBB", width, height, 8, 6, 0, 0, 0))
            + chunk(b"IDAT", zlib.compress(raw, 9)) + chunk(b"IEND", b""))


background = png(WIDTH, HEIGHT, lambda y: (b"\x7f\x69\x27\xff" if y >= HEIGHT - FLOOR else b"\xff\xff\xff\xff") * WIDTH)
sprite = png(50, 50, lambda y: b"\x31\x31\x31\xff" * 50)


def brick(kind, *formulas, inner=""):
    body = "".join(f"""
                    <formula category="{category}">{value}
                    </formula>""" for category, value in formulas)
    return f"""<brick type="{kind}">
                  <brickId>00000000-0000-4000-8000-0000000000{next(ids):02x}</brickId>
                  <commentedOut>false</commentedOut>{f'''
                  <formulaList>{body}
                  </formulaList>''' if formulas else ''}{f'''
                  <loopBricks>
                {inner}</loopBricks>''' if inner else ''}
                </brick>
                """


def leaf(kind, value, left="", right="", third=""):
    child = lambda tag, inner: f"\n<{tag}>{inner}\n</{tag}>" if inner else ""
    extra = child("org.catrobat.catroid.formulaeditor.FormulaElement", third)
    return f"""
                      {f"<additionalChildren>{extra}</additionalChildren>" if third else "<additionalChildren/>"}{child("leftChild", left)}{child("rightChild", right)}
                      <type>{kind}</type>
                      <value>{value}</value>"""


touching = leaf("FUNCTION", "COLLIDES_WITH_COLOR", leaf("STRING", "#7f6927"))
# Falls in steps of 10 until it touches the floor's colour; below the floor's top it has fallen through.
fallen_through = leaf("OPERATOR", "SMALLER_THAN", leaf("SENSOR", "OBJECT_Y"), leaf("NUMBER", "-900"))
bricks = (brick("PlaceAtBrick", ("Y_POSITION", leaf("NUMBER", "0")), ("X_POSITION", leaf("NUMBER", "0")))
          + brick("SetSizeToBrick", ("SIZE", leaf("NUMBER", "80")))
          + brick("RepeatUntilBrick", ("REPEAT_UNTIL_CONDITION", leaf("OPERATOR", "LOGICAL_OR", touching, fallen_through)),
                  inner=brick("ChangeYByNBrick", ("Y_POSITION_CHANGE", leaf("NUMBER", "-10"))))
          + brick("AssertEqualsBrick", ("ASSERT_EQUALS_EXPECTED", leaf("FUNCTION", "TRUE")), ("ASSERT_EQUALS_ACTUAL", touching))
          # Within 2 %: the GPU may round the floor's colour by one.
          + brick("AssertEqualsBrick", ("ASSERT_EQUALS_EXPECTED", leaf("FUNCTION", "TRUE")),
                  ("ASSERT_EQUALS_ACTUAL", leaf("FUNCTION", "COLOR_EQUALS_COLOR",
                                                leaf("FUNCTION", "COLOR_AT_XY", leaf("NUMBER", "0"), leaf("NUMBER", "-900")),
                                                leaf("STRING", "#7f6927"), leaf("NUMBER", "2"))))
          + brick("FinishStageBrick"))
x = x.replace("<screenMode>STRETCH</screenMode>", "<screenMode>MAXIMIZE</screenMode>")
x = x.replace("<programName>testTouchesColorRotate</programName>", "<programName>touching-color-maximize</programName>")
start = x.index("<brickList>") + len("<brickList>")
x = x[:start] + "\n                " + bricks + x[x.index("</brickList>", start):]
with zipfile.ZipFile(out, "w", zipfile.ZIP_DEFLATED) as z:
    z.writestr("code.xml", x)
    z.writestr(f"{scene}/images/Background.png", background)
    z.writestr(f"{scene}/images/My actor or object.png", sprite)
