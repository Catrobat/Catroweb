/*!
 * Color mode toggler for Bootstrap's docs (https://getbootstrap.com/)
 * Copyright 2011-2025 The Bootstrap Authors
 * Licensed under the Creative Commons Attribution 3.0 Unported License.
 * Adapted by the Catroweb Project
 */

import { showSnackbar, SnackbarDuration } from './Snackbar'
import { escapeAttr, escapeHtml } from '../Components/HtmlEscape'

const getStoredTheme = () => localStorage.getItem('theme')
const setStoredTheme = (theme) => localStorage.setItem('theme', theme)
const getSelectedTheme = () => {
  const storedTheme = getStoredTheme()

  return storedTheme === 'light' || storedTheme === 'dark' || storedTheme === 'auto'
    ? storedTheme
    : 'auto'
}

const setTheme = (theme) => {
  if (theme === 'auto') {
    theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
  }
  document.documentElement.setAttribute('data-bs-theme', theme)
  document.documentElement.setAttribute('data-swal2-theme', theme)
}

setTheme(getSelectedTheme())

const showActiveTheme = (theme) => {
  const menuItem = document.getElementById('top-app-bar__btn-color-scheme')
  if (!menuItem) return

  const labels = {
    light: menuItem.dataset.transLight,
    dark: menuItem.dataset.transDark,
    auto: menuItem.dataset.transAuto,
  }
  const icons = {
    light: 'light_mode',
    dark: 'dark_mode',
    auto: 'contrast',
  }
  const icon = menuItem.querySelector('.js-color-scheme-icon')
  const currentValue = menuItem.querySelector('.js-color-scheme-current')

  if (icon) {
    icon.textContent = icons[theme] || icons.auto
  }

  if (currentValue) {
    currentValue.textContent = labels[theme] || labels.auto || ''
  }

  menuItem.setAttribute(
    'aria-label',
    `${menuItem.dataset.transTitle || 'Theme'}: ${labels[theme] || labels.auto || ''}`,
  )
}

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
  const storedTheme = getSelectedTheme()
  if (storedTheme !== 'light' && storedTheme !== 'dark') {
    setTheme(storedTheme)
    showActiveTheme(storedTheme)
  }
})

async function showThemePicker(menuItem) {
  const { default: Swal } = await import('sweetalert2')
  const themeOptions = [
    {
      value: 'light',
      icon: 'light_mode',
      label: menuItem.dataset.transLight,
      description: menuItem.dataset.transLightDescription,
    },
    {
      value: 'dark',
      icon: 'dark_mode',
      label: menuItem.dataset.transDark,
      description: menuItem.dataset.transDarkDescription,
    },
    {
      value: 'auto',
      icon: 'contrast',
      label: menuItem.dataset.transAuto,
      description: menuItem.dataset.transAutoDescription,
    },
  ]
  const selectedTheme = getSelectedTheme()

  const renderThemePickerHtml = (activeTheme) =>
    `<div class="theme-picker" role="radiogroup" aria-label="${escapeAttr(menuItem.dataset.transTitle || 'Theme')}">
      ${themeOptions
        .map(
          (option) => `<button type="button"
              class="theme-picker__option${option.value === activeTheme ? ' is-active' : ''}"
              data-theme-value="${escapeAttr(option.value)}"
              role="radio"
              aria-checked="${option.value === activeTheme ? 'true' : 'false'}">
              <span class="theme-picker__option__icon material-icons" aria-hidden="true">${option.icon}</span>
              <span class="theme-picker__option__content">
                <span class="theme-picker__option__label">${escapeHtml(option.label || '')}</span>
                <span class="theme-picker__option__description">${escapeHtml(option.description || '')}</span>
              </span>
              <span class="theme-picker__option__check material-icons" aria-hidden="true">${option.value === activeTheme ? 'radio_button_checked' : 'radio_button_unchecked'}</span>
            </button>`,
        )
        .join('')}
      <input id="theme-picker-value" type="hidden" value="${escapeAttr(activeTheme)}">
    </div>`

  const result = await Swal.fire({
    title: menuItem.dataset.transTitle || 'Theme',
    html: renderThemePickerHtml(selectedTheme),
    showCancelButton: true,
    focusConfirm: false,
    confirmButtonText: menuItem.dataset.transConfirm || 'Apply',
    cancelButtonText: menuItem.dataset.transCancel || 'Cancel',
    customClass: {
      confirmButton: 'btn btn-primary',
      cancelButton: 'btn btn-outline-primary ms-2',
    },
    buttonsStyling: false,
    preConfirm: () =>
      Swal.getPopup()?.querySelector('#theme-picker-value')?.value || getSelectedTheme(),
    didOpen: (popup) => {
      const themePicker = popup.querySelector('.theme-picker')
      const themeValueInput = popup.querySelector('#theme-picker-value')
      const themeOptionsElements = popup.querySelectorAll('[data-theme-value]')

      const updateThemeSelection = (activeTheme) => {
        if (!themePicker || !themeValueInput) {
          return
        }

        themeValueInput.value = activeTheme
        themeOptionsElements.forEach((optionElement) => {
          const isActive = optionElement.dataset.themeValue === activeTheme
          optionElement.classList.toggle('is-active', isActive)
          optionElement.setAttribute('aria-checked', isActive ? 'true' : 'false')

          const checkIcon = optionElement.querySelector('.theme-picker__option__check')
          if (checkIcon) {
            checkIcon.textContent = isActive ? 'radio_button_checked' : 'radio_button_unchecked'
          }
        })
      }

      themeOptionsElements.forEach((optionElement) => {
        optionElement.addEventListener('click', () => {
          updateThemeSelection(optionElement.dataset.themeValue)
        })
      })
    },
  })

  if (result.isConfirmed && result.value) {
    setStoredTheme(result.value)
    setTheme(result.value)
    showActiveTheme(result.value)
  }
}

const initializeColorScheme = () => {
  showActiveTheme(getSelectedTheme())

  const menuItem = document.getElementById('top-app-bar__btn-color-scheme')
  if (!menuItem) return

  menuItem.addEventListener('click', () => {
    showThemePicker(menuItem).catch((error) => {
      console.error('Failed to show color scheme picker', error)
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
    const url = shareItem.dataset.shareUrl || window.location.href
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
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard
      .writeText(text)
      .then(() => {
        showSnackbar('#share-snackbar', successMessage)
      })
      .catch(() => {
        fallbackCopyToClipboard(text, successMessage, failMessage)
      })
  } else {
    fallbackCopyToClipboard(text, successMessage, failMessage)
  }
}

function fallbackCopyToClipboard(text, successMessage, failMessage) {
  const textarea = document.createElement('textarea')
  textarea.value = text
  textarea.style.position = 'fixed'
  textarea.style.left = '-9999px'
  textarea.style.top = '-9999px'
  document.body.appendChild(textarea)
  textarea.select()
  try {
    document.execCommand('copy')
    showSnackbar('#share-snackbar', successMessage)
  } catch {
    showSnackbar('#share-snackbar', failMessage, SnackbarDuration.error)
  }
  document.body.removeChild(textarea)
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
