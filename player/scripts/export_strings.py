"""Writes Catroid's English strings as {R.string id: text} for the player's Context.getString.

  export_strings.py <javap> <R.jar> <out.json> <res/values dir>...

and, for the start screen (web/start.js), the few strings it shows in every language Catroid ships, as
<out dir>/<language tag>.json ({name: text}, English where a translation lacks one) plus <out dir>/locales.json:

  export_strings.py --start <out dir> <res dir>...

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


# What the start screen says: about a project's required resources (Brick.java), from StageResourceHolder's dialog,
# the formula editor's device categories and the app's name;
START = ["prestage_resource_not_available_title", "prestage_resource_not_available_text",
         "prestage_no_acceleration_sensor_available", "prestage_no_inclination_sensor_available",
         "prestage_no_compass_sensor_available", "prestage_no_gps_sensor_available", "prestage_no_vibration_available",
         "prestage_no_back_camera_available", "prestage_no_front_camera_available", "prestage_no_camera_available",
         "prestage_no_flash_available", "prestage_no_face_detection_available", "prestage_no_pose_detection_available",
         "prestage_no_text_detection_available", "prestage_no_object_detection_available",
         "prestage_default_resource_not_available", "formula_editor_device_sensors", "formula_editor_sensor_loudness",
         "formula_editor_speech_recognition", "formula_editor_device_nfc", "formula_editor_device_lego_nxt",
         "formula_editor_device_lego_ev3", "formula_editor_device_phiro", "formula_editor_device_arduino",
         "formula_editor_device_raspberry", "formula_editor_device_cast", "preference_title_multiplayer_variables_enabled",
         "app_name", "google_play_store_logo",
         # its status and buttons, and the player's prompts (catroid/BrowserHandoff)
         "loading", "please_wait", "status_downloading", "main_menu_play", "play", "error_load_project",
         "stage_dialog_back", "save", "brick_open", "cancel"]


def language_tag(qualifier):
    """values-pt-rBR -> pt-br; Android's old codes iw/in -> he/id. None for values-v25 and the like."""
    match = re.fullmatch(r"([a-z]{2,3})(?:-r([A-Z]{2}))?", qualifier)
    if not match:
        return None
    language = {"iw": "he", "in": "id"}.get(match[1], match[1])
    return f"{language}-{match[2].lower()}" if match[2] else language


def read_strings(values_dir):
    texts = {}
    for file in sorted(Path(values_dir).glob("*.xml")):
        for element in ET.parse(file).getroot().iter("string"):
            texts[element.get("name")] = android_text("".join(element.itertext()))
    return texts


def export_start(out, *res_dirs):
    english = {}
    for res in res_dirs:
        english.update(read_strings(Path(res) / "values"))
    missing = [name for name in START if name not in english]
    assert not missing, f"Catroid no longer has {missing}"
    tags = {"en": {name: english[name] for name in START}}
    for res in res_dirs:
        for values in sorted(Path(res).glob("values-*")):
            tag = language_tag(values.name[len("values-"):])
            if tag:
                texts = read_strings(values)
                tags.setdefault(tag, dict(tags["en"])).update({n: texts[n] for n in START if n in texts})
    Path(out).mkdir(parents=True, exist_ok=True)
    for tag, texts in tags.items():
        (Path(out) / f"{tag}.json").write_text(json.dumps(texts, ensure_ascii=False, separators=(",", ":")))
    (Path(out) / "locales.json").write_text(json.dumps(sorted(tags)))
    print(f"start screen strings in {len(tags)} languages")


def main(javap, r_jar, out, *values_dirs):
    listing = subprocess.run([javap, "-constants", "-cp", r_jar, "org.catrobat.catroid.R$string"],
                             capture_output=True, text=True, check=True).stdout
    ids = dict(re.findall(r"static final int (\w+) = (-?\d+);", listing))
    texts = {}
    for values in values_dirs:
        texts.update(read_strings(values))
    strings = {ids[name]: text for name, text in texts.items() if name in ids}
    Path(out).write_text(json.dumps(strings, ensure_ascii=False, separators=(",", ":")))
    print(f"{len(strings)} strings for Context.getString")


if __name__ == "__main__":
    assert android_text('Hello   \\n "a  b" it\\\'s') == "Hello \n a  b it's", android_text('Hello   \\n "a  b" it\\\'s')
    assert android_text('" "') == " " and android_text("  a  ") == "a"
    assert language_tag("pt-rBR") == "pt-br" and language_tag("iw") == "he" and language_tag("v25") is None
    if sys.argv[1] == "--start":
        export_start(*sys.argv[2:])
    else:
        main(*sys.argv[1:])
