import { setCookie } from './CookieHelper'

export class LoginTokenHandler {
  constructor () {
    const routingDataset = document.getElementById('js-api-routing').dataset
    this.baseUrl = routingDataset.baseUrl
    this.indexPath = routingDataset.index
    this.authenticationPath = routingDataset.authentication
  }

  getRedirectUri () {
    const self = this
    const targetPath = document.getElementById('target-path')
    return (targetPath && targetPath.value && targetPath.value !== '') ? targetPath.value : self.indexPath
  }

  initListeners () {
    const self = this
    document.getElementById('login-form').addEventListener('submit', function (event) {
      event.preventDefault()
      const data = JSON.stringify({
        username: document.getElementById('username__input').value,
        password: document.getElementById('password__input').value
      })

      self.login(data)
    })
  }

  login (data) {
    const self = this
    const xhr = new XMLHttpRequest()
    xhr.open('POST', self.authenticationPath, true)
    xhr.setRequestHeader('Content-Type', 'application/json')
    xhr.addEventListener('readystatechange', function () {
      if (this.readyState === this.DONE) {
        if (this.status === 200) {
          setCookie('LOGGED_IN', 'true', 'Tue, 19 Jan 2038 00:00:01 GMT', self.baseUrl + '/')
          window.location.href = self.getRedirectUri()
        } else {
          document.getElementById('login-alert').style.display = 'block'
        }
      }
    })

    xhr.send(data)
  }
}
