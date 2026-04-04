import '../Components/TabBar'
import '../Components/FullscreenListModal'
import '../Components/Switch'
import '../Components/TextArea'
import '../Components/TextField'
import { showSnackbar, SnackbarDuration } from '../Layout/Snackbar'
import Swal from 'sweetalert2'
import StudioCommentHandler from './StudioCommentHandler'
import AcceptLanguage from '../Api/AcceptLanguage'
require('../Project/ProjectList.scss')
require('./AdminSettings.scss')
require('./MembersList.scss')
require('./ActivityList.scss')
require('./Studio.scss')
document.getElementById('std-header-form')?.addEventListener('change', () => {
  event.preventDefault()
  const fileInput = document.getElementById('std-header')
  const studioId = document.getElementById('studio-id').value
  const url = document.getElementById('js-api-routing').dataset.baseUrl + '/api/studio/' + studioId
  if (fileInput.files.length > 0) {
    uploadCoverImage(url, fileInput.files[0], studioId)
  }
})

document.querySelectorAll('.comment-delete-button').forEach((element) =>
  element.addEventListener('click', () => {
    const studioId = document.getElementById('studio-id').value
    Swal.fire({
      title: document.getElementById('trans-are-you-sure')?.value || 'Are you sure?',
      text: document.getElementById('trans-no-way-of-return')?.value || 'This cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      allowOutsideClick: false,
      customClass: {
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-outline-primary',
      },
      buttonsStyling: false,
      confirmButtonText: document.getElementById('trans-delete-it')?.value || 'Delete',
      cancelButtonText: document.getElementById('trans-cancel')?.value || 'Cancel',
    }).then((result) => {
      if (result.isConfirmed) {
        new StudioCommentHandler().removeComment(
          studioId,
          element,
          element.dataset.commentId,
          false,
          0,
        )
      }
    })
  }),
)

document.getElementById('studio-send-comment')?.addEventListener('click', () => {
  const studioId = document.getElementById('studio-id').value
  new StudioCommentHandler().postComment(studioId, false)
})

document.querySelectorAll('.comment-replies').forEach((element) =>
  element.addEventListener('click', () => {
    const studioId = document.getElementById('studio-id').value
    new StudioCommentHandler().loadReplies(studioId, element, element.dataset.commentId, false, 0)
  }),
)
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
const reportStudioBtn = document.getElementById('btn-report-studio')
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
        pageContentContainerChild[i].style.paddingLeft = '0' // Reset left padding to 0
        pageContentContainerChild[i].style.paddingRight = '0' // Reset right padding to 0
      }
    }
  }

  document.querySelectorAll('.ajaxRequestJoinLeaveReport').forEach((el) => {
    el.addEventListener('click', (event) => {
      event.preventDefault()
      const url = el.getAttribute('data-url')
      makeAjaxRequest(url)
    })
  })

  // Join request switches are handled by the settings form submission.
  // Toggle state is persisted when the admin clicks the "done" (submit) button.
})

document.addEventListener('DOMContentLoaded', function () {
  const clickedProjectsAdminRemove = []
  let AJAXdata = ''

  document.querySelectorAll('.removeProjectsAdmin').forEach((el) => {
    el.addEventListener('click', (event) => {
      const projectId = event.target.id
      handleImageClickRemove(projectId)
    })

    function handleImageClickRemove(projectId) {
      const index = clickedProjectsAdminRemove.indexOf(projectId)
      const image = document.getElementById(projectId)
      if (index === -1) {
        image.classList.add('red-background')
        clickedProjectsAdminRemove.push(projectId)
      } else {
        image.classList.remove('red-background')
        clickedProjectsAdminRemove.splice(index, 1)
      }
      AJAXdata =
        clickedProjectsAdminRemove.length > 0 ? JSON.stringify(clickedProjectsAdminRemove) : ''
    }
  })
  const ajaxRequestDeleteProject = document.getElementById('ajaxRequestDeleteProject')
  if (ajaxRequestDeleteProject) {
    ajaxRequestDeleteProject.addEventListener('click', function () {
      if (AJAXdata !== '') {
        Swal.fire({
          title: document.getElementById('trans-are-you-sure')?.value || 'Are you sure?',
          text:
            document.getElementById('trans-no-way-of-return')?.value || 'This cannot be undone.',
          icon: 'warning',
          showCancelButton: true,
          allowOutsideClick: false,
          customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-outline-primary',
          },
          buttonsStyling: false,
          confirmButtonText: document.getElementById('trans-delete-it')?.value || 'Delete',
          cancelButtonText: document.getElementById('trans-cancel')?.value || 'Cancel',
        }).then((result) => {
          if (result.isConfirmed) {
            const formData = new FormData()
            formData.append('projects_remove', AJAXdata)
            formData.append('studio_id', ajaxRequestDeleteProject.dataset.studioId)

            const url = ajaxRequestDeleteProject.dataset.url
            fetch(url, {
              method: 'POST',
              credentials: 'same-origin',
              body: formData,
            })
              .then((response) => {
                if (!response.ok) {
                  throw new Error('Network response was not ok')
                }
                return response.json()
              })
              .then((data) => {
                if (data.redirect_url) {
                  window.location.href = data.redirect_url
                }
              })
              .catch((error) => {
                console.error('There was an error with the fetch operation:', error)
              })
          }
        })
      }
    })
  }
})

function makeAjaxRequest(url) {
  fetch(url, {
    method: 'POST',
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
        return response.json()
      }
    })
    .then((data) => {
      if (!data) {
        showSnackbar(
          '#share-snackbar',
          'Oops, that did not work. Please try again!',
          SnackbarDuration.error,
        )
        console.error('Studio request returned empty data')
      } else {
        showSnackbar('#share-snackbar', data.message.toString())
        window.location.reload()
      }
    })
    .catch((error) => {
      console.error('There was an error with the fetch operation:', error)
    })
}
