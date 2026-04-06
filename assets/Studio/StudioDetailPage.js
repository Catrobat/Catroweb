import '../Components/TabBar'
import '../Components/FullscreenListModal'
import '../Components/Switch'
import '../Components/TextArea'
import '../Components/TextField'
import { showSnackbar, SnackbarDuration } from '../Layout/Snackbar'
import AcceptLanguage from '../Api/AcceptLanguage'
require('../Project/ProjectList.scss')
require('./AdminSettings.scss')
require('./MembersList.scss')
require('./ActivityList.scss')
require('./Studio.scss')

document.getElementById('std-header-form')?.addEventListener('change', (event) => {
  event.preventDefault()
  const fileInput = document.getElementById('std-header')
  const studioId = document.getElementById('studio-id').value
  const url = document.getElementById('js-api-routing').dataset.baseUrl + '/api/studio/' + studioId
  if (fileInput.files.length > 0) {
    uploadCoverImage(url, fileInput.files[0], studioId)
  }
})

async function uploadCoverImage(url, file) {
  const allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif']
  const maxFileSize = 1048576 // 1MB

  if (!allowedMimeTypes.includes(file.type)) {
    showSnackbar(
      '#share-snackbar',
      document.getElementById('trans-image-invalid-type')?.value ||
        'Invalid image type. Please use JPEG, PNG, or GIF.',
      SnackbarDuration.error,
    )
    return
  }

  if (file.size > maxFileSize) {
    showSnackbar(
      '#share-snackbar',
      document.getElementById('trans-image-too-large')?.value ||
        'Image is too large. Maximum size is 1 MB.',
      SnackbarDuration.error,
    )
    return
  }

  const formData = new FormData()
  formData.append('image_file', file)

  const response = await fetch(url, {
    method: 'POST',
    credentials: 'same-origin',
    body: formData,
    headers: {
      Accept: 'application/json',
      'Accept-Language': new AcceptLanguage().get(),
    },
  })

  if (response.status === 200) {
    response.json().then(function (data) {
      document.querySelector('#studio-img-container img').src = data.image_path
    })
  }

  if (response.status === 422) {
    response.text().then(function (text) {
      showSnackbar('#share-snackbar', text, SnackbarDuration.error)
    })
  }
}

// Report studio button
const reportStudioBtn = document.getElementById('top-app-bar__btn-report-studio')
if (reportStudioBtn) {
  import('../Moderation/ReportDialog').then(({ showReportDialog }) => {
    reportStudioBtn.addEventListener('click', () => {
      showReportDialog({
        contentType: reportStudioBtn.dataset.contentType,
        contentId: reportStudioBtn.dataset.contentId,
        apiUrl: reportStudioBtn.dataset.reportUrl,
        loginUrl: reportStudioBtn.dataset.loginUrl,
        isLoggedIn: reportStudioBtn.dataset.loggedIn === 'true',
        translations: {
          title: reportStudioBtn.dataset.transReportTitle,
          submit: reportStudioBtn.dataset.transReportSubmit,
          cancel: reportStudioBtn.dataset.transReportCancel,
          success: reportStudioBtn.dataset.transReportSuccess,
          error: reportStudioBtn.dataset.transReportError,
          duplicate: reportStudioBtn.dataset.transReportDuplicate,
          trustTooLow: reportStudioBtn.dataset.transReportTrustTooLow,
          rateLimited: reportStudioBtn.dataset.transReportRateLimited,
          notePlaceholder: reportStudioBtn.dataset.transReportPlaceholder,
          unverified: reportStudioBtn.dataset.transReportUnverified,
          suspended: reportStudioBtn.dataset.transReportSuspended,
        },
      })
    })
  })
}

// Admin settings form submission via API
document.getElementById('studio-settings__submit-button')?.addEventListener('click', () => {
  submitStudioSettings()
})

async function submitStudioSettings() {
  const modal = document.getElementById('studio-admin-settings-modal')
  if (!modal) return

  const studioId = modal.dataset.studioId
  const baseUrl = document.getElementById('js-api-routing').dataset.baseUrl
  const url = baseUrl + '/api/studio/' + studioId

  const nameInput = document.querySelector('#studio-settings__studio-name__input')
  const descTextarea = document.querySelector(
    '#studio-settings textarea[name="studio_description"]',
  )
  const commentsSwitch = document.querySelector(
    '#studio-setting__switch-enable-comments input[name="allow_comments"]',
  )
  const publicSwitch = document.querySelector(
    '#studio-setting__switch-studio-privacy input[name="is_public"]',
  )

  const formData = new FormData()
  if (nameInput) formData.append('name', nameInput.value)
  if (descTextarea) formData.append('description', descTextarea.value)
  if (commentsSwitch)
    formData.append(
      'enable_comments',
      commentsSwitch.value === 'true' || commentsSwitch.value === '1' ? 'true' : 'false',
    )
  if (publicSwitch)
    formData.append(
      'is_public',
      publicSwitch.value === 'true' || publicSwitch.value === '1' ? 'true' : 'false',
    )

  try {
    const response = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      body: formData,
      headers: {
        Accept: 'application/json',
        'Accept-Language': new AcceptLanguage().get(),
      },
    })

    if (response.ok) {
      showSnackbar(
        '#share-snackbar',
        modal.dataset.transSaveSuccess || 'Settings saved.',
        SnackbarDuration.short,
      )
      window.location.reload()
    } else if (response.status === 422) {
      const text = await response.text()
      showSnackbar('#share-snackbar', text, SnackbarDuration.error)
    } else {
      showSnackbar(
        '#share-snackbar',
        modal.dataset.transSaveError || 'Failed to save settings.',
        SnackbarDuration.error,
      )
    }
  } catch (error) {
    console.error('Studio settings save failed:', error)
    showSnackbar(
      '#share-snackbar',
      modal.dataset.transSaveError || 'Failed to save settings.',
      SnackbarDuration.error,
    )
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const bodyContentParent = document.getElementById('main_container_content')
  if (bodyContentParent) {
    const pageContentContainerChild = bodyContentParent.children
    if (pageContentContainerChild) {
      for (let i = 0; i < pageContentContainerChild.length; i++) {
        pageContentContainerChild[i].style.removeProperty('padding-left')
        pageContentContainerChild[i].style.removeProperty('padding-right')
        pageContentContainerChild[i].style.paddingTop = '1.5rem'
        pageContentContainerChild[i].style.paddingBottom = '1.5rem'
        pageContentContainerChild[i].style.paddingLeft = '0'
        pageContentContainerChild[i].style.paddingRight = '0'
      }
    }
  }

  document.querySelectorAll('.ajaxRequestJoinLeaveReport').forEach((el) => {
    el.addEventListener('click', (event) => {
      event.preventDefault()
      const url = el.getAttribute('data-url')
      const method = el.getAttribute('data-method') || 'POST'
      makeAjaxRequest(url, method)
    })
  })
})

function makeAjaxRequest(url, method) {
  fetch(url, {
    method: method,
    credentials: 'same-origin',
  })
    .then((response) => {
      if (!response.ok) {
        showSnackbar(
          '#share-snackbar',
          'Oops, that did not work. Please try again!',
          SnackbarDuration.error,
        )
        console.error('Studio request failed:', response.status)
      } else {
        window.location.reload()
      }
    })
    .catch((error) => {
      console.error('There was an error with the fetch operation:', error)
    })
}
