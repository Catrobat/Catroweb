const { test, expect } = require('@playwright/test')

const { getAppURL, seedDataset } = require('../support/catrobatTestEnv')
const { setCookie } = require('../support/appHelpers')

test.describe('web/general cookie consent', () => {
  test.beforeAll(() => {
    seedDataset('minimal')
  })

  test('shows the banner on the first visit', async ({ page }) => {
    await page.goto('/')

    await expect(page.locator('.cookie-consent-banner')).toBeVisible()
    await expect(page.locator('.cookie-consent-accept')).toBeVisible()
    await expect(page.locator('.cookie-consent-decline')).toBeVisible()
  })

  test('accepting cookies hides the banner and stores consent', async ({ page }) => {
    await page.goto('/')

    await page.locator('.cookie-consent-accept').click()
    await expect(page.locator('.cookie-consent-banner')).toHaveCount(0)

    await expect
      .poll(async () => {
        const cookies = await page.context().cookies()
        return cookies.find((cookie) => cookie.name === 'cookie_consent')?.value
      })
      .toBe('accepted')
  })

  test('declining cookies hides the banner and stores consent', async ({ page }) => {
    await page.goto('/')

    await page.locator('.cookie-consent-decline').click()
    await expect(page.locator('.cookie-consent-banner')).toHaveCount(0)

    await expect
      .poll(async () => {
        const cookies = await page.context().cookies()
        return cookies.find((cookie) => cookie.name === 'cookie_consent')?.value
      })
      .toBe('declined')
  })

  test('does not show the banner again after acceptance', async ({ page }) => {
    await setCookie(page, 'cookie_consent', 'accepted')
    await page.goto('/')

    await expect(page.locator('.cookie-consent-banner')).toHaveCount(0)
  })

  test('does not show the banner again after declining', async ({ page }) => {
    await setCookie(page, 'cookie_consent', 'declined')
    await page.goto('/')

    await expect(page.locator('.cookie-consent-banner')).toHaveCount(0)
  })

  test('reopens the banner from the cookie settings link', async ({ page }) => {
    await setCookie(page, 'cookie_consent', 'accepted')
    await page.goto('/')
    await expect(page.locator('.cookie-consent-banner')).toHaveCount(0)

    await page.locator('.js-cookie-settings').click()
    await expect(page.locator('.cookie-consent-banner')).toBeVisible()

    await expect
      .poll(async () => {
        const cookies = await page.context().cookies(getAppURL('/'))
        return cookies.find((cookie) => cookie.name === 'cookie_consent')
      })
      .toBeUndefined()
  })
})
