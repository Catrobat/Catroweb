"""Self-check for check.py: each broken page must be caught, the good one must pass."""
import sys
import tempfile
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent))
import check  # noqa: E402

with tempfile.TemporaryDirectory() as tmp:
    root = Path(tmp).resolve()
    check.ROOT = root
    (root / "locales.txt").write_text("# comment\nde_DE German\nes_ES Spanish\n")
    (root / "en").mkdir()
    (root / "reference" / "bricks").mkdir(parents=True)
    (root / "en" / "other.md").write_text("---\ntitle: Other\n---\n")
    cases = {
        "good.md": ("---\ntitle: Good\n---\n## Top\n[x](other.md) [y](https://a.b) [z](#top)\n::brick{type=SetXBrick}\n", []),
        "nofm.md": ("Text\n", ["missing front matter"]),
        "notitle.md": ("---\norder: 1\n---\n", ["front matter has no title"]),
        "link.md": ("---\ntitle: L\n---\n[x](missing.md)\n", ["broken link: missing.md"]),
        "embed.md": ("---\ntitle: E\n---\n::brick{}\n::nope{a=b}\n", ["::brick needs type=…", "unknown embed ::nope"]),
    }
    for name, (text, expected) in cases.items():
        page = root / "en" / name
        page.write_text(text)
        assert check.check_page(page) == expected, (name, check.check_page(page))
    prose = root / "reference" / "bricks" / "SetXBrick.md"
    prose.write_text("Sets the x position.\n")
    assert check.check_page(prose) == []
    (root / "de_DE" / "reference" / "bricks").mkdir(parents=True)
    de_prose = root / "de_DE" / "reference" / "bricks" / "SetXBrick.md"
    de_prose.write_text("---\nreviewed: true\n---\nSetzt die x-Position.\n")
    assert check.check_page(de_prose) == []
    (root / "en" / "index.md").write_text("---\ntitle: Home\n---\n")
    assert check.locales() == ["de_DE", "es_ES"]
    (root / "en" / "video.md").write_text("---\ntitle: V\n---\n[o](other.md)\n::video{youtube=abc}\n")
    h = check.sha256(root / "en" / "video.md")
    (root / "es_ES").mkdir()
    translations = {
        "es_ES/video.md": (f"---\ntitle: V\nsource_hash: '{h[:7]}'\n---\n[o](other.md)\n::video{{youtube=abc}}\n", []),
        "es_ES/stale.md": ("---\ntitle: S\nsource_hash: 'abcdef0'\n---\n", ["source_hash, but no English page en/stale.md"]),
        "es_ES/nohash.md": ("---\ntitle: N\n---\n", ["translation has no source_hash (see TRANSLATING.md)"]),
        "es_ES/unquoted.md": ("---\ntitle: U\nsource_hash: 1234567\n---\n", ["source_hash must be quoted lowercase hex, at least 7 digits: source_hash: '1a2b3c4'"]),
        "es_ES/other.md": (f"---\ntitle: O\nsource_hash: \"{check.sha256(root / 'en' / 'other.md')}\"\n---\n::brick{{type=X}}\n", ["embeds differ from en/other.md"]),
        "xx/other.md": ("---\ntitle: O\n---\n", ["xx/ is not a locale in locales.txt"]),
    }
    for name, (text, expected) in translations.items():
        page = root / name
        page.parent.mkdir(exist_ok=True)
        page.write_text(text)
        assert check.check_page(page) == expected, (name, check.check_page(page))
    # A stale translation may differ from the English page; its links may point to pages only English has yet.
    (root / "es_ES" / "video.md").write_text("---\ntitle: V\nsource_hash: '0000000'\n---\n[o](other.md)\n")
    assert check.check_page(root / "es_ES" / "video.md") == []
    # A translation the pull request adds or changes must be current; a reviewed one needs its source_hash then too.
    assert check.check_page(root / "es_ES" / "video.md", touched=True) == ["source_hash does not match today's en/video.md: translate the current English page"]
    (root / "es_ES" / "video.md").write_text(f"---\ntitle: V\nsource_hash: '{h}'\n---\n")
    assert check.check_page(root / "es_ES" / "video.md", touched=True) == ["embeds differ from en/video.md"]
    (root / "es_ES" / "video.md").write_text("---\ntitle: V\nreviewed: True\n---\n")
    assert check.check_page(root / "es_ES" / "video.md") == []
    assert check.check_page(root / "es_ES" / "video.md", touched=True) == ["translation has no source_hash (see TRANSLATING.md)"]
    assert check.translation_state({"source_hash": f"'{h[:7]}'"}, h) == "current"
    assert check.translation_state({"source_hash": "'0000000'"}, h) == "stale"
    assert check.translation_state({"source_hash": "'0000000'", "reviewed": "true"}, h) == "reviewed-stale"
    assert check.translation_state({"reviewed": "true"}, h) == "current"
    assert check.translation_state({}, h) == "stale"
    assert check.translation_state({"source_hash": "'0000000'", "reviewed": "TRUE"}, h) == "reviewed-stale"
    tsv = root / "redirects.tsv"
    tsv.write_text("old_url\tnew_path\na\t/\nb\t/other\nc\t/reference/bricks/StartScript\nd\t/de_DE/reference/bricks/X\ne\t/gone\nf\t/de_DE\n")
    assert check.check_redirects(tsv) == ["redirect target missing: e -> /gone", "redirect target missing: f -> /de_DE"], check.check_redirects(tsv)
    # Heading ids as share renders them: expected values from league/commonmark's SlugNormalizer (PHP).
    slugs = {
        "Füge ein Objekt aus der Medienbibliothek hinzu": "füge-ein-objekt-aus-der-medienbibliothek-hinzu",
        "Añadir un objeto de la librería de medios": "añadir-un-objeto-de-la-librería-de-medios",
        "  Ça va? Größe & Ü-Ei: 3 Schritte!  ": "ça-va-größe--ü-ei-3-schritte",
        "Ein  — Gedankenstrich": "ein--gedankenstrich",
        "Wähle „Speichern“ (2/3)": "wähle-speichern-23",
        "Ελληνικά & 中文 標題": "ελληνικά--中文-標題",
        "Tab\tund_Unterstrich": "tab-undunterstrich",
    }
    for title, expected in slugs.items():
        assert check.slug(title) == expected, (title, check.slug(title))
    page_text = "## Intro\n```\n# not a heading\n```\n## Intro ##\n### [Link](x.md) `code` <b>bold</b> &amp; more\n#### !!!\n"
    assert check.heading_ids(page_text) == {"intro", "intro-1", "link-code-bold--more", "section"}, check.heading_ids(page_text)
    (root / "en" / "target.md").write_text("---\ntitle: T\n---\n## Añadir un objeto\n")
    anchors = root / "en" / "anchors.md"
    anchors.write_text("---\ntitle: A\n---\n## Top\n[a](#top) [b](target.md#añadir-un-objeto) [c](#Object) [d](target.md#Object)\n")
    broken = ["broken anchor: #Object (the target has no heading with that id)", "broken anchor: target.md#Object (the target has no heading with that id)"]
    assert check.check_page(anchors, touched=True) == broken, check.check_page(anchors, touched=True)
    assert check.check_page(anchors) == ["warning: " + e for e in broken]  # untouched pages only warn
    media = root / "media"
    media.mkdir()
    for name, expected in {"a.png": [], "b.PDF": [], ".gitkeep": [], "c.svg": [".svg is not allowed, only .gif, .jpeg, .jpg, .pdf, .png, .webp"], "d.html": [".html is not allowed, only .gif, .jpeg, .jpg, .pdf, .png, .webp"]}.items():
        (media / name).write_bytes(b"x")
        assert check.check_media(media / name) == expected, (name, check.check_media(media / name))
    (media / "big.png").write_bytes(b"x" * (check.MAX_MEDIA_BYTES + 1))
    assert check.check_media(media / "big.png") == [f"{check.MAX_MEDIA_BYTES + 1} bytes, more than {check.MAX_MEDIA_BYTES}"]
    (media / "out").symlink_to(root / "en")
    assert check.check_media(media / "out") == ["a link: put the file itself into media/"]
    # A media file may be added or deleted, never changed (it is cached for a year).
    check.git("init", "-q")
    check.git("-c", "user.name=t", "-c", "user.email=t@t", "add", "media/a.png", "media/b.PDF")
    check.git("-c", "user.name=t", "-c", "user.email=t@t", "commit", "-q", "-m", "media")
    (media / "a.png").write_bytes(b"new")
    (media / "b.PDF").unlink()
    (media / "e.png").write_bytes(b"added")
    assert check.changed_media("HEAD") == ["media/a.png"], check.changed_media("HEAD")
print("check.py self-test passed")
