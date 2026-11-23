"""Checks every page: front matter, relative links and images, image sizes, translations. Exit 1 on any problem.

Run from anywhere: python3 user-docs/scripts/check.py [--base <ref>], or user-docs/check.sh
With --base, translations added or changed since that ref must be current (CI passes the PR's base branch).
"""
import hashlib
import html
import re
import subprocess
import sys
import unicodedata
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
MAX_MEDIA_BYTES = 1_000_000
# What share links (DocsSync::MEDIA_EXTENSIONS). The web server serves media/ as static files: no SVG (it could run
# script on share's origin when opened directly), no HTML, no links leading out of media/.
MEDIA_SUFFIXES = {".png", ".jpg", ".jpeg", ".gif", ".webp", ".pdf"}
LINK = re.compile(r"!?\[[^\]]*\]\(([^)\s]+)(?:\s+\"[^\"]*\")?\)")
EMBED = re.compile(r"^::(\w+)\{([^}]*)\}\s*$", re.M)
EMBEDS = {"brick": "type", "formula": "name", "project": "id", "video": "youtube"}
HEADING = re.compile(r"^ {0,3}#{1,6}[ \t]+(.*?)(?:[ \t]+#+)?[ \t]*$", re.M)
FENCE = re.compile(r"^ {0,3}(`{3,}|~{3,}).*?^ {0,3}\1[ \t]*$", re.M | re.S)
SOURCE_HASH = re.compile(r"""^(['"])([0-9a-f]{7,64})\1$""")  # quoted: YAML reads an unquoted 1234567 as a number


def locales():
    """Translation folders from locales.txt (first word of each line)."""
    lines = (ROOT / "locales.txt").read_text(encoding="utf-8").splitlines()
    return [line.split()[0] for line in lines if line.strip() and not line.startswith("#")]


def english_source(rel):
    """The English file a translation comes from: es_ES/a/b.md -> en/a/b.md, es_ES/reference/bricks/X.md -> reference/bricks/X.md."""
    rest = Path(*rel.parts[1:])
    return ROOT / (rest if rest.parts[:1] == ("reference",) else Path("en") / rest)


def sha256(path):
    return hashlib.sha256(path.read_bytes()).hexdigest()


def is_reviewed(fields):
    return fields.get("reviewed", "").lower() == "true"


def git(*args):
    return subprocess.run(["git", *args], cwd=ROOT, check=True, capture_output=True, text=True).stdout.split("\n")


def changed_media(base):
    """Files in media/ modified (not added or deleted) since the merge base with `base`, uncommitted changes included.
    Media are cached for a year, so a changed file would show the old version: it needs a new name."""
    merge_base = git("merge-base", base, "HEAD")[0]
    return [f for f in git("diff", "--name-only", "--relative", "--no-renames", "--diff-filter=M", merge_base, "--", "media") if f]


def touched_since(base):
    """Files added or changed since the merge base with `base`, including uncommitted and untracked ones."""
    merge_base = git("merge-base", base, "HEAD")[0]
    if not git("ls-tree", "--name-only", merge_base, "--", ".")[0]:
        return set()  # the base has no docs yet (the move from Catrobat/docs): nothing was translated against it
    # -M100%: a moved file is not touched. --relative: paths below ROOT (user-docs/), like ls-files prints them.
    files = git("diff", "--name-only", "--relative", "-M100%", "--diff-filter=AM", merge_base) + git("ls-files", "--others", "--exclude-standard")
    return {ROOT / f for f in files if f}


def translation_state(fields, english_hash):
    """current, stale or reviewed-stale, as share decides it: the source_hash is a prefix of the English file's sha256.
    A reviewed page without source_hash was written by a person (the WordPress import) and counts as current."""
    match = SOURCE_HASH.match(fields.get("source_hash", ""))
    reviewed = is_reviewed(fields)
    if match is None:
        return "current" if reviewed and "source_hash" not in fields else "stale"
    if english_hash.startswith(match.group(2)):
        return "current"
    return "reviewed-stale" if reviewed else "stale"


def front_matter(text):
    """The `key: value` lines between the leading `---` lines, or None without front matter."""
    if not text.startswith("---\n"):
        return None
    end = text.find("\n---\n", 4)
    if end < 0:
        return None
    fields = {}
    for line in text[4:end].splitlines():
        key, sep, value = line.partition(":")
        if sep and not line.startswith((" ", "-")):
            fields[key.strip()] = value.strip()
    return fields


def slug(title):
    """share's heading id: league/commonmark's SlugNormalizer as src/Docs/DocsMarkdown.php calls it."""
    slug = re.sub(r"\s+", "-", title.strip(" \t\n\r\0\x0b").lower())  # PHP trim() strips only these
    slug = "".join(c for c in slug if c == "-" or unicodedata.category(c)[0] in "LM" or unicodedata.category(c) in ("Nd", "Nl"))
    return slug[:255]


def heading_ids(text):
    """The ids share gives the headings of a page, duplicates numbered -1, -2 … like DocsMarkdown::convert()."""
    ids, used = set(), {}
    for title in HEADING.findall(FENCE.sub("", text)):
        title = re.sub(r"!?\[([^\]]*)\]\([^)]*\)", r"\1", title)  # a link or image counts with its text only
        title = html.unescape(re.sub(r"<[^>]+>", "", title))  # inline HTML is dropped, entities decoded
        base = slug(title) or "section"
        used[base] = used.get(base, 0) + 1
        ids.add(base if used[base] == 1 else f"{base}-{used[base] - 1}")
    return ids


def check_page(page, touched=False):
    errors = []
    text = page.read_text(encoding="utf-8")
    fields = front_matter(text)
    rel = page.relative_to(ROOT).parts
    in_reference = rel[0] == "reference" or rel[1:2] == ("reference",)  # brick prose, also <locale>/reference/
    if fields is None and not in_reference:
        errors.append("missing front matter")
    elif fields is not None and not in_reference and not fields.get("title"):
        errors.append("front matter has no title")
    translation = rel[0] not in ("en", "reference")
    if translation:
        errors += check_translation(page, text, fields or {}, touched)
    for target in LINK.findall(text):
        if re.match(r"^[a-z][a-z0-9+.-]*:|^/", target):
            continue  # external or site-absolute link
        file, _, anchor = target.partition("#")
        path = (page.parent / file).resolve() if file else page
        if not path.exists() and translation and path.is_relative_to(ROOT / rel[0]):
            path = english_source(path.relative_to(ROOT))  # share shows the English page until this one is translated
        if not path.exists():
            errors.append(f"broken link: {target}")
        elif anchor and path.suffix == ".md" and anchor not in heading_ids(path.read_text(encoding="utf-8")):
            # Untouched pages only warn, so an older page does not block an unrelated pull request.
            errors.append(f"{'' if touched else 'warning: '}broken anchor: {target} (the target has no heading with that id)")
    for kind, args in EMBED.findall(text):
        if kind not in EMBEDS:
            errors.append(f"unknown embed ::{kind}")
        elif not re.search(rf"\b{EMBEDS[kind]}=\S", args):
            errors.append(f"::{kind} needs {EMBEDS[kind]}=…")
    return errors


def check_translation(page, text, fields, touched=False):
    rel = page.relative_to(ROOT)
    if rel.parts[0] not in locales():
        return [f"{rel.parts[0]}/ is not a locale in locales.txt"]
    source = english_source(rel)
    if "source_hash" not in fields:
        return [] if is_reviewed(fields) and not touched else ["translation has no source_hash (see TRANSLATING.md)"]
    if not SOURCE_HASH.match(fields["source_hash"]):
        return ["source_hash must be quoted lowercase hex, at least 7 digits: source_hash: '1a2b3c4'"]
    if not source.exists():
        return [f"source_hash, but no English page {source.relative_to(ROOT)}"]
    current = translation_state(fields, sha256(source)) == "current"
    if touched and not current:
        return [f"source_hash does not match today's {source.relative_to(ROOT)}: translate the current English page"]
    if current and sorted(EMBED.findall(text)) != sorted(EMBED.findall(source.read_text(encoding="utf-8"))):
        return [f"embeds differ from {source.relative_to(ROOT)}"]
    return []


def check_media(path):
    if path.is_symlink():
        return ["a link: put the file itself into media/"]
    if path.name == ".gitkeep" or not path.is_file():
        return []
    if path.suffix.lower() not in MEDIA_SUFFIXES:
        return [f"{path.suffix or 'no extension'} is not allowed, only {', '.join(sorted(MEDIA_SUFFIXES))}"]
    if path.suffix.lower() != ".pdf" and path.stat().st_size > MAX_MEDIA_BYTES:
        return [f"{path.stat().st_size} bytes, more than {MAX_MEDIA_BYTES}"]
    return []


def check_redirects(tsv):
    """Every redirect target is a page file, or a generated reference page: /reference (a category: /reference#category_looks),
    /reference/bricks/<Type>, also with a locale (/de_DE/reference/bricks/<Type>)."""
    errors = []
    for line in tsv.read_text(encoding="utf-8").splitlines()[1:]:
        old, _, target = line.partition("\t")
        rel = target.strip("/")
        if re.fullmatch(r"(?:[a-z]{2}(?:_[A-Z]{2})?/)?reference(?:/bricks/\w+|#category_\w+)?", rel):
            continue
        base = rel if re.match(r"^[a-z]{2}(?:_[A-Z]{2})?(?:/|$)", rel) else ("en/" + rel).rstrip("/")
        if not ((ROOT / (base + ".md")).exists() or (ROOT / base / "index.md").exists()):
            errors.append(f"redirect target missing: {old} -> {target}")
    return errors


def main(argv):
    problems = [] if (ROOT / "en" / "index.md").is_file() else ["en/index.md missing: share refuses to sync docs without it"]
    touched = touched_since(argv[argv.index("--base") + 1]) if "--base" in argv else set()
    if "--base" in argv:
        problems += [f"{f}: changed; replacing an image? give it a new file name" for f in changed_media(argv[argv.index("--base") + 1])]
    redirects = ROOT / "import" / "redirects.tsv"
    if redirects.exists():
        problems += [f"import/redirects.tsv: {e}" for e in check_redirects(redirects)]
    for page in sorted(ROOT.rglob("*.md")):
        rel = page.relative_to(ROOT)
        if rel.parts[0] in {".github", "scripts", "import"} or len(rel.parts) == 1:
            continue  # repo files (README, CONTRIBUTING), not pages
        problems += [f"{rel}: {e}" for e in check_page(page, page in touched)]
    for media in sorted((ROOT / "media").rglob("*")):
        problems += [f"{media.relative_to(ROOT)}: {e}" for e in check_media(media)]
    for problem in problems:
        print(problem)
    warnings = sum(": warning: " in p for p in problems)
    print(f"{len(problems) - warnings} problem(s), {warnings} warning(s)")
    return 1 if len(problems) > warnings else 0


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
