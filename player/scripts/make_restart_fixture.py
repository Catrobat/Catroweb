"""Builds player/fixtures/restart.catrobat for scripts/restart_check.py. Usage: python3 make_restart_fixture.py <out>

A 480x800 project whose only sprite counts the variable "counter" up from its initial 0, every 0.3 s, and draws a
blue line along y = 0 from x = -200 to x = -200 + 10 * counter: the line on the stage shows the counter."""
import sys
import zipfile


def node(type_, value, left="", right=""):
    left = f"<leftChild>{left}</leftChild>" if left else ""
    right = f"<rightChild>{right}</rightChild>" if right else ""
    return f"{left}{right}<type>{type_}</type><value>{value}</value>"


def brick(type_, number, formulas=(), inside=""):
    formulas = "".join(f'<formula category="{category}">{formula}</formula>' for category, formula in formulas)
    formulas = f"<formulaList>{formulas}</formulaList>" if formulas else ""
    return (f'<brick type="{type_}"><brickId>00000000-0000-4000-8000-0000000000d{number}</brickId>'
            f"<commentedOut>false</commentedOut>{formulas}{inside}</brick>")


COUNTER = ('<userVariable type="UserVariable" serialization="custom"><userVariable><default><initialIndex>0</initialIndex>'
           "<deviceValueKey>00000000-0000-4000-8000-0000000000e0</deviceValueKey><name>counter</name></default>"
           "</userVariable></userVariable>")
x_of_counter = node("OPERATOR", "PLUS", node("NUMBER", "-200"),
                    node("OPERATOR", "MULT", node("USER_VARIABLE", "counter"), node("NUMBER", "10")))
loop = (brick("ChangeVariableBrick", 4, [("VARIABLE_CHANGE", node("NUMBER", "1"))], COUNTER)
        + brick("PlaceAtBrick", 5, [("X_POSITION", x_of_counter), ("Y_POSITION", node("NUMBER", "0"))])
        + brick("WaitBrick", 6, [("TIME_TO_WAIT_IN_SECONDS", node("NUMBER", "0.3"))]))
bricks = (brick("PlaceAtBrick", 0, [("X_POSITION", node("NUMBER", "-200")), ("Y_POSITION", node("NUMBER", "0"))])
          + brick("SetPenSizeBrick", 1, [("PEN_SIZE", node("NUMBER", "8"))])
          + brick("PenDownBrick", 2)
          + brick("ForeverBrick", 3, inside=f"<loopBricks>{loop}</loopBricks>"))
code = f"""<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>
<program><header><applicationName>Pocket Code</applicationName><catrobatLanguageVersion>1.11</catrobatLanguageVersion>
<isCastProject>false</isCastProject><landscapeMode>false</landscapeMode><platform>Android</platform>
<programName>Restart</programName><scenesEnabled>true</scenesEnabled><screenHeight>800</screenHeight>
<screenMode>STRETCH</screenMode><screenWidth>480</screenWidth></header><settings/>
<scenes><scene><name>Scene (1)</name><objectList><object type="Sprite" name="Background"><lookList/><soundList/>
<scriptList><script type="StartScript" posX="0.0" posY="0.0"><brickList>{bricks}</brickList>
<commentedOut>false</commentedOut><scriptId>00000000-0000-4000-8000-0000000000f0</scriptId></script></scriptList>
<nfcTagList/><userVariables><userVariable reference="../../scriptList/script/brickList/brick[4]/loopBricks/brick/userVariable"/>
</userVariables><userLists/><userDefinedBrickList/></object></objectList></scene></scenes>
<programVariableList/><programListOfLists/><programMultiplayerVariableList/></program>
"""
with zipfile.ZipFile(sys.argv[1], "w", zipfile.ZIP_DEFLATED) as z:
    z.writestr("code.xml", code)
