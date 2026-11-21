"""Saves a shared Scratch project as a fixture for scratch_check.sh: fixtures/scratch/<name>/ gets project.json, every
costume and sound (named <md5>.<ext>, as the converter expects them) and credit.json (title, author, link, licence:
shared Scratch projects are CC BY-SA 2.0). Run once by hand; the checks themselves never go online.
Usage: python3 fetch_scratch_fixture.py <scratch project id> <name>"""
import json
import sys
import urllib.request
from pathlib import Path


def get(url):
    with urllib.request.urlopen(urllib.request.Request(url, headers={"User-Agent": "catroweb-player-fixtures"}), timeout=30) as r:
        return r.read()


scratch_id, name = sys.argv[1], sys.argv[2]
meta = json.loads(get(f"https://api.scratch.mit.edu/projects/{scratch_id}"))
project = get(f"https://projects.scratch.mit.edu/{scratch_id}?token={meta['project_token']}")
targets = json.loads(project).get("targets")
if not targets:
    sys.exit("Not a Scratch 3 project.json")
out = Path(__file__).resolve().parent.parent / "fixtures" / "scratch" / name
out.mkdir(parents=True, exist_ok=True)
(out / "project.json").write_bytes(project)
for asset in {a["md5ext"] for t in targets for a in t.get("costumes", []) + t.get("sounds", [])}:
    (out / asset).write_bytes(get(f"https://assets.scratch.mit.edu/internalapi/asset/{asset}/get/"))
credit = {"scratch_id": int(scratch_id), "title": meta["title"], "author": meta["author"]["username"],
          "url": f"https://scratch.mit.edu/projects/{scratch_id}/", "license": "CC BY-SA 2.0"}
(out / "credit.json").write_text(json.dumps(credit, ensure_ascii=False, indent=1) + "\n")
print(name, sum(f.stat().st_size for f in out.iterdir()) // 1024, "KB")
