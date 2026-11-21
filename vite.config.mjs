import path from 'node:path'
import { defineConfig } from 'vite'
import symfony from 'vite-plugin-symfony'

import { cssEntries, jsEntries } from './vite/entries.mjs'
import { materialIconsSubset, SUBSET_PATH } from './vite/material-icons-subset.mjs'
import { cssEntryWrappers, purgeCssPlugin, staticCopy } from './vite/plugins.mjs'

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
      alias: {
        '@': path.resolve(rootDir, 'assets'),
        'material-icons-subset.woff2': path.resolve(rootDir, SUBSET_PATH),
      },
    },

    build: {
      outDir: 'public/build',
      emptyOutDir: true,
      manifest: true,
      // 'hidden' in prod: maps are emitted (so upload-sourcemaps can ship
      // them to Bugsnag) but the JS bundles carry no sourceMappingURL, so
      // the browser doesn't expose them publicly.
      sourcemap: mode === 'production' ? 'hidden' : true,
      // Never inline fonts: the small Roboto subsets (as base64 woff) made up ~34 KB of the
      // render-blocking base CSS, although a browser only downloads the subsets a page uses.
      assetsInlineLimit: (file) => (/\.(woff2?|ttf|otf)$/.test(file) ? false : undefined),
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

    css: { devSourcemap: true },

    plugins: [
      materialIconsSubset({ rootDir }),
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
          // Default URLs of Pocket Code's look, background and web request bricks.
          { src: 'assets/brick-defaults', dest: '../brick-defaults' },
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
