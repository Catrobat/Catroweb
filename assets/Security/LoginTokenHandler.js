import { setCookie } from './CookieHelper'

export class LoginTokenHandler {
  constructor() {
    const routingDataset = document.getElementById('js-api-routing').dataset
    this.baseUrl = routingDataset.baseUrl
    this.indexPath = routingDataset.index
    this.authenticationPath = routingDataset.authentication
  }

  getRedirectUri() {
    const self = this
    const targetPath = document.getElementById('target-path')
    return targetPath && targetPath.value && targetPath.value !== ''
      ? targetPath.value
      : self.indexPath
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
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    })
      .then((response) => {
        if (response.status === 200) {
          return response.json()
        }
      })
      .then((data) => {
        setCookie('BEARER', data.token, 'Tue, 19 Jan 2038 00:00:01 GMT', this.baseUrl + '/')
        setCookie(
          'REFRESH_TOKEN',
          data.refresh_token,
          'Tue, 19 Jan 2038 00:00:01 GMT',
          this.baseUrl + '/',
        )
        window.location.href = this.getRedirectUri()
      })
      .catch(() => {
        const element = document.getElementById('login-alert')
        if (element) {
          element.style.display = 'block'
        }
      })
  }
}
