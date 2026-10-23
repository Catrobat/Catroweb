"""Serves the player for scripts/smoke.sh. The page gets an image that arrives after WAIT seconds, which holds back
the load event, so headless Chrome's --screenshot (taken on load) sees the stage after it has run for a while.
It listens on a free port and writes it to PORT_FILE. Usage: python3 smoke_server.py <webapp dir> <port file> <wait seconds>"""
import functools
import http.server
import sys
import time

ROOT, PORT_FILE, WAIT = sys.argv[1], sys.argv[2], float(sys.argv[3])
GIF = b"GIF89a\x01\x00\x01\x00\x80\x00\x00\x00\x00\x00\xff\xff\xff!\xf9\x04\x01\x00\x00\x00\x00,\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02D\x01\x00;"


class Handler(http.server.SimpleHTTPRequestHandler):
    def do_GET(self):
        if self.path == "/__wait":
            time.sleep(WAIT)
            self.reply(GIF, "image/gif")
        elif self.path == "/":
            with open(ROOT + "/index.html", "rb") as f:
                page = f.read()
            # The player starts on "load", which the image now holds back; start it as soon as the DOM is there.
            page = page.replace(b'addEventListener("load", start)', b'addEventListener("DOMContentLoaded", start)')
            self.reply(page.replace(b"</body>", b'<img src="/__wait" width="1" height="1"></body>'), "text/html")
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
