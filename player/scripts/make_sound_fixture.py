"""Builds player/fixtures/sound-play-and-wait.catrobat from Catroid's catrobatTests/bricks/looks/testSetTransparency.catrobat.
Usage: python3 make_sound_fixture.py <testSetTransparency.catrobat> <out.catrobat>"""
import io, math, re, struct, sys, wave, zipfile
src, out = sys.argv[1], sys.argv[2]
x = zipfile.ZipFile(src).read("code.xml").decode()
# Its "test loop delay" assertion compares the loop's game time with the wall-clock timer, which a frame stalled on a
# loaded machine skews (the next frame's delta reaches back before the timer reset): commented out, without moving
# the brick[n] indexes that XStream references use, so the fixture only fails on what it adds.
loop = x.rindex('<brick type="AssertEqualsBrick">', 0, x.index("<value>loopRuntime</value>"))
x = x[:loop] + x[loop:].replace("<commentedOut>false</commentedOut>", "<commentedOut>true</commentedOut>", 1)
scene = re.search(r"<scene>\s*<name>([^<]*)</name>", x)[1]
buf = io.BytesIO()
with wave.open(buf, "wb") as w:  # one second, 440 Hz, 8 kHz mono 16-bit
    w.setnchannels(1); w.setsampwidth(2); w.setframerate(8000)
    w.writeframes(b"".join(struct.pack("<h", int(8000 * math.sin(2 * math.pi * 440 * i / 8000))) for i in range(8000)))
# The sound list of the object whose first script gets the new bricks.
sounds = x.rindex("<soundList/>", 0, x.index("<brickList>"))
x = x[:sounds] + '<soundList>\n            <sound fileName="tone.wav" name="tone">\n              <midiFile>false</midiFile>\n            </sound>\n          </soundList>' + x[sounds + len("<soundList/>"):]
bricks = """<brick type="ResetTimerBrick">
                  <brickId>00000000-0000-4000-8000-000000000001</brickId>
                  <commentedOut>false</commentedOut>
                </brick>
                <brick type="PlaySoundAndWaitBrick">
                  <brickId>00000000-0000-4000-8000-000000000002</brickId>
                  <commentedOut>false</commentedOut>
                  <sound reference="../../../../../soundList/sound"/>
                </brick>
                <brick type="AssertEqualsBrick">
                  <brickId>00000000-0000-4000-8000-000000000003</brickId>
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
                        <value>0.9</value>
                      </rightChild>
                      <type>OPERATOR</type>
                      <value>GREATER_THAN</value>
                    </formula>
                  </formulaList>
                </brick>
                """
# Before the first script's closing FinishStageBrick: inserting earlier would shift the brick[n] indexes that the
# project's XStream references use.
end = x.index("</brickList>")
finish = x.rindex('<brick type="FinishStageBrick">', 0, end)
x = x[:finish] + bricks + x[finish:]
with zipfile.ZipFile(src) as base, zipfile.ZipFile(out, "w", zipfile.ZIP_DEFLATED) as z:
    for name in base.namelist():  # the base project's looks
        if "/images/" in name and not name.endswith(".nomedia"):
            z.writestr(name, base.read(name))
    z.writestr("code.xml", x)
    z.writestr(f"{scene}/sounds/tone.wav", buf.getvalue())
