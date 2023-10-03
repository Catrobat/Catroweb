import { deleteCookie } from './CookieHelper'

export class LogoutTokenHandler {
  constructor() {
    const routingDataset = document.getElementById('js-api-routing').dataset
    this.baseUrl = routingDataset.baseUrl
    this.authenticationPath = routingDataset.authentication
    this.initListeners()
  }

  initListeners() {
    const self = this
    const logoutButton = document.getElementById('btn-logout')
    if (!logoutButton) {
      return
    }
    logoutButton.onclick = function () {
      const xhr = new XMLHttpRequest()
      xhr.addEventListener('readystatechange', function () {
        if (this.readyState === this.DONE) {
          deleteCookie('BEARER', self.baseUrl + '/')
          deleteCookie('REFRESH_TOKEN', self.baseUrl + '/')
          if (logoutButton.dataset && logoutButton.dataset.logoutPath) {
            window.location.href = logoutButton.dataset.logoutPath
          }
        }
      })

      xhr.open('DELETE', self.authenticationPath)
      xhr.setRequestHeader('X-Refresh', 'token')
      xhr.send()
    }
  }
}
