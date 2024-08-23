import './Snackbar.scss'

const SnackbarDuration = {
  short: 4500,
  long: 7500,
}

export function showSnackbar(id, text = '', duration = SnackbarDuration.short) {
  const snackbar = document.querySelector(id)
  const snackbarLabel = document.querySelector(id + '-label')

  // When multiple snackbar updates are necessary, they should appear one at a time
  const allSnacks = document.querySelectorAll('.mdc-snackbar')
  const visibleSnacks = Array.from(allSnacks).filter((snack) => {
    const style = window.getComputedStyle(snack)
    return style.display !== 'none' && style.opacity !== '0'
  }).length

  if (visibleSnacks > 0) {
    window.setTimeout(function () {
      showSnackbar(id, text)
    }, 250)
    return
  }

  snackbarLabel.textContent = text
  snackbarLabel.style.visibility = 'visible'
  snackbar.style.display = 'block'
  snackbar.style.opacity = '1'
  snackbar.children[0].style.opacity = '1'

  setTimeout(() => {
    snackbar.style.opacity = '0'
    setTimeout(() => {
      snackbar.style.display = 'none'
    }, 400)
  }, duration)
}
