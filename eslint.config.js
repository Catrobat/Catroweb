const { defineConfig, globalIgnores } = require('eslint/config')
const globals = require('globals')
const js = require('@eslint/js')
const prettierPlugin = require('eslint-plugin-prettier/recommended')
const importPlugin = require('eslint-plugin-import-x')
// const pluginPromise = require('eslint-plugin-promise')

module.exports = defineConfig([
  globalIgnores(['assets/vendor', 'assets/Legacy', 'vendor', 'public', 'node_modules']),
  {
    files: ['**/*.js'],
    languageOptions: {
      globals: { ...globals.browser, ...globals.node },
      parserOptions: {
        ecmaVersion: 'latest',
        sourceType: 'module',
      },
    },
  },
  js.configs.recommended,
  prettierPlugin,
  importPlugin.flatConfigs.recommended,
  // pluginPromise.configs['flat/recommended'], // ToDo: Fix & enable (prio low)
])
