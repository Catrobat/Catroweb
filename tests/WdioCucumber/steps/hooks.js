const fs = require('node:fs/promises')
const path = require('node:path')

const { Before, After } = require('@wdio/cucumber-framework')
const { browser } = require('@wdio/globals')

const { seedDataset } = require('../../Playwright/support/catrobatTestEnv')
const { getDatasetFromTags } = require('../../Gherkin/support/webGeneralCatalog')

const SCREENSHOT_DIR = path.resolve(process.cwd(), 'tests/TestReports/WdioCucumber/screenshots')
let seededDataset = null

Before(async function ({ pickle }) {
  const dataset = getDatasetFromTags(pickle.tags.map((tag) => tag.name))
  if (dataset && dataset !== seededDataset) {
    seedDataset(dataset)
    seededDataset = dataset
  }

  await browser.url('/')
  await browser.deleteCookies()
  await browser.execute(() => {
    document.cookie = 'cookie_consent=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/'
    document.cookie = 'hl=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/'
    window.localStorage.clear()
    window.sessionStorage.clear()
  })
})

After(async function ({ pickle, result }) {
  if (result?.status !== 'FAILED') {
    return
  }

  await fs.mkdir(SCREENSHOT_DIR, { recursive: true })
  const screenshotName = pickle.name
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-|-$/g, '')
  await browser.saveScreenshot(path.join(SCREENSHOT_DIR, `${screenshotName}.png`))
})
