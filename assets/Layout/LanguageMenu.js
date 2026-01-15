require('./LanguageMenu.scss')

document.addEventListener('DOMContentLoaded', function () {
  const cancelButton = document.querySelector('#language_button_cancel')

  cancelButton.addEventListener('click', hideLanguageMenu)
})

function hideLanguageMenu() {
  const languageMenu = document.querySelector('.language-body')
  const languageMenuOverlay = document.querySelector('.language-body-overlay')
  languageMenu.style.display = 'none'
  languageMenuOverlay.style.display = 'none'
  document.body.style.overflow = 'auto'

  const lang = getCookie('hl')
  const radioButtons = document.querySelectorAll('.language-option-radio')
  radioButtons.forEach((radio) => {
    if (lang != null && radio.value === lang) {
      radio.checked = true
    }
  })
}

document.addEventListener('DOMContentLoaded', function () {
  const radioButtons = document.querySelectorAll('.language-option-radio')

  radioButtons.forEach((radio) => {
    radio.addEventListener('change', function () {
      updateLanguageSelection()
    })
  })

  const okButton = document.querySelector('#language_button_ok')

  okButton.addEventListener('click', function () {
    const radioButtons = document.querySelectorAll('.language-option-radio')
    radioButtons.forEach((radio) => {
      if (radio.checked) {
        changeLanguage(radio.value)
      }
    })
    hideLanguageMenu()
  })
})

function updateLanguageSelection() {
  const radioButtons = document.querySelectorAll('.language-option-radio')

  radioButtons.forEach((radio) => {
    const label = radio.closest('.language-option')
    const checkmark = label.querySelector('.language-check')

    if (radio.checked) {
      label.classList.add('language-option-selected')
      if (!checkmark) {
        const check = document.createElement('span')
        check.className = 'language-check'
        check.textContent = '✓'
        label.appendChild(check)
      }
    } else {
      label.classList.remove('language-option-selected')
      if (checkmark) {
        checkmark.remove()
      }
    }
  })
}

function changeLanguage(lang) {
  document.cookie = `hl=${lang}; path=/`
  window.location.reload()
}

function getCookie(name) {
  const cookieArr = document.cookie.split(';')

  for (let i = 0; i < cookieArr.length; i++) {
    const cookiePair = cookieArr[i].split('=')

    if (name === cookiePair[0].trim()) {
      return decodeURIComponent(cookiePair[1])
    }
  }

  return null
}
