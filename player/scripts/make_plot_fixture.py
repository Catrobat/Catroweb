"""Builds player/fixtures/plot-and-open-url.catrobat from Catroid's catrobatTests/bricks/looks/testSetTransparency.catrobat.
Usage: python3 make_plot_fixture.py <testSetTransparency.catrobat> <out.catrobat>"""
import sys, zipfile
src, out = sys.argv[1], sys.argv[2]
x = zipfile.ZipFile(src).read("code.xml").decode()
ids = iter(range(0xc0, 0x100))


def brick(kind, *formulas):
    body = "".join(f"""
                    <formula category="{category}">{value}
                    </formula>""" for category, value in formulas)
    return f"""<brick type="{kind}">
                  <brickId>00000000-0000-4000-8000-0000000000{next(ids):02x}</brickId>
                  <commentedOut>false</commentedOut>{f'''
                  <formulaList>{body}
                  </formulaList>''' if formulas else ''}
                </brick>
                """


def leaf(kind, value):
    return f"""
                      <additionalChildren/>
                      <type>{kind}</type>
                      <value>{value}</value>"""


def place_at(x, y):
    return brick("PlaceAtBrick", ("Y_POSITION", leaf("NUMBER", y)), ("X_POSITION", leaf("NUMBER", x)))


bricks = (place_at(-300, -600) + brick("StartPlotBrick") + place_at(300, -600) + brick("StopPlotBrick")
          + brick("SavePlotBrick", ("WRITE_FILENAME", leaf("STRING", "plot")))
          + brick("OpenUrlBrick", ("OPEN_URL", leaf("STRING", "example.org/?q=&lt;b&gt;"))))
# Before the first script's closing FinishStageBrick, as in make_sound_fixture.py.
end = x.index("</brickList>")
finish = x.rindex('<brick type="FinishStageBrick">', 0, end)
x = x[:finish] + bricks + x[finish:]
with zipfile.ZipFile(src) as base, zipfile.ZipFile(out, "w", zipfile.ZIP_DEFLATED) as z:
    for name in base.namelist():  # the base project's looks
        if "/images/" in name and not name.endswith(".nomedia"):
            z.writestr(name, base.read(name))
    z.writestr("code.xml", x)
