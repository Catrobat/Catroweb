/* eslint-env jquery */

(function LogoutTokenHandler() {
  const logoutButton = document.getElementById('btn-logout');
  logoutButton.onclick = function () {
    const xhr = new XMLHttpRequest()
    xhr.addEventListener('readystatechange', function () {
      if (this.readyState === this.DONE) {
        deleteCookie('BEARER')
        if(logoutButton.dataset.logoutPath) {
          window.location.href = logoutButton.dataset.logoutPath
        }
      }
    })

    xhr.open('DELETE', '/api/authentication')
    xhr.setRequestHeader('X-Refresh', 'token')
    xhr.send()
  }

  function init() {
    if(getCookie('BEARER')) {
      document.getElementById('logout-nav-item').style.display = "block"
    }
  }

  function deleteCookie(cname) {
    document.cookie = cname + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/;'
  }

  function getCookie(cname) {
    let name = cname + "="
    let decodedCookie = decodeURIComponent(document.cookie)
    let ca = decodedCookie.split(';')
    for(let i = 0; i < ca.length; i++) {
      let c = ca[i]
      while (c.charAt(0) == ' ') {
        c = c.substring(1)
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length)
      }
    }
    return null
  }

  init()
})();