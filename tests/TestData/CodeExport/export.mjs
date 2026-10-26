// A stand-in for the player's code export (player/web/code-export/export.mjs) in tests, where the player is not
// built: same command line and output format, but it only lists the objects, scripts and bricks of a code.xml by
// their XML types. Strings are markers (CodeViewLocalizer) where it knows one. "BROKEN" in code.xml makes it say the
// project cannot be shown; "CRASH" makes Node fail; "READ:<path>" makes it read that file too, which Node's permission
// model must deny; "ENV:<name>" writes that environment variable, which it must not have (CodeExportRunnerTest).
import { readFileSync } from 'node:fs'

const MARKERS = {
  StartScript: '\u0001s:brick_when_started\u0002',
  WaitBrick: '\u0001s:brick_wait\u0002',
  ShowBrick: '\u0001s:brick_show\u0002',
  HideBrick: '\u0001s:brick_hide\u0002',
}

function attribute(tag, name) {
  return new RegExp(`${name}="([^"]*)"`).exec(tag)?.[1]
}

function brick(type) {
  const parts = [{ kind: 'label', text: MARKERS[type] ?? type }]
  if (type === 'WaitBrick') {
    parts.push(
      { kind: 'formula', text: '1' },
      { kind: 'label', text: '\u0001p:second_plural\u001f1\u0002' },
    )
  }
  return {
    type,
    commented_out: false,
    parts,
    category: type.endsWith('Script') ? 'event' : 'control',
  }
}

function exportXml(xml) {
  if (xml.includes('BROKEN')) return { error: 'code.xml is not valid XML' }
  if (xml.includes('CRASH')) process.exit(3)
  const read = /READ:(\S+)/.exec(xml)
  if (read) return { read: readFileSync(read[1], 'utf8').length }
  const env = /ENV:(\w+)/.exec(xml)
  if (env) return { env: process.env[env[1]] ?? null }
  const objects = []
  for (const tag of xml.match(/<object [^>]*>|<script [^>]*>|<brick [^>]*>/g) ?? []) {
    if (tag.startsWith('<object')) {
      objects.push({ name: attribute(tag, 'name') ?? '', looks: [], sounds: [], scripts: [] })
    } else if (tag.startsWith('<script') && objects.length > 0) {
      objects.at(-1).scripts.push({ ...brick(attribute(tag, 'type') ?? ''), bricks: [] })
    } else if (objects.at(-1)?.scripts.length > 0) {
      objects
        .at(-1)
        .scripts.at(-1)
        .bricks.push(brick(attribute(tag, 'type') ?? ''))
    }
  }
  return { files_in_scene_folders: false, scenes: [{ name: 'Scene 1', objects }] }
}

process.stdout.write(JSON.stringify(exportXml(readFileSync(process.argv[2], 'utf8'))))
