const { execFileSync } = require('node:child_process')
const fs = require('node:fs')
const path = require('node:path')

const {
  getBaseURL,
  preparePlaywrightEnvironment,
  waitForApp,
} = require('../Playwright/support/catrobatTestEnv')

const WORKDIR = process.cwd()
const FEATURE_DIR = path.resolve(__dirname, '../Gherkin/web-general')
const REPORT_DIR = path.resolve(WORKDIR, 'tests/TestReports/WdioCucumber')
const WDIO_BINARY = path.resolve(WORKDIR, 'node_modules/.bin/wdio')
const WDIO_CONFIG = path.resolve(__dirname, 'wdio.conf.js')

async function main() {
  await waitForApp(getBaseURL())
  preparePlaywrightEnvironment()

  fs.rmSync(path.join(REPORT_DIR, 'junit'), { recursive: true, force: true })
  fs.rmSync(path.join(REPORT_DIR, 'screenshots'), { recursive: true, force: true })

  const featureFiles = fs
    .readdirSync(FEATURE_DIR)
    .filter((fileName) => fileName.endsWith('.feature'))
    .sort()

  for (const featureFile of featureFiles) {
    execFileSync(WDIO_BINARY, ['run', WDIO_CONFIG, '--spec', path.join(FEATURE_DIR, featureFile)], {
      cwd: WORKDIR,
      env: {
        ...process.env,
        WDIO_SKIP_PREPARE: '1',
      },
      stdio: 'inherit',
    })
  }
}

main().catch((error) => {
  console.error(error)
  process.exit(1)
})
