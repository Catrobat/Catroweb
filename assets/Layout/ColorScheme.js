/*!
 * Color mode toggler for Bootstrap's docs (https://getbootstrap.com/)
 * Copyright 2011-2025 The Bootstrap Authors
 * Licensed under the Creative Commons Attribution 3.0 Unported License.
 * Adapted by the Catroweb Project
 */

const getStoredTheme = () => localStorage.getItem('theme')
const setStoredTheme = (theme) => localStorage.setItem('theme', theme)

const getPreferredTheme = () => {
  const storedTheme = getStoredTheme()
  if (storedTheme) {
    return storedTheme
  }

  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

const setTheme = (theme) => {
  if (theme === 'auto') {
    theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
  }
  document.documentElement.setAttribute('data-bs-theme', theme)
  document.documentElement.setAttribute('data-swal2-theme', theme)
}

setTheme(getPreferredTheme())

const showActiveTheme = (theme) => {
  const menuItemToActivate = document.querySelector(`#color-scheme-menu [data-value="${theme}"]`)
  const activeBtnIcon = menuItemToActivate.querySelector(
    '.mdc-deprecated-list-item__graphic',
  ).innerText

  document.querySelectorAll('#color-scheme-menu [role="menuitem"]').forEach((element) => {
    element.classList.remove('mdc-deprecated-list-item--activated')
    element.setAttribute('aria-pressed', 'false')
  })

  menuItemToActivate.classList.add('mdc-deprecated-list-item--activated')
  menuItemToActivate.setAttribute('aria-pressed', 'true')
  document.getElementById('top-app-bar__btn-color-scheme').childNodes[2].textContent = activeBtnIcon
}

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
  const storedTheme = getStoredTheme()
  if (storedTheme !== 'light' && storedTheme !== 'dark') {
    setTheme(getPreferredTheme())
  }
})

window.addEventListener('DOMContentLoaded', () => {
  showActiveTheme(getPreferredTheme())

  document.querySelectorAll('#color-scheme-menu [role="menuitem"]').forEach((element) => {
    element.addEventListener('click', () => {
      if (element.dataset.value) {
        setStoredTheme(element.dataset.value)
        setTheme(element.dataset.value)
        showActiveTheme(element.dataset.value)
      }
    })
  })
})
