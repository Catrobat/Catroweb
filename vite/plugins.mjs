import { PurgeCSS } from 'purgecss'
import { writeFile, readdir, cp } from 'node:fs/promises'
import path from 'node:path'

// Copy static asset trees verbatim into the build output, preserving each
// source's subdirectory structure. Use for things Twig references directly via
// asset('images/...') — anything Vite doesn't see imported from JS/CSS.
//
// `dest` is resolved relative to build.outDir. Pass '../images' to land
// outside the build/ dir (e.g., public/images/).
export function staticCopy(targets, { rootDir }) {
  return {
    name: 'static-copy',
    apply: 'build',
    async writeBundle({ dir }) {
      for (const { src, dest } of targets) {
        await cp(path.resolve(rootDir, src), path.resolve(dir, dest), {
          recursive: true,
          force: true,
        })
      }
      this.info(`copied ${targets.length} static tree(s)`)
    },
  }
}

// PurgeCSS safelist mirrors the old webpack PurgeCSSPlugin 1:1 — these
// patterns are added by Twig conditionally, by JS at runtime, or by libraries
// (Bootstrap, SweetAlert, MDC), so they're never present in the static content
// scan.
const PURGE_SAFELIST = [
  /^swal2/,
  /^modal/,
  /^mdc/,
  /^data-bs-theme/,
  /^cookie-consent/,
  /^code-stats-row--level-/,
  /^cv-block--/,
  /^projects-list/,
  /^comment-/,
  /^remix-graph-/,
  /^notification-/,
  /^follower-/,
  /^following-/,
  /^studio-/,
  /^lazyload/,
  /^carousel/,
]

export function purgeCssPlugin({ rootDir }) {
  return {
    name: 'purgecss-post',
    apply: ({ mode }) => mode === 'production' && 'build',
    enforce: 'post',
    async writeBundle({ dir }) {
      const cssDir = path.join(dir, 'css')
      const cssFiles = (await readdir(cssDir).catch(() => []))
        .filter((f) => f.endsWith('.css'))
        .map((f) => path.join(cssDir, f))
      if (cssFiles.length === 0) return

      const result = await new PurgeCSS().purge({
        content: [
          path.resolve(rootDir, 'templates/**/*.html.twig'),
          path.resolve(rootDir, 'assets/**/*.js'),
          path.resolve(rootDir, 'assets/**/*.svg'),
        ],
        css: cssFiles,
        safelist: { standard: PURGE_SAFELIST },
        rejected: true,
        defaultExtractor: (content) => content.match(/[\w-/:]+(?<!:)/g) || [],
      })

      let saved = 0
      await Promise.all(
        result.map(async ({ file, css, rejected = [] }) => {
          if (!file) return
          saved += rejected.reduce((n, sel) => n + sel.length, 0)
          await writeFile(file, css)
        }),
      )
      this.info(`purged ${result.length} files, saved ~${(saved / 1024).toFixed(1)} KB`)
    },
  }
}

// Rollup dedupes pure-CSS entries that share the same content (theme .scss
// files that all just @import 'pocketcode'). Wrapping each entry in a virtual
// JS module gives it its own chunk identity, so the manifest emits a unique
// entry per theme name.
//
// Returns { plugin, input } — pass `input` straight to rollupOptions.input
// alongside your real JS entries.
export function cssEntryWrappers(entries, { rootDir }) {
  const PUBLIC = 'virtual-css-entry:'
  const VIRTUAL = `\0${PUBLIC}`

  return {
    plugin: {
      name: 'css-entry-wrappers',
      resolveId(id) {
        if (!id.startsWith(PUBLIC)) return null
        return VIRTUAL + id.slice(PUBLIC.length)
      },
      load(id) {
        if (!id.startsWith(VIRTUAL)) return null
        const target = entries[id.slice(VIRTUAL.length)]
        return target ? `import ${JSON.stringify(path.resolve(rootDir, target))}\n` : null
      },
    },
    input: Object.fromEntries(Object.keys(entries).map((name) => [name, PUBLIC + name])),
  }
}
