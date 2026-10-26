"""Writes what the code export (src/main/java/org/catrobat/web/codeview) needs from Catroid's resources.

  export_code_resources.py <javap> <R.jar> <res dir> <flavour res dir> <out dir>

<out dir>/resources.json, read by the export itself:
  layouts:  {R.layout id: view tree} for the brick layouts and what they include, as LayoutInflater would build them
  strings:  {R.string id: marker}, plurals: {R.plurals id: marker}, arrays: {R.array id: [marker, ...]}
<out dir>/strings/<locale>.json, Catroid's translations, which share puts in for the markers:
  {"s": {name: text}, "p": {name: {quantity: text}}, "a": {name: [text, ...]}}, locale as in values-<locale>.

A marker stands for a string in the export's output, so one export serves every language:
\\x01 kind ":" name (\\x1f argument)* \\x02, where kind is s (string), p (plural; first argument: the quantity) or a
(array; first argument: the index). A string's marker carries the format specifiers of its English text as
arguments, which String.format replaces with the values (see CodeViewLocalizer on share)."""
import json
import re
import subprocess
import sys
import xml.etree.ElementTree as ET
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent))
from export_strings import android_text  # noqa: E402

A = "{http://schemas.android.com/apk/res/android}"
FORMAT = re.compile(r"%%|%n|%(?:\d+\$)?[-#+ 0,(]*\d*(?:\.\d+)?[a-zA-Z]")  # Java format specifiers (and %%, %n)
START, SEP, END = "\x01", "\x1f", "\x02"


def r_ids(javap, r_jar, kind):
    listing = subprocess.run([javap, "-constants", "-cp", r_jar, f"org.catrobat.catroid.R${kind}"],
                             capture_output=True, text=True, check=True).stdout
    return {name: int(value) for name, value in re.findall(r"static final int (\w+) = (-?\d+);", listing)}


def read_values(*dirs):
    """Strings, plurals and arrays of these values directories, a later one winning (the flavour's after main)."""
    strings, plurals, arrays = {}, {}, {}
    for values in dirs:
        for file in sorted(Path(values).glob("*.xml")) if Path(values).is_dir() else []:
            root = ET.parse(file).getroot()
            for e in root.iter("string"):
                strings[e.get("name")] = android_text("".join(e.itertext()))
            for e in root.iter("plurals"):
                plurals[e.get("name")] = {i.get("quantity"): android_text("".join(i.itertext())) for i in e.iter("item")}
            for e in list(root.iter("string-array")) + list(root.iter("array")):
                arrays[e.get("name")] = [android_text("".join(i.itertext())) for i in e.iter("item")]
    for name, items in arrays.items():  # @string/x items are that string
        arrays[name] = [strings.get(i[8:], i) if i.startswith("@string/") else i for i in items]
    return strings, plurals, arrays


def specifiers(text):
    return [f for f in FORMAT.findall(text) if f not in ("%%", "%n")]


def marker(kind, name, *args):
    return START + kind + ":" + name + "".join(SEP + str(a) for a in args) + END


def view_tree(layout_dir, name, ids, string_ids, seen=()):
    """One layout as {t: type (Group, BrickLayout, Text, Spinner, CheckBox, RadioButton, View, Category), i: id, s: style, x: string id | l: literal text, g: 1 if gone, c: [children]}."""
    if name in seen:
        raise ValueError("layout includes itself: " + name)
    return node(ET.parse(layout_dir / f"{name}.xml").getroot(), layout_dir, ids, string_ids, seen + (name,))


def node(el, layout_dir, ids, string_ids, seen):
    tag = el.tag.rsplit(".", 1)[-1]
    if el.tag == "include":
        layout = el.get("layout").split("/")[-1]
        if layout.startswith("icon_brick_category_"):
            return {"t": "Category", "s": layout[len("icon_brick_category_"):]}
        out = view_tree(layout_dir, layout, ids, string_ids, seen)
    elif tag == "merge" or len(el) > 0 or tag.endswith("Layout") or tag.endswith("Group"):
        # Catroid's own BrickLayout as one, the other layouts as a LinearLayout (CodeLayoutInflater)
        out = {"t": "BrickLayout" if tag == "BrickLayout" else "Group",
               "c": [node(c, layout_dir, ids, string_ids, seen) for c in el if isinstance(c.tag, str)]}
    elif tag in ("CheckBox", "RadioButton"):
        out = {"t": tag}
    elif tag.endswith("Spinner"):
        out = {"t": "Spinner"}
    elif tag.endswith("TextView") or tag in ("EditText", "Button"):
        out = {"t": "Text"}
    else:
        out = {"t": "View"}
    style = el.get("style", "").replace("@style/", "")
    if style:
        out["s"] = style
    view_id = (el.get(A + "id") or "").split("/")[-1]
    if view_id in ids:
        out["i"] = ids[view_id]
    if el.get(A + "visibility") == "gone":
        out["g"] = 1
    text = el.get(A + "text")
    if text and text.startswith("@string/") and text[8:] in string_ids:
        out["x"] = string_ids[text[8:]]
    elif text and not text.startswith("@"):
        out["l"] = android_text(text)
    return out


def main(javap, r_jar, res, flavour_res, out):
    res, out = Path(res), Path(out)
    ids, layout_ids = r_ids(javap, r_jar, "id"), r_ids(javap, r_jar, "layout")
    string_ids, plural_ids, array_ids = r_ids(javap, r_jar, "string"), r_ids(javap, r_jar, "plurals"), r_ids(javap, r_jar, "array")
    strings, plurals, arrays = read_values(res / "values", Path(flavour_res) / "values")

    layouts = {}
    for file in sorted((res / "layout").glob("*.xml")):
        if file.stem in layout_ids and (file.stem.startswith("brick_") or file.stem.startswith("view_brick")):
            layouts[layout_ids[file.stem]] = view_tree(res / "layout", file.stem, ids, string_ids)
    resources = {
        "layouts": layouts,
        "strings": {string_ids[n]: marker("s", n, *specifiers(t)) for n, t in strings.items() if n in string_ids},
        "plurals": {plural_ids[n]: marker("p", n)[:-1] for n in plurals if n in plural_ids},  # + quantity + END
        "arrays": {array_ids[n]: [marker("a", n, i) for i in range(len(items))] for n, items in arrays.items() if n in array_ids},
    }
    out.mkdir(parents=True, exist_ok=True)
    (out / "resources.json").write_text(json.dumps(resources, ensure_ascii=False, separators=(",", ":")))

    (out / "strings").mkdir(exist_ok=True)
    locales = [("en", res / "values", Path(flavour_res) / "values")]
    for values in sorted(res.glob("values-*")):
        locale = values.name[len("values-"):]
        if re.fullmatch(r"[a-z]{2,3}(-r[A-Z]{2})?", locale):
            locales.append((locale, values, Path(flavour_res) / values.name))
    for locale, *dirs in locales:
        # Untranslated names fall back to English on share, so a locale only lists its own.
        s, p, a = read_values(*dirs)
        (out / "strings" / f"{locale}.json").write_text(
            json.dumps({"s": s, "p": p, "a": a}, ensure_ascii=False, separators=(",", ":"), sort_keys=True))
    print(f"code export: {len(layouts)} layouts, {len(resources['strings'])} strings, {len(locales)} languages")


if __name__ == "__main__":
    assert marker("s", "x", "%1$s") == "\x01s:x\x1f%1$s\x02"
    assert specifiers("%1$s at %d%% of %.1f") == ["%1$s", "%d", "%.1f"]
    main(*sys.argv[1:])
