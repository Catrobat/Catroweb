document.getElementById('btn-logout').onclick = function () { removeToken() }
function removeToken () {
  const refreshToken = localStorage.getItem('refresh_token')
  const data = JSON.stringify({
    'X-Refresh': refreshToken
  })

  const xhr = new XMLHttpRequest()
  xhr.withCredentials = true
  xhr.addEventListener('readystatechange', function () {
    if (this.readyState === this.DONE) {
      localStorage.removeItem('refresh_token')
      localStorage.removeItem('jwt_token')
    }
  })

  xhr.open('DELETE', 'http://' + window.location.host + '/api/authentication')
  xhr.setRequestHeader('content-type', 'application/json')
  xhr.setRequestHeader('Authorization', 'Bearer <Bearer Token>')
  xhr.setRequestHeader('X-Refresh', refreshToken)
  xhr.send(data)
}
