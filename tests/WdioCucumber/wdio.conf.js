const path = require('node:path')

const {
  getBaseURL,
  preparePlaywrightEnvironment,
  waitForApp,
} = require('../Playwright/support/catrobatTestEnv')

exports.config = {
  runner: 'local',
  specs: [path.resolve(__dirname, '../Gherkin/web-general/**/*.feature')],
  maxInstances: 1,
  maxInstancesPerCapability: 1,
  logLevel: 'warn',
  bail: 0,
  baseUrl: getBaseURL(),
  waitforTimeout: 10000,
  connectionRetryTimeout: 120000,
  connectionRetryCount: 1,
  framework: 'cucumber',
  reporters: [
    'spec',
    [
      'junit',
      {
        outputDir: path.resolve(process.cwd(), 'tests/TestReports/WdioCucumber/junit'),
      },
    ],
  ],
  cucumberOpts: {
    require: [path.resolve(__dirname, 'steps/**/*.js')],
    timeout: 60000,
    failFast: false,
  },
  capabilities: [
    {
      browserName: 'chrome',
      browserVersion: 'stable',
      'wdio:maxInstances': 1,
      'goog:chromeOptions': {
        args: ['--headless=new', '--disable-gpu', '--no-sandbox', '--window-size=412,823'],
      },
    },
  ],
  onPrepare: async function () {
    if (process.env.WDIO_SKIP_PREPARE === '1') {
      return
    }

    await waitForApp(getBaseURL())
    preparePlaywrightEnvironment()
  },
}
