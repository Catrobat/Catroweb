"""Builds player/fixtures/speak-and-wait.catrobat from Catroid's catrobatTests/bricks/looks/testSetTransparency.catrobat.
Usage: python3 make_speak_fixture.py <testSetTransparency.catrobat> <out.catrobat>"""
import sys, zipfile
src, out = sys.argv[1], sys.argv[2]
x = zipfile.ZipFile(src).read("code.xml").decode()
bricks = """<brick type="SpeakAndWaitBrick">
                  <brickId>00000000-0000-4000-8000-0000000000b1</brickId>
                  <commentedOut>false</commentedOut>
                  <formulaList>
                    <formula category="SPEAK">
                      <additionalChildren/>
                      <type>STRING</type>
                      <value>Hello</value>
                    </formula>
                  </formulaList>
                </brick>
                """
# Before the first script's closing FinishStageBrick, as in make_sound_fixture.py.
end = x.index("</brickList>")
finish = x.rindex('<brick type="FinishStageBrick">', 0, end)
x = x[:finish] + bricks + x[finish:]
with zipfile.ZipFile(src) as base, zipfile.ZipFile(out, "w", zipfile.ZIP_DEFLATED) as z:
    for name in base.namelist():  # the base project's looks
        if "/images/" in name and not name.endswith(".nomedia"):
            z.writestr(name, base.read(name))
    z.writestr("code.xml", x)
