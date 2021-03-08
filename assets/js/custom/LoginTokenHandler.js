document.getElementById('_submit').onclick = function () { refreshToken() }
function refreshToken () {
  const data = JSON.stringify({
    username: document.getElementById('username').value,
    password: document.getElementById('password').value
  })

  const xhr = new XMLHttpRequest()
  xhr.withCredentials = true
  xhr.addEventListener('readystatechange', function () {
    let tokens
    let jwtToken
    let jwtRefreshToken
    if (this.readyState === this.DONE) {
      tokens = JSON.parse(this.responseText)
      jwtToken = tokens.token
      jwtRefreshToken = tokens.refresh_token
      localStorage.setItem('refresh_token', jwtRefreshToken)
      localStorage.setItem('jwt_token', jwtToken)
    }
  })

  xhr.open('POST', window.location.protocol + '//' + window.location.host + '/api/authentication')
  xhr.setRequestHeader('content-type', 'application/json')
  xhr.send(data)
}
