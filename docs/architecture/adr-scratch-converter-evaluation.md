# ADR: Scratch to Pocket Code conversion

**Date**: 2026-10-22
**Status**: Implemented (v1)
**Issue**: [#7419](https://github.com/Catrobat/Catroweb/issues/7419)

## Decision

`POST /api/projects/scratch/{id}` converts a public Scratch 3 project into a Catrobat 1.14 archive inside
Catroweb (`src/Project/Scratch/Converter`) and publishes it through the normal upload pipeline (malware scan,
sanitizer, validation) under a shadow account of the Scratch author. A converted project that is unchanged on
Scratch is served from Share without converting again.

## Why this shape

- **Synchronous and in-process.** Conversion takes 1–25 s for real projects (Paper Minecraft: 12,700 bricks, 24 s),
  because assets download in parallel. A queue and worker container would add operations for no current need.
  Revisit when people regularly hit "busy".
- **Load is bounded like uploads.** Only real conversions count: 5 per 15 minutes and 20 per day per IP
  (`scratch_convert_*` rate limiters). One conversion runs at a time per server (file lock). Hard caps: 16 MiB
  project.json, 32 MiB per asset, 128 MiB of assets, 100,000 blocks, 120 s.
- **The target is the current app.** The mapping follows Catroid `develop` (language 1.14) and Scratch 3's sb3
  format. The archived ScratchToCatrobat converter is only a hint.

## How it maps

- **Stage:** 480×360 with `MAXIMIZE`, so Scratch coordinates are 1:1. Costumes are padded so Scratch's rotation
  center, measured from the SVG viewBox origin, becomes the image center. SVGs are rendered with `rsvg-convert`,
  and IMA-ADPCM wavs are transcoded with `sox`.
- **Start-up state:** Pocket Code stores none. The background sets variables and lists, then `BroadcastWait
__init_sprites` restores each sprite's position, size, direction, costume and visibility, and only then
  `__green_flag` starts the "when flag clicked" scripts.
- **Cross-sprite reads** (`sensing_of`, go to/point towards/distance to a sprite) read `__<sprite>_<property>`
  program variables that the owner updates every frame. "Create clone of <sprite>" becomes a broadcast that only
  the original sprite answers.
- **References:** looks, sounds, variables, lists and custom-block signatures are written in full at every use.
  `XStreamReferences` then turns all but the first occurrence into back-references, so forward references (which
  fail the load) cannot happen.
- **Unsupported blocks** become NoteBricks, so the project still loads and shows what was dropped. Examples:
  drag mode, sound effects, list monitors, and graphic effects other than color, ghost and brightness.
- **Loop pace:** Scratch ends every pass of a loop with a frame (30 per second) once anything on stage changed;
  Catroid only holds a pass for 0.02 s, and only in loops with motion or look bricks, so variable-only game loops ran
  hundreds of passes a second. Each loop body therefore ends with a wait until the next tick of a 30 per second
  clock on the timer (plus one 1/60 s display frame, because the timer runs up to a frame ahead of Catroid's
  scripts), measured at 30 passes a second in the browser player. Not in "run without screen refresh" custom blocks,
  not when the body already waits, and not in a counted loop that changes nothing visible right in a script (set-up
  of lists and counters, which Scratch runs at once when nothing else moves). The converter's own helper loops stay
  unthrottled: they poll input and copy state.
- **Converter versions:** `ScratchToCatrobatConverter::VERSION` is stored with each converted project
  (`program.scratch_converter_version`). A copy from an older version is converted again on its next import;
  `bin/console catro:scratch:reconvert` converts all of them (`--dry-run` lists them, `--limit`, `--pause` seconds
  between conversions, default 5). It is idempotent: converted projects are not picked up again, and projects
  Scratch no longer serves keep their old copy.
- **Keyboard:** every key a project uses gets an on-screen button sprite that sets the key's variable and broadcasts
  the key.
- **Scratch 2** projects (not saved since 2019) are translated to the Scratch 3 shape first (`Scratch2Project`, a
  port of scratch-vm's `sb2.js`), then converted like any other.

## Verified

Pong Starter and Paper Minecraft convert, import into Pocket Code 1.4.1 (develop build) on Android 13, and run.
Custom blocks with inputs open correctly in the editor. `ScratchToCatrobatConverterTest` checks references, files,
header, bracket placement and costume padding offline.

## Known gaps

- Scratch 1.4 projects are rejected.
- Some text engines built from custom blocks (Paper Minecraft's settings values) don't render yet.
- Scratch compares strings case-insensitively; Pocket Code compares them case-sensitively (broadcasts are matched).
- The start-up takes a frame per generated brick, so projects with long saved lists start a few seconds late.
- Variable monitors that a project shows from the start are not converted, only "show variable" blocks.
