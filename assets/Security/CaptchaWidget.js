/**
 * Initializes a Cap CAPTCHA widget inside the given container.
 * Returns a getter function that retrieves the current token.
 *
 * @param {string} apiEndpoint - The Cap API endpoint URL
 * @param {string} containerId - DOM element ID for the widget container
 * @returns {Promise<{getToken: () => string, destroy: () => void}>}
 */
export async function initCaptchaWidget(apiEndpoint, containerId = 'captcha-container') {
  let token = ''

  const container = document.getElementById(containerId)
  if (!container) {
    return { getToken: () => token, destroy: () => {} }
  }

  await import('@cap.js/widget')
  const widget = document.createElement('cap-widget')
  widget.setAttribute('data-cap-api-endpoint', apiEndpoint)
  widget.addEventListener('solve', (e) => {
    token = e.detail.token
  })
  container.appendChild(widget)

  return {
    getToken: () => token,
    destroy: () => widget.remove(),
  }
}
