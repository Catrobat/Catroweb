/* eslint-env jquery */

(function LoginTokenHandler() {
  document.getElementById('_submit').onclick = function () {
    const data = JSON.stringify({
      username: document.getElementById('username').value,
      password: document.getElementById('password').value
    })

    let xhr = new XMLHttpRequest()
    xhr.open('POST', '/api/authentication', true)
    xhr.setRequestHeader("Content-Type", "application/json")
    xhr.addEventListener('readystatechange', function () {
      if (this.readyState === this.DONE) {
        if(this.status === 200) {
          window.location.href = "/"
        } else {
          $( "#login-alert" ).show()
        }
      }
    })

    xhr.send(data)
  }
})();