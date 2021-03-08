document.getElementById("_submit").onclick = function() {refreshToken()};
function refreshToken(){
  const data = JSON.stringify({
    username: document.getElementById("username").value,
    password: document.getElementById("password").value
  })

  const xhr = new XMLHttpRequest()
  xhr.withCredentials = true;
  xhr.addEventListener('readystatechange', function() {
    let tokens;
    let jwt_token;
    let jwt_refresh_token;
    if (this.readyState === this.DONE) {
      tokens = JSON.parse(this.responseText)
      jwt_token = tokens.token;
      jwt_refresh_token = tokens.refresh_token;
      localStorage.setItem('refresh_token', jwt_refresh_token);
      localStorage.setItem('jwt_token', jwt_token);
    }
  })

  xhr.open('POST', 'http://' + window.location.host + '/api/authentication')
  xhr.setRequestHeader('content-type', 'application/json')
  xhr.send(data)
}