const { test, expect } = require('@playwright/test')

const { seedDataset } = require('../support/catrobatTestEnv')
const { acceptCookies } = require('../support/appHelpers')

test.describe('web/general app version', () => {
  test.beforeAll(() => {
    seedDataset('minimal')
  })

  const pages = [
    { label: 'homepage', path: '/' },
    { label: 'login', path: '/app/login' },
    { label: 'register', path: '/app/register' },
    { label: 'project details', path: '/app/project/9002' },
    { label: 'profile', path: '/app/user/9001' },
    { label: 'luna landing page', path: '/luna' },
  ]

  for (const pageDefinition of pages) {
    test(`shows the version marker on ${pageDefinition.label}`, async ({ page }) => {
      await acceptCookies(page)
      await page.goto(pageDefinition.path)

      const versionMarker = page.locator('#app-version')
      await expect(versionMarker).toContainText('TEST_VERSION')
    })
  }

  test('keeps the version marker invisible to users', async ({ page }) => {
    await acceptCookies(page)
    await page.goto('/')

    const versionMarker = page.locator('#app-version')
    await expect(versionMarker).toContainText('TEST_VERSION')
    await expect(versionMarker).not.toBeVisible()
  })
})
