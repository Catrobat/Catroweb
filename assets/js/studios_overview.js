// import $ from 'jquery'
import { MDCMenu } from '@material/menu'

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
})

document.addEventListener('DOMContentLoaded', function() {
  const submitButton = document.getElementById('studioCreateFormSubmit')
  submitButton.addEventListener('click', submitForm)

  const cancelButton = document.getElementById('studioCreateFormCancel')
  cancelButton.addEventListener('click', cancelForm)

  const nameInput = document.getElementById('inputStudioName')
  nameInput.addEventListener('input', resetCssInvalidNameInputfieled)

  const checkboxes = document.getElementsByClassName('check_studios');

  for (let i = 0; i < checkboxes.length; i++) {
    checkboxes[i].addEventListener('input', resetCssInvalidCheckbox)
  }

  const checkboxes_public = document.getElementsByClassName('check_studios_public');

  for (let i = 0; i < checkboxes_public.length; i++) {
    checkboxes_public[i].addEventListener('input', resetCssInvalidCheckbox);
  }

  const checkboxes_comments = document.getElementsByClassName('check_studios_comments');

  for (let i = 0; i < checkboxes_comments.length; i++) {
    checkboxes_comments[i].addEventListener('input', resetCssInvalidCheckbox);
  }

})

function submitForm() {
  const nameInput = document.getElementById('inputStudioName').value.trim()
  const descriptionInput = document.getElementById('inputStudioDescription').value.trim()
  const is_enabledValue = document.querySelector('.check_studios[name="form[is_enabled]"]:checked') ? '1' : '0';
  const is_publicValue = document.querySelector('.other_check_studios[name="form[is_public]"]:checked') ? '1' : '0';
  const allow_commentsValue = document.querySelector('.another_check_studios[name="form[allow_comments]"]:checked') ? '1' : '0';


  if (!parseInput()) {
    return
  }

  const formData = new FormData()
  formData.append('name', nameInput)
  formData.append('description', descriptionInput)
  formData.append('name', nameInput);
  formData.append('description', descriptionInput);
  formData.append('is_enabled', is_enabledValue);
  formData.append('is_public', is_publicValue);
  formData.append('allow_comments', allow_commentsValue);
  const submitButton = document.getElementById('studioCreateFormSubmit')
  const url = submitButton.getAttribute('data-url')
  const urlBack = submitButton.getAttribute('data-url-back')

  fetch(url, {
    method: 'POST',
    body: formData,
  })
    .then(response => {
      if (!response.ok) {
        return response.json() // Parse the JSON from the response
      } else {
        return response.json() // Parse the JSON from the successful response
      }
    })
    .then(data => {
      if (!data) {
        console.error('There was a problem with the server.')
        const warningMessage = document.getElementById('nameWarning')

        warningMessage.textContent = 'There was a problem with the server'
      } else if (data.message) {
        console.error('There was a problem with the server:', data.message)
        const warningMessage = document.getElementById('nameWarning')
        document.getElementById('inputStudioName').classList.add('is-invalid')
        warningMessage.textContent = data.message
      } else {
        console.log('Form submitted successfully', data)
        window.location.href = urlBack
      }
    })
    .catch(error => {
      console.error('There was an error with the fetch operation:', error)
      const warningMessage = document.getElementById('nameWarning')
      warningMessage.textContent = 'There was an error with the fetch operation'
    })
}

function cancelForm() {
  const cancelButton = document.getElementById('studioCreateFormCancel')
  const url = cancelButton.getAttribute('data-url')

  console.log('Cancel request successful')
  window.location.href = url
}

function parseInput() {
  const isEnableChecked = document.querySelector('.check_studios[name="form[is_enabled]"]:checked');
  const isPublicChecked = document.querySelector('.check_studios_public[name="form[is_public]"]:checked');
  const allowCommentsChecked = document.querySelector('.check_studios_comments[name="form[allow_comments]"]:checked');

  const nameInput = document.getElementById('inputStudioName')
  let wrongInput=false
  if (nameInput.value.trim() === '') {
    nameInput.classList.add('is-invalid')
    const warningMessage = document.getElementById('name-warning')
    warningMessage.textContent = 'Please fill in all required fields.'
    wrongInput = true
  }
  if(!isEnableChecked){
    const radioInputs = document.getElementsByClassName('check_studios');
    for (let i = 0; i < radioInputs.length; i++) {
      radioInputs[i].classList.add('warning');
    }
    const warningMessage = document.getElementById('name-warning')
    warningMessage.textContent = 'Please select whether to enable the studio.'
    wrongInput = true
  }

  if(!allowCommentsChecked){
    const radioInputs = document.getElementsByClassName('check_studios');
    for (let i = 0; i < radioInputs.length; i++) {
      radioInputs[i].classList.add('warning');
    }
    const warningMessage = document.getElementById('name-warning')
    warningMessage.textContent = 'Please select whether to enable the studio.'
    wrongInput = true
  }
  if(!isPublicChecked){
    const radioInputs = document.getElementsByClassName('check_studios');
    for (let i = 0; i < radioInputs.length; i++) {
      radioInputs[i].classList.add('warning');
    }
    const warningMessage = document.getElementById('name-warning')
    warningMessage.textContent = 'Please select whether to enable the studio.'
    wrongInput = true
  }
  if (wrongInput)
  {
    return false
  }
  const warningMessage = document.getElementById('nameWarning')
  warningMessage.textContent = ''
  nameInput.classList.remove('is-invalid')
  nameInput.classList.add('is-valid')

  return true
}

function resetCssInvalidNameInputfieled() {
  const nameInput = document.getElementById('inputStudioName')
  if (nameInput.classList.contains('is-invalid')) {
    const warningMessage = document.getElementById('name-warning')
    warningMessage.textContent = '' // Reset warning message
    nameInput.classList.remove('is-invalid')
  }
}
function resetCssInvalidCheckbox() {
  const allCheckboxes = document.querySelectorAll('.check_studios, .check_studios_comments , .check_studios_public');

  for (let i = 0; i < allCheckboxes.length; i++) {
    const checkbox = allCheckboxes[i];
    if (checkbox.classList.contains('warning')) {
      checkbox.classList.remove('warning');
    }
  }
}
