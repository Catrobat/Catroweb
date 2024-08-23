import '../../../styles/Admin/UserCommunication/broadcast_notification.scss'

function broadcastNotification() {
  document.querySelectorAll('.btn').forEach((button) => {
    button.addEventListener('click', () => {
      const resultBox = document.querySelector('.resultBox')
      resultBox.innerHTML = ''

      const message = document.querySelector('#msg').value

      fetch(`send?Message=${encodeURIComponent(message)}`, {
        method: 'GET',
      })
        .then((response) => response.text())
        .then((data) => {
          if (data === 'OK') {
            resultBox.classList.remove('error')
            resultBox.classList.add('success')
          } else {
            resultBox.classList.remove('success')
            resultBox.classList.add('error')
          }
          resultBox.innerHTML = data
        })
        .catch((error) => {
          console.error('Error:', error)
          resultBox.classList.remove('success')
          resultBox.classList.add('error')
          resultBox.innerHTML = 'Error sending notification'
        })
    })
  })
}

document.addEventListener('DOMContentLoaded', () => {
  broadcastNotification()
})
