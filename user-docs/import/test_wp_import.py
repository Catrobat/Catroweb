"""Self-check for the importer's pure parts: python3 import/test_wp_import.py"""
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent))
import wp_import as w  # noqa: E402

assert w.media_key("http://www.catrobat.org/wp-content/uploads/2024/07/a_b-1024x196.png?crop=1") == \
    "https://catrobat.org/wp-content/uploads/2024/07/a_b.png"
assert w.auto_brick("set-x-to", {"SetXBrick", "SetYBrick"}) == "SetXBrick"
assert w.auto_brick("when-tapped", {"WhenBrick"}) is None
assert w.redirect_target("en/a/index.md") == "/a" and w.redirect_target("de_DE/a/b.md") == "/a/b?locale=de_DE"
assert w.redirect_target("en/index.md") == "/" and w.redirect_target("reference/bricks/SetXBrick.md") == "/reference/bricks/SetXBrick"

assert w.redirect_target("de_DE/index.md") == "/?locale=de_DE"

html = (
    '<p id="A"><strong>Head</strong></p><p>One <em>two <strong>three</strong></em><br>x_1 _y_ [z]</p>'
    '<div class="wp-block-button"><a href="#A">Go</a></div>'
    '<figure><a href="https://catrobat.org/docs/x/"><img src="https://catrobat.org/wp-content/uploads/2024/07/i-300x58.png" alt="I"/></a></figure>'
    '<span class="embed-youtube"><iframe src="https://www.youtube.com/embed/75i10o_uv0U?version=3"></iframe></span>'
    '<ol class="schema-how-to-steps"><li><strong class="schema-how-to-step-name">Start</strong>'
    '<p class="schema-how-to-step-text">Tap it.</p></li></ol>'
)
md = w.Converter(lambda h: "x.md" if "/docs/x/" in h else h, lambda u: "m/" + w.media_key(u).rsplit("/", 1)[1]).convert(html)
expected = """### Head

One *two **three***

x_1 \\_y\\_ \\[z\\]

[Go](#head)

[![I](m/i.png)](x.md)

::video{youtube=75i10o_uv0U}

### 1. Start

Tap it.
"""
assert md == expected, md
assert w.describe("![a](b)\n\nShort.\n\nThis is the first real sentence. More.") == "This is the first real sentence."
pdf = b"/Author (Alex Example)/Title (x)"
assert w.strip_pdf_author(pdf) == b"/Author (            )/Title (x)"
try:
    w.cache_file("https://catrobat.org/wp-content/uploads/2024/../../../x.png")
    raise AssertionError("path escape not rejected")
except ValueError:
    pass
print("wp_import self-test passed")
