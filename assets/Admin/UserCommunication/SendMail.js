import './SendMail.scss'

function adminMail() {
  const sendBtn = document.querySelector('.btn-send')
  const previewBtn = document.querySelector('.btn-preview')
  const usernameInput = document.querySelector('#username')
  const subjectInput = document.querySelector('#subject')
  const titleInput = document.querySelector('#title')
  const messageInput = document.querySelector('#message-content')
  const templateSelect = document.querySelector('#template-select')
  const resultBox = document.querySelector('.result-box')

  const subjectGroup = document.querySelector('#subject-group')
  const titleGroup = document.querySelector('#title-group')
  const contentGroup = document.querySelector('#content-group')

  if (!sendBtn || !previewBtn || !templateSelect) {
    console.error('SendMail: required elements not found')
    return
  }

  function updateFieldVisibility() {
    const isBasic = templateSelect.value === 'basic'
    if (subjectGroup) subjectGroup.style.display = isBasic ? '' : 'none'
    if (titleGroup) titleGroup.style.display = isBasic ? '' : 'none'
    if (contentGroup) contentGroup.style.display = isBasic ? '' : 'none'
  }

  templateSelect.addEventListener('change', updateFieldVisibility)
  updateFieldVisibility()

  function showResult(message, isSuccess) {
    if (!resultBox) return
    resultBox.style.display = ''
    resultBox.classList.remove('error', 'success')
    resultBox.classList.add(isSuccess ? 'success' : 'error')
    resultBox.textContent = message
  }

  function sendMail() {
    const username = usernameInput?.value || ''
    if (!username) {
      showResult('Please enter a username', false)
      return
    }
    const subject = subjectInput?.value || ''
    const title = titleInput?.value || ''
    const message = messageInput?.value || ''
    const template = templateSelect.value
    const url = `send?username=${encodeURIComponent(username)}&template=${encodeURIComponent(template)}&subject=${encodeURIComponent(subject)}&title=${encodeURIComponent(title)}&message=${encodeURIComponent(message)}`

    fetch(url)
      .then((response) => {
        if (response.ok || response.status === 404 || response.status === 400) {
          return response.text()
        } else {
          throw new Error(`Error sending mail: ${response.statusText}`)
        }
      })
      .then((data) => {
        const isSuccess = data && data.length >= 2 && data.substring(0, 2) === 'OK'
        showResult(data, isSuccess)
      })
      .catch((error) => {
        console.error(error)
        showResult('Error sending mail', false)
      })
  }

  function previewMail() {
    const username = usernameInput?.value || ''
    if (!username) {
      showResult('Please enter a username to preview', false)
      return
    }
    const subject = subjectInput?.value || ''
    const title = titleInput?.value || ''
    const message = messageInput?.value || ''
    const template = templateSelect.value
    const url = `preview?username=${encodeURIComponent(username)}&subject=${encodeURIComponent(subject)}&title=${encodeURIComponent(title)}&message=${encodeURIComponent(message)}&template=${encodeURIComponent(template)}`
    window.open(url, '_blank')
  }

  sendBtn.addEventListener('click', sendMail)
  previewBtn.addEventListener('click', previewMail)
}

document.readyState === 'loading'
  ? document.addEventListener('DOMContentLoaded', adminMail)
  : adminMail()
