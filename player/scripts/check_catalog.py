"""Checks the brick and formula catalog (README.md, "Brick catalog"): every category of Catroid's CategoryBricksFactory
is there with its bricks, at least MIN_BRICKS different ones, each shown as the app does (the code export's checks), the
formula editor's Functions, Sensors and Operators are there (functions and sensors with their names), and TeaVM
stubbed no brick method (stub-report.tsv), which would leave that brick unusable in the player and wrong here.

  check_catalog.py <built code-export dir> <stub-report.tsv> <CategoryBricksFactory.kt>"""
import json
import re
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent))
from check_code_export import problems  # noqa: E402

MIN_BRICKS = 200
NOT_A_CATEGORY = {"category_recently_used"}  # the picker's history, not a list of the factory's own
MISSING = "drop-down without a selection"


def allowed(brick, problem):
    """A drop-down may select nothing only where it has nothing to choose from: the project's or the device's choices
    (the browser has no speech languages), which the empty project lacks. An array resource's cannot be empty
    (WebCompat.arrayAdapterFromResource)."""
    if not problem.endswith(MISSING):
        return False
    parts = brick.get("parts", []) + [p for s in brick.get("sections") or [] for p in s.get("parts", [])]
    return all(p.get("choices") == 0 for p in parts if p.get("missing"))


def factory_categories(source):
    body = source[source.index("fun getBricks("):]
    body = body[:body.index("else ->")]
    return set(re.findall(r"R\.string\.(category_\w+)", body)) - NOT_A_CATEGORY


def check(catalog, stubs, categories):
    found = []
    listed = {c["key"]: c for c in catalog["categories"]}
    for key in sorted(categories - listed.keys()):
        found.append(f"category {key} missing")
    types = set()
    for category in catalog["categories"]:
        if "error" in category:
            found.append(f"category {category['key']}: {category['error']}")
        if not category["bricks"]:
            found.append(f"category {category['key']}: no bricks")
        for brick in category["bricks"]:
            types.add(brick["type"])
            found += [p for p in problems(brick, f"{category['key']}:") if not allowed(brick, p)]
    if len(types) < MIN_BRICKS:
        found.append(f"only {len(types)} different bricks, expected at least {MIN_BRICKS}")
    for kind in ("functions", "sensors", "operators"):
        elements = catalog["formula"][kind]
        if not elements:
            found.append(f"no formula {kind}")
        # The editor shows some operators only as a key (POW is "^"), with no name of their own
        found += [f"formula {kind} {e['name']}: no name in the formula editor" for e in elements
                  if not e["label"] and kind != "operators"]
    found += [f"stubbed: {line.split(chr(9))[0]} (uses {line.split(chr(9))[-1]})" for line in stubs
              if line.startswith("org/catrobat/catroid/content/bricks/")]
    return found, len(types)


def main(export_dir, stub_report, factory):
    catalog = json.loads((Path(export_dir) / "catalog.json").read_text())
    found, bricks = check(catalog, Path(stub_report).read_text().splitlines(), factory_categories(Path(factory).read_text()))
    for line in found:
        print(line)
    formula = catalog["formula"]
    print(f"{len(catalog['categories'])} categories, {bricks} bricks, {len(formula['functions'])} functions, "
          f"{len(formula['sensors'])} sensors, {len(formula['operators'])} operators; {len(found)} problems")
    return 1 if found else 0


if __name__ == "__main__":
    assert factory_categories('fun getBricks(c) { when (c) {\n context.getString(R.string.category_motion) -> a()\n'
                              ' context.getString(R.string.category_recently_used) -> b()\n else -> c()\n'
                              ' R.string.category_pen') == {"category_motion"}
    ok = {"categories": [{"key": "category_motion", "bricks": [{"type": "SetXBrick", "category": "motion", "parts": [
        {"kind": "choice", "text": "", "missing": True, "choices": 0}]}]}], "formula": {k: [{"name": "X", "label": "x"}] for k in (
            "functions", "sensors", "operators")}}
    assert check(ok, [], {"category_motion"}) == ([f"only 1 different bricks, expected at least {MIN_BRICKS}"], 1)
    ok["categories"][0]["bricks"][0]["parts"][0]["choices"] = 3
    assert check(ok, [], {"category_motion"})[0][0] == "category_motion: SetXBrick: drop-down without a selection"
    found, _ = check(ok, ["org/catrobat/catroid/content/bricks/SetXBrick.getView()V\tjava/util/Locale.x()"], {"category_pen"})
    assert found[0] == "category category_pen missing" and found[-1].startswith("stubbed: org/catrobat/catroid/content/bricks/SetXBrick")
    sys.exit(main(*sys.argv[1:]))
