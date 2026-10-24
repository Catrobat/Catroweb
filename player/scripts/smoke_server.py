"""Serves the player for scripts/smoke.sh. The page gets an image that arrives after WAIT seconds, or as soon as a
test project has reported its result (__test-finished), which holds back the load event: headless Chrome's
--screenshot and --dump-dom (taken on load) see the stage after it has run for a while or the test has finished.
It listens on a free port and writes it to PORT_FILE. /project-files/ comes from FIXTURES, so test projects never land in
the deployed webapp. Usage: python3 smoke_server.py <webapp dir> <port file> <wait seconds> [fixtures dir]"""
import functools
import http.server
import sys
import threading
import urllib.parse
from pathlib import Path

ROOT, PORT_FILE, WAIT = sys.argv[1], sys.argv[2], float(sys.argv[3])
FINISHED = threading.Event()
FIXTURES = Path(sys.argv[4] if len(sys.argv) > 4 else ROOT)
# Every fixture file by its URL, listed once: a request path never reaches the file system.
PROJECT_FILES = {"/project-files/" + f.relative_to(FIXTURES / "project-files").as_posix(): f
                 for f in (FIXTURES / "project-files").rglob("*") if f.is_file()}
GIF = b"GIF89a\x01\x00\x01\x00\x80\x00\x00\x00\x00\x00\xff\xff\xff!\xf9\x04\x01\x00\x00\x00\x00,\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02D\x01\x00;"


class Handler(http.server.SimpleHTTPRequestHandler):
    def do_GET(self):
        if self.path == "/__wait":
            FINISHED.wait(WAIT)
            self.reply(GIF, "image/gif")
        elif self.path.endswith("/__test-finished"):
            FINISHED.set()
            self.reply(b"", "text/plain")
        elif self.path.split("?")[0] == "/":
            with open(ROOT + "/index.html", "rb") as f:
                page = f.read()
            # The player starts on "load", which the image now holds back; start it as soon as the DOM is there.
            page = page.replace(b'addEventListener("load", start)', b'addEventListener("DOMContentLoaded", start)')
            self.reply(page.replace(b"</body>", b'<img src="/__wait" width="1" height="1"></body>'), "text/html")
        elif urllib.parse.unquote(self.path) in PROJECT_FILES:  # share decodes the path the same way
            self.reply(PROJECT_FILES[urllib.parse.unquote(self.path)].read_bytes(), "application/octet-stream")
        elif self.path.startswith("/project-files/"):
            self.send_error(404)
        else:
            super().do_GET()

    def reply(self, body, kind):
        self.send_response(200)
        self.send_header("Content-Type", kind)
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def log_message(self, *args):
        pass


server = http.server.ThreadingHTTPServer(("127.0.0.1", 0), functools.partial(Handler, directory=ROOT))
with open(PORT_FILE, "w") as f:
    f.write(str(server.server_address[1]))
server.serve_forever()
