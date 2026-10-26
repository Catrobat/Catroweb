"""Plays fixtures/sensors.catrobat in headless Chrome as on an emulated phone and checks that Catroid's formulas read
what the phone reports: Chrome's DevTools protocol (cdp.py) sets the orientation (alpha 90, beta 20, gamma -30),
linear acceleration (1, 2, 3) and location (47.07, 15.44), and Chrome's fake microphone beeps. The fixture
(scripts/make_sensors_fixture.py) waits until the values have arrived and reports 7777 if they are right; the page must
also have vibrated. Usage: python3 sensors_check.py <chrome> <player url>#<project id>"""
import sys
import time

from cdp import Chrome

chrome, url = sys.argv[1], sys.argv[2]
with Chrome(chrome, ["--window-size=480,800", "--autoplay-policy=no-user-gesture-required",
                     "--use-fake-ui-for-media-stream", "--use-fake-device-for-media-stream"]) as browser:
    page = browser.open()
    origin = "/".join(url.split("/", 3)[:3])
    browser.send("Browser.grantPermissions", origin=origin, permissions=["geolocation", "audioCapture", "sensors"])  # all others are denied
    browser.send("Emulation.setGeolocationOverride", page, latitude=47.07, longitude=15.44, accuracy=10)
    browser.send("Page.addScriptToEvaluateOnNewDocument", page, source="navigator.vibrate = function (ms) {"
                 " document.documentElement.dataset.vibrated = ms; return true; };")
    browser.send("Page.navigate", page, url=url)
    browser.send("Emulation.setSensorOverrideEnabled", page, enabled=True, type="linear-acceleration")
    result = ""
    for _ in range(120):  # the readings each second, as a phone keeps sending them
        browser.send("DeviceOrientation.setDeviceOrientationOverride", page, alpha=90, beta=20, gamma=-30)
        browser.send("Emulation.setSensorOverrideReadings", page, type="linear-acceleration",
                     reading={"xyz": {"x": 1, "y": 2, "z": 3}})
        time.sleep(1)
        result = browser.evaluate(page, "(document.querySelector('[data-test-result]')"
                                  "?.getAttribute('data-test-result') ?? '') + ' vibrated '"
                                  " + document.documentElement.dataset.vibrated")
        if not result.startswith(" "):
            break
    print(f"sensors: {result}")
    if not result.startswith("7777 "):
        print("\n".join(browser.console[-20:]))
    sys.exit(0 if result.startswith("7777 ") and "vibrated 300" in result else 1)
