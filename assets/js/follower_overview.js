import $ from 'jquery'
import Swal from 'sweetalert2'

import './components/tab_bar'

require('../styles/custom/profile.scss')

const $followerOverview = $('.js-follower-overview')
let numberOfFollow = $followerOverview.data('number-of-following')
const emptyContainerMessage = $('#no-followers')

/**
 * Register EventListeners
 */
$(() => {
  $('.unfollow-btn').on('click', (e) => {
    e.preventDefault()
    e.stopImmediatePropagation()
    unfollow($(e.target).data('user-id'), $(e.target).data('user-name'))
  })

  $('.follow-btn').on('click', (e) => {
    e.preventDefault()
    e.stopImmediatePropagation()
    follow($(e.target).data('user-id'))
  })
})

/**
 *
 */
function unfollow (id, username) {
  const $followerItems = $('.follower-item-' + id)
  const $buttons = $followerItems.find('.btn-follow button').attr('disabled', true)

  Swal.fire({
    title: $followerOverview.data('unfollow-question'),
    icon: 'question',
    showCancelButton: true,
    allowOutsideClick: false,
    confirmButtonText: $followerOverview.data('unfollow-button').replace('%username%', username),
    cancelButtonText: $followerOverview.data('cancel-button'),
    customClass: {
      confirmButton: 'btn btn-primary',
      cancelButton: 'btn btn-outline-primary'
    },
    buttonsStyling: false
  }).then((result) => {
    if (result.value) {
      $.ajax({
        url: $followerOverview.data('unfollow-url') + '/' + id,
        type: 'delete',
        success: function () {
          $buttons.attr('disabled', false)
          --numberOfFollow
          if (numberOfFollow <= 0) {
            emptyContainerMessage.removeClass('d-none').addClass('d-block')
          }
          window.location.reload()
        },
        error: function (xhr) {
          handleError(xhr, $buttons)
        }
      })
    } else {
      $buttons.attr('disabled', false)
    }
  })
}

function follow (id) {
  const $followerItems = $('.follower-item-' + id)
  const $buttons = $followerItems.find('.btn-follow button')
  $buttons.attr('disabled', true)
  const url = $followerOverview.data('follow-url')

  $.ajax({
    url: url + '/' + id,
    type: 'post',
    success: function () {
      $buttons.attr('disabled', false)
      ++numberOfFollow
      window.location.reload()
      emptyContainerMessage.removeClass('d-block').addClass('d-none')
    },
    error: function (xhr) {
      handleError(xhr, $buttons)
    }
  })
}

function handleError (xhr, $buttons) {
  if (xhr.status === 401) {
    // a user must be logged in to (un)follow someone
    window.location.href = $followerOverview.data('login-url')
    return false
  }

  $buttons.attr('disabled', false)
  fireSomeThingWentWrongPopUp()
}

function fireSomeThingWentWrongPopUp () {
  Swal.fire({
    title: $followerOverview.data('something-went-wrong-error'),
    text: $followerOverview.data('follow-error'),
    icon: 'error',
    customClass: {
      confirmButton: 'btn btn-primary'
    },
    buttonsStyling: false,
    allowOutsideClick: false
  })
}
