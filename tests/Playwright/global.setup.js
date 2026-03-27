const {
  getBaseURL,
  preparePlaywrightEnvironment,
  waitForApp,
} = require('./support/catrobatTestEnv')

async function globalSetup() {
  const baseURL = getBaseURL()

  console.log(`[playwright] Waiting for Catroweb at ${baseURL}`)
  await waitForApp(baseURL)

  console.log('[playwright] Preparing test environment')
  preparePlaywrightEnvironment()
}

module.exports = globalSetup
