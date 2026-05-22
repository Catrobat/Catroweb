import { defineConfig } from 'vite'
import symfony from 'vite-plugin-symfony'
import path from 'node:path'

import { jsEntries, cssEntries } from './vite/entries.mjs'
import { purgeCssPlugin, cssEntryWrappers, staticCopy } from './vite/plugins.mjs'

const rootDir = import.meta.dirname

const ASSET_DIRS = {
  '.css': 'css',
  '.woff': 'fonts',
  '.woff2': 'fonts',
  '.ttf': 'fonts',
  '.otf': 'fonts',
  '.eot': 'fonts',
  '.png': 'images',
  '.jpg': 'images',
  '.jpeg': 'images',
  '.gif': 'images',
  '.svg': 'images',
  '.webp': 'images',
  '.ico': 'images',
}

export default defineConfig(({ mode }) => {
  const themes = cssEntryWrappers(cssEntries, { rootDir })

  return {
    base: '/build/',
    publicDir: false,

    resolve: {
      alias: { '@': path.resolve(rootDir, 'assets') },
    },

    build: {
      outDir: 'public/build',
      emptyOutDir: true,
      manifest: true,
      // 'hidden' in prod: maps are emitted (so upload-sourcemaps can ship
      // them to Bugsnag) but the JS bundles carry no sourceMappingURL, so
      // the browser doesn't expose them publicly.
      sourcemap: mode === 'production' ? 'hidden' : true,
      rollupOptions: {
        input: { ...jsEntries, ...themes.input },
        output: {
          entryFileNames: 'js/[name]-[hash].js',
          chunkFileNames: 'js/chunks/[name]-[hash].js',
          assetFileNames: (info) => {
            const dir = ASSET_DIRS[path.extname(info.name || '').toLowerCase()] ?? 'assets'
            return `${dir}/[name]-[hash][extname]`
          },
        },
      },
    },

    css: {
      devSourcemap: true,
      preprocessorOptions: {
        scss: {
          api: 'modern-compiler',
          // Bootstrap 5 Sass deprecations — drop on Bootstrap 6 upgrade.
          silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function'],
          loadPaths: [path.resolve(rootDir, 'node_modules')],
        },
      },
    },

    plugins: [
      themes.plugin,
      purgeCssPlugin({ rootDir }),
      // Twig references images via asset('images/...'); land them at
      // public/images/ (= one level up from build.outDir) so the URL resolves
      // without bundler involvement. webp-on-demand.php writes generated
      // variants alongside.
      staticCopy(
        [
          { src: 'assets/images', dest: '../images' },
          { src: 'assets/images/favicon.ico', dest: '../favicon.ico' },
        ],
        { rootDir },
      ),
      symfony({
        stimulus: './assets/controllers.json',
        viteDevServerHostname: 'localhost',
      }),
    ],
  }
})
