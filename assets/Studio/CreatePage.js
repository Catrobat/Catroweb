import '../Components/Switch'
import { getCookie } from '../Security/CookieHelper'
import { showValidationMessage } from '../Components/TextField'
import AcceptLanguage from '../Api/AcceptLanguage'

require('./CreateStudio.scss')

document.addEventListener('DOMContentLoaded', function () {
  const saveButton = document.getElementById('top-app-bar__btn-save')
  const createForm = document.getElementById('studio-create-form')
  saveButton.addEventListener('click', function (event) {
    if (createForm.reportValidity()) {
      event.preventDefault()
      submitForm(
        {
          url: document.getElementById('js-api-routing').dataset.baseUrl + '/api/studio',
        },
        {
          name: createForm.querySelector('#studio-name__input').value,
          description: createForm.querySelector('#studio-description__input').value,
          is_public: createForm.querySelector('[name="is-public"]').value,
          enable_comments: createForm.querySelector('[name="enable-comments"]').value,
          image_file: createForm.querySelector('#studio-file-input').files[0],
        },
      )
    }
  })

  async function submitForm(config, input) {
    const formData = new FormData()
    formData.append('name', input.name)
    formData.append('description', input.description)
    formData.append('is_public', input.is_public === '1')
    formData.append('enable_comments', input.enable_comments === '1')
    formData.append('image_file', input.image_file)

    const response = await fetch(config.url, {
      method: 'POST',
      body: formData,
      headers: {
        Accept: 'application/json',
        Authorization: 'Bearer ' + getCookie('BEARER'),
        'Accept-Language': new AcceptLanguage().get(),
      },
    })

    if (response.status === 201) {
      window.location.href = response.headers.get('Location')
      return
    }

    if (response.status === 422) {
      response.text().then(function (text) {
        handleValidationError(text)
      })
    }
  }

  function handleValidationError(responseText) {
    const responseObj = JSON.parse(responseText)
    showValidationMessage(responseObj.name, 'studio-name')
    showValidationMessage(responseObj.description, 'studio-description')
    if (responseObj.image_file) {
      fileName.textContent = responseObj.image_file
      fileName.classList.add('error-text')
    } else {
      fileName.textContent = currentFileName
      fileName.classList.remove('error-text')
    }
  }

  const fileInput = document.getElementById('studio-file-input')
  const fileName = document.getElementById('studio-file-name')
  const previewImage = document.getElementById('studio-preview-image')
  const deleteButton = document.getElementById('studio-delete-button')
  const emptyFileMsg = fileName.textContent
  let currentFileName = emptyFileMsg

  fileInput.addEventListener('change', function (event) {
    const file = event.target.files[0]
    if (file) {
      currentFileName = file.name.length > 20 ? file.name.substring(0, 17) + '...' : file.name
      fileName.textContent = currentFileName

      // Display image preview
      const reader = new FileReader()
      reader.onload = function (e) {
        previewImage.src = e.target.result
        previewImage.style.display = 'block'
        deleteButton.style.display = 'flex'
      }
      reader.readAsDataURL(file)
    } else {
      resetFileInput()
    }
  })

  deleteButton.addEventListener('click', function () {
    resetFileInput()
  })

  function resetFileInput() {
    fileInput.value = ''
    currentFileName = emptyFileMsg
    fileName.textContent = currentFileName
    fileName.classList.remove('error-text')
    previewImage.style.display = 'none'
    deleteButton.style.display = 'none'
  }
})
