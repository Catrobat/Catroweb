document.getElementById("btn-logout").onclick = function() {removeToken()}
function removeToken () {
  const refresh_token = localStorage.getItem('refresh_token');
  const data = JSON.stringify({
    'X-Refresh': refresh_token,
  });

  const xhr = new XMLHttpRequest()
  xhr.withCredentials = true;
  xhr.addEventListener('readystatechange', function() {
    if (this.readyState === this.DONE) {
      localStorage.removeItem('refresh_token');
      localStorage.removeItem('jwt_token')
    }
  })

  xhr.open('DELETE', 'http://' + window.location.host + '/api/authentication')
  xhr.setRequestHeader('content-type', 'application/json')
  xhr.setRequestHeader('Authorization', 'Bearer <Bearer Token>');
  xhr.setRequestHeader('X-Refresh', refresh_token);
  xhr.send(data)
}