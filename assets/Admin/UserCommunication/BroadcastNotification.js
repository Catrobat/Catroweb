import './BroadcastNotification.scss'

function broadcastNotification() {
  const container = document.querySelector('[data-api-url]')
  if (!container) return

  const apiUrl = container.dataset.apiUrl

  document.querySelectorAll('.btn').forEach((button) => {
    button.addEventListener('click', () => {
      const resultBox = document.querySelector('.resultBox')
      resultBox.innerHTML = ''

      const message = document.querySelector('#msg').value.trim()
      if (!message) {
        resultBox.classList.remove('success')
        resultBox.classList.add('error')
        resultBox.textContent = 'Message must not be empty.'
        return
      }

      button.disabled = true

      fetch(apiUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message }),
      })
        .then((response) => response.json().then((data) => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
          if (ok) {
            resultBox.classList.remove('error')
            resultBox.classList.add('success')
            resultBox.textContent = `Notifications sent to ${data.count} users.`
          } else {
            resultBox.classList.remove('success')
            resultBox.classList.add('error')
            resultBox.textContent = data.error || 'An error occurred.'
          }
        })
        .catch((error) => {
          console.error('Error:', error)
          resultBox.classList.remove('success')
          resultBox.classList.add('error')
          resultBox.textContent = 'Error sending notification.'
        })
        .finally(() => {
          button.disabled = false
        })
    })
  })
}

document.addEventListener('DOMContentLoaded', () => {
  broadcastNotification()
})
