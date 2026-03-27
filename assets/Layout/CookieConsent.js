import { getCookie, setCookie } from '../Security/CookieHelper'

const COOKIE_NAME = 'cookie_consent'
const COOKIE_EXPIRY_DAYS = 365

function getExpiry(days) {
  const date = new Date()
  date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000)
  return date.toUTCString()
}

function getConsentStatus() {
  return getCookie(COOKIE_NAME)
}

function initGTM() {
  const gtmEl = document.getElementById('gtm-container-id')
  if (!gtmEl) return
  const gtmContainerId = gtmEl.dataset.gtmContainerId
  if (!gtmContainerId) return

  import('analytics').then(({ Analytics }) => {
    import('@analytics/google-tag-manager').then(({ default: googleTagManager }) => {
      const analytics = Analytics({
        app: 'share.catrob.at',
        plugins: [googleTagManager({ containerId: gtmContainerId })],
      })
      analytics.page()
    })
  })
}

function createBanner() {
  const config = document.getElementById('cookie-consent-config')
  if (!config) return null

  const message = config.dataset.transMessage || 'We use cookies to help improve our services.'
  const acceptText = config.dataset.transAccept || 'Accept'
  const declineText = config.dataset.transDecline || 'Decline'
  const privacyText = config.dataset.transPrivacyLinkText || 'Privacy Policy'
  const privacyUrl = config.dataset.privacyUrl || '/pocketcode/privacy-policy'

  const banner = document.createElement('div')
  banner.className = 'cookie-consent-banner'
  banner.setAttribute('role', 'dialog')
  banner.setAttribute('aria-label', 'Cookie consent')
  banner.innerHTML = `
    <div class="cookie-consent-content">
      <p class="cookie-consent-message">
        ${escapeHtml(message)}
        <a href="${escapeAttr(privacyUrl)}" class="cookie-consent-link">${escapeHtml(privacyText)}</a>
      </p>
      <div class="cookie-consent-actions">
        <button class="cookie-consent-btn cookie-consent-decline" type="button">${escapeHtml(declineText)}</button>
        <button class="cookie-consent-btn cookie-consent-accept" type="button">${escapeHtml(acceptText)}</button>
      </div>
    </div>
  `

  banner.querySelector('.cookie-consent-accept').addEventListener('click', () => {
    setCookie(COOKIE_NAME, 'accepted', getExpiry(COOKIE_EXPIRY_DAYS), '/')
    hideBanner(banner)
    initGTM()
  })

  banner.querySelector('.cookie-consent-decline').addEventListener('click', () => {
    setCookie(COOKIE_NAME, 'declined', getExpiry(COOKIE_EXPIRY_DAYS), '/')
    hideBanner(banner)
  })

  return banner
}

function showBanner() {
  const existing = document.querySelector('.cookie-consent-banner')
  if (existing) {
    existing.classList.add('cookie-consent-visible')
    return
  }

  const banner = createBanner()
  if (!banner) return
  document.body.appendChild(banner)
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      banner.classList.add('cookie-consent-visible')
    })
  })
}

function hideBanner(banner) {
  banner.classList.remove('cookie-consent-visible')
  banner.addEventListener('transitionend', () => banner.remove(), { once: true })
}

function escapeHtml(str) {
  const div = document.createElement('div')
  div.appendChild(document.createTextNode(str))
  return div.innerHTML
}

function escapeAttr(str) {
  return str
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
}

export function initAnalyticsIfConsented() {
  const status = getConsentStatus()
  if (status === 'accepted') {
    initGTM()
  } else if (!status) {
    showBanner()
  }
}

export function showCookieSettings() {
  setCookie(COOKIE_NAME, '', 'Thu, 01 Jan 1970 00:00:01 GMT', '/')
  showBanner()
}
