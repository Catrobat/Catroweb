import './Snackbar.scss'

export const SnackbarDuration = {
  short: 4500,
  long: 7500,
  error: 10000,
}

export function showSnackbar(id, text = '', duration = SnackbarDuration.short) {
  const snackbar = document.querySelector(id)
  const snackbarLabel = document.querySelector(id + '-label')

  if (!snackbar || !snackbarLabel) return

  // When multiple snackbar updates are necessary, they should appear one at a time
  const allSnacks = document.querySelectorAll('.mdc-snackbar')
  const visibleSnacks = Array.from(allSnacks).filter((snack) => {
    const style = window.getComputedStyle(snack)
    return style.display !== 'none' && style.opacity !== '0'
  }).length

  if (visibleSnacks > 0) {
    window.setTimeout(function () {
      showSnackbar(id, text, duration)
    }, 250)
    return
  }

  snackbarLabel.textContent = text
  snackbarLabel.style.visibility = 'visible'
  snackbar.style.display = 'block'
  snackbar.style.opacity = '1'
  snackbar.children[0].style.opacity = '1'

  // Add dismiss button for longer/error messages
  let dismissBtn = snackbar.querySelector('.snackbar-dismiss')
  if (dismissBtn) {
    dismissBtn.remove()
  }

  if (duration >= SnackbarDuration.error) {
    dismissBtn = document.createElement('button')
    dismissBtn.className = 'snackbar-dismiss'
    dismissBtn.textContent = '\u2715'
    dismissBtn.setAttribute('aria-label', 'Dismiss')
    snackbar.children[0].appendChild(dismissBtn)
    dismissBtn.addEventListener('click', () => hideSnackbar(snackbar))
  }

  const hideTimer = setTimeout(() => hideSnackbar(snackbar), duration)

  // Store timer reference so dismiss can clear it
  snackbar._hideTimer = hideTimer
}

function hideSnackbar(snackbar) {
  if (snackbar._hideTimer) {
    clearTimeout(snackbar._hideTimer)
    snackbar._hideTimer = null
  }
  snackbar.style.opacity = '0'
  setTimeout(() => {
    snackbar.style.display = 'none'
    const dismissBtn = snackbar.querySelector('.snackbar-dismiss')
    if (dismissBtn) {
      dismissBtn.remove()
    }
  }, 400)
}
