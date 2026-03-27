const { test, expect } = require('@playwright/test')

const { seedDataset } = require('../support/catrobatTestEnv')
const {
  acceptCookies,
  expectAnyVisibleElementToContainText,
  loginAs,
} = require('../support/appHelpers')

test.describe('web/general homepage', () => {
  test.beforeAll(() => {
    seedDataset('homepage')
  })

  test('shows scratch remixes without unrelated projects', async ({ page }) => {
    await acceptCookies(page)
    await page.goto('/')

    const scratchSection = page.locator('#home-projects__scratch')
    await expect(page.locator('#feature-slider')).toBeVisible()
    await expect(scratchSection).toContainText('project 6')
    await expect(scratchSection).toContainText('project 7')
    await expect(scratchSection).not.toContainText('project 1')
  })

  test('shows the expected homepage sections', async ({ page }) => {
    await acceptCookies(page)
    await page.goto('/')

    await expect(page.locator('#feature-slider')).toBeVisible()

    const sectionTitles = page.locator('.project-list__title')
    await expectAnyVisibleElementToContainText(sectionTitles, 'Examples')
    await expectAnyVisibleElementToContainText(sectionTitles, 'Most downloaded')
    await expectAnyVisibleElementToContainText(sectionTitles, 'Scratch remixes')
    await expectAnyVisibleElementToContainText(sectionTitles, 'Random projects')
    await expectAnyVisibleElementToContainText(sectionTitles, 'Popular projects')
  })

  test('shows the default welcome section', async ({ page }) => {
    await acceptCookies(page)
    await page.goto('/')

    await expect(page.locator('#welcome-section')).toBeVisible()
    await expect(page.locator('.video-container > iframe')).toHaveAttribute(
      'src',
      /https:\/\/www\.youtube\.com\/embed\/BHe2r2WU-T8/,
    )
    await expect(page.getByText('Google Play Store')).toBeVisible()
    await expect(page.getByText('iOS App Store')).toBeVisible()
    await expect(page.getByText('Huawei AppGallery')).toBeVisible()
  })

  test('shows the luna welcome section', async ({ page }) => {
    await acceptCookies(page)
    await page.goto('/luna')

    await expect(page.locator('#welcome-section')).toBeVisible()
    await expect(page.locator('.video-container > iframe')).toHaveAttribute(
      'src',
      /https:\/\/www\.youtube\.com\/embed\/-6AEZrSbOMg/,
    )
    await expect(page.getByText('Google Play Store')).toBeVisible()
    await expect(page.getByText('Discord Chat')).toBeVisible()
    await expect(page.getByText('iOS App Store')).toHaveCount(0)
    await expect(page.getByText('Huawei AppGallery')).toHaveCount(0)
  })

  test('shows the embroidery welcome section', async ({ page }) => {
    await acceptCookies(page)
    await page.goto('/embroidery')

    await expect(page.locator('#welcome-section')).toBeVisible()
    await expect(page.locator('.video-container > iframe')).toHaveAttribute(
      'src',
      /https:\/\/www\.youtube\.com\/embed\/IjHI0UZzuWM/,
    )
    await expect(page.getByText('Google Play Store')).toBeVisible()
    await expect(page.getByText('Instagram')).toBeVisible()
    await expect(page.getByText('iOS App Store')).toHaveCount(0)
    await expect(page.getByText('Huawei AppGallery')).toHaveCount(0)
  })

  test('shows the mindstorms welcome section', async ({ page }) => {
    await acceptCookies(page)
    await page.goto('/mindstorms')

    await expect(page.locator('#welcome-section')).toBeVisible()
    await expect(page.locator('.video-container > iframe')).toHaveAttribute(
      'src',
      /https:\/\/www\.youtube\.com\/embed\/YnSl-fSV-nY/,
    )
    await expect(page.getByText('Google Play Store')).toBeVisible()
    await expect(page.getByText('iOS App Store')).toHaveCount(0)
    await expect(page.getByText('Huawei AppGallery')).toHaveCount(0)
  })

  test('hides the welcome section for logged-in users', async ({ page }) => {
    await loginAs(page)
    await expect(page).toHaveURL(/\/app\/$/)
    await expect(page.locator('#welcome-section')).toHaveCount(0)
  })

  test('shows featured programs and external links in slider order', async ({ page }) => {
    await acceptCookies(page)
    await page.goto('/')

    const hrefs = await page
      .locator('.carousel-item')
      .evaluateAll((elements) => elements.map((element) => element.getAttribute('href') || ''))

    expect(hrefs).toHaveLength(3)
    expect(hrefs[0]).toBe('http://www.google.at/')
    expect(hrefs[1]).toContain('/project/9402')
    expect(hrefs[2]).toContain('/project/9403')
  })

  test('shows legally required footer links', async ({ page }) => {
    await acceptCookies(page)
    await page.goto('/')

    await expect(page.getByText('About Catrobat')).toBeVisible()
    await expect(page.getByText('License to play')).toBeVisible()
    await expect(page.getByText('Privacy policy')).toBeVisible()
    await expect(page.getByText('Terms of Use')).toBeVisible()
  })
})
