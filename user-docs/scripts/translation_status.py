"""Translation status per locale: which pages are missing, stale or current.

A task is one top-level section of one locale (`es_ES: getting-started`, `es_ES: reference/bricks`), one pull request.

  python3 user-docs/scripts/translation_status.py            table of all locales
  python3 user-docs/scripts/translation_status.py es_ES      the tasks of one locale, with the files still to do
  python3 user-docs/scripts/translation_status.py --json     everything, for scripts
"""
import json
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent))
import check  # noqa: E402

STATES = ("missing", "stale", "reviewed-stale", "current")
TOP_LEVEL = "top-level pages"


def sources():
    """English source file -> (section, path inside a locale folder)."""
    result = {}
    for page in sorted((check.ROOT / "en").rglob("*.md")):
        rel = page.relative_to(check.ROOT / "en")
        result[page] = (rel.parts[0] if len(rel.parts) > 1 else TOP_LEVEL, rel)
    for page in sorted((check.ROOT / "reference" / "bricks").glob("*.md")):
        result[page] = ("reference/bricks", page.relative_to(check.ROOT))
    return result


def status():
    """{locale: {section: {state: [english file, ...]}}}, sections in source order."""
    english = {page: (section, rel, check.sha256(page)) for page, (section, rel) in sources().items()}
    result = {}
    for locale in check.locales():
        sections = result.setdefault(locale, {})
        for page, (section, rel, english_hash) in english.items():
            target = check.ROOT / locale / rel
            if target.exists():
                fields = check.front_matter(target.read_text(encoding="utf-8")) or {}
                state = check.translation_state(fields, english_hash)
            else:
                state = "missing"
            sections.setdefault(section, {s: [] for s in STATES})[state].append(str(page.relative_to(check.ROOT)))
    return result


def counts(sections):
    return {s: sum(len(files[s]) for files in sections.values()) for s in STATES}


def main(argv):
    result = status()
    if "--json" in argv:
        print(json.dumps(result, indent=1))
        return 0
    wanted = [a for a in argv if not a.startswith("-")]
    if wanted:
        for locale in wanted:
            if locale not in result:
                print(f"{locale} is not in locales.txt")
                return 1
            for section, files in result[locale].items():
                todo = len(files["missing"]) + len(files["stale"]) + len(files["reviewed-stale"])
                print(f"{locale}: {section}  ({'done' if not todo else f'{todo} to do'})")
                for state in STATES[:3]:
                    for file in files[state]:
                        print(f"    {state:14} {file}")
        return 0
    print(f"{'locale':10} " + " ".join(f"{s:>14}" for s in STATES))
    for locale, sections in result.items():
        c = counts(sections)
        print(f"{locale:10} " + " ".join(f"{c[s]:>14}" for s in STATES))
    return 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
