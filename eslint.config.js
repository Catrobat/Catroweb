const { defineConfig, globalIgnores } = require('eslint/config')
const globals = require('globals')
const js = require('@eslint/js')
const prettierPlugin = require('eslint-plugin-prettier/recommended')
const importPlugin = require('eslint-plugin-import')
// const pluginPromise = require('eslint-plugin-promise')
const babelParser = require('@babel/eslint-parser') // Import the parser object

module.exports = defineConfig([
  globalIgnores([
    'assets/vendor',
    'assets/catblocks',
    'assets/Legacy',
    'vendor',
    'public',
    'node_modules',
  ]),
  {
    files: ['**/*.js'],
    languageOptions: {
      globals: { ...globals.browser, ...globals.node },
      parser: babelParser, // Use the imported parser object
      parserOptions: {
        ecmaVersion: 'latest',
        sourceType: 'module', // Adjust if your files are not all ES modules
        babelOptions: {
          presets: ['@babel/preset-env'], // Optional: If you use Babel presets
        },
      },
    },
  },
  js.configs.recommended,
  prettierPlugin,
  importPlugin.flatConfigs.recommended,
  // pluginPromise.configs['flat/recommended'], // ToDo: Fix & enable (prio low)
])
