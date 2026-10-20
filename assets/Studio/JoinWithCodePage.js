import '../Components/TextField'
import AcceptLanguage from '../Api/AcceptLanguage'

const form = document.getElementById('studio-join-with-code-form')
const errorElement = document.getElementById('studio-join-with-code-error')
const submitButton = document.getElementById('studio-join-with-code-submit')

function showError(message) {
  errorElement.textContent = message
  errorElement.style.display = ''
}

form?.addEventListener('submit', async (event) => {
  event.preventDefault()
  const code = form.querySelector('#studio-join-code').value.trim()
  if (code === '') {
    showError(form.dataset.transInvalid)
    return
  }

  errorElement.style.display = 'none'
  submitButton.disabled = true
  try {
    const response = await fetch(
      `${document.getElementById('js-api-routing').dataset.baseUrl}/api/studios/join-with-code`,
      {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'Accept-Language': new AcceptLanguage().get(),
        },
        body: JSON.stringify({ code }),
      },
    )

    // 409 = already a member: just take them to the studio.
    if (response.status === 200 || response.status === 409) {
      const studio = await response.json()
      window.location.href = form.dataset.studioPath.replace('__ID__', studio.id)
      return
    }

    const messages = {
      403: form.dataset.transBanned,
      404: form.dataset.transInvalid,
      429: form.dataset.transRateLimited,
    }
    showError(messages[response.status] || form.dataset.transError)
  } catch {
    showError(form.dataset.transError)
  }
  submitButton.disabled = false
})
