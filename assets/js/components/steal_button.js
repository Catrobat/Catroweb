export function steal (url, projectId, buttonId, spinnerId, iconId = null) {
  const button = document.getElementById(buttonId)
  const icon = document.getElementById(iconId)
  const loadingSpinner = document.getElementById(spinnerId)

  // loading
  button.disabled = true
  if (iconId) {
    icon.classList.add('d-none')
  }
  loadingSpinner.classList.remove('d-none')

  // call function
  // eslint-disable-next-line no-undef
  fetch(url, {
    method: 'PUT',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      id: projectId
    })
  }).then(r => {
    if (r.status === 200) {
      window.location.reload()
    } else {
      throw new Error(r.statusText + ' (' + r.status + ')')
    }
  }).catch(e => {
    console.log('Steal', e)

    loadingSpinner.classList.add('d-none')
    if (iconId) {
      icon.classList.remove('d-none')
    }
    button.disabled = false
  })
}
