/*!
 * Color mode toggler for Bootstrap's docs (https://getbootstrap.com/)
 * Copyright 2011-2025 The Bootstrap Authors
 * Licensed under the Creative Commons Attribution 3.0 Unported License.
 * Adapted by the Catroweb Project
 */

import { showSnackbar, SnackbarDuration } from './Snackbar'

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
  const menuContainer = document.getElementById('top-app-bar__options-menu')
  if (!menuContainer) return

  const menuItemToActivate = menuContainer.querySelector(`[data-value="${theme}"]`)
  if (!menuItemToActivate) return

  menuContainer.querySelectorAll('[data-value]').forEach((element) => {
    element.classList.remove('mdc-deprecated-list-item--activated')
    element.setAttribute('aria-pressed', 'false')
  })

  menuItemToActivate.classList.add('mdc-deprecated-list-item--activated')
  menuItemToActivate.setAttribute('aria-pressed', 'true')
}

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
  const storedTheme = getStoredTheme()
  if (storedTheme !== 'light' && storedTheme !== 'dark') {
    setTheme(getPreferredTheme())
  }
})

const initializeColorScheme = () => {
  showActiveTheme(getPreferredTheme())

  const menuContainer = document.getElementById('top-app-bar__options-menu')
  if (!menuContainer) return

  menuContainer.querySelectorAll('[data-value]').forEach((element) => {
    element.addEventListener('click', () => {
      if (element.dataset.value) {
        setStoredTheme(element.dataset.value)
        setTheme(element.dataset.value)
        showActiveTheme(element.dataset.value)
      }
    })
  })
}

const initializeShareButton = () => {
  const shareItem = document.getElementById('top-app-bar__btn-share-page')
  if (!shareItem) return

  const clipboardSuccess = shareItem.dataset.transClipboardSuccess
  const clipboardFail = shareItem.dataset.transClipboardFail
  const shareSuccess = shareItem.dataset.transShareSuccess

  shareItem.addEventListener('click', async () => {
    const url = window.location.href
    const title = document.title

    if (navigator.share) {
      try {
        await navigator.share({ title, url })
        showSnackbar('#share-snackbar', shareSuccess)
      } catch (e) {
        if (e.name !== 'AbortError') {
          copyToClipboard(url, clipboardSuccess, clipboardFail)
        }
      }
    } else {
      copyToClipboard(url, clipboardSuccess, clipboardFail)
    }
  })
}

function copyToClipboard(text, successMessage, failMessage) {
  navigator.clipboard
    .writeText(text)
    .then(() => {
      showSnackbar('#share-snackbar', successMessage)
    })
    .catch(() => {
      showSnackbar('#share-snackbar', failMessage, SnackbarDuration.error)
    })
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    initializeColorScheme()
    initializeShareButton()
  })
} else {
  initializeColorScheme()
  initializeShareButton()
}
