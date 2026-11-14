// Lazy load images (performance) --------------------------------------------------------------------------------------
// lazysizes does not need any JS configuration:
// Add the class "lazyload" to your images/iframes in conjunction with a data-src and/or data-srcset attribute.
// Optionally you can also add a src attribute with a low quality image
import 'lazysizes'

// Icons (Google) ------------------------------------------------------------------------------------------------------
// https://fonts.google.com/icons
// no further config is needed - just add the class material-icons to element
// E.g: <i class="material-icons">thumb_up</i>
import 'material-icons/iconfont/material-icons.css'
import '@material/web/progress/circular-progress.js'

import textFillDefault from '../Components/TextFillDefault'
import './TopBar'
import './Sidebar'
import { LogoutTokenHandler } from '../Security/LogoutTokenHandler'
import { initAnalyticsIfConsented, showCookieSettings } from './CookieConsent'
import { showSnackbar } from './Snackbar'

// Start the stimulus app
import '../bootstrap'

const appVersion = document.getElementById('app-version').dataset.appVersion
const bugsnagApiKey = document.getElementById('bugsnag').dataset.apiKey
if (bugsnagApiKey) {
  import('@bugsnag/js').then(({ default: Bugsnag }) => {
    Bugsnag.start({ apiKey: bugsnagApiKey, appVersion })
    import('@bugsnag/browser-performance').then(({ default: BugsnagPerformance }) => {
      BugsnagPerformance.start({ apiKey: bugsnagApiKey, appVersion })
    })
  })
}

initAnalyticsIfConsented()

import './Base.css'
import './Footer.css'

new LogoutTokenHandler()

document.addEventListener('DOMContentLoaded', () => {
  showFlashSnackbar()
  fitHeadingFontSizeToAvailableWidth()
  initScrollToHash()
  initCookieSettingsLink()
})

function initCookieSettingsLink() {
  const link = document.querySelector('.js-cookie-settings')
  if (link) {
    link.addEventListener('click', (e) => {
      e.preventDefault()
      showCookieSettings()
    })
  }
}

function showFlashSnackbar() {
  const snackbarFlashMessages = document.getElementsByClassName('js-flash-snackbar')
  Array.from(snackbarFlashMessages).forEach((jsMsgObj) => {
    showSnackbar('#share-snackbar', jsMsgObj.dataset.msg)
  })
}

function fitHeadingFontSizeToAvailableWidth() {
  // Adjust heading font size or break word
  ;['h1', '.h1', 'h2', '.h2', 'h3', '.h3'].forEach((element) => {
    document.querySelectorAll(`${element}:not(.no-textfill)`).forEach((el) => {
      textFillDefault(el)
    })
  })
}

function initScrollToHash() {
  window.addEventListener('load', () => {
    // getElementById, not querySelector: a hash is not always a valid selector, and the
    // hash can be cleared (e.g. by history.pushState) while we are still polling.
    const id = window.location.hash.slice(1)
    if (id === '') {
      return
    }

    let attempts = 0
    const poll = window.setInterval(() => {
      const target = document.getElementById(id)

      if (target) {
        window.scrollTo(0, target.offsetTop)
        window.clearInterval(poll)
      } else if (attempts++ > 100) {
        // cancel the interval after 100 attempts (== 10s)
        window.clearInterval(poll)
      }
    }, 100)
  })
}
