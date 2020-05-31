/* eslint-env jquery */
/* global Swal */

// eslint-disable-next-line no-unused-vars
function Follower (csrfToken, unfollowUrl, followUrl, somethingWentWrongError, followError, unfollowError,
  unfollowButton, unfollowQuestion, cancelButton, numberOfFollow, privateView) {
  const self = this
  self.unfollowUrl = unfollowUrl
  self.followUrl = followUrl
  self.somethingWentWrongError = somethingWentWrongError
  self.followError = followError
  self.unfollowError = unfollowError
  self.unfollowButton = unfollowButton
  self.unfollowQuestion = unfollowQuestion
  self.cancelButton = cancelButton
  self.csrfToken = csrfToken

  // in the follower section only the following tab must be updated without a page reload(We could unfollow users)
  // on public profile pages we must update the followers section without a reload (We could follow the user)
  self.emptyContainerMessage = privateView ? $('#no-following') : $('#no-followers')

  self.unfollow = function (id, username) {
    const $followerItems = $('.follower-item-' + id)
    const $buttons = $followerItems.find('.btn-follow button').attr('disabled', true)

    Swal.fire({
      title: self.unfollowQuestion,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: self.unfollowButton.replace('%username%', username),
      cancelButtonText: self.cancelButton
    }).then((result) => {
      if (result.value) {
        $.ajax({
          url: self.unfollowUrl + '/' + id + '?token=' + encodeURIComponent(csrfToken),
          type: 'get',
          success: function () {
            $buttons.attr('disabled', false)
            --numberOfFollow
            if (numberOfFollow <= 0) {
              self.emptyContainerMessage.removeClass('d-none').addClass('d-block')
            }
            reloadSources()
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

  self.follow = function (id) {
    const $followerItems = $('.follower-item-' + id)
    const $buttons = $followerItems.find('.btn-follow button').attr('disabled', true)

    $.ajax({
      url: self.followUrl + '/' + id + '?token=' + encodeURIComponent(csrfToken),
      type: 'get',
      success: function () {
        $buttons.attr('disabled', false)
        ++numberOfFollow
        reloadSources()
        self.emptyContainerMessage.removeClass('d-block').addClass('d-none')
      },
      error: function (xhr) {
        handleError(xhr, $buttons)
      }
    })
  }

  function reloadSources () {
    $('#following-cards').load(window.location.href + ' #following-cards>*')
    $('#follower-cards').load(window.location.href + ' #follower-cards>*')
    $('#user-information').load(window.location.href + ' #user-information>*')
    $('#new-notifications-container').load(window.location.href + ' #new-notifications-container>*', '')
    $('#old-notifications-container').load(window.location.href + ' #old-notifications-container>*', '')
  }

  function handleError (xhr, $buttons) {
    if (xhr.status === 401) {
      // a user must be logged in to (un)follow someone
      window.location.assign('fos_user_security_login')
      return
    }
    if (xhr.status === 422) {
      // can't (un)follow yourself, or a user that does not exist
      window.location.assign('profile')
      return
    }
    $buttons.attr('disabled', false)
    Swal.fire(somethingWentWrongError, followError, 'error')
  }
}
