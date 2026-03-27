const fs = require('node:fs/promises')
const path = require('node:path')

const {
  Before,
  BeforeAll,
  After,
  AfterAll,
  Status,
  setDefaultTimeout,
} = require('@cucumber/cucumber')
const { chromium } = require('@playwright/test')

const {
  getBaseURL,
  preparePlaywrightEnvironment,
  seedDataset,
  waitForApp,
} = require('../../Playwright/support/catrobatTestEnv')
const { getDatasetFromTags } = require('../../Gherkin/support/webGeneralCatalog')

const SCREENSHOT_DIR = path.resolve(
  process.cwd(),
  'tests/TestReports/CucumberPlaywright/screenshots',
)

let browser

setDefaultTimeout(60 * 1000)

BeforeAll(async function () {
  await waitForApp(getBaseURL())
  preparePlaywrightEnvironment()
  browser = await chromium.launch({ headless: true })
})

Before(async function ({ pickle }) {
  const dataset = getDatasetFromTags(pickle.tags.map((tag) => tag.name))
  if (dataset) {
    seedDataset(dataset)
  }

  this.context = await browser.newContext({
    baseURL: getBaseURL(),
    viewport: {
      width: 412,
      height: 823,
    },
  })
  this.page = await this.context.newPage()
  this.lastResponse = null
})

After(async function ({ pickle, result }) {
  if (result?.status === Status.FAILED && this.page) {
    await fs.mkdir(SCREENSHOT_DIR, { recursive: true })
    const screenshotName = pickle.name
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-|-$/g, '')
    const screenshotPath = path.join(SCREENSHOT_DIR, `${screenshotName}.png`)
    await this.page.screenshot({ path: screenshotPath, fullPage: true })
  }

  await this.context?.close()
  this.page = null
  this.context = null
  this.lastResponse = null
})

AfterAll(async function () {
  await browser?.close()
})
