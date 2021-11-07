import jwtDecode from 'jwt-decode'
import { deleteCookie, getCookie, setCookie } from './CookieHelper'
import { setScopedInterval } from './Utils'

export class TokenExpirationHandler {
  constructor () {
    const routingDataset = document.getElementById('js-api-routing').dataset
    this.baseUrl = routingDataset.baseUrl
    this.indexPath = routingDataset.index
    this.authenticationRefreshPath = routingDataset.authenticationRefresh
    this.checkBearerTokenExpiration()
    this.interval = setScopedInterval(this.checkBearerTokenExpiration, 1000, this)
  }

  checkBearerTokenExpiration () {
    const self = this
    const bearerToken = getCookie('BEARER')
    if (bearerToken && jwtDecode) {
      const decodedToken = jwtDecode(bearerToken)
      if (decodedToken && decodedToken.exp) {
        const now = Date.now().valueOf() / 1000
        if (decodedToken.exp < now || decodedToken.exp < (now + 60)) {
          this.refreshToken()
        } else {
          document.getElementById('logout-nav-item').style.display = 'block'
          setCookie('LOGGED_IN', 'true', 'Tue, 19 Jan 2038 00:00:01 GMT', self.baseUrl + '/')
        }
      }
    } else if (getCookie('LOGGED_IN')) {
      this.refreshToken()
    }
  }

  refreshToken () {
    const self = this
    const xhr = new XMLHttpRequest()
    xhr.open('POST', self.authenticationRefreshPath, true)
    xhr.addEventListener('readystatechange', function () {
      if (this.readyState === this.DONE) {
        if (this.status === 401) {
          deleteCookie('BEARER', self.baseUrl + '/')
          deleteCookie('LOGGED_IN', self.baseUrl + '/')
          document.getElementById('logout-nav-item').style.display = 'none'
          clearInterval(self.interval)
        } else if (this.status === 200) {
          document.getElementById('logout-nav-item').style.display = 'block'
          setCookie('LOGGED_IN', 'true', 'Tue, 19 Jan 2038 00:00:01 GMT', self.baseUrl + '/')
        }
      }
    })

    xhr.send()
  }
}
