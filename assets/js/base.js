import $ from 'jquery'
import './analytics/analytics'
import textFillDefault from './components/text_fill_default'
import './layout/top_bar'
import './layout/footer'
import './layout/sidebar'
import { TokenExpirationHandler } from './custom/TokenExpirationHandler'
import { LogoutTokenHandler } from './custom/LogoutTokenHandler'

// Start the stimulus app
import './bootstrap'

require('../styles/base.scss')

new TokenExpirationHandler()
new LogoutTokenHandler()

$(() => {
  fitHeadingFontSizeToAvailableWidth()
  initScrollToHash()
})

function fitHeadingFontSizeToAvailableWidth () {
  // Adjust heading font size or break word
  ['h1', '.h1', 'h2', '.h2', 'h3', '.h3'].forEach(function (element) {
    $(element + ':not(.no-textfill)').each(function () {
      textFillDefault(this)
    })
  })
}

function initScrollToHash () {
  $(window).on('load', function () {
    let hash
    let timeout = 0
    const poll = window.setInterval(function () {
      hash = $(window.location.hash)

      if (hash.length) {
        $('html, body').animate({ scrollTop: hash.offset().top })
        window.clearInterval(poll)
      } else if (timeout++ > 100) { // cancel the interval after 100 attempts (== 10s)
        window.clearInterval(poll)
      }
    }, 100)
  })
}
