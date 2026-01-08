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

import textFillDefault from '../Components/TextFillDefault'
import './TopBar'
import './Sidebar'
import { TokenExpirationHandler } from '../Security/TokenExpirationHandler'
import { LogoutTokenHandler } from '../Security/LogoutTokenHandler'
import { showSnackbar } from './Snackbar'

import Bugsnag from '@bugsnag/js'
import BugsnagPerformance from '@bugsnag/browser-performance'

import { Analytics } from 'analytics'
import googleTagManager from '@analytics/google-tag-manager'

// Start the stimulus app
import '../bootstrap'

import './ColorSchemeMenu'

const appVersion = document.getElementById('app-version').dataset.appVersion
const bugsnagApiKey = document.getElementById('bugsnag').dataset.apiKey
if (bugsnagApiKey) {
  Bugsnag.start({ apiKey: bugsnagApiKey, appVersion })
  BugsnagPerformance.start({ apiKey: bugsnagApiKey, appVersion })
}

const gtmContainerId = document.getElementById('gtm-container-id').dataset.gtmContainerId
if (gtmContainerId) {
  const analytics = Analytics({
    app: 'share.catrob.at',
    plugins: [
      googleTagManager({
        containerId: gtmContainerId,
      }),
    ],
  })
  analytics.page() /* Track a page view */
}

require('./Base.scss')
require('./Footer.scss')

new TokenExpirationHandler()
new LogoutTokenHandler()

document.addEventListener('DOMContentLoaded', () => {
  showFlashSnackbar()
  fitHeadingFontSizeToAvailableWidth()
  initScrollToHash()
})

function showFlashSnackbar() {
  const snackbarFlashMessages = document.getElementsByClassName('js-flash-snackbar')
  Array.from(snackbarFlashMessages).forEach((jsMsgObj) => {
    showSnackbar('#share-snackbar', jsMsgObj.dataset.msg)
  })
}

function fitHeadingFontSizeToAvailableWidth() {
  // Adjust heading font size or break word
  ;['h1', '.h1', 'h2', '.h2', 'h3', '.h3'].forEach(function (element) {
    document.querySelectorAll(element + ':not(.no-textfill)').forEach(function (el) {
      textFillDefault(el)
    })
  })
}

function initScrollToHash() {
  window.addEventListener('load', function () {
    let hash
    let timeout = 0
    if (window.location.hash === '') {
      return
    }

    const poll = window.setInterval(function () {
      hash = document.querySelector(window.location.hash)

      if (hash) {
        window.scrollTo(0, hash.offsetTop)
        window.clearInterval(poll)
      } else if (timeout++ > 100) {
        // cancel the interval after 100 attempts (== 10s)
        window.clearInterval(poll)
      }
    }, 100)
  })
}
