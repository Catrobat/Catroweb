export class LoginTokenHandler {
  constructor() {
    const routingDataset = document.getElementById('js-api-routing').dataset
    this.indexPath = routingDataset.index
    this.authenticationPath = routingDataset.authentication
  }

  getRedirectUri() {
    const self = this
    const targetPath = document.getElementById('target-path')
    const rawValue =
      targetPath && typeof targetPath.value === 'string' ? targetPath.value.trim() : ''

    // Only use a provided target path if it results in a safe same-origin HTTP(S) URL.
    if (rawValue !== '') {
      try {
        // The URL constructor resolves relative paths against the current origin and
        // also handles absolute URLs, allowing the same-origin check to be the security boundary.
        const url = new URL(rawValue, window.location.origin)
        // Enforce same origin and restrict to HTTP(S) protocols to prevent open redirects.
        const isSameOrigin = url.origin === window.location.origin
        const isHttpProtocol = url.protocol === 'http:' || url.protocol === 'https:'
        if (isSameOrigin && isHttpProtocol) {
          return url.pathname + url.search + url.hash
        }
      } catch {
        // If URL construction fails, fall through to the safe default.
      }
    }

    return self.indexPath
  }

  initListeners() {
    const self = this
    document.getElementById('login-form').addEventListener('submit', function (event) {
      event.preventDefault()
      self.login({
        username: document.getElementById('username__input').value,
        password: document.getElementById('password__input').value,
      })
    })
  }

  login(data) {
    fetch(this.authenticationPath, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Auth-Mode': 'cookie',
      },
      body: JSON.stringify(data),
    })
      .then((response) => {
        if (!response.ok) {
          return response
            .json()
            .then((body) => {
              this.showLoginError(
                body?.error_code === 'account_suspended' ? 'suspended' : 'invalid',
              )
            })
            .catch(() => {
              this.showLoginError('invalid')
            })
        }

        window.location.href = this.getRedirectUri()
      })
      .catch(() => {
        this.showLoginError('invalid')
      })
  }

  showLoginError(type) {
    const alertInvalid = document.getElementById('login-alert')
    const alertSuspended = document.getElementById('login-alert-suspended')

    if (alertInvalid) {
      alertInvalid.style.display = type === 'suspended' ? 'none' : 'block'
    }

    if (alertSuspended) {
      alertSuspended.style.display = type === 'suspended' ? 'block' : 'none'
    }
  }
}
