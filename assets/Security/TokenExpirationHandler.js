import { jwtDecode } from 'jwt-decode'
import { deleteCookie, getCookie, setCookie } from './CookieHelper'

export class TokenExpirationHandler {
  constructor() {
    const routingDataset = document.getElementById('js-api-routing').dataset
    this.baseUrl = routingDataset.baseUrl
    this.indexPath = routingDataset.index
    this.authenticationRefreshPath = routingDataset.authenticationRefresh
    this.checkBearerTokenExpiration()
    this.interval = this.setScopedInterval(this.checkBearerTokenExpiration, 60000, this)
  }

  checkBearerTokenExpiration() {
    const refreshToken = getCookie('REFRESH_TOKEN')
    if (!refreshToken) {
      clearInterval(this.interval)
      return
    }

    const bearerToken = getCookie('BEARER')
    if (bearerToken && jwtDecode) {
      const decodedToken = jwtDecode(bearerToken)
      if (decodedToken && decodedToken.exp) {
        const now = Date.now().valueOf() / 1000
        if (decodedToken.exp < now || decodedToken.exp < now + 60) {
          this.refreshToken()
        }
      }
    } else if (getCookie('REFRESH_TOKEN')) {
      this.refreshToken()
    }
  }

  refreshToken() {
    fetch(this.authenticationRefreshPath, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ refresh_token: getCookie('REFRESH_TOKEN') }),
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
      })
      .catch(() => {
        deleteCookie('BEARER', this.baseUrl + '/')
        deleteCookie('REFRESH_TOKEN', this.baseUrl + '/')
      })
  }

  setScopedInterval(func, millis, scope) {
    return setInterval(function () {
      func.apply(scope)
    }, millis)
  }
}
