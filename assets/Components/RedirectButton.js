export function redirect(url, buttonId, spinnerId, iconId = null) {
  const button = document.getElementById(buttonId)
  button.disabled = true

  if (iconId) {
    const icon = document.getElementById(iconId)
    icon.classList.add('d-none')
  }

  const loadingSpinner = document.getElementById(spinnerId)
  loadingSpinner.classList.remove('d-none')
  window.location.href = url
}
