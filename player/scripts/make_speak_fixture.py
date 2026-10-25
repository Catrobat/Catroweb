"""Builds player/fixtures/speak-and-wait.catrobat from Catroid's catrobatTests/bricks/looks/testSetTransparency.catrobat.
Usage: python3 make_speak_fixture.py <testSetTransparency.catrobat> <out.catrobat>"""
import sys, zipfile
src, out = sys.argv[1], sys.argv[2]
x = zipfile.ZipFile(src).read("code.xml").decode()
bricks = """<brick type="ResetTimerBrick">
                  <brickId>00000000-0000-4000-8000-0000000000b0</brickId>
                  <commentedOut>false</commentedOut>
                </brick>
                <brick type="SpeakAndWaitBrick">
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
                <brick type="AssertEqualsBrick">
                  <brickId>00000000-0000-4000-8000-0000000000b2</brickId>
                  <commentedOut>false</commentedOut>
                  <formulaList>
                    <formula category="ASSERT_EQUALS_EXPECTED">
                      <additionalChildren/>
                      <type>FUNCTION</type>
                      <value>TRUE</value>
                    </formula>
                    <formula category="ASSERT_EQUALS_ACTUAL">
                      <additionalChildren/>
                      <leftChild>
                        <additionalChildren/>
                        <type>SENSOR</type>
                        <value>TIMER</value>
                      </leftChild>
                      <rightChild>
                        <additionalChildren/>
                        <type>NUMBER</type>
                        <value>0.3</value>
                      </rightChild>
                      <type>OPERATOR</type>
                      <value>GREATER_THAN</value>
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
