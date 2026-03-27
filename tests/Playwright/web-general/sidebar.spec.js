const { test, expect } = require('@playwright/test')

const { seedDataset } = require('../support/catrobatTestEnv')
const { acceptCookies, openSidebar } = require('../support/appHelpers')

test.describe('web/general sidebar', () => {
  test.beforeAll(() => {
    seedDataset('minimal')
  })

  test('opens and closes the sidebar from the toggle button', async ({ page }) => {
    await acceptCookies(page)
    await page.goto('/')

    await expect(page.locator('#sidebar')).not.toHaveClass(/\bactive\b/)
    await expect(page.locator('#sidebar-overlay')).toBeHidden()

    await page.locator('#top-app-bar__btn-sidebar-toggle').click({ force: true })
    await expect(page).toHaveURL(/\/app\/$/)
    await expect(page.locator('#sidebar')).toHaveClass(/\bactive\b/)
    await expect(page.locator('#sidebar-overlay')).toBeVisible()

    await page.locator('#top-app-bar__btn-sidebar-toggle').click({ force: true })
    await expect(page).toHaveURL(/\/app\/$/)
    await expect(page.locator('#sidebar')).not.toHaveClass(/\bactive\b/)
    await expect(page.locator('#sidebar-overlay')).toBeHidden()
  })

  test('closes the sidebar when navigating back', async ({ page }) => {
    await acceptCookies(page)
    await page.goto('/')

    await openSidebar(page)
    await page.locator('#btn-login').click()
    await expect(page).toHaveURL(/\/app\/login$/)
    await page.waitForLoadState('networkidle')

    await page.locator('#top-app-bar__btn-sidebar-toggle').click({ force: true })
    await expect(page.locator('#sidebar')).toHaveClass(/\bactive\b/)
    await expect(page.locator('#sidebar-overlay')).toBeVisible()

    await page.goBack()
    await expect(page).toHaveURL(/\/app\/login$/)
    await expect(page.locator('#sidebar')).not.toHaveClass(/\bactive\b/)
    await expect(page.locator('#sidebar-overlay')).toBeHidden()
  })
})
