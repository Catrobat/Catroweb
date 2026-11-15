// Material Icons, cut down at build time to the icons this codebase can show.
//
// The full font is ~126 KB and was the biggest render-blocking download on the
// start page. Icons are ligatures ("home" -> glyph), and many names are picked
// at runtime (JS lookup maps, Twig include args, PHP choice lists), so the
// subset keeps every Material Icons name that appears as a word ANYWHERE in
// templates/, assets/, translations/ or src/. False positives ("list", "home")
// cost ~70 bytes each; a missed icon would render as its plain-text name.
//
// The one thing this cannot see is a name that lives only in the database or
// in remote data. Such a name must also be written somewhere in the code (the
// maintenance icons are, in MaintenanceInformationAdmin's choice list).
//
// The build fails when markup puts a literal name inside a .material-icons
// element that the font does not have: that icon never rendered.
import { mkdir, readdir, readFile, writeFile } from 'node:fs/promises'
import { createRequire } from 'node:module'
import path from 'node:path'
import * as hb from 'harfbuzzjs'
import wawoff2 from 'wawoff2'

const SCAN_DIRS = ['templates', 'assets', 'translations', 'src']
const SCAN_EXT = /\.(twig|js|mjs|css|scss|php|ya?ml|xlf|json)$/
const FONT = 'node_modules/material-icons/iconfont/material-icons.woff2'
export const SUBSET_PATH = 'node_modules/.cache/material-icons/material-icons-subset.woff2'
// Ligature input characters; they must stay in the font for ligatures to form.
const LIGATURE_CHARS = 'abcdefghijklmnopqrstuvwxyz0123456789_'
// A literal name directly inside a .material-icons element, in Twig or JS markup.
const LITERAL_IN_ELEMENT = /class="[^"]*\bmaterial-icons\b[^"]*"[^>]*>\s*([a-z0-9_]+)\s*</g

async function sourceFiles(rootDir) {
  const files = []
  for (const dir of SCAN_DIRS) {
    const entries = await readdir(path.join(rootDir, dir), { recursive: true }).catch(() => [])
    for (const rel of entries) {
      if (SCAN_EXT.test(rel)) files.push(path.join(rootDir, dir, rel))
    }
  }
  return files
}

/** Name -> glyph id of the icon, or undefined when it is no icon. */
function iconGlyphs(sfnt) {
  const font = new hb.Font(new hb.Face(new hb.Blob(sfnt)))
  return (name) => {
    // A single letter or digit shapes to one glyph too, but is no icon.
    if (name.length < 2) return undefined
    const buffer = new hb.Buffer()
    buffer.addText(name)
    buffer.guessSegmentProperties()
    hb.shape(font, buffer)
    const glyphs = buffer.getGlyphInfos()
    return glyphs.length === 1 ? glyphs[0].codepoint : undefined // .codepoint is the glyph id after shaping
  }
}

/**
 * hb-subset --gids=<glyphs> --text=<LIGATURE_CHARS> --layout-features=* --no-layout-closure.
 * Without --no-layout-closure every ligature reachable from a-z would stay in, i.e. all of them.
 */
async function subsetSfnt(sfnt, glyphIds) {
  const wasm = await readFile(
    createRequire(import.meta.url).resolve('harfbuzzjs/dist/harfbuzz-subset.wasm'),
  )
  const { exports: h } = (await WebAssembly.instantiate(wasm)).instance
  h._initialize()
  const heap = () => new Uint8Array(h.memory.buffer) // memory growth detaches older views
  const fontPtr = h.malloc(sfnt.byteLength)
  heap().set(sfnt, fontPtr)
  const blob = h.hb_blob_create(fontPtr, sfnt.byteLength, 2 /* HB_MEMORY_MODE_WRITABLE */, 0, 0)
  const face = h.hb_face_create(blob, 0)
  h.hb_blob_destroy(blob)

  const input = h.hb_subset_input_create_or_fail()
  const features = h.hb_subset_input_set(input, 6 /* HB_SUBSET_SETS_LAYOUT_FEATURE_TAG */)
  h.hb_set_clear(features)
  h.hb_set_invert(features)
  h.hb_subset_input_set_flags(input, 0x200 /* HB_SUBSET_FLAGS_NO_LAYOUT_CLOSURE */)
  const gids = h.hb_subset_input_glyph_set(input)
  for (const gid of glyphIds) h.hb_set_add(gids, gid)
  const unicodes = h.hb_subset_input_unicode_set(input)
  for (const ch of LIGATURE_CHARS) h.hb_set_add(unicodes, ch.codePointAt(0))

  const subset = h.hb_subset_or_fail(face, input)
  h.hb_subset_input_destroy(input)
  h.hb_face_destroy(face)
  if (subset === 0) {
    h.free(fontPtr)
    throw new Error('hb_subset_or_fail failed')
  }
  const out = h.hb_face_reference_blob(subset)
  const data = h.hb_blob_get_data(out, 0)
  const bytes = heap().slice(data, data + h.hb_blob_get_length(out))
  h.hb_blob_destroy(out)
  h.hb_face_destroy(subset)
  h.free(fontPtr)
  return bytes
}

/**
 * @returns {Promise<{names: string[], unknown: string[]}>} icon names found in
 * the sources, and literal names in .material-icons markup the font lacks.
 */
async function collectIcons(rootDir, glyphOf) {
  const names = new Set()
  const unknown = new Set()
  const known = new Map()
  const isIcon = (word) => {
    if (!known.has(word)) known.set(word, glyphOf(word) !== undefined)
    return known.get(word)
  }
  for (const file of await sourceFiles(rootDir)) {
    const text = await readFile(file, 'utf8')
    for (const [word] of text.matchAll(/[a-z0-9_]+/g)) {
      if (isIcon(word)) names.add(word)
    }
    for (const [, name] of text.matchAll(LITERAL_IN_ELEMENT)) {
      if (!isIcon(name)) unknown.add(`${name} (${path.relative(rootDir, file)})`)
    }
  }
  return { names: [...names].sort(), unknown: [...unknown].sort() }
}

export async function buildSubset(rootDir) {
  // HarfBuzz reads plain sfnt only, not woff2.
  const sfnt = await wawoff2.decompress(await readFile(path.join(rootDir, FONT)))
  const glyphOf = iconGlyphs(sfnt)
  const { names, unknown } = await collectIcons(rootDir, glyphOf)
  if (unknown.length > 0) {
    throw new Error(
      `Not a Material Icons name, would render as text:\n  ${unknown.join('\n  ')}\n` +
        'Pick a name from https://fonts.google.com/icons?icon.set=Material+Icons',
    )
  }
  const subset = await wawoff2.compress(await subsetSfnt(sfnt, names.map(glyphOf)))
  const out = path.join(rootDir, SUBSET_PATH)
  await mkdir(path.dirname(out), { recursive: true })
  await writeFile(out, subset)
  return { names, bytes: subset.length }
}

export function materialIconsSubset({ rootDir }) {
  return {
    name: 'material-icons-subset',
    async buildStart() {
      const { names, bytes } = await buildSubset(rootDir)
      this.info(`${names.length} icons, ${(bytes / 1024).toFixed(1)} KB`)
    },
  }
}
