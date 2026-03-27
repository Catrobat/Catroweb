const { expect } = require('@playwright/test')

const { getAppURL } = require('./catrobatTestEnv')

async function setCookie(page, name, value) {
  await page.context().addCookies([
    {
      url: getAppURL('/'),
      name,
      value,
    },
  ])
}

async function acceptCookies(page) {
  await setCookie(page, 'cookie_consent', 'accepted')
}

async function loginAs(page, username = 'PlaywrightCatrobat', password = '123456') {
  await acceptCookies(page)
  await page.goto('/app/login')
  await expect(page).toHaveURL(/\/app\/login$/)
  await page.getByLabel(/^Username/).fill(username)
  await page.getByLabel(/^Password/).fill(password)
  await page.getByRole('button', { name: /Login/ }).click()
}

async function setLanguageCookie(page, languageCode) {
  await setCookie(page, 'hl', languageCode)
}

async function expectAnyVisibleElementToContainText(locator, expectedText) {
  await expect
    .poll(async () => {
      const texts = await locator.evaluateAll((elements) =>
        elements
          .filter(
            (element) =>
              !(element instanceof HTMLElement) ||
              window.getComputedStyle(element).visibility !== 'hidden',
          )
          .map((element) => element.textContent || ''),
      )

      return texts.some((text) => text.includes(expectedText))
    })
    .toBe(true)
}

async function expectNoVisibleElementToContainText(locator, unwantedText) {
  await expect
    .poll(async () => {
      const texts = await locator.evaluateAll((elements) =>
        elements
          .filter(
            (element) =>
              !(element instanceof HTMLElement) ||
              window.getComputedStyle(element).visibility !== 'hidden',
          )
          .map((element) => element.textContent || ''),
      )

      return texts.every((text) => !text.includes(unwantedText))
    })
    .toBe(true)
}

async function openSidebar(page) {
  await page.locator('#top-app-bar__btn-sidebar-toggle').click({ force: true })
  await expect(page.locator('#sidebar')).toHaveClass(/\bactive\b/)
  await expect(page.locator('#sidebar-overlay')).toBeVisible()
}

module.exports = {
  acceptCookies,
  expectAnyVisibleElementToContainText,
  expectNoVisibleElementToContainText,
  loginAs,
  openSidebar,
  setCookie,
  setLanguageCookie,
}
