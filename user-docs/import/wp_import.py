"""Import the WordPress docs at catrobat.org/docs/ into this repo. Stdlib only, deterministic, rerunnable.

    python3 import/wp_import.py            # use import/.cache, fetch what is missing
    python3 import/wp_import.py --refresh  # fetch the page JSON again

Reads the public REST API (rendered HTML), downloads every referenced upload once into
import/.cache/, and writes en/, de_DE/, reference/bricks/, de_DE/reference/bricks/, media/ plus
the maps and REPORT.md in import/. Hand-made inputs: brick-hand.tsv, pairs.tsv,
de-only.tsv, translations/, brick-types.txt (Catroid's CategoryBricksFactory).
"""
import concurrent.futures
import html
import json
import re
import shutil
import sys
import urllib.error
import urllib.parse
import urllib.request
import zlib
from html.parser import HTMLParser
from pathlib import Path, PurePosixPath

ROOT = Path(__file__).resolve().parent.parent
IMP = ROOT / "import"
CACHE = IMP / ".cache"
API = "https://catrobat.org/wp-json/wp/v2/docs?per_page=100&orderby=id&order=asc&page={}"
OLD = "https://catrobat.org/docs/"
UA = {"User-Agent": "catrobat-docs-import/1.0"}
MAX_MEDIA = 1_000_000
UPLOAD = re.compile(r"https?://(?:www\.)?catrobat\.org/wp-content/uploads/[^\s\"'<>)?]+")
DOCLINK = re.compile(r"^https?://(?:www\.)?catrobat\.org/docs/([^?#]*)(#.*)?$")
YOUTUBE = re.compile(r"youtube(?:-nocookie)?\.com/embed/([\w-]{11})")

# Root sections: (EN root slug -> new folder). German roots are paired to these via pairs.tsv.
EN_ROOTS = {
    "getting-started": "getting-started", "example-projects": "example-projects",
    "brickdocumentation": "bricks", "educators": "educators", "pocket-code": "pocket-code",
    "pocket-paint": "pocket-paint", "createschool": "createschool",
    "embroidery-designer": "embroidery-designer", "plot-tutorials": "plot-tutorials",
    "game-tutorials": "game-tutorials", "tutorials": "embroidery-tutorials",
}
DE_ROOTS = {"erste-schritte", "beispielprojekte", "brick-dokumentation-de", "spiele-tutorials",
            "lehrpersonen-und-workshopleiterinnen", "plot-tutorials-de", "tutorials-de"}
FLAVORS = {"embroidery-tutorials": "[embroidery]"}


def tsv(name):
    rows = []
    for line in (IMP / name).read_text(encoding="utf-8").splitlines():
        if line.strip() and not line.startswith("#"):
            rows.append(line.split("\t"))
    return rows


def fetch(url):
    req = urllib.request.Request(urllib.parse.quote(url, safe=":/%?&=+"), headers=UA)
    with urllib.request.urlopen(req, timeout=120) as r:
        return r.read()


def load_pages(refresh):
    CACHE.mkdir(parents=True, exist_ok=True)
    pages, n = [], 1
    while True:
        f = CACHE / f"docs{n}.json"
        if refresh or not f.exists():
            try:
                f.write_bytes(fetch(API.format(n)))
            except urllib.error.HTTPError as e:
                if e.code == 400:  # past the last page
                    break
                raise
        batch = json.loads(f.read_text(encoding="utf-8"))
        if not batch:
            break
        pages += batch
        if len(batch) < 100:
            break
        n += 1
    return sorted(pages, key=lambda p: p["id"])


def media_key(url):
    """The original upload a URL points to: no query, no -WxH size suffix, https, no www."""
    url = url.split("?")[0].replace("http://", "https://").replace("://www.", "://")
    return re.sub(r"-\d+x\d+(\.\w+)$", r"\1", url)


def cache_file(url):
    """Where an upload is cached. The URL comes from scraped HTML, so it must not leave import/.cache/media."""
    rel = url.split("/wp-content/uploads/", 1)[1]
    base = (CACHE / "media").resolve()
    f = (base / rel).resolve()
    if ".." in rel.split("/") or not f.is_relative_to(base):
        raise ValueError(f"upload path outside the cache: {url}")
    return f


def download(url):
    f = cache_file(url)
    if f.exists():
        return url, "ok"
    if f.with_suffix(f.suffix + ".missing").exists():
        return url, "missing"
    f.parent.mkdir(parents=True, exist_ok=True)
    try:
        data = fetch(url)
    except urllib.error.HTTPError as e:
        if e.code in (403, 404, 410):
            f.with_suffix(f.suffix + ".missing").write_text(str(e.code))
            return url, "missing"
        raise
    f.write_bytes(data)
    return url, "ok"


def recompress_png(data):
    """Lossless: re-deflate the image data at level 9 and drop ancillary chunks that are not needed to display it."""
    if data[:8] != b"\x89PNG\r\n\x1a\n":
        return data
    chunks, idat, pos = [], b"", 8
    while pos < len(data):
        length = int.from_bytes(data[pos:pos + 4], "big")
        kind = data[pos + 4:pos + 8]
        body = data[pos + 8:pos + 8 + length]
        pos += 12 + length
        if kind == b"IDAT":
            idat += body
        elif kind[0:1].isupper() or kind in (b"tRNS", b"gAMA", b"cHRM", b"sRGB", b"iCCP", b"pHYs"):
            chunks.append((kind, body))
    out = [data[:8]]
    raw = zlib.compress(zlib.decompress(idat), 9)
    for kind, body in chunks:
        if kind == b"IEND":
            out.append(chunk(b"IDAT", raw))
        out.append(chunk(kind, body))
    return b"".join(out)  # always: dropping text/EXIF chunks also drops any personal metadata


def strip_pdf_author(data):
    """Blank the PDF Info /Author with spaces of the same length, so every xref offset stays valid."""
    return re.sub(rb"(/Author\s*)(\((?:\\.|[^\\)])*\)|<[0-9A-Fa-f\s]*>)",
                  lambda m: m.group(1) + m.group(2)[:1] + b" " * (len(m.group(2)) - 2) + m.group(2)[-1:], data)


def chunk(kind, body):
    return len(body).to_bytes(4, "big") + kind + body + zlib.crc32(kind + body).to_bytes(4, "big")


# ---------- HTML -> Markdown ----------

VOID = {"img", "br", "hr", "source", "meta", "link", "input", "wbr"}


class Node:
    def __init__(self, tag, attrs, parent=None):
        self.tag, self.attrs, self.parent, self.kids = tag, dict(attrs), parent, []

    def cls(self):
        return self.attrs.get("class") or ""


class Tree(HTMLParser):
    def __init__(self, text):
        super().__init__(convert_charrefs=True)
        self.root = self.cur = Node("root", {})
        self.feed(text)
        self.close()

    def handle_starttag(self, tag, attrs):
        node = Node(tag, attrs, self.cur)
        self.cur.kids.append(node)
        if tag not in VOID:
            self.cur = node

    def handle_startendtag(self, tag, attrs):
        self.cur.kids.append(Node(tag, attrs, self.cur))

    def handle_endtag(self, tag):
        node = self.cur
        while node is not self.root and node.tag != tag:
            node = node.parent
        if node is not self.root:
            self.cur = node.parent

    def handle_data(self, data):
        self.cur.kids.append(data)


def escape(text):
    text = re.sub(r"([\\`*\[\]<])", r"\\\1", text)
    return re.sub(r"(?<![A-Za-z0-9])_|_(?![A-Za-z0-9])", r"\\_", text)


def heading_slug(text):
    text = re.sub(r"[^\w\- ]", "", text.lower().replace("\\", ""))
    return re.sub(r" ", "-", text.strip())


class Converter:
    """Renders one page. `link` maps an href to a new href, `image` maps an upload URL to a path."""

    def __init__(self, link, image):
        self.link, self.image, self.anchors = link, image, {}

    def convert(self, text):
        tree = Tree(text).root
        self.collect_anchors(tree)
        blocks = self.blocks(tree)
        md = "\n\n".join(b for b in blocks if b.strip())
        return re.sub(r"\n{3,}", "\n\n", md).strip() + "\n"

    def collect_anchors(self, node):
        for kid in node.kids:
            if isinstance(kid, Node):
                if kid.attrs.get("id") and kid.tag in ("p", "h2", "h3", "h4"):
                    self.anchors[kid.attrs["id"]] = heading_slug(self.text(kid))
                self.collect_anchors(kid)

    def text(self, node):
        return "".join(k if isinstance(k, str) else self.text(k) for k in node.kids)

    def blocks(self, node):
        out, inline = [], []

        def flush():
            para = "".join(inline)
            inline.clear()
            out.extend(p.strip() for p in para.split("\n\n") if p.strip())

        for kid in node.kids:
            if isinstance(kid, str) or kid.tag in ("a", "strong", "b", "em", "i", "img", "code", "span", "br", "sup", "sub", "mark", "u", "s"):
                if isinstance(kid, Node) and kid.tag == "span" and "embed-youtube" in kid.cls():
                    flush()
                    out += self.blocks(kid)
                    continue
                inline.append(self.inline(kid))
                continue
            flush()
            out += self.block(kid)
        flush()
        return out

    def block(self, n):
        cls = n.cls()
        if n.tag == "p":
            body = self.inline(n).strip()
            if not body:
                return []
            if n.attrs.get("id") or "has-medium-font-size" in cls:
                return ["### " + strip_bold(body)]
            if "has-large-font-size" in cls:
                return ["## " + strip_bold(body)]
            return [p.strip() for p in body.split("\n\n") if p.strip()]
        if n.tag in ("h1", "h2", "h3", "h4", "h5", "h6"):
            body = strip_bold(self.inline(n).strip().replace("\n\n", " "))
            return ["#" * max(2, int(n.tag[1])) + " " + body] if body else []
        if n.tag == "hr":
            return ["***"]
        if n.tag in ("ul", "ol"):
            if "schema-how-to-steps" in cls:
                return self.howto(n)
            return [self.list(n)]
        if n.tag == "blockquote":
            inner = "\n\n".join(self.blocks(n))
            return ["\n".join("> " + line if line else ">" for line in inner.splitlines())]
        if n.tag == "pre":
            code = self.text(n).strip("\n")
            return ["```\n" + code + "\n```"] if code.strip() else []
        if n.tag == "iframe":
            m = YOUTUBE.search(n.attrs.get("src", ""))
            return [f"::video{{youtube={m.group(1)}}}"] if m else [f"[Embedded content]({n.attrs.get('src', '')})"]
        if n.tag == "object":
            return []  # PDF preview; the file block's link follows
        if n.tag == "figcaption":
            body = self.inline(n).strip()
            return ["*" + body + "*"] if body else []
        if n.tag == "div" and "wp-block-file" in cls:
            return self.file_block(n)
        if n.tag == "div" and "wp-block-spacer" in cls:
            return []
        if n.tag == "div" and "wp-block-button" == cls.strip():
            return [self.inline(n).strip()]
        return self.blocks(n)

    def file_block(self, n):
        links = [k for k in n.kids if isinstance(k, Node) and k.tag == "a" and "wp-block-file__button" not in k.cls()]
        out = []
        for a in links:
            label = escape(self.text(a).strip()) or "Download"
            out.append(f"[{label}]({self.href(a.attrs.get('href', ''))})")
        return out

    def howto(self, n):
        out, step = [], 0
        for li in n.kids:
            if not isinstance(li, Node) or li.tag != "li":
                continue
            step += 1
            name = text = ""
            for k in li.kids:
                if isinstance(k, Node) and "schema-how-to-step-name" in k.cls():
                    name = strip_bold(self.inline(k).strip())
                elif isinstance(k, Node) and "schema-how-to-step-text" in k.cls():
                    text = self.inline(k).strip()
            out.append(f"### {step}. {name}" if name else f"### {step}.")
            out += [p.strip() for p in text.split("\n\n") if p.strip()]
        return out

    def list(self, n, depth=0):
        lines, i = [], 0
        for li in n.kids:
            if not isinstance(li, Node) or li.tag != "li":
                continue
            i += 1
            marker = f"{i}." if n.tag == "ol" else "-"
            pad = " " * (len(marker) + 1)
            parts, sub = [], []
            for k in li.kids:
                if isinstance(k, Node) and k.tag in ("ul", "ol"):
                    sub.append(self.list(k, depth + 1))
                else:
                    parts.append(self.inline(k))
            body = "".join(parts).strip().replace("\n\n", "\\\n" + pad)
            lines.append(marker + " " + body)
            lines += ["\n".join(pad + line for line in s.splitlines()) for s in sub]
        return "\n".join(lines)

    def inline(self, n):
        if isinstance(n, str):
            return escape(re.sub(r"\s+", " ", n.replace("\xa0", " ")))
        t = n.tag
        if t == "br":
            return "\n\n"
        if t == "img":
            return self.img(n)
        inner = "".join(self.inline(k) for k in n.kids)
        if t in ("strong", "b"):
            return wrap(inner, "**")
        if t in ("em", "i"):
            return wrap(inner, "*")
        if t == "code":
            return "`" + self.text(n).replace("`", "") + "`" if self.text(n).strip() else ""
        if t == "a":
            href = n.attrs.get("href", "")
            if not href:
                return inner
            imgs = [k for k in n.kids if isinstance(k, Node) and k.tag == "img"]
            if imgs and UPLOAD.match(href) and media_key(href).lower().endswith((".png", ".jpg", ".jpeg", ".gif")):
                return inner  # lightbox link to the image itself
            if not inner.strip():
                return ""
            lead, core, trail = re.match(r"^(\s*)(.*?)(\s*)$", inner, re.S).groups()
            if "\n\n" in core:  # a link around several paragraphs: link each one
                return lead + "\n\n".join(f"[{c}]({self.href(href)})" for c in core.split("\n\n") if c.strip()) + trail
            return f"{lead}[{core}]({self.href(href)}){trail}"
        return inner

    def img(self, n):
        src = n.attrs.get("data-orig-file") or n.attrs.get("src", "")
        alt = re.sub(r"[\[\]\n]", " ", html.unescape(n.attrs.get("alt") or n.attrs.get("data-image-title") or "")).strip()
        return f"![{alt}]({self.image(src)})" if src else ""

    def href(self, href):
        href = html.unescape(href)
        if href.startswith("#"):
            return "#" + self.anchors.get(href[1:], href[1:])
        if UPLOAD.match(href):
            return self.image(href)
        return self.link(href)


def wrap(inner, mark):
    lead, core, trail = re.match(r"^(\s*)(.*?)(\s*)$", inner, re.S).groups()
    if not core:
        return inner
    if "\n\n" in core:
        return lead + "\n\n".join(wrap(c, mark) for c in core.split("\n\n")) + trail
    if core.startswith(mark) and core.endswith(mark):
        return lead + core + trail
    return f"{lead}{mark}{core}{mark}{trail}"


def strip_bold(text):
    return re.sub(r"\*\*", "", text).strip()


def describe(md):
    """First sentence of the first prose paragraph, for the description field."""
    for para in md.split("\n\n"):
        if para.startswith(("#", "!", "[", "**[", "*[", "::", "***", ">", "-", "1.", "```")):
            continue
        text = re.sub(r"!?\[([^\]]*)\]\([^)]*\)", r"\1", para)
        text = re.sub(r"\\(.)", r"\1", text).replace("**", "").replace("*", "")
        text = " ".join(text.split())
        if len(text) < 15:
            continue
        m = re.match(r"(.+?[.!?])(\s|$)", text)
        sentence = m.group(1) if m else text
        return sentence if len(sentence) <= 200 else sentence[:197].rsplit(" ", 1)[0] + " …"
    return ""


def yaml_str(value):
    if re.search(r"[:#\[\]{}&*!|>'\"%@`,]|^[\s-]|\s$", value):
        return '"' + value.replace("\\", "\\\\").replace('"', '\\"') + '"'
    return value


def front(fields):
    lines = ["---"]
    for key, value in fields:
        if value not in (None, ""):
            lines.append(f"{key}: {value}")
    return "\n".join(lines + ["---", ""])


def relpath(target, start_dir):
    t, s = PurePosixPath(target).parts, PurePosixPath(start_dir).parts
    i = 0
    while i < min(len(t), len(s)) and t[i] == s[i]:
        i += 1
    return "/".join([".."] * (len(s) - i) + list(t[i:])) or "."


def safe_name(name):
    name = html.unescape(name)
    for a, b in (("ä", "ae"), ("ö", "oe"), ("ü", "ue"), ("Ä", "Ae"), ("Ö", "Oe"), ("Ü", "Ue"), ("ß", "ss")):
        name = name.replace(a, b)
    return re.sub(r"[^A-Za-z0-9._-]+", "-", name).strip("-")


# ---------- the import ----------

def main():
    pages = load_pages("--refresh" in sys.argv)
    by_id = {p["id"]: p for p in pages}
    kids = {}
    for p in pages:
        kids.setdefault(p["parent"], []).append(p["id"])

    def root_of(p):
        while p["parent"]:
            p = by_id[p["parent"]]
        return p

    def depth(p):
        return 0 if not p["parent"] else 1 + depth(by_id[p["parent"]])

    lang = {p["id"]: ("de" if root_of(p)["slug"] in DE_ROOTS else "en") for p in pages}
    title = {p["id"]: html.unescape(p["title"]["rendered"]).strip() for p in pages}
    empty = {p["id"] for p in pages if not p["content"]["rendered"].strip()}
    old_url = {p["id"]: p["link"] for p in pages}

    # Brick pages: grandchildren of a brick documentation root.
    brick_roots = {p["id"] for p in pages if p["slug"] in ("brickdocumentation", "brick-dokumentation-de")}
    is_brick = {p["id"] for p in pages if p["parent"] in by_id and by_id[p["parent"]]["parent"] in brick_roots}
    types = {r[0] for r in tsv("brick-types.txt")}
    hand = {int(r[0]): r[1] for r in tsv("brick-hand.tsv")}
    auto = {}
    for pid in sorted(is_brick):
        if lang[pid] == "en":
            auto[pid] = auto_brick(by_id[pid]["slug"], types)
    brick_type, method = {}, {}
    for pid in sorted(is_brick):
        if pid in hand:
            brick_type[pid], method[pid] = hand[pid], ("auto" if auto.get(pid) == hand[pid] else "hand")
        elif auto.get(pid):
            brick_type[pid], method[pid] = auto[pid], "auto"
    unknown_types = sorted({t for t in brick_type.values() if t not in types})
    # Key every brick by its code.xml type: a script brick is stored as its script (WhenStartedBrick -> StartScript).
    script = {r[0]: r[1] for r in tsv("script-bricks.tsv")}
    brick_class = dict(brick_type)
    brick_type = {pid: script.get(c, c) for pid, c in brick_class.items()}

    # EN/DE pairs.
    en_of = {int(d): int(e) for e, d in tsv("pairs.tsv")}
    en_brick = {}
    for pid in sorted(is_brick):
        if lang[pid] == "en" and pid in brick_type and pid not in empty:
            en_brick.setdefault(brick_type[pid], pid)
    for pid in sorted(is_brick):
        if lang[pid] == "de" and pid not in empty and brick_type.get(pid) in en_brick:
            en_of[pid] = en_brick[brick_type[pid]]
    de_only = {int(r[0]): r[1] for r in tsv("de-only.tsv")}

    # New paths. EN path from the EN slug chain; DE pages take the path of their EN pair.
    de_of_en = {e: d for d, e in en_of.items()}

    def new_kids(pid):
        """Children that become pages below this one (bricks go to reference/ instead)."""
        own = [k for k in kids.get(pid, []) if k not in is_brick]
        if lang[pid] == "en" and pid in de_of_en:
            own += [k for k in kids.get(de_of_en[pid], []) if k in de_only]
        return own

    def folder(pid):
        """The page's path below en/ without extension; its children live below it."""
        p = by_id[pid]
        if lang[pid] == "de" and pid in en_of:
            return folder(en_of[pid])
        slug = de_only[pid] if lang[pid] == "de" else EN_ROOTS.get(p["slug"], p["slug"]) if not p["parent"] else p["slug"]
        return folder(p["parent"]) + "/" + slug if p["parent"] else slug

    def target(pid):
        """Repo path of a page's file, or None for an empty page without children."""
        if pid in is_brick:
            if pid in empty or pid not in brick_type:
                return None
            return ("de_DE/" if lang[pid] == "de" else "") + f"reference/bricks/{brick_type[pid]}.md"
        if pid in empty and not new_kids(pid):
            return None
        if lang[pid] == "de" and pid in en_of and target(en_of[pid]):
            return "de_DE/" + target(en_of[pid])[3:]
        return f"{'de_DE' if lang[pid] == 'de' else 'en'}/{folder(pid)}" + ("/index.md" if new_kids(pid) else ".md")

    path = {pid: target(pid) for pid in by_id}
    # An English twin for each German-only page.
    en_twin = {pid: "en/" + path[pid].split("/", 1)[1] for pid in de_only if path[pid]}

    # Duplicate file targets: keep the first (non-empty, lowest id), report the rest.
    owner, dup = {}, {}
    for pid in sorted(by_id, key=lambda i: (i in empty, i)):
        if path[pid] is None:
            continue
        if path[pid] in owner:
            dup[pid] = owner[path[pid]]
            path[pid] = None
        else:
            owner[path[pid]] = pid

    # Links to old docs URLs.
    by_old_path = {}
    for p in pages:
        by_old_path[p["link"].replace("http://", "https://").split("catrobat.org/docs/", 1)[1].strip("/")] = p["id"]
    unresolved = []

    def moved_page(old):
        """A page that moved inside its section: same root, and its slug ends the old last segment."""
        root, last = old.split("/")[0], old.split("/")[-1]
        hits = [p["id"] for p in pages if root_of(p)["slug"] == root and p["id"] not in empty
                and (last == p["slug"] or last.endswith("-" + p["slug"]))]
        return hits[0] if len(hits) == 1 else None

    def page_link(href, here_pid):
        m = DOCLINK.match(href.strip())
        if not m:
            return href
        pid = by_old_path.get(m.group(1).strip("/")) or moved_page(m.group(1).strip("/"))
        anchor = m.group(2) or ""
        if pid is None:
            unresolved.append((here_pid, href))
            return href
        want = path.get(pid)
        if lang[here_pid] == "de" and lang[pid] == "en" and de_of_en.get(pid) and path.get(de_of_en[pid]):
            want = path[de_of_en[pid]]
        if lang[here_pid] == "en" and lang[pid] == "de":
            want = en_twin.get(pid) or (path.get(en_of[pid]) if pid in en_of else None) or want
        if not want:
            parent = by_id[pid]["parent"]
            want = path.get(parent) if parent else None
            if not want:
                unresolved.append((here_pid, href))
                return href
        return relpath(want, PurePosixPath(path_here[here_pid]).parent) + anchor

    # Media: every upload any page references (src, srcset, links, embeds).
    refs = {}
    for p in pages:
        for u in UPLOAD.findall(html.unescape(p["content"]["rendered"])):
            refs.setdefault(media_key(u), set()).add(p["id"])
    with concurrent.futures.ThreadPoolExecutor(8) as pool:
        status = dict(pool.map(download, sorted(refs)))

    def section(pid):
        f = path.get(pid) or (en_twin.get(pid))
        if not f:
            r = root_of(by_id[pid])
            return EN_ROOTS.get(r["slug"]) or folder(r["id"]).split("/")[0]
        parts = PurePosixPath(f).parts
        if parts[0] == "de_DE":
            parts = parts[1:]
        return "bricks" if parts[0] == "reference" else parts[1] if parts[0] == "en" else parts[0]

    media_path, taken = {}, set()
    for url in sorted(refs, key=lambda u: (min(refs[u]), u)):
        if status[url] != "ok":
            continue
        y, m, name = url.split("/wp-content/uploads/")[1].split("/", 2)
        name = safe_name(name)
        sec = section(min(refs[url]))
        cand = f"media/{sec}/{name}"
        if cand.lower() in taken:
            stem, ext = name.rsplit(".", 1)
            cand = f"media/{sec}/{stem}-{y}{m}.{ext}"
        webp = cand[:-4] + ".webp"
        if cand.lower().endswith(".png") and (ROOT / webp).exists():
            cand = webp  # hand-converted lossless WebP for a PNG over the 1 MB limit, see REPORT.md
        taken.add(cand.lower())
        media_path[url] = cand

    # Write pages.
    path_here = {pid: path[pid] for pid in by_id if path[pid]}
    used_media = set()
    order_of = {pid: by_id[pid]["menu_order"] for pid in by_id}
    written = {}
    for pid in sorted(by_id):
        if not path[pid]:
            continue
        p = by_id[pid]

        def image(url, here=pid):
            key = media_key(html.unescape(url))
            if key in media_path:
                used_media.add(key)
                return relpath(media_path[key], PurePosixPath(path_here[here]).parent)
            return key  # missing upload: keep the original URL

        body = Converter(lambda h, here=pid: page_link(h, here), image).convert(p["content"]["rendered"])
        fields = []
        if pid in is_brick:  # brick prose: the brick page has the title (CONTRIBUTING.md)
            text = (front([("reviewed", "true")]) + "\n" if lang[pid] == "de" else "") + body
        else:
            fields = [("title", yaml_str(title[pid])), ("description", yaml_str(describe(body))),
                      ("order", order_of[pid] if pid not in is_brick else None)]
            sec = path[pid].split("/")[1]
            fields.append(("flavors", FLAVORS.get(sec) if lang[pid] == "en" else None))
            if lang[pid] == "de":
                fields.append(("reviewed", "true"))
            text = front(fields) + ("\n" + body if body.strip() else "")
        written[path[pid]] = text

    # English twins of German-only pages, from translations/<id>.md.
    missing_translations = []
    for pid in sorted(en_twin):
        src = IMP / "translations" / f"{pid}.md"
        if not src.exists():
            missing_translations.append(pid)
            continue
        first, _, body = src.read_text(encoding="utf-8").partition("\n")
        body = body.strip() + "\n"
        fields = [("title", yaml_str(first.lstrip("# ").strip())), ("description", yaml_str(describe(body))),
                  ("order", order_of[pid]), ("translated_from", "de")]
        written[en_twin[pid]] = front(fields) + ("\n" + body if body.strip() else "")
        for m in re.findall(r"\]\(((?:\.\./)+media/[^)]+)\)", body):
            used_media.add(next((u for u, mp in media_path.items() if mp == m.lstrip("./")), None))

    for rel, text in sorted(written.items()):
        f = ROOT / rel
        f.parent.mkdir(parents=True, exist_ok=True)
        f.write_text(text, encoding="utf-8")

    # Media files.
    big = []
    for url, rel in sorted(media_path.items()):
        if rel.endswith(".webp"):
            continue
        data = cache_file(url).read_bytes()
        if rel.lower().endswith(".png"):
            data = recompress_png(data)
        elif rel.lower().endswith(".pdf"):
            data = strip_pdf_author(data)
        f = ROOT / rel
        f.parent.mkdir(parents=True, exist_ok=True)
        if not f.exists() or f.read_bytes() != data:
            f.write_bytes(data)
        if len(data) > MAX_MEDIA and not rel.lower().endswith(".pdf"):
            big.append((rel, len(data)))

    write_maps(pages, by_id, lang, title, empty, old_url, path, en_twin, is_brick, brick_type, brick_class, method, auto,
               en_of, de_of_en, dup, refs, status, media_path, used_media, unresolved, big, unknown_types,
               missing_translations, root_of)


FILLER = {"to", "by", "n", "the", "a", "an", "at", "in", "of", "for", "on", "is", "it", "1", "10"}


def auto_brick(slug, types):
    """The brick type whose name has the same words as the page slug, ignoring filler words."""
    words = [w for w in slug.split("-") if w not in FILLER]
    hits = []
    for t in sorted(types):
        name = [w.lower() for w in re.findall(r"[A-Z][a-z]*|[0-9]+", t.removesuffix("Brick"))]
        if [w for w in name if w not in FILLER] == words:
            hits.append(t)
    return hits[0] if len(hits) == 1 else None


def write_maps(pages, by_id, lang, title, empty, old_url, path, en_twin, is_brick, brick_type, brick_class, method, auto,
               en_of, de_of_en, dup, refs, status, media_path, used_media, unresolved, big, unknown_types,
               missing_translations, root_of):
    rows = ["wp_id\tlang\tstatus\told_url\tnew_path\tpair_wp_id\ttitle"]
    counts = {}
    for p in pages:
        pid = p["id"]
        if path[pid]:
            st = "brick" if pid in is_brick else "imported"
        elif pid in dup:
            st = f"duplicate-of-{dup[pid]}"
        else:
            st = "empty-container" if pid in empty else "unmapped"
        counts[st.split("-of-")[0]] = counts.get(st.split("-of-")[0], 0) + 1
        pair = de_of_en.get(pid) if lang[pid] == "en" else en_of.get(pid, "")
        rows.append(f"{pid}\t{lang[pid]}\t{st}\t{old_url[pid]}\t{path[pid] or ''}\t{pair or ''}\t{title[pid]}")
    (IMP / "pages.tsv").write_text("\n".join(rows) + "\n", encoding="utf-8")

    rows = ["wp_id\tlang\tslug\ttitle\tbrick_class\ttype\tmethod"]
    for pid in sorted(is_brick):
        rows.append(f"{pid}\t{lang[pid]}\t{by_id[pid]['slug']}\t{title[pid]}\t{brick_class.get(pid, '')}\t{brick_type.get(pid, '')}\t{method.get(pid, 'unmapped')}")
    (IMP / "brick-map.tsv").write_text("\n".join(rows) + "\n", encoding="utf-8")

    rows = ["old_url\tnew_path"]
    for p in sorted(pages, key=lambda p: p["link"]):
        pid = p["id"]
        new = path[pid]
        if not new and pid in brick_type:  # empty brick page: the generated brick page still exists
            new = ("de_DE/" if lang[pid] == "de" else "") + f"reference/bricks/{brick_type[pid]}.md"
        if not new and lang[pid] == "de" and path.get(en_of.get(pid)):  # the English page until M5 translates it
            new = path[en_of[pid]]
        anc = p
        while not new and anc["parent"]:
            anc = by_id[anc["parent"]]
            new = path[anc["id"]]
        new = new or ("de_DE/index.md" if lang[pid] == "de" else "en/index.md")
        rows.append(f"{p['link']}\t{redirect_target(new)}")
    (IMP / "redirects.tsv").write_text("\n".join(rows) + "\n", encoding="utf-8")

    rows = ["url\tstatus\tbytes\tnew_path\treferenced_in_markdown"]
    total = 0
    for url in sorted(refs):
        rel = media_path.get(url, "")
        size = (ROOT / rel).stat().st_size if rel else 0
        total += size
        rows.append(f"{url}\t{status[url]}\t{size}\t{rel}\t{'yes' if url in used_media else 'no'}")
    (IMP / "media.tsv").write_text("\n".join(rows) + "\n", encoding="utf-8")

    missing = sorted(u for u in refs if status[u] != "ok")
    n_en = sum(1 for p in pages if lang[p["id"]] == "en")
    n_pairs = sum(1 for pid in en_of if path.get(pid) and path.get(en_of[pid]))
    bricks_en = [pid for pid in is_brick if lang[pid] == "en"]
    bricks_de = [pid for pid in is_brick if lang[pid] == "de"]
    by_method = {}
    for pid in is_brick:
        by_method[method.get(pid, "unmapped")] = by_method.get(method.get(pid, "unmapped"), 0) + 1
    auto_right = sum(1 for pid in bricks_en if auto.get(pid) and auto[pid] == brick_class.get(pid))
    auto_wrong = sorted(pid for pid in bricks_en if auto.get(pid) and auto[pid] != brick_class.get(pid))
    accounted = sum(v for k, v in counts.items() if k != "unmapped")
    lines = [
        "# WordPress import report",
        "",
        "Generated by `import/wp_import.py` from the public REST API of catrobat.org/docs/ (rendered HTML).",
        "",
        "## Pages",
        "",
        f"**{accounted}/{len(pages)}** pages accounted for ({n_en} English, {len(pages) - n_en} German).",
        "",
        "| Status | Pages |",
        "| --- | --- |",
    ]
    lines += [f"| {k} | {v} |" for k, v in sorted(counts.items())]
    lines += [
        "",
        "- `imported`: a guide page in `en/` or `de_DE/`.",
        "- `brick`: a brick page, now `reference/bricks/<Type>.md` (English) or `de_DE/reference/bricks/<Type>.md` (German).",
        "- `empty-container`: the page had no content and no children of its own on the new side; listed, no file.",
        "- `duplicate`: a second page for the same brick; the other one was kept.",
        "",
        f"English twins translated from German-only pages: {len(en_twin)} (`translated_from: de`).",
        f"EN/DE pairs mapped (both sides have a file): {n_pairs}. Every pair is in `pages.tsv`.",
        "",
        "Every page with its status, old URL, new path and EN/DE pair: [`pages.tsv`](pages.tsv).",
        "Old URL to new docs path for the redirects (M9): [`redirects.tsv`](redirects.tsv). Paths are relative to the docs root (`/getting-started/what-is-catrobat`, `/de_DE/…` for German, `/reference/bricks/<Type>` for bricks); an empty page redirects to its nearest ancestor with a page, or to the docs home.",
        "",
        "## Media",
        "",
        f"**{len(refs)}/{len(refs)}** referenced uploads accounted for: {len(refs) - len(missing)} stored in `media/`, {len(missing)} missing on the server (404; the pages keep the original URL).",
        f"Stored size after lossless PNG recompression: {total / 1e6:.1f} MB. Uploads only reachable through a resized variant or a link are stored too (`referenced_in_markdown = no`).",
        "",
        "oxipng is not installed on the importing machine, so PNGs were recompressed with zlib level 9 only (lossless). Running `oxipng -o max --strip safe` over `media/` later saves more.",
        "",
        "Metadata: PNG text and EXIF chunks are dropped, and the `/Author` field of every PDF is blanked (it held first names of the people who made the tutorials).",
        "",
    ]
    webps = sorted(rel for rel in media_path.values() if rel.endswith(".webp"))
    if webps:
        lines += ["PNGs over the 1 MB limit even after recompression are stored as lossless WebP (`cwebp -lossless -exact`, pixel-identical, checked with `magick compare -metric AE`). The WebP is committed by hand; the importer uses it whenever it exists next to the PNG name:", ""]
        lines += [f"- `{rel}`" for rel in webps] + [""]
    if missing:
        lines += ["Missing on the server (an upload with the same name minus WordPress' `-1`/`-2` suffix may be the same screenshot; replacing the reference is left to a human):", ""]
        for u in missing:
            twin = media_path.get(re.sub(r"-\d+(\.\w+)$", r"\1", u))
            lines.append(f"- {u}" + (f" (same name without suffix: `{twin}`)" if twin else ""))
        lines.append("")
    if big:
        lines += ["Images over 1 MB after recompression (CI limit for new images):", ""]
        lines += [f"- `{rel}`: {size / 1e6:.2f} MB" for rel, size in big] + [""]
    lines += [
        "Every upload with status, size and new path: [`media.tsv`](media.tsv).",
        "",
        "## Bricks",
        "",
        f"{len(bricks_en)} English and {len(bricks_de)} German brick pages. Files are keyed by the code.xml `type`; brick classes are checked against [`brick-types.txt`](brick-types.txt) (`CategoryBricksFactory`).",
        "",
        f"- Automatic (slug matches the type name): {auto_right} of {len(bricks_en)} English pages.",
        f"- Hand table [`brick-hand.tsv`](brick-hand.tsv): the rest, and all German pages (their slugs are German).",
        f"- By method: " + ", ".join(f"{k} {v}" for k, v in sorted(by_method.items())) + ".",
        f"- Automatic guesses the hand table overrides: {len(auto_wrong)}" + (" (" + ", ".join(map(str, auto_wrong)) + ")" if auto_wrong else "") + ".",
        f"- Types not in the brick list: {', '.join(unknown_types) or 'none'}.",
        "",
        "Script (hat) bricks are keyed by the code.xml type of the script they start, as the catalog and `CodeStatisticsParser` do: `WhenStartedBrick` is `StartScript`, `WhenBrick` is `WhenScript`, `BroadcastReceiverBrick` is `BroadcastScript` ([`script-bricks.tsv`](script-bricks.tsv), from each `ScriptBrick`'s `getScript()`). The auto match and the hand table work on brick class names; `brick-map.tsv` lists both.",
        "",
        "Full table: [`brick-map.tsv`](brick-map.tsv).",
        "",
    ]
    if unresolved:
        lines += ["## Links kept as old URLs", ""]
        lines += [f"- {by_id[pid]['link']} links to {href}" for pid, href in sorted(set(unresolved))] + [""]
    if missing_translations:
        lines += ["## Missing translations", ""] + [f"- {pid} {title[pid]}" for pid in missing_translations] + [""]
    (IMP / "REPORT.md").write_text("\n".join(lines), encoding="utf-8")
    print(f"pages {accounted}/{len(pages)} {counts}; media {len(refs) - len(missing)}+{len(missing)} missing/{len(refs)}; big {len(big)}; unresolved {len(set(unresolved))}; untranslated {len(missing_translations)}")


def redirect_target(rel):
    """The docs path a file becomes, from the docs root: en/a/b.md -> /a/b, de_DE/a/index.md -> /de_DE/a, en/index.md -> /."""
    rel = re.sub(r"(^|/)index\.md$", "", rel).removesuffix(".md")
    return "/" + (rel[3:] if rel.startswith("en/") else "" if rel == "en" else rel)


if __name__ == "__main__":
    main()
