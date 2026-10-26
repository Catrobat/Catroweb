"""Plays fixtures/restart.catrobat (scripts/make_restart_fixture.py) with the start screen in headless Chrome
(cdp.py) and checks the restart button: the counter variable counts up, the button resets it to its initial 0 (the
pen line it draws starts again, so the variable, the pen and the scripts were reset), and it counts again.
The counter is read off the stage: the blue pixels on its middle row. Usage: python3 restart_check.py <chrome> <url>"""
import sys
import time

from cdp import Chrome

# The line runs from x = -200 to -200 + 10 * counter, 8 wide with round ends, on a 480 wide stage.
COUNTER = """(() => {
  const stage = document.getElementById('canvas')
  const copy = Object.assign(document.createElement('canvas'), {width: stage.width, height: stage.height})
  const context = copy.getContext('2d')
  context.drawImage(stage, 0, 0)
  const row = context.getImageData(0, Math.round(stage.height / 2), stage.width, 1).data
  let blue = 0
  for (let i = 0; i < row.length; i += 4) blue += row[i + 2] > 150 && row[i] < 100 && row[i + 1] < 100
  return Math.max(0, Math.round((blue * 480 / stage.width - 8) / 10))
})()"""


def wait_for(what, condition, seconds, step=0.2):
    end = time.monotonic() + seconds
    while time.monotonic() < end:
        value = condition()
        if value:
            return value
        time.sleep(step)
    print(f"restart: timed out waiting for {what}")
    print("\n".join(browser.console[-20:]))
    sys.exit(1)


chrome, url = sys.argv[1], sys.argv[2]
with Chrome(chrome, ["--window-size=480,800", "--autoplay-policy=no-user-gesture-required"]) as browser:
    page = browser.open()
    browser.send("Page.navigate", page, url=url)
    evaluate = lambda expression: browser.evaluate(page, expression)  # noqa: E731
    wait_for("the play button", lambda: evaluate("!!document.querySelector('#start-screen .play.ready')"), 180, 1)
    evaluate("document.querySelector('#start-screen .play').click()")
    label = wait_for("the restart button", lambda: evaluate("document.getElementById('restart-button')?.ariaLabel"), 120, 1)
    before = wait_for("the counter to reach 10", lambda: (n := evaluate(COUNTER)) >= 10 and n, 60)
    evaluate("document.getElementById('restart-button').click()")
    after = wait_for("the counter to start again from 0", lambda: (n := evaluate(COUNTER)) <= 2 and str(n), 10, 0.05)
    again = wait_for("the counter to count again", lambda: (n := evaluate(COUNTER)) >= 5 and n, 30)
    print(f'restart ("{label}"): counted to {before}, restarted at {after}, counted again to {again}')
