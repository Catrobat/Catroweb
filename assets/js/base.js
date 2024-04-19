import $ from 'jquery'
// import './analytics/analytics'
import textFillDefault from './components/text_fill_default'
import './layout/top_bar'
import './layout/sidebar'
import { TokenExpirationHandler } from './security/TokenExpirationHandler'
import { showSnackbar } from './components/snackbar'
import { LogoutTokenHandler } from './security/LogoutTokenHandler'

import Bugsnag from '@bugsnag/js'
import BugsnagPerformance from '@bugsnag/browser-performance'

// Start the stimulus app
import './bootstrap'

const appVersion = $('#app-version').data('app-version')
const bugsnagApiKey = $('#bugsnag').data('api-key')
if (bugsnagApiKey) {
  Bugsnag.start({ apiKey: bugsnagApiKey, appVersion })
  BugsnagPerformance.start({ apiKey: bugsnagApiKey, appVersion: appVersion })
}

require('../styles/base.scss')
require('../styles/layout/footer.scss')

new TokenExpirationHandler()
new LogoutTokenHandler()

$(() => {
  showFlashSnackbar()
  fitHeadingFontSizeToAvailableWidth()
  initScrollToHash()
})

function showFlashSnackbar() {
  const snackbarFlashMessages =
    document.getElementsByClassName('js-flash-snackbar')
  Array.from(snackbarFlashMessages).forEach((jsMsgObj) => {
    showSnackbar('#share-snackbar', jsMsgObj.dataset.msg)
  })
}

function fitHeadingFontSizeToAvailableWidth() {
  // Adjust heading font size or break word
  ;['h1', '.h1', 'h2', '.h2', 'h3', '.h3'].forEach(function (element) {
    $(element + ':not(.no-textfill)').each(function () {
      textFillDefault(this)
    })
  })
}

function initScrollToHash() {
  $(window).on('load', function () {
    let hash
    let timeout = 0
    const poll = window.setInterval(function () {
      hash = $(window.location.hash)

      if (hash.length) {
        $('html, body').animate({ scrollTop: hash.offset().top })
        window.clearInterval(poll)
      } else if (timeout++ > 100) {
        // cancel the interval after 100 attempts (== 10s)
        window.clearInterval(poll)
      }
    }, 100)
  })
}
