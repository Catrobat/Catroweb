const { test, expect } = require('@playwright/test')

const { seedDataset } = require('../support/catrobatTestEnv')
const {
  acceptCookies,
  expectAnyVisibleElementToContainText,
  expectNoVisibleElementToContainText,
  setLanguageCookie,
} = require('../support/appHelpers')

test.describe('web/general language switching', () => {
  test.beforeAll(() => {
    seedDataset('language-switcher')
  })

  test('shows English content by default and can switch to German', async ({ page }) => {
    await acceptCookies(page)
    await page.goto('/')

    await expect
      .poll(async () => {
        const cookies = await page.context().cookies()
        return cookies.find((cookie) => cookie.name === 'hl')?.value || 'en'
      })
      .toBe('en')

    await expect(page.getByText('featured')).toBeVisible()
    await expect(page.getByText('Empfohlen')).toHaveCount(0)

    await setLanguageCookie(page, 'de_DE')
    await page.reload()

    await expect
      .poll(async () => {
        const cookies = await page.context().cookies()
        return cookies.find((cookie) => cookie.name === 'hl')?.value
      })
      .toBe('de_DE')

    await expect(page.getByText('Empfohlen')).toBeVisible()
    await expect(page.getByText('featured')).toHaveCount(0)
  })

  test('renders translated homepage sections after switching languages', async ({ page }) => {
    await acceptCookies(page)
    await setLanguageCookie(page, 'en')
    await page.goto('/')

    const sectionTitles = page.locator('.project-list__title')
    await expectAnyVisibleElementToContainText(sectionTitles, 'Most downloaded')
    await expect(page.locator('#home-projects__most_downloaded')).toBeVisible()

    await setLanguageCookie(page, 'ru_RU')
    await page.reload()
    await expectAnyVisibleElementToContainText(sectionTitles, 'Самые скачиваемые')
    await expect(page.locator('#home-projects__most_downloaded')).toBeVisible()

    await setLanguageCookie(page, 'fr_FR')
    await page.reload()
    await expectAnyVisibleElementToContainText(sectionTitles, 'Les plus téléchargés')
    await expect(page.locator('#home-projects__most_downloaded')).toBeVisible()

    await setLanguageCookie(page, 'de_DE')
    await page.reload()
    await expectAnyVisibleElementToContainText(sectionTitles, 'heruntergeladen')
    await expectNoVisibleElementToContainText(sectionTitles, 'Most downloaded')
    await expect(page.locator('#home-projects__most_downloaded')).toBeVisible()
  })

  test('shows translated project details after switching to Russian', async ({ page }) => {
    await acceptCookies(page)
    await setLanguageCookie(page, 'en')
    await page.goto('/app/project/9601')

    const downloadButton = page.locator('#projectDownloadButton')
    await expect(downloadButton).toContainText('Download')

    await setLanguageCookie(page, 'ru_RU')
    await page.reload()

    await expect(downloadButton).toContainText('Скачать')
  })
})
