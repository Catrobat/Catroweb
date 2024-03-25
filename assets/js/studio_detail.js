// import $ from 'jquery'
import './components/tab_bar'
import './components/fullscreen_list_modal'
import './components/switch'
import './components/text_area'
import './components/text_field'
import { showSnackbar } from './components/snackbar'
import Swal from 'sweetalert2'
require('../styles/components/project_list.scss')
require('../styles/components/studio_admin_settings.scss')
require('../styles/components/studio_members_list.scss')
require('../styles/components/studio_activity_list.scss')
require('../styles/custom/studio.scss')

// $('.studio-detail__header__details__button--upload-image').on('click', () => {
//   uploadCoverImage()
// })

// function showMoreLessDescription (element) {
//   const more = $('#showMore-text').val()
//   const less = $('#showLess-text').val()
//   $('#studio-desc').toggleClass('desc-show-less')
//   if (element.text() === more) {
//     element.text(less)
//   } else {
//     element.text(more)
//   }
// }
//
// function removeProject (projectID) {
//   const removeSuccess = $('#project-remove-success').val()
//   const removeError = $('#project-remove-error').val()
//   const studioID = $('#studio-id').val()
//   $.ajax({
//     url: '../removeStudioProject/',
//     type: 'POST',
//     data: {
//       studioID: studioID,
//       projectID: projectID
//     },
//     success: function (data, status) {
//       if (status === 'success') {
//         // eslint-disable-next-line no-undef
//         showSnackbar('#share-snackbar', removeSuccess)
//         $('#project-' + projectID).fadeOut()
//         $('#projects-count').text(data.projects_count)
//         $('#activities_count').text(data.activities_count)
//       } else {
//         // eslint-disable-next-line no-undef
//         showSnackbar('#share-snackbar', removeError)
//       }
//     },
//     fail: function () {
//       // eslint-disable-next-line no-undef
//       showSnackbar('#share-snackbar', removeError)
//     }
//   })
// }
//
// function removeComment (element, commentID, isReply, parentID) {
//   const studioID = $('#studio-id').val()
//   const removeError = $('#comment-remove-error').val()
//   $.ajax({
//     url: '../removeStudioComment/',
//     type: 'POST',
//     data: {
//       studioID: studioID,
//       commentID: commentID,
//       parentID: parentID,
//       isReply: isReply
//     },
//     success: function (data, status) {
//       if (status === 'success') {
//         element.parents('.studio-comment').fadeOut().next('hr').fadeOut()
//         $('#comments-count').text(data.comments_count)
//         $('#activities_count').text(data.activities_count)
//         if (isReply && parentID > 0) {
//           $('#info-' + parentID).text(data.replies_count)
//         }
//       } else {
//         // eslint-disable-next-line no-undef
//         showSnackbar('#share-snackbar', removeError)
//       }
//     },
//     fail: function () {
//       // eslint-disable-next-line no-undef
//       showSnackbar('#share-snackbar', removeError)
//     }
//   })
// }
//
// function postComment (isReply) {
//   const studioID = $('#studio-id').val()
//   const comment = isReply ? $('#add-reply').find('input').val() : $('#add-comment').find('input').val()
//   const commentError = $('#comment-error').val()
//   const parentID = isReply ? $('#cmtID').val() : 0
//   if (comment.trim() === '') {
//     return
//   }
//   $.ajax({
//     url: '../postCommentToStudio/',
//     type: 'POST',
//     data: {
//       studioID: studioID,
//       comment: comment,
//       isReply: isReply,
//       parentID: parentID
//     },
//     success: function (data, status) {
//       if (status === 'success') {
//         if (isReply) {
//           $('#add-reply').before(data.comment).find('input').val('')
//           $('#info-' + parentID).text(data.replies_count)
//         } else {
//           $('#add-comment').before(data.comment).find('input').val('')
//           $('#comments-count').text(data.comments_count)
//           $('#no-comments').hide()
//         }
//         $('#activities_count').text(data.activities_count)
//       } else {
//         // eslint-disable-next-line no-undef
//         showSnackbar('#share-snackbar', commentError)
//       }
//     },
//     fail: function () {
//       // eslint-disable-next-line no-undef
//       showSnackbar('#share-snackbar', commentError)
//     }
//   })
// }
//
// function loadReplies (commentID) {
//   $('#modal-body').html('')
//   $('#cmtID').val(commentID)
//   $.ajax({
//     url: '../loadCommentReplies/',
//     type: 'GET',
//     data: {
//       commentID: commentID
//     },
//     success: function (data, status) {
//       if (status === 'success') {
//         $('#comment-replies-body').html(data)
//       }
//     },
//     fail: function () {
//       $('#comment-replies-body').html('<h1>Failed to load replies</h1>')
//     }
//   })
// }
//
// function uploadCoverImage () {
//   const updateCoverError = $('#update-cover-error').val()
//   if ($('#std-header').val() !== '') {
//     $.ajax({
//       type: 'POST',
//       url: '../uploadStudioCover/',
//       cache: false,
//       processData: false,
//       contentType: false,
//       data: new FormData(document.getElementById('std-header-form')),
//       success: function (data, status) {
//         if (status === 'success') {
//           $('#studio-img-container').find('img').attr('src', data.new_cover)
//         } else {
//           showSnackbar('#share-snackbar', updateCoverError)
//         }
//       },
//       fail: function () {
//         showSnackbar('#share-snackbar', updateCoverError)
//       }
//     })
//   }
// }
// }

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

  const pendingJoinButton = document.querySelectorAll(
    '#pending-join-requests button.mdc-switch',
  )
  const buttonsDeclined = document.querySelectorAll(
    '#declined-join-requests button.mdc-switch',
  )
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
        clickedProjectsAdminRemove.length > 0
          ? JSON.stringify(clickedProjectsAdminRemove)
          : ''
    }
  })
  const ajaxRequestDeleteProject = document.getElementById(
    'ajaxRequestDeleteProject',
  )
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
