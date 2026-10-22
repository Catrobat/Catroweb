# ADR: Scratch to Pocket Code conversion

**Date**: 2026-10-22
**Status**: Implemented (v1)
**Issue**: [#7419](https://github.com/Catrobat/Catroweb/issues/7419)

## Decision

Opening `/app/scratch/project/{id}` converts a public Scratch 3 project into a Catrobat 1.14 archive inside
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
  keyboard, drag mode, sound effects, list monitors, and graphic effects other than color, ghost and brightness.

## Verified

Pong Starter and Paper Minecraft convert, import into Pocket Code 1.4.1 (develop build) on Android 13, and run.
Custom blocks with inputs open correctly in the editor. `ScratchToCatrobatConverterTest` checks references, files,
header, bracket placement and costume padding offline.

## Known gaps

- Scratch 2 projects are rejected; Scratch serves them unconverted.
- Some text engines built from custom blocks (Paper Minecraft's settings values) don't render yet.
- No keyboard: "when key pressed" scripts never fire; on-screen key sprites are a possible next step.
- Scratch compares strings case-insensitively; Pocket Code compares them case-sensitively.
