const assert = require('node:assert/strict')

const { Given, When, Then } = require('@cucumber/cucumber')
const { expect } = require('@playwright/test')

const {
  acceptCookies,
  expectAnyVisibleElementToContainText,
  loginAs,
  openSidebar,
  setCookie,
  setLanguageCookie,
} = require('../../Playwright/support/appHelpers')
const { getBaseURL, getAppURL } = require('../../Playwright/support/catrobatTestEnv')
const {
  getLanguageCookie,
  getPagePath,
  getSectionSelector,
  getTableTexts,
} = require('../../Gherkin/support/webGeneralCatalog')

async function getCookie(page, cookieName) {
  const cookies = await page.context().cookies(getAppURL('/'))
  return cookies.find((cookie) => cookie.name === cookieName)
}

async function expectVisibleText(page, text) {
  await expect(page.getByText(text, { exact: false }).first()).toBeVisible()
}

async function expectMissingText(page, text) {
  await expect(page.getByText(text, { exact: false })).toHaveCount(0)
}

Given('I have accepted cookies', async function () {
  await acceptCookies(this.page)
})

Given('cookie consent is {string}', async function (value) {
  await setCookie(this.page, 'cookie_consent', value)
})

Given('the selected language is {string}', async function (languageName) {
  await setLanguageCookie(this.page, getLanguageCookie(languageName))
})

When('I open the {string} page', async function (pageName) {
  await this.page.goto(getPagePath(pageName))
})

When('I open the path {string}', async function (path) {
  await this.page.goto(path)
})

When('I reload the page', async function () {
  await this.page.reload()
})

When('I accept cookies', async function () {
  await this.page.locator('.cookie-consent-accept').click()
})

When('I decline cookies', async function () {
  await this.page.locator('.cookie-consent-decline').click()
})

When('I open cookie settings', async function () {
  await this.page.locator('.js-cookie-settings').click()
})

When('I switch the language to {string}', async function (languageName) {
  await setLanguageCookie(this.page, getLanguageCookie(languageName))
  await this.page.reload()
})

When('I request the help page with language {string}', async function (languageName) {
  this.lastResponse = await fetch(new URL('/app/help', getBaseURL()), {
    headers: {
      Cookie: `hl=${getLanguageCookie(languageName)}`,
    },
    redirect: 'manual',
  })
})

When('I log in as {string} with password {string}', async function (username, password) {
  await loginAs(this.page, username, password)
})

When('I open the sidebar', async function () {
  await openSidebar(this.page)
})

When('I toggle the sidebar', async function () {
  await this.page.locator('#top-app-bar__btn-sidebar-toggle').click({ force: true })
})

When('I click the login link from the sidebar', async function () {
  await this.page.locator('#btn-login').click()
})

When('I navigate back in the browser', async function () {
  await this.page.goBack()
})

Then('the app version marker should contain {string}', async function (expectedText) {
  await expect(this.page.locator('#app-version')).toContainText(expectedText)
})

Then('the app version marker should not be visible', async function () {
  await expect(this.page.locator('#app-version')).not.toBeVisible()
})

Then('the cookie banner should be visible', async function () {
  await expect(this.page.locator('.cookie-consent-banner')).toBeVisible()
  await expect(this.page.locator('.cookie-consent-accept')).toBeVisible()
  await expect(this.page.locator('.cookie-consent-decline')).toBeVisible()
})

Then('the cookie banner should not be visible', async function () {
  await expect(this.page.locator('.cookie-consent-banner')).toHaveCount(0)
})

Then('the {string} cookie should be {string}', async function (cookieName, expectedValue) {
  await expect.poll(async () => (await getCookie(this.page, cookieName))?.value).toBe(expectedValue)
})

Then('the {string} cookie should be cleared', async function (cookieName) {
  await expect.poll(async () => getCookie(this.page, cookieName)).toBeUndefined()
})

Then('the response should redirect to {string}', async function (location) {
  assert.ok(this.lastResponse, 'Expected an HTTP response')
  assert.equal(this.lastResponse.status, 302)
  assert.equal(this.lastResponse.headers.get('location'), location)
})

Then('the {string} section should contain:', async function (sectionName, dataTable) {
  const section = this.page.locator(getSectionSelector(sectionName))

  for (const text of getTableTexts(dataTable)) {
    await expect(section).toContainText(text)
  }
})

Then('the {string} section should not contain:', async function (sectionName, dataTable) {
  const section = this.page.locator(getSectionSelector(sectionName))

  for (const text of getTableTexts(dataTable)) {
    await expect(section).not.toContainText(text)
  }
})

Then('the homepage section titles should contain:', async function (dataTable) {
  const sectionTitles = this.page.locator('.project-list__title')

  for (const text of getTableTexts(dataTable)) {
    await expectAnyVisibleElementToContainText(sectionTitles, text)
  }
})

Then('the welcome section should show the video {string}', async function (videoUrl) {
  await expect(this.page.locator('#welcome-section')).toBeVisible()
  await expect(this.page.locator('.video-container > iframe')).toHaveAttribute(
    'src',
    new RegExp(videoUrl),
  )
})

Then('the welcome section should show:', async function (dataTable) {
  for (const text of getTableTexts(dataTable)) {
    await expectVisibleText(this.page, text)
  }
})

Then('the welcome section should not show:', async function (dataTable) {
  for (const text of getTableTexts(dataTable)) {
    await expectMissingText(this.page, text)
  }
})

Then('the welcome section should not exist', async function () {
  await expect(this.page.locator('#welcome-section')).toHaveCount(0)
})

Then('the featured slider links should be:', async function (dataTable) {
  const expectedLinks = getTableTexts(dataTable)
  const hrefs = await this.page
    .locator('.carousel-item')
    .evaluateAll((elements) => elements.map((element) => element.getAttribute('href') || ''))

  expect(hrefs).toHaveLength(expectedLinks.length)

  expectedLinks.forEach((expectedLink, index) => {
    if (expectedLink.startsWith('http')) {
      expect(hrefs[index]).toBe(expectedLink)
      return
    }

    expect(hrefs[index]).toContain(expectedLink)
  })
})

Then('the footer should show:', async function (dataTable) {
  for (const text of getTableTexts(dataTable)) {
    await expectVisibleText(this.page, text)
  }
})

Then('the selected language should be {string}', async function (languageName) {
  const expectedCookie = getLanguageCookie(languageName)

  await expect
    .poll(async () => (await getCookie(this.page, 'hl'))?.value || 'en')
    .toBe(expectedCookie)
})

Then('the current page should show {string}', async function (text) {
  await expectVisibleText(this.page, text)
})

Then('the current page should not show {string}', async function (text) {
  await expectMissingText(this.page, text)
})

Then('the {string} section should be visible', async function (sectionName) {
  await expect(this.page.locator(getSectionSelector(sectionName))).toBeVisible()
})

Then('the project download button should say {string}', async function (text) {
  await expect(this.page.locator('#projectDownloadButton')).toContainText(text)
})

Then('the current URL should end with {string}', async function (pathSuffix) {
  await expect.poll(async () => new URL(this.page.url()).pathname).toBe(pathSuffix)
})

Then('the sidebar should be open', async function () {
  await expect(this.page.locator('#sidebar')).toHaveClass(/\bactive\b/)
  await expect(this.page.locator('#sidebar-overlay')).toBeVisible()
})

Then('the sidebar should be closed', async function () {
  await expect(this.page.locator('#sidebar')).not.toHaveClass(/\bactive\b/)
  await expect(this.page.locator('#sidebar-overlay')).toBeHidden()
})

Then('the page is settled', async function () {
  await this.page.waitForLoadState('networkidle')
})
