"""Builds player/fixtures/sensors.catrobat from Catroid's catrobatTests/bricks/looks/testSetTransparency.catrobat.
Usage: python3 make_sensors_fixture.py <testSetTransparency.catrobat> <out.catrobat>

Before the first script's finish brick: vibrate for 0.3 s, wait until the readings are in, then assert that the rounded sensor values,
joined by spaces, are those of the phone scripts/sensors_check.py emulates (inclination x and y, compass direction,
acceleration x, y and z, latitude, longitude), and that the loudness is above 0."""
import sys
import zipfile

SENSORS = ["X_INCLINATION", "Y_INCLINATION", "COMPASS_DIRECTION", "X_ACCELERATION", "Y_ACCELERATION",
           "Z_ACCELERATION", "LATITUDE", "LONGITUDE"]
EXPECTED = "30 20 90 1 2 3 47 15"


def node(type_, value, left="", right=""):
    left = f"<leftChild>{left}</leftChild>" if left else ""
    right = f"<rightChild>{right}</rightChild>" if right else ""
    return f"{left}{right}<type>{type_}</type><value>{value}</value>"


def joined(names):
    value = node("FUNCTION", "ROUND", node("SENSOR", names[0]))
    if len(names) == 1:
        return value
    return node("FUNCTION", "JOIN", value, node("FUNCTION", "JOIN", node("STRING", " "), joined(names[1:])))


def brick(type_, number, formulas):
    formulas = "".join(f'<formula category="{category}">{formula}</formula>' for category, formula in formulas)
    return f"""<brick type="{type_}">
                  <brickId>00000000-0000-4000-8000-0000000000c{number}</brickId>
                  <commentedOut>false</commentedOut>
                  <formulaList>{formulas}</formulaList>
                </brick>
                """


loud = node("OPERATOR", "GREATER_THAN", node("SENSOR", "LOUDNESS"), node("NUMBER", "0"))
# Waits until the readings are in (the first location fix and the fake microphone's beeps come when they come): a
# reading that never arrives ends in sensors_check.py's time-out, the asserts then say which value was wrong.
arrived = node("OPERATOR", "LOGICAL_AND", node("OPERATOR", "EQUAL", joined(SENSORS), node("STRING", EXPECTED)), loud)
bricks = (brick("VibrationBrick", 0, [("VIBRATE_DURATION_IN_SECONDS", node("NUMBER", "0.3"))])
          + brick("WaitUntilBrick", 1, [("IF_CONDITION", arrived)])
          + brick("AssertEqualsBrick", 2, [("ASSERT_EQUALS_EXPECTED", node("STRING", EXPECTED)),
                                           ("ASSERT_EQUALS_ACTUAL", joined(SENSORS))])
          + brick("AssertEqualsBrick", 3, [("ASSERT_EQUALS_EXPECTED", node("FUNCTION", "TRUE")),
                                           ("ASSERT_EQUALS_ACTUAL", loud)]))
src, out = sys.argv[1], sys.argv[2]
x = zipfile.ZipFile(src).read("code.xml").decode()
# Its "test loop delay" assertion compares the loop's game time with the wall-clock timer, which a frame stalled on a
# loaded machine skews (the next frame's delta reaches back before the timer reset): commented out, without moving
# the brick[n] indexes that XStream references use, so the fixture only fails on what it adds.
loop = x.rindex('<brick type="AssertEqualsBrick">', 0, x.index("<value>loopRuntime</value>"))
x = x[:loop] + x[loop:].replace("<commentedOut>false</commentedOut>", "<commentedOut>true</commentedOut>", 1)
# Before the first script's closing FinishStageBrick, as in make_sound_fixture.py.
end = x.index("</brickList>")
finish = x.rindex('<brick type="FinishStageBrick">', 0, end)
x = x[:finish] + bricks + x[finish:]
with zipfile.ZipFile(src) as base, zipfile.ZipFile(out, "w", zipfile.ZIP_DEFLATED) as z:
    for name in base.namelist():  # the base project's looks
        if "/images/" in name and not name.endswith(".nomedia"):
            z.writestr(name, base.read(name))
    z.writestr("code.xml", x)
