"""Headless Chrome driven over its DevTools protocol (--remote-debugging-pipe, no packages needed), for the checks
that need more than --dump-dom: sensors_check.py and restart_check.py.

    with Chrome(chrome, ["--some-flag"]) as browser:
        page = browser.open()                   # a new tab's session
        browser.send("Page.navigate", page, url=url)
        browser.evaluate(page, "1 + 1")
        browser.console                         # the page's console messages and exceptions"""
import fcntl
import json
import os
import subprocess
import tempfile


def _high(fd):  # clear of 3 and 4, which the child's fds fill
    moved = fcntl.fcntl(fd, fcntl.F_DUPFD_CLOEXEC, 10)
    os.close(fd)
    return moved


class Chrome:
    def __init__(self, chrome, args=()):
        self.profile = tempfile.TemporaryDirectory(ignore_cleanup_errors=True)  # its helpers may write after the kill
        to_chrome, self.out, to_us, from_chrome = map(_high, os.pipe() + os.pipe())

        def child_fds():  # Chrome reads commands from fd 3 and writes replies to fd 4
            os.dup2(to_chrome, 3)
            os.dup2(from_chrome, 4)

        self.process = subprocess.Popen([chrome, "--headless=new", "--remote-debugging-pipe", "--disable-gpu-sandbox",
                                         "--use-angle=swiftshader", "--enable-unsafe-swiftshader", "--no-first-run",
                                         f"--user-data-dir={self.profile.name}", *args, "about:blank"],
                                        pass_fds=(3, 4), preexec_fn=child_fds, stderr=subprocess.DEVNULL)
        os.close(to_chrome)
        os.close(from_chrome)
        self.reader = os.fdopen(to_us, "rb")
        self.buffer = b""
        self.ids = iter(range(1, 10**6))
        self.console = []

    def __enter__(self):
        return self

    def __exit__(self, *exc):
        self.process.kill()
        self.process.wait()
        self.profile.cleanup()

    def send(self, method, session=None, **params):
        message_id = next(self.ids)
        message = {"id": message_id, "method": method, "params": params}
        if session:
            message["sessionId"] = session
        os.write(self.out, json.dumps(message).encode() + b"\0")
        while True:
            while b"\0" not in self.buffer:
                chunk = self.reader.read1(65536)
                if not chunk:
                    raise SystemExit(f"Chrome closed the pipe during {method}")
                self.buffer += chunk
            raw, self.buffer = self.buffer.split(b"\0", 1)
            reply = json.loads(raw)
            if reply.get("method") in ("Runtime.consoleAPICalled", "Runtime.exceptionThrown"):
                event = reply["params"]
                self.console.append(event.get("exceptionDetails", {}).get("exception", {}).get("description", "")[:1500]
                                    or " ".join(str(a.get("value", "")) for a in event.get("args", []))[:300])
            if reply.get("id") == message_id:
                if "error" in reply:
                    raise SystemExit(f"{method}: {reply['error']}")
                return reply.get("result", {})

    def open(self):
        target = self.send("Target.createTarget", url="about:blank")["targetId"]
        page = self.send("Target.attachToTarget", targetId=target, flatten=True)["sessionId"]
        self.send("Page.enable", page)
        self.send("Runtime.enable", page)
        return page

    def evaluate(self, page, expression):
        return self.send("Runtime.evaluate", page, returnByValue=True, awaitPromise=True,
                         expression=expression)["result"].get("value")
