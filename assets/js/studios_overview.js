// import $ from 'jquery'
import { MDCMenu } from '@material/menu'
import { showSnackbar } from './components/snackbar'
require('../styles/custom/studios.scss')

const menus = []

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.studios-list-item .mdc-menu').forEach((el) => {
    const id = el.dataset.studioId
    if (id) {
      menus[id] = new MDCMenu(el)
      for (const child of el.children[0].children) {
        child.addEventListener('click', (ev) => {
          ev.preventDefault()
        })
      }
    }
  })

  document
    .querySelectorAll('.studios-list-item .mdc-icon-button')
    .forEach((el) => {
      el.addEventListener('click', (ev) => {
        ev.preventDefault()
        const id = el.dataset.studioId
        menus[id].open = menus[id].open ? !menus[id].open : true
      })
    })

  document.querySelectorAll('.ajaxRequestJoinLeaveReport').forEach((el) => {
    el.addEventListener('click', (event) => {
      event.preventDefault()
      const url = el.getAttribute('data-url')
      makeAjaxRequest(url)
    })
  })
})
function makeAjaxRequest(url) {
  fetch(url, {
    method: 'POST',
  })
    .then((response) => {
      if (!response.ok) {
        console.error('There was a problem with the server.')
      } else {
        return response.json()
      }
    })
    .then((data) => {
      if (!data) {
        console.error('There was a problem with the server.')
      } else {
        showSnackbar('#share-snackbar', data.message.toString())
        window.location.reload()
      }
    })
    .catch((error) => {
      console.error('There was an error with the fetch operation:', error)
    })
}

document.addEventListener('DOMContentLoaded', function () {
  const submitButton = document.getElementById('studioCreateFormSubmit')
  submitButton.addEventListener('click', submitForm)

  const cancelButton = document.getElementById('studioCreateFormCancel')
  cancelButton.addEventListener('click', cancelForm)

  const nameInput = document.getElementById('inputStudioName')
  nameInput.addEventListener('input', resetCssInvalidNameInputfield)

  const checkboxes = document.getElementsByClassName('check-studios')
  for (let i = 0; i < checkboxes.length; i++) {
    checkboxes[i].addEventListener('input', function (event) {
      resetCssInvalidCheckbox(checkboxes)
      resetWarningMessage('enable-studio-name-warning')
    })
  }

  const checkboxesPublic = document.getElementsByClassName(
    'check-studios-public',
  )
  for (let i = 0; i < checkboxesPublic.length; i++) {
    checkboxesPublic[i].addEventListener('input', function (event) {
      resetCssInvalidCheckbox(checkboxesPublic)
      resetWarningMessage('is-public-warning')
    })
  }

  const checkboxesComments = document.getElementsByClassName(
    'check-studios-comments',
  )
  for (let i = 0; i < checkboxesComments.length; i++) {
    checkboxesComments[i].addEventListener('input', function (event) {
      resetCssInvalidCheckbox(checkboxesComments)
      resetWarningMessage('allow-comments-warning')
    })
  }
})

function submitForm() {
  const nameInput = document.getElementById('inputStudioName').value.trim()
  const descriptionInput = document
    .getElementById('inputStudioDescription')
    .value.trim()
  const isEnabledValue = document.querySelector(
    '.check-studios[name="form[is_enabled]"]:checked',
  )
  const isPublicValue = document.querySelector(
    '.check-studios-public[name="form[is_public]"]:checked',
  )
  const allowCommentsValue = document.querySelector(
    '.check-studios-comments[name="form[allow_comments]"]:checked',
  )

  if (!parseInput()) {
    return
  }

  const formData = new FormData()
  formData.append('name', nameInput)
  formData.append('description', descriptionInput)
  formData.append('is_enabled', isEnabledValue.value)
  formData.append('is_public', isPublicValue.value)
  formData.append('allow_comments', allowCommentsValue.value)
  const submitButton = document.getElementById('studioCreateFormSubmit')
  const url = submitButton.getAttribute('data-url')
  const urlBack = submitButton.getAttribute('data-url-back')

  fetch(url, {
    method: 'POST',
    body: formData,
  })
    .then((response) => {
      if (!response.ok) {
        console.error('There was a problem with the server.')
        const warningMessage = document.getElementById('name-warning')
        document.getElementById('inputStudioName').classList.add('is-invalid')
        warningMessage.textContent = 'There was a problem with the server.'
      } else {
        return response.json()
      }
    })
    .then((data) => {
      if (!data) {
        console.error('There was a problem with the server.')
        const warningMessage = document.getElementById('name-warning')
        warningMessage.textContent = 'There was a problem with the server'
      } else if (data.message) {
        showSnackbar('#share-snackbar', data.message.toString())
        window.location.href = urlBack
      }
    })
    .catch((error) => {
      console.error('There was an error with the fetch operation:', error)
      const warningMessage = document.getElementById('name-warning')
      warningMessage.textContent = 'There was an error with the fetch operation'
    })
}

function cancelForm() {
  const cancelButton = document.getElementById('studioCreateFormCancel')
  window.location.href = cancelButton.getAttribute('data-url')
}

function parseInput() {
  const isEnableChecked = document.querySelector(
    '.check-studios[name="form[is_enabled]"]:checked',
  )
  const isPublicChecked = document.querySelector(
    '.check-studios-public[name="form[is_public]"]:checked',
  )
  const allowCommentsChecked = document.querySelector(
    '.check-studios-comments[name="form[allow_comments]"]:checked',
  )

  const nameInput = document.getElementById('inputStudioName')
  let wrongInput = false
  if (nameInput.value.trim() === '') {
    nameInput.classList.add('is-invalid')
    const warningMessage = document.getElementById('name-warning')
    warningMessage.textContent = 'Please fill in all required fields.'
    wrongInput = true
  }
  if (!isEnableChecked) {
    const radioInputs = document.getElementsByClassName('check-studios')
    for (let i = 0; i < radioInputs.length; i++) {
      radioInputs[i].classList.add('warning')
    }
    const warningMessage = document.getElementById('enable-studio-name-warning')
    warningMessage.textContent = 'Please select whether to enable the studio!'
    wrongInput = true
  }

  if (!allowCommentsChecked) {
    const radioInputs = document.getElementsByClassName(
      'check-studios-comments',
    )
    for (let i = 0; i < radioInputs.length; i++) {
      radioInputs[i].classList.add('warning')
    }
    const warningMessage = document.getElementById('allow-comments-warning')
    warningMessage.textContent =
      'Please select whether to allow comments or not in the studio!'
    wrongInput = true
  }
  if (!isPublicChecked) {
    const radioInputs = document.getElementsByClassName('check-studios-public')
    for (let i = 0; i < radioInputs.length; i++) {
      radioInputs[i].classList.add('warning')
    }
    const warningMessage = document.getElementById('is-public-warning')
    warningMessage.textContent =
      'Please select whether the studio should be private or public!'
    wrongInput = true
  }
  if (wrongInput) {
    return false
  }
  const warningMessage = document.getElementById('name-warning')
  warningMessage.textContent = ''
  nameInput.classList.remove('is-invalid')
  nameInput.classList.add('is-valid')

  return true
}

function resetCssInvalidNameInputfield() {
  const nameInput = document.getElementById('inputStudioName')
  if (nameInput.classList.contains('is-invalid')) {
    const warningMessage = document.getElementById('name-warning')
    warningMessage.textContent = '' // Reset warning message
    nameInput.classList.remove('is-invalid')
  }
}

function resetCssInvalidCheckbox(checkboxes) {
  for (let i = 0; i < checkboxes.length; i++) {
    const checkbox = checkboxes[i]
    if (checkbox.classList.contains('warning')) {
      checkbox.classList.remove('warning')
    }
  }
}

function resetWarningMessage(elementIds) {
  const element = document.getElementById(elementIds)
  if (element) {
    element.textContent = ''
  }
}
