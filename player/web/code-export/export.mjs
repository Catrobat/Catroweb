// Runs the code view's export (codeview/CodeExport, compiled to code-export.js) under Node: code.xml in, the code
// view's JSON out. The player's loader reads code.xml with the browser's DOMParser, which Node gets from xmldom.
//
//   node export.mjs code.xml > code.json
//
// share runs it with Node's permission model, allowed to read only this folder and the file it exports (no writes,
// child processes or workers), and with an empty environment: the XML is untrusted (CodeExportRunner on share).
import { readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { runInThisContext } from 'node:vm'
import { DOMParser } from '@xmldom/xmldom'

const here = dirname(fileURLToPath(import.meta.url))

// A malformed code.xml must fail the way the browser's does: ProjectLoader looks for a parsererror element.
class StrictDOMParser extends DOMParser {
  constructor() {
    super({
      onError: (level, message) => {
        if (level !== 'warning') throw new Error(`code.xml is not valid XML: ${message}`)
      },
    })
  }
}

// stdout is for the JSON alone: Catroid's log (android.util.Log on the web) goes to stderr
console.log = console.info = console.debug = console.warn = console.error

// The loader's upgrade of projects before Catrobat language 0.93 (java/Dom) walks the text nodes with a TreeWalker,
// which xmldom has not: just enough of one, over a list taken first (the loader removes nodes as it goes)
Object.getPrototypeOf(new DOMParser().parseFromString('<a/>', 'text/xml')).createTreeWalker = (
  root,
  show,
) => {
  const nodes = []
  const walk = (node) => {
    for (let child = node.firstChild; child; child = child.nextSibling) {
      if (show & (1 << (child.nodeType - 1))) nodes.push(child)
      walk(child)
    }
  }
  walk(root)
  let index = -1
  return {
    get currentNode() {
      return nodes[index] ?? root
    },
    nextNode() {
      index++
      return nodes[index] ?? null
    },
  }
}

const resources = JSON.parse(readFileSync(join(here, 'resources.json'), 'utf8'))
globalThis.window = globalThis
globalThis.DOMParser = StrictDOMParser
globalThis.catrobatCode = resources
globalThis.catrobatStrings = resources.strings
globalThis.catrobatPlurals = resources.plurals
globalThis.catrobatArrays = resources.arrays

// TeaVM's module puts main into an `exports` object when there is one
const teavm = {}
globalThis.exports = teavm
runInThisContext(readFileSync(join(here, 'code-export.js'), 'utf8'), { filename: 'code-export.js' })
delete globalThis.exports
teavm.main([])

// The export's strings are markers made of U+0001, U+0002 and U+001F (CodeExport): a name in code.xml must not
// contain them, written out or as a character reference, or it could show as any of Catroid's strings.
// biome-ignore lint/suspicious/noControlCharactersInRegex: these control characters are what it takes out
const MARKER_CHARACTERS = /[\x01\x02\x1f]|&#(?:0*(?:1|2|31)|[xX]0*(?:1|2|1[fF]));/g

let json
try {
  let xml = readFileSync(process.argv[2], 'utf8')
  // until none is left: taking one out can join a new one ("&&#1;#1;")
  for (let before = ''; before !== xml; ) {
    before = xml
    xml = xml.replace(MARKER_CHARACTERS, '')
  }
  json = globalThis.catrobatCodeExport(xml)
} catch (e) {
  json = JSON.stringify({ error: String(e?.message ?? e) })
}
process.stdout.write(json)
