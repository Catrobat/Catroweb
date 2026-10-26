"""Plays fixtures/sensors.catrobat in headless Chrome as on an emulated phone and checks that Catroid's formulas read
what the phone reports: Chrome's DevTools protocol (over --remote-debugging-pipe, no packages needed) sets the
orientation (alpha 90, beta 20, gamma -30), linear acceleration (1, 2, 3) and location (47.07, 15.44), and Chrome's
fake microphone beeps. The fixture's assertions (scripts/make_sensors_fixture.py) report 7777 only if the values
arrived; the page must also have vibrated. Usage: python3 sensors_check.py <chrome> <player url>#<project id>"""
import fcntl
import json
import os
import subprocess
import sys
import tempfile
import time

chrome, url = sys.argv[1], sys.argv[2]
profile = tempfile.TemporaryDirectory()


def high(fd):  # clear of 3 and 4, which child_fds fills
    moved = fcntl.fcntl(fd, fcntl.F_DUPFD_CLOEXEC, 10)
    os.close(fd)
    return moved


to_chrome, from_us, to_us, from_chrome = map(high, os.pipe() + os.pipe())


def child_fds():  # Chrome reads commands from fd 3 and writes replies to fd 4
    os.dup2(to_chrome, 3)
    os.dup2(from_chrome, 4)


browser = subprocess.Popen([chrome, "--headless=new", "--remote-debugging-pipe", "--disable-gpu-sandbox",
                            "--use-angle=swiftshader", "--enable-unsafe-swiftshader", "--window-size=480,800",
                            "--autoplay-policy=no-user-gesture-required", "--use-fake-ui-for-media-stream",
                            "--use-fake-device-for-media-stream", "--no-first-run", f"--user-data-dir={profile.name}",
                            "about:blank"],
                           pass_fds=(3, 4), preexec_fn=child_fds, stderr=subprocess.DEVNULL)
os.close(to_chrome)
os.close(from_chrome)
reader = os.fdopen(to_us, "rb")
buffer = b""
ids = iter(range(1, 10**6))
console = []


def send(method, session=None, **params):
    global buffer
    message_id = next(ids)
    message = {"id": message_id, "method": method, "params": params}
    if session:
        message["sessionId"] = session
    os.write(from_us, json.dumps(message).encode() + b"\0")
    while True:
        while b"\0" not in buffer:
            chunk = reader.read1(65536)
            if not chunk:
                raise SystemExit(f"Chrome closed the pipe during {method}")
            buffer += chunk
        raw, buffer = buffer.split(b"\0", 1)
        reply = json.loads(raw)
        if reply.get("method") in ("Runtime.consoleAPICalled", "Runtime.exceptionThrown"):
            params = reply["params"]
            console.append(params.get("exceptionDetails", {}).get("exception", {}).get("description", "")[:1500]
                           or " ".join(str(a.get("value", "")) for a in params.get("args", []))[:300])
        if reply.get("id") == message_id:
            if "error" in reply:
                raise SystemExit(f"{method}: {reply['error']}")
            return reply.get("result", {})


try:
    target = send("Target.createTarget", url="about:blank")["targetId"]
    page = send("Target.attachToTarget", targetId=target, flatten=True)["sessionId"]
    origin = url.split("/", 3)[:3]
    send("Browser.grantPermissions", origin="/".join(origin), permissions=["geolocation", "audioCapture", "sensors"])  # all others are denied
    send("Emulation.setGeolocationOverride", page, latitude=47.07, longitude=15.44, accuracy=10)
    send("Page.enable", page)
    send("Runtime.enable", page)
    send("Page.addScriptToEvaluateOnNewDocument", page, source="navigator.vibrate = function (ms) {"
         " document.documentElement.dataset.vibrated = ms; return true; };")
    send("Page.navigate", page, url=url)
    send("Emulation.setSensorOverrideEnabled", page, enabled=True, type="linear-acceleration")
    result = ""
    for _ in range(120):  # the readings each second, as a phone keeps sending them
        send("DeviceOrientation.setDeviceOrientationOverride", page, alpha=90, beta=20, gamma=-30)
        send("Emulation.setSensorOverrideReadings", page, type="linear-acceleration", reading={"xyz": {"x": 1, "y": 2, "z": 3}})
        time.sleep(1)
        result = send("Runtime.evaluate", page, returnByValue=True, expression="(document.querySelector('[data-test-result]')"
                      "?.getAttribute('data-test-result') ?? '') + ' vibrated ' + document.documentElement.dataset.vibrated"
                      )["result"]["value"]
        if not result.startswith(" "):
            break
    print(f"sensors: {result}")
    if not result.startswith("7777 "):
        print("\n".join(console[-20:]))
    sys.exit(0 if result.startswith("7777 ") and "vibrated 300" in result else 1)
finally:
    browser.kill()
    browser.wait()
    profile.cleanup()
