"""Writes Catroid's English strings as {R.string id: text} for the player's Context.getString.

  export_strings.py <javap> <R.jar> <out.json> <res/values dir>...

The ids come from R.jar (the one the engine was compiled with); the texts from the <string> elements in values/*.xml
of each directory, a later one winning (the flavour's after main, as in the build),
read the way Android's aapt does: inner markup counts as text, quotes keep whitespace, otherwise whitespace collapses,
and backslash escapes apply."""
import json
import re
import subprocess
import sys
import xml.etree.ElementTree as ET
from pathlib import Path

ESCAPES = {"n": "\n", "t": "\t"}
UNQUOTED_SPACE = "\0"  # collapsed whitespace outside quotes, which alone is trimmed at the ends


def android_text(raw):
    out, quoted, i = [], False, 0
    while i < len(raw):
        c = raw[i]
        if c == "\\" and i + 1 < len(raw):
            n = raw[i + 1]
            if n == "u" and re.fullmatch(r"[0-9a-fA-F]{4}", raw[i + 2:i + 6]):
                out.append(chr(int(raw[i + 2:i + 6], 16)))
                i += 6
                continue
            out.append(ESCAPES.get(n, n))
            i += 2
            continue
        if c == '"':
            quoted = not quoted
        elif not quoted and c.isspace():
            if out and out[-1] != UNQUOTED_SPACE:
                out.append(UNQUOTED_SPACE)
        else:
            out.append(c)
        i += 1
    return "".join(out).strip(UNQUOTED_SPACE).replace(UNQUOTED_SPACE, " ")


def main(javap, r_jar, out, *values_dirs):
    listing = subprocess.run([javap, "-constants", "-cp", r_jar, "org.catrobat.catroid.R$string"],
                             capture_output=True, text=True, check=True).stdout
    ids = dict(re.findall(r"static final int (\w+) = (-?\d+);", listing))
    texts = {}
    for values in values_dirs:
        for file in sorted(Path(values).glob("*.xml")):
            for element in ET.parse(file).getroot().iter("string"):
                texts[element.get("name")] = android_text("".join(element.itertext()))
    strings = {ids[name]: text for name, text in texts.items() if name in ids}
    Path(out).write_text(json.dumps(strings, ensure_ascii=False, separators=(",", ":")))
    print(f"{len(strings)} strings for Context.getString")


if __name__ == "__main__":
    assert android_text('Hello   \\n "a  b" it\\\'s') == "Hello \n a  b it's", android_text('Hello   \\n "a  b" it\\\'s')
    assert android_text('" "') == " " and android_text("  a  ") == "a"
    main(*sys.argv[1:])
