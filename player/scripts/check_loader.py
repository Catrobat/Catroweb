"""Checks the project loader against Catroid's own test projects.

  check_loader.py prepare <out dir> <dir with .catrobat files>...   writes every code.xml into
                                                                     <out>/project-files/check.txt (id, newline, XML;
                                                                     NUL between projects) and the expected results
  check_loader.py compare <out dir> <dumped page>                compares them with what index.html#check wrote

Compares per project what ProjectLoader.summary reports: the number of scenes, sprites, scripts and top-level bricks,
and the class of each sprite, script and top-level brick."""
import html
import re
import sys
import zipfile
import xml.etree.ElementTree as ET
from pathlib import Path


def resolve(element, parents):
    """Follows XStream's reference="../../object[2]" to the element it stands for."""
    while element.get("reference"):
        for step in element.get("reference").split("/"):
            if step == "..":
                element = parents[element]
                continue
            match = re.fullmatch(r"([^\[]+)(?:\[(\d+)\])?", step)
            element = [child for child in element if child.tag == match[1]][int(match[2] or 1) - 1]
    return element


UPGRADE = Path(__file__).parent.parent / "build/catroid/catroid/src/main/java/org/catrobat/catroid/io/BackwardCompatibleCatrobatLanguageXStream.java"


def legacy_classes():
    """Before Catrobat language 0.93 scripts and bricks were elements like <foreverBrick>: Catroid's XML upgrade
    (modifyXMLToSupportUnknownFields) names their classes from its scriptInfoMap and brickInfoMap."""
    classes, brick = {"setGhostEffectBrick": "SetTransparencyBrick", "changeGhostEffectByNBrick": "ChangeTransparencyByNBrick"}, None
    for line in UPGRADE.read_text().splitlines():
        if found := re.search(r"new BrickInfo\((\w+)\.class", line):
            brick = found[1]
        elif found := re.search(r'brickInfoMap\.put\("(\w+)", brickInfo\)', line):
            classes[found[1]] = brick
        elif found := re.search(r'scriptInfoMap\.put\("(\w+)", (\w+)\.class', line):
            classes[found[1]] = found[2]
    return classes


LEGACY = {}


def simple_name(element, entry=None):
    """Catroid's converters write the class as type="RepeatBrick"; an element without an alias is named after it, or
    in the oldest projects by the alias of its entry in the list (a reference's entry names the class it stands for)."""
    if element.get("type"):
        return {"CollisionScript": "WhenBounceOffScript"}.get(element.get("type"), element.get("type"))
    tag = (element if entry is None else entry).tag
    if tag[0].islower() and not LEGACY:
        LEGACY.update(legacy_classes())
    return LEGACY.get(tag, tag.rsplit(".", 1)[-1])


def counts(code_xml):
    root = ET.fromstring(code_xml)
    parents = {child: parent for parent in root.iter() for child in parent}
    children = lambda element, path: [resolve(e, parents) for e in resolve(element, parents).findall(path)]
    scenes = children(root, "./scenes/*") or [root]  # before scenes the program held what is now one scene
    sprites, scripts, bricks, classes = 0, 0, 0, ""
    for sprite in (o for s in scenes for o in children(s, "./objectList/*")):
        sprites += 1
        # Catroid's sprite converter reads the legacy type="SingleSprite", or none at all, as Sprite
        classes += " " + (sprite.get("type") or "Sprite").replace("SingleSprite", "Sprite") + ":"
        for script in children(sprite, "./scriptList/*"):
            scripts += 1
            names = [simple_name(resolve(b, parents), b) for b in resolve(script, parents).findall("./brickList/*")]
            bricks += len(names)
            classes += f" {simple_name(script)}(" + "".join(" " + n for n in names) + " )"
    return f"{len(scenes)} scenes, {sprites} sprites, {scripts} scripts, {bricks} bricks |{classes}"


def prepare(out, *sources):
    projects, expected = [], []
    found = sorted((project, source) for source in sources for project in Path(source).rglob("*.catrobat"))
    for n, (project, source) in enumerate(found):
        with zipfile.ZipFile(project) as archive:
            code_xml = archive.read("code.xml")
        projects.append(f"{n}\n".encode() + code_xml)
        expected.append(f"{n}\t{counts(code_xml)}\t{project.relative_to(source)}")
    Path(out, "project-files").mkdir(parents=True, exist_ok=True)
    Path(out, "project-files", "check.txt").write_bytes(b"\0".join(projects))
    Path(out, "expected.tsv").write_text("\n".join(expected) + "\n")


def compare(out, page):
    found = re.search(r'<pre id="loader-check">(.*?)</pre>', Path(page).read_text(), re.S)
    if not found:
        sys.exit("The player wrote no results: it did not start, see the browser console")
    actual = dict(line.split("\t", 1) for line in html.unescape(found[1]).splitlines() if line)
    failures = []
    for line in Path(out, "expected.tsv").read_text().splitlines():
        n, want, name = line.split("\t")
        if actual.get(n) != want:
            failures.append(f"{name}: expected {want}, got {actual.get(n, 'nothing')}")
    total = len(Path(out, "expected.tsv").read_text().splitlines())
    print(f"Loader: {total - len(failures)} of {total} test projects load like the app")
    for failure in failures:
        print("  " + failure)
    return failures


if __name__ == "__main__":
    if sys.argv[1] == "prepare":
        prepare(sys.argv[2], *sys.argv[3:])
    else:
        sys.exit(1 if compare(sys.argv[2], sys.argv[3]) else 0)
