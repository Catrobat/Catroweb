import './SendMail.scss'

function adminMail() {
  const sendBtn = document.querySelector('.btn-send')
  const previewBtn = document.querySelector('.btn-preview')
  const usernameInput = document.querySelector('#username')
  const subjectInput = document.querySelector('#subject')
  const titleInput = document.querySelector('#title')
  const messageInput = document.querySelector('#content')
  const templateSelect = document.querySelector('#template-select')
  const resultBox = document.querySelector('.resultBox')

  function sendMail() {
    const username = usernameInput.value
    const subject = subjectInput.value
    const title = titleInput.value
    const message = messageInput.value
    const url = `send?username=${encodeURIComponent(username)}&subject=${encodeURIComponent(subject)}&title=${encodeURIComponent(title)}&message=${encodeURIComponent(message)}`

    fetch(url)
      .then((response) => {
        if (response.ok || response.status === 404 || response.status === 400) {
          return response.text()
        } else {
          throw new Error(`Error sending mail: ${response.statusText}`)
        }
      })
      .then((data) => {
        if (data && data.length >= 2 && data.substring(0, 2) === 'OK') {
          resultBox.classList.remove('error')
          resultBox.classList.add('success')
        } else {
          resultBox.classList.remove('success')
          resultBox.classList.add('error')
        }
        resultBox.innerHTML = data
      })
      .catch((error) => {
        console.error(error)
        resultBox.classList.remove('success')
        resultBox.classList.add('error')
        resultBox.innerHTML = 'Error sending mail'
      })
  }

  function previewMail() {
    const username = usernameInput.value
    const subject = subjectInput.value
    const title = titleInput.value
    const message = messageInput.value
    const template = templateSelect.value
    const url = `preview?username=${encodeURIComponent(username)}&subject=${encodeURIComponent(subject)}&title=${encodeURIComponent(title)}&message=${encodeURIComponent(message)}&template=${encodeURIComponent(template)}`
    window.open(url, '_blank')
  }

  sendBtn.addEventListener('click', sendMail)
  previewBtn.addEventListener('click', previewMail)
}

document.addEventListener('DOMContentLoaded', () => {
  adminMail()
})
