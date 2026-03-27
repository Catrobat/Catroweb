const assert = require('node:assert/strict')

const { Given, When, Then } = require('@wdio/cucumber-framework')
const { browser, $ } = require('@wdio/globals')

const { getBaseURL } = require('../../Playwright/support/catrobatTestEnv')
const {
  getLanguageCookie,
  getPagePath,
  getSectionSelector,
  getTableTexts,
} = require('../../Gherkin/support/webGeneralCatalog')

async function setCookie(name, value) {
  const currentUrl = await browser.getUrl()
  if (!currentUrl || currentUrl === 'about:blank' || currentUrl === 'data:,') {
    await browser.url('/')
  }

  await browser.setCookies([{ name, value, path: '/' }])
}

async function getCookie(name) {
  const cookies = await browser.getCookies()
  const browserCookie = cookies.find((cookie) => cookie.name === name)
  if (browserCookie) {
    return browserCookie
  }

  const documentCookie = await browser.execute((cookieName) => {
    const cookieEntry = document.cookie
      .split('; ')
      .find((entry) => entry.startsWith(`${cookieName}=`))

    if (!cookieEntry) {
      return null
    }

    const [resolvedName, value] = cookieEntry.split('=')
    return {
      name: resolvedName,
      value,
    }
  }, name)

  return documentCookie || undefined
}

async function clickElement(selector) {
  await browser.waitUntil(async () => await $(selector).isExisting())
  await browser.execute((resolvedSelector) => {
    document.querySelector(resolvedSelector)?.click()
  }, selector)
}

async function getTextContent(selector) {
  return browser.execute((resolvedSelector) => {
    return document.querySelector(resolvedSelector)?.textContent || ''
  }, selector)
}

async function anyVisibleElementContainsText(text) {
  return browser.execute((needle) => {
    const normalizedNeedle = needle.toLowerCase()
    return (document.body?.innerText || document.body?.textContent || '')
      .toLowerCase()
      .includes(normalizedNeedle)
  }, text)
}

async function containerContainsText(selector, text) {
  return browser.execute(
    (resolvedSelector, needle) => {
      const normalizedNeedle = needle.toLowerCase()
      const content =
        document.querySelector(resolvedSelector)?.innerText ||
        document.querySelector(resolvedSelector)?.textContent ||
        ''

      return content.toLowerCase().includes(normalizedNeedle)
    },
    selector,
    text,
  )
}

async function anyFooterElementContainsText(text) {
  return browser.execute((needle) => {
    const normalizedNeedle = needle.toLowerCase()

    return Array.from(
      document.querySelectorAll('footer, .footer-download, [role="contentinfo"]'),
    ).some((element) =>
      (element.innerText || element.textContent || '').toLowerCase().includes(normalizedNeedle),
    )
  }, text)
}

async function acceptCookies() {
  await setCookie('cookie_consent', 'accepted')
}

async function loginAs(username, password) {
  await acceptCookies()
  await browser.url('/app/login')
  await $('#username__input').setValue(username)
  await $('#password__input').setValue(password)
  await $('button=Login now').click()
  await browser.waitUntil(async () => new URL(await browser.getUrl()).pathname === '/app/')
}

async function getSidebarState() {
  return browser.execute(() => {
    const sidebar = document.querySelector('#sidebar')
    const overlay = document.querySelector('#sidebar-overlay')

    return {
      className: sidebar?.className ?? '',
      overlayDisplay: overlay ? window.getComputedStyle(overlay).display : 'none',
    }
  })
}

Given('I have accepted cookies', async function () {
  await acceptCookies()
})

Given('cookie consent is {string}', async function (value) {
  await setCookie('cookie_consent', value)
})

Given('the selected language is {string}', async function (languageName) {
  await setCookie('hl', getLanguageCookie(languageName))
})

When('I open the {string} page', async function (pageName) {
  await browser.url(getPagePath(pageName))
})

When('I open the path {string}', async function (path) {
  await browser.url(path)
})

When('I reload the page', async function () {
  await browser.refresh()
})

When('I accept cookies', async function () {
  await clickElement('.cookie-consent-accept')
})

When('I decline cookies', async function () {
  await clickElement('.cookie-consent-decline')
})

When('I open cookie settings', async function () {
  await clickElement('.js-cookie-settings')
})

When('I switch the language to {string}', async function (languageName) {
  await setCookie('hl', getLanguageCookie(languageName))
  await browser.url(await browser.getUrl())
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
  await loginAs(username, password)
})

When('I open the sidebar', async function () {
  await browser.execute(() => {
    document.querySelector('#top-app-bar__btn-sidebar-toggle')?.click()
  })

  await browser.waitUntil(async () => (await getSidebarState()).className.includes('active'))
})

When('I toggle the sidebar', async function () {
  await browser.execute(() => {
    document.querySelector('#top-app-bar__btn-sidebar-toggle')?.click()
  })
})

When('I click the login link from the sidebar', async function () {
  await browser.execute(() => {
    document.querySelector('#btn-login')?.click()
  })
})

When('I navigate back in the browser', async function () {
  await browser.back()
})

Then('the app version marker should contain {string}', async function (expectedText) {
  await browser.waitUntil(async () => (await getTextContent('#app-version')).includes(expectedText))
})

Then('the app version marker should not be visible', async function () {
  assert.equal(await $('#app-version').isDisplayed(), false)
})

Then('the cookie banner should be visible', async function () {
  assert.equal(await $('.cookie-consent-banner').isDisplayed(), true)
  assert.equal(await $('.cookie-consent-accept').isDisplayed(), true)
  assert.equal(await $('.cookie-consent-decline').isDisplayed(), true)
})

Then('the cookie banner should not be visible', async function () {
  await browser.waitUntil(async () => {
    const banner = await $('.cookie-consent-banner')
    return !(await banner.isExisting()) || !(await banner.isDisplayed())
  })
})

Then('the {string} cookie should be {string}', async function (cookieName, expectedValue) {
  await browser.waitUntil(async () => (await getCookie(cookieName))?.value === expectedValue)
})

Then('the {string} cookie should be cleared', async function (cookieName) {
  await browser.waitUntil(async () => !((await getCookie(cookieName)) || false))
})

Then('the response should redirect to {string}', async function (location) {
  assert.ok(this.lastResponse, 'Expected an HTTP response')
  assert.equal(this.lastResponse.status, 302)
  assert.equal(this.lastResponse.headers.get('location'), location)
})

Then('the {string} section should contain:', async function (sectionName, dataTable) {
  for (const text of getTableTexts(dataTable)) {
    await browser.waitUntil(async () =>
      containerContainsText(getSectionSelector(sectionName), text),
    )
  }
})

Then('the {string} section should not contain:', async function (sectionName, dataTable) {
  for (const text of getTableTexts(dataTable)) {
    await browser.waitUntil(
      async () => !(await containerContainsText(getSectionSelector(sectionName), text)),
    )
  }
})

Then('the homepage section titles should contain:', async function (dataTable) {
  for (const text of getTableTexts(dataTable)) {
    await browser.waitUntil(async () => {
      const values = await browser.execute(() =>
        Array.from(document.querySelectorAll('.project-list__title')).map(
          (element) => element.textContent || '',
        ),
      )

      return values.some((value) => value.includes(text))
    })
  }
})

Then('the welcome section should show the video {string}', async function (videoUrl) {
  assert.equal(await $('#welcome-section').isDisplayed(), true)
  const actualVideoUrl = await $('.video-container > iframe').getAttribute('src')
  assert.ok(actualVideoUrl.includes(videoUrl))
})

Then('the welcome section should show:', async function (dataTable) {
  for (const text of getTableTexts(dataTable)) {
    await browser.waitUntil(async () => containerContainsText('#welcome-section', text))
  }
})

Then('the welcome section should not show:', async function (dataTable) {
  for (const text of getTableTexts(dataTable)) {
    await browser.waitUntil(async () => !(await containerContainsText('#welcome-section', text)))
  }
})

Then('the welcome section should not exist', async function () {
  assert.equal(await $('#welcome-section').isExisting(), false)
})

Then('the featured slider links should be:', async function (dataTable) {
  const expectedLinks = getTableTexts(dataTable)
  const hrefs = await browser.execute(() =>
    Array.from(document.querySelectorAll('.carousel-item')).map(
      (element) => element.getAttribute('href') || '',
    ),
  )

  assert.equal(hrefs.length, expectedLinks.length)

  expectedLinks.forEach((expectedLink, index) => {
    if (expectedLink.startsWith('http')) {
      assert.equal(hrefs[index], expectedLink)
      return
    }

    assert.ok(hrefs[index].includes(expectedLink))
  })
})

Then('the footer should show:', async function (dataTable) {
  for (const text of getTableTexts(dataTable)) {
    await browser.waitUntil(async () => anyFooterElementContainsText(text))
  }
})

Then('the selected language should be {string}', async function (languageName) {
  const expectedCookie = getLanguageCookie(languageName)
  await browser.waitUntil(async () => ((await getCookie('hl'))?.value || 'en') === expectedCookie)
})

Then('the current page should show {string}', async function (text) {
  await browser.waitUntil(async () => anyVisibleElementContainsText(text))
})

Then('the current page should not show {string}', async function (text) {
  await browser.waitUntil(async () => !(await anyVisibleElementContainsText(text)))
})

Then('the {string} section should be visible', async function (sectionName) {
  assert.equal(await $(getSectionSelector(sectionName)).isDisplayed(), true)
})

Then('the project download button should say {string}', async function (text) {
  await browser.waitUntil(async () => containerContainsText('#projectDownloadButton', text))
})

Then('the current URL should end with {string}', async function (pathSuffix) {
  await browser.waitUntil(async () => new URL(await browser.getUrl()).pathname === pathSuffix)
})

Then('the sidebar should be open', async function () {
  await browser.waitUntil(async () => {
    const state = await getSidebarState()
    return state.className.includes('active') && state.overlayDisplay !== 'none'
  })
})

Then('the sidebar should be closed', async function () {
  await browser.waitUntil(async () => {
    const state = await getSidebarState()
    return !state.className.includes('active') && state.overlayDisplay === 'none'
  })
})

Then('the page is settled', async function () {
  await browser.waitUntil(
    async () => (await browser.execute(() => document.readyState)) === 'complete',
  )
})
