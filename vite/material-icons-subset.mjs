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
import path from 'node:path'
import fontverter from 'fontverter'
import * as hb from 'harfbuzzjs'
import subsetFont from 'subset-font'

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

/** Name -> codepoint of the icon's glyph, or undefined when it is no icon. */
function iconCodepoints(fontBytes) {
  const face = new hb.Face(new hb.Blob(fontBytes))
  const font = new hb.Font(face)
  const shape = (text) => {
    const buffer = new hb.Buffer()
    buffer.addText(text)
    buffer.guessSegmentProperties()
    hb.shape(font, buffer)
    return buffer.getGlyphInfos().map((g) => g.codepoint)
  }
  const codepointOfGlyph = new Map()
  for (const cp of Array.from(face.collectUnicodes())) {
    const [gid] = shape(String.fromCodePoint(cp))
    if (!codepointOfGlyph.has(gid)) codepointOfGlyph.set(gid, cp)
  }
  return (name) => {
    // A single letter or digit shapes to one glyph too, but is no icon.
    if (name.length < 2) return undefined
    const glyphs = shape(name)
    return glyphs.length === 1 ? codepointOfGlyph.get(glyphs[0]) : undefined
  }
}

/**
 * @returns {Promise<{names: string[], unknown: string[]}>} icon names found in
 * the sources, and literal names in .material-icons markup the font lacks.
 */
async function collectIcons(rootDir, codepointOf) {
  const names = new Set()
  const unknown = new Set()
  const known = new Map()
  const isIcon = (word) => {
    if (!known.has(word)) known.set(word, codepointOf(word) !== undefined)
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
  const fontBytes = await fontverter.convert(await readFile(path.join(rootDir, FONT)), 'sfnt')
  const codepointOf = iconCodepoints(fontBytes)
  const { names, unknown } = await collectIcons(rootDir, codepointOf)
  if (unknown.length > 0) {
    throw new Error(
      `Not a Material Icons name, would render as text:\n  ${unknown.join('\n  ')}\n` +
        'Pick a name from https://fonts.google.com/icons?icon.set=Material+Icons',
    )
  }
  const text = LIGATURE_CHARS + names.map((n) => String.fromCodePoint(codepointOf(n))).join('')
  const subset = await subsetFont(fontBytes, text, {
    targetFormat: 'woff2',
    noLayoutClosure: true, // else every ligature reachable from a-z stays in
  })
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
