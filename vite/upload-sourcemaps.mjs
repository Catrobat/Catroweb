// Uploads built sourcemaps to Bugsnag via their multipart upload API. No-op
// when BUGSNAG_API_KEY is unset (local dev). Exits non-zero on real upload
// failures so CI fails loudly.
//
// Uses Node's built-in fetch/FormData (Node 18+) — no npm dep. Replaces the
// retired @bugsnag/source-maps package, which was flagged unmaintained and
// dragged in 30+ low-popularity transitive deps.

import { readFile, readdir } from 'node:fs/promises'
import path from 'node:path'

const API_KEY = process.env.BUGSNAG_API_KEY
if (!API_KEY) {
  process.exit(0)
}

const ENDPOINT = 'https://upload.bugsnag.com/sourcemap'
const APP_VERSION = process.env.APP_VERSION || ''
const JS_DIR = path.resolve('public/build/js')
const URL_PREFIX = '*/build/js/'

async function uploadOne(jsName) {
  const mapName = `${jsName}.map`
  const [jsContent, mapContent] = await Promise.all([
    readFile(path.join(JS_DIR, jsName)),
    readFile(path.join(JS_DIR, mapName)),
  ])

  const form = new FormData()
  form.set('apiKey', API_KEY)
  form.set('overwrite', 'true')
  if (APP_VERSION) form.set('appVersion', APP_VERSION)
  form.set('minifiedUrl', URL_PREFIX + jsName)
  form.set('sourceMap', new Blob([mapContent], { type: 'application/json' }), mapName)
  form.set('minifiedFile', new Blob([jsContent], { type: 'application/javascript' }), jsName)

  const res = await fetch(ENDPOINT, { method: 'POST', body: form })
  if (!res.ok) {
    throw new Error(`${jsName}: ${res.status} ${await res.text()}`)
  }
}

const allFiles = await readdir(JS_DIR).catch(() => [])
const jsFiles = allFiles.filter((f) => f.endsWith('.js') && allFiles.includes(`${f}.map`))

if (jsFiles.length === 0) {
  console.log('[bugsnag] no .js/.map pairs to upload')
  process.exit(0)
}

const results = await Promise.allSettled(jsFiles.map(uploadOne))
const failures = results.filter((r) => r.status === 'rejected')
if (failures.length > 0) {
  for (const f of failures) console.error('[bugsnag]', f.reason.message)
  console.error(`[bugsnag] ${failures.length}/${jsFiles.length} uploads failed`)
  process.exit(1)
}

console.log(`[bugsnag] uploaded ${jsFiles.length} sourcemaps`)
