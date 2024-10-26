import '../Components/TabBar'
import '../Components/FullscreenListModal'
import '../Components/Switch'
import '../Components/TextArea'
import '../Components/TextField'
import { showSnackbar } from '../Layout/Snackbar'
import Swal from 'sweetalert2'
import StudioCommentHandler from './StudioCommentHandler'
import { getCookie } from '../Security/CookieHelper'
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
  element.addEventListener('click', (e) => {
    const studioId = document.getElementById('studio-id').value
    new StudioCommentHandler().removeComment(studioId, element, element.dataset.commentId, false, 0)
  }),
)

document.getElementById('studio-send-comment')?.addEventListener('click', () => {
  const studioId = document.getElementById('studio-id').value
  new StudioCommentHandler().postComment(studioId, false)
})

document.querySelectorAll('.comment-replies').forEach((element) =>
  element.addEventListener('click', (e) => {
    const studioId = document.getElementById('studio-id').value
    new StudioCommentHandler().loadReplies(studioId, element, element.dataset.commentId, false, 0)
  }),
)
async function uploadCoverImage(url, file) {
  const formData = new FormData()
  formData.append('image_file', file)

  const response = await fetch(url, {
    method: 'POST',
    body: formData,
    headers: {
      Accept: 'application/json',
      Authorization: 'Bearer ' + getCookie('BEARER'),
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
      showSnackbar('#share-snackbar', text)
    })
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

  const pendingJoinButton = document.querySelectorAll('#pending-join-requests button.mdc-switch')
  const buttonsDeclined = document.querySelectorAll('#declined-join-requests button.mdc-switch')
  pendingJoinButton.forEach(function (button) {
    button.addEventListener('click', function () {
      const buttonAriaChecked = button.getAttribute('aria-checked')
      const labelFor = button.getAttribute('id')
      const label = document.querySelector(`label[for="${labelFor}"]`)

      if (buttonAriaChecked === 'true') {
        Swal.fire({
          title: `${label.textContent} gets declined`,
          text: 'Pending Join Requests Button!',
          icon: 'success',
          confirmButtonText: 'OK',
        })
      } else {
        Swal.fire({
          title: `${label.textContent} gets approved`,
          text: 'Pending Join Requests!',
          icon: 'success',
          confirmButtonText: 'OK',
        })
      }
    })
  })

  buttonsDeclined.forEach(function (button) {
    button.addEventListener('click', function () {
      const buttonAriaChecked = button.getAttribute('aria-checked')
      const labelFor = button.getAttribute('id')
      const label = document.querySelector(`label[for="${labelFor}"]`)

      if (buttonAriaChecked === 'true') {
        Swal.fire({
          title: `${label.textContent} stay declined`,
          text: 'Declined Join Requests Button Clicked!',
          icon: 'success',
          confirmButtonText: 'OK',
        })
      } else {
        Swal.fire({
          title: `${label.textContent} gets approved`,
          text: 'Declined Join Requests Button Clicked!',
          icon: 'success',
          confirmButtonText: 'OK',
        })
      }
    })
  })
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
        const formData = new FormData()
        formData.append('projects_remove', AJAXdata)
        formData.append('studio_id', event.currentTarget.dataset.studioId)

        const url = event.currentTarget.dataset.url
        fetch(url, {
          method: 'POST',
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

function makeAjaxRequest(url) {
  fetch(url, {
    method: 'POST',
  })
    .then((response) => {
      if (!response.ok) {
        showSnackbar('#share-snackbar', 'There was a problem with the server.')
        console.error('There was a problem with the server.')
      } else {
        return response.json()
      }
    })
    .then((data) => {
      if (!data) {
        showSnackbar('#share-snackbar', 'There was a problem with the server.')
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
