# Loader fixtures

Projects for `scripts/check_loader.py` that Catroid's own `catrobatTests` do not cover. `smoke.sh` checks them together.

- `lego-nxt-settings.catrobat`: Catroid's `catrobatTests/motion/testMoveStepsBrick.catrobat` with a Lego NXT setting
  (four sensor ports) added to the header, the shape Mindstorms projects have.
- `sound-play-and-wait.catrobat`: Catroid's `catrobatTests/bricks/looks/testSetTransparency.catrobat` with a one-second
  tone (`Scene (1)/sounds/tone.wav`) and, before its first script's finish brick, reset timer, play sound and wait,
  and an assertion that the timer is past 0.9 s. Made by `scripts/make_sound_fixture.py`; `smoke.sh` plays it on the
  stage.
- `speak-and-wait.catrobat`: plain `testSetTransparency.catrobat` with reset timer, "speak Hello and wait" and an
  assertion that the timer is past 0.3 s before that finish brick: it passes only if "speak and wait" waits (for the
  speech, or its time-out where the browser never reports the end). Made by `scripts/make_speak_fixture.py`;
  `smoke.sh` plays it on the stage.
