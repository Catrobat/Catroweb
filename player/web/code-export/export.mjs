// Runs the code view's export (codeview/CodeExport, compiled to code-export.js) under Node: code.xml in, the code
// view's JSON out. The player's loader reads code.xml with the browser's DOMParser, which Node gets from xmldom.
//
//   node export.mjs < code.xml > code.json      one project
//   node export.mjs a.xml b.xml ...             one JSON line per file, in order (tests, batches)
//
// share runs it with Node's permission model, allowed to read only this folder and the file it exports: the XML is
// untrusted, so no network, no child processes and no writes (CodeExportRunner on share).
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

function exportXml(xml) {
  try {
    return globalThis.catrobatCodeExport(xml)
  } catch (e) {
    return JSON.stringify({ error: String(e?.message ?? e) })
  }
}

const files = process.argv.slice(2)
if (files.length === 0) {
  process.stdout.write(exportXml(readFileSync(0, 'utf8')))
} else {
  for (const file of files) {
    process.stdout.write(`${exportXml(readFileSync(file, 'utf8'))}\n`)
  }
}
