"""Plays Scratch projects converted by share's converter (scratch_check.sh) on the stage in headless Chrome (cdp.py).
Scratch projects report no test result, so each must: load (the launcher logs it), run SECONDS without an
exception or console error, and show something on the stage (more than one colour). Prints one line per project and
exits 1 if any failed. Usage: python3 scratch_check.py <chrome> <player url> <project id>..."""
import sys
import time

from cdp import Chrome

SECONDS = 10
STATE = """(() => {
  if (document.querySelector('details')) return 'failed: ' + document.body.innerText.replace(/\\s+/g, ' ').slice(0, 300)
  const stage = document.getElementById('canvas')
  const copy = Object.assign(document.createElement('canvas'), {width: 48, height: 80})
  copy.getContext('2d').drawImage(stage, 0, 0, 48, 80)
  const data = copy.getContext('2d').getImageData(0, 0, 48, 80).data
  const colours = new Set()
  for (let i = 0; i < data.length; i += 4) colours.add((data[i] >> 4) << 8 | (data[i + 1] >> 4) << 4 | data[i + 2] >> 4)
  return 'running ' + colours.size
})()"""

chrome, url, ids = sys.argv[1], sys.argv[2], sys.argv[3:]
failed = []
with Chrome(chrome, ["--window-size=480,800", "--autoplay-policy=no-user-gesture-required"]) as browser:
    page = browser.open()
    for project in ids:
        browser.errors.clear()
        browser.console.clear()
        browser.send("Page.navigate", page, url=f"{url}/?autostart#{project}")
        # ?autostart has no start screen: the stage starts right after the launcher logs that the project loaded.
        state, end = "starting", time.monotonic() + 180
        while state == "starting" and not browser.errors and time.monotonic() < end:
            time.sleep(1)
            state = browser.evaluate(page, STATE)
            if state.startswith("running") and not any(line.startswith('player: loaded "') for line in browser.console):
                state = "starting"
        if state.startswith("running") and not browser.errors:
            time.sleep(SECONDS)
            state = browser.evaluate(page, STATE)
        problem = (f"error: {browser.errors[0].splitlines()[0][:300]}" if browser.errors else
                   "did not start within 180 s" if state == "starting" else state if not state.startswith("running") else
                   "blank stage" if state == "running 1" else "")
        print(f"{project}: {problem or f'ran {SECONDS} s, {state.split()[1]} colours on the stage'}")
        if problem:
            failed.append(project)
        browser.send("Page.navigate", page, url="about:blank")  # the next project starts on a fresh page
sys.exit(1 if failed else 0)
