"""Checks the code view's export (README.md, "Code view export") on Catroid's own test projects and the fixtures:
every project exports, and every brick shows as the app does, with no getView failure, no layout missing, no category
unknown, no empty formula field and no drop-down without a selection.

  check_code_export.py <built code-export dir> <dir with .catrobat files>...

Runs export.mjs under Node like share does (one process per project, with Node's permission model) and prints one line
per problem, then a summary with the export's start and per-project times."""
import json
import os
import subprocess
import sys
import tempfile
import time
import zipfile
from pathlib import Path


def problems(brick, where):
    found = []
    name = f"{where} {brick.get('type')}"
    if "error" in brick:
        found.append(f"{name}: getView failed: {brick['error']}")
    if brick.get("category", "unknown") == "unknown":
        found.append(f"{name}: no category")
    for part in brick.get("parts", []):
        if part["kind"] == "formula" and not part["text"].strip():
            found.append(f"{name}: empty formula field")
        if part.get("missing"):
            found.append(f"{name}: drop-down without a selection")
    for child in brick.get("bricks") or []:
        found += problems(child, where)
    for section in brick.get("sections") or []:
        for child in section.get("bricks", []):
            found += problems(child, where)
    return found


def count(node):
    """Scripts and bricks (each has a type) in an export."""
    if isinstance(node, dict):
        return ("type" in node) + sum(count(v) for v in node.values())
    return sum(count(v) for v in node) if isinstance(node, list) else 0


def check(export, project):
    if "error" in export:
        return [f"{project}: not exported: {export['error']}"]
    found = [] if not export.get("missing_layouts") else [f"{project}: {export['missing_layouts']} layouts missing"]
    for scene in export["scenes"]:
        for sprite in scene["objects"] + [g for o in scene["objects"] for g in o.get("group", [])]:
            for script in sprite["scripts"]:
                found += problems(script, f"{project} / {sprite['name']}:")
    return found


def run(export_dir, xml):
    return subprocess.run(["node", "--permission", f"--allow-fs-read={export_dir}", f"--allow-fs-read={xml}",
                           "--max-old-space-size=1024", str(Path(export_dir) / "export.mjs"), str(xml)],
                          capture_output=True, text=True, timeout=120)


def main(export_dir, *project_dirs):
    export_dir = str(Path(export_dir).resolve())
    found, times, bricks = [], [], 0
    with tempfile.TemporaryDirectory() as tmp:
        empty = Path(tmp) / "empty.xml"
        empty.write_text("<program/>")
        start = time.time()
        run(export_dir, empty)
        cold = time.time() - start
        projects = sorted(p for d in project_dirs for p in Path(d).rglob("*.catrobat"))
        for project in projects:
            with zipfile.ZipFile(project) as archive:
                xml = Path(tmp) / "code.xml"
                xml.write_bytes(archive.read("code.xml"))
            start = time.time()
            result = run(export_dir, xml)
            times.append(time.time() - start)
            name = str(project.relative_to(project.parents[1]))
            if result.returncode:
                found.append(f"{name}: Node failed: {result.stderr.strip()[-300:]}")
                continue
            export = json.loads(result.stdout)
            bricks += count(export)
            found += check(export, name)
    for line in found:
        print(line)
    print(f"{len(projects)} projects, {bricks} scripts and bricks, {len(found)} problems; Node with the export: "
          f"{cold:.2f} s to start, {sum(times) / max(1, len(times)):.2f} s per project on average")
    return 1 if found else 0


if __name__ == "__main__":
    assert problems({"type": "X", "category": "motion", "parts": [{"kind": "formula", "text": " "}]}, "p") == [
        "p X: empty formula field"]
    assert problems({"type": "X", "category": "unknown", "parts": [], "error": "e"}, "p") == [
        "p X: getView failed: e", "p X: no category"]
    assert count({"scenes": [{"objects": [{"scripts": [{"type": "S", "bricks": [{"type": "B", "sections": []}]}]}]}]}) == 2
    sys.exit(main(*sys.argv[1:]))
