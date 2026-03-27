const { test, expect } = require('@playwright/test')

const { seedDataset } = require('../support/catrobatTestEnv')
const { acceptCookies } = require('../support/appHelpers')

test.describe('web/general footer statistics', () => {
  test.beforeAll(() => {
    seedDataset('statistics-footer')
  })

  test('shows project and user statistics in the footer', async ({ page }) => {
    await acceptCookies(page)
    await page.goto('/')

    const footer = page.locator('.footer-download')
    await expect(footer).toContainText('10')
    await expect(footer).toContainText('17')
  })
})
