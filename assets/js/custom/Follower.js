/* eslint-env jquery */
/* global Swal */

// eslint-disable-next-line no-unused-vars
function Follower (unfollowUrl, followUrl, somethingWentWrongError, followError, unfollowError,
  visibleFollowing = 5, visibleFollowers = 5, showStep = 5,
  minAmountOfVisibleFollowers = 5, totalFollowing = 5, totalFollowers = 5) {
  const self = this
  let amountOfVisibleFollowing
  let amountOfVisibleFollowers
  self.unfollowUrl = unfollowUrl
  self.followUrl = followUrl
  self.somethingWentWrongError = somethingWentWrongError
  self.followError = followError
  self.unfollowError = unfollowError
  $(function () {
    amountOfVisibleFollowing = visibleFollowing
    amountOfVisibleFollowers = visibleFollowers
    restoreAmountOfVisibleFollowingFromSession()
    restoreAmountOfVisibleFollowersFromSession()
    if (amountOfVisibleFollowers < 5 && totalFollowers >= 5) {
      amountOfVisibleFollowers = 5
    }
    if (amountOfVisibleFollowing < 5 && totalFollowing >= 5) {
      amountOfVisibleFollowing = 5
    }
    updateFollowingVisibility()
    updateFollowingButtonVisibility()
    updateFollowersVisibility()
    updateFollowerButtonVisibility()
  })
  self.unfollow = function (id, username) {
    Swal.fire({
      title: 'Are you sure you want to unfollow ' + username,
      text: self.notificationDeleteAllMessage,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Unfollow ' + username,
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.value) {
        $.ajax({
          url: self.unfollowUrl + '/' + id,
          type: 'get',
          success: function (data) {
            if (!data.success) {
              if (data.message === 'Please login') {
                window.location.replace('fos_user_security_login')
              } else if (data.message === 'Cannot follow yourself') {
                window.location.replace('profile')
              }
              return
            }
            window.sessionStorage.setItem('visibleFollowers', JSON.stringify(amountOfVisibleFollowers))
            $('#no-following').load(window.location.href + ' #no-following>*')
            $('#following-cards').load(window.location.href + ' #following-cards>*')
            $('#follower-cards').load(window.location.href + ' #follower-cards>*')
            $('#follow-btn').load(window.location.href + ' #follow-btn>*')
            $('#user-information').load(window.location.href + ' #user-information>*')
            $('#new-notifications-container').load(window.location.href + ' #new-notifications-container>*', '')
            $('#old-notifications-container').load(window.location.href + ' #old-notifications-container>*', '')
            totalFollowing--
            if (amountOfVisibleFollowing > totalFollowing) {
              amountOfVisibleFollowing = totalFollowing
            }
            if (amountOfVisibleFollowing > 5) {
              amountOfVisibleFollowing = 5
            }
            restoreAmountOfVisibleFollowersFromSession()
            updateFollowingVisibility()
            updateFollowingButtonVisibility()
            if (amountOfVisibleFollowers > 5) {
              $('#show-more-followers-button').show()
              $('#show-less-followers-button').hide()
            }
          },
          error: function () {
            Swal.fire(somethingWentWrongError, unfollowError, 'error')
          }
        })
      }
    })
  }
  self.follow = function (id) {
    $.ajax({
      url: self.followUrl + '/' + id,
      type: 'get',
      success: function (data) {
        if (!data.success) {
          if (data.message === 'Please login') {
            window.location.replace('fos_user_security_login')
          } else if (data.message === 'Cannot follow yourself') {
            window.location.replace('profile')
          }
          return
        }
        window.sessionStorage.setItem('visibleFollowers', JSON.stringify(amountOfVisibleFollowers))
        window.sessionStorage.setItem('visibleFollowing', JSON.stringify(amountOfVisibleFollowing))
        $('#no-following').load(window.location.href + ' #no-following>*')
        $('#following-cards').load(window.location.href + ' #following-cards>*')
        $('#follower-cards').load(window.location.href + ' #follower-cards>*')
        $('#follow-btn').load(window.location.href + ' #follow-btn>*')
        $('#user-information').load(window.location.href + ' #user-information>*')
        $('#new-notifications-container').load(window.location.href + ' #new-notifications-container>*', '')
        $('#old-notifications-container').load(window.location.href + ' #old-notifications-container>*', '')
      },
      error: function () {
        Swal.fire(somethingWentWrongError, followError, 'error')
      }
    })
    totalFollowing++
    amountOfVisibleFollowing++
    if (amountOfVisibleFollowing > 5) {
      amountOfVisibleFollowing = 5
    }
    updateFollowingVisibility()
    updateFollowingButtonVisibility()
    restoreAmountOfVisibleFollowersFromSession()
    if (amountOfVisibleFollowers > 5) {
      $('#show-more-followers-button').show()
      $('#show-less-followers-button').hide()
    }
  }
  $(document).on('click', '#show-more-followers-button', function () {
    showMoreFollowers (showStep)
  })
  $(document).on('click', '#show-less-followers-button', function () {
    showLessFollowers (showStep)
  })
  $(document).on('click', '#show-more-following-button', function () {
    showMoreFollowing (showStep)
  })
  $(document).on('click', '#show-less-following-button', function () {
    showLessFollowing (showStep)
  })
  
  function restoreAmountOfVisibleFollowersFromSession () {
    let lastSessionAmount = JSON.parse(window.sessionStorage.getItem('visibleFollowers'))
    if (lastSessionAmount !== null) {
      amountOfVisibleFollowers = lastSessionAmount
    }
    if (amountOfVisibleFollowers > totalFollowers) {
      amountOfVisibleFollowers = totalFollowers
    }
  }
  
  function updateFollowersVisibility () {
    $('.single-follower').each(function(index, user2) {
      if (index < amountOfVisibleFollowers) {
        $(user2).show()
      }
      else {
        $(user2).hide()
      }
    })
  }
  
  function updateFollowerButtonVisibility () {
    if (amountOfVisibleFollowers > minAmountOfVisibleFollowers) {
      $('#show-less-followers-button').show()
    } else {
      $('#show-less-followers-button').hide()
    }
    if (amountOfVisibleFollowers < totalFollowers) {
      $('#show-more-followers-button').show()
    } else {
      $('#show-more-followers-button').hide()
    }
  }
  
  function restoreAmountOfVisibleFollowingFromSession () {
    let lastSessionAmount = JSON.parse(window.sessionStorage.getItem('visibleFollowing'))
    if (lastSessionAmount !== null) {
      amountOfVisibleFollowing = lastSessionAmount
    }
    if (amountOfVisibleFollowing > totalFollowing) {
      amountOfVisibleFollowing = totalFollowing
    }
  }
  
  function updateFollowingVisibility () {
    $('.single-following').each(function(index, user) {
      if (index < amountOfVisibleFollowing) {
        $(user).show()
      } else {
        $(user).hide()
      }
    })
  }
  
  function updateFollowingButtonVisibility () {
    if (amountOfVisibleFollowing > minAmountOfVisibleFollowers) {
      $('#show-less-following-button').show()
    } else {
      $('#show-less-following-button').hide()
    }
    if (amountOfVisibleFollowing < totalFollowing) {
      $('#show-more-following-button').show()
    } else {
      $('#show-more-following-button').hide()
    }
  }
  
  function showMoreFollowers (step) {
    amountOfVisibleFollowers = Math.min(amountOfVisibleFollowers + step, totalFollowers)
    window.sessionStorage.setItem('visibleFollowers', JSON.stringify(amountOfVisibleFollowers))
    updateFollowersVisibility()
    updateFollowerButtonVisibility()
  }
  
  function showLessFollowers (step) {
    amountOfVisibleFollowers = Math.max(amountOfVisibleFollowers - step,
      minAmountOfVisibleFollowers)
    window.sessionStorage.setItem('visibleFollowers', JSON.stringify(amountOfVisibleFollowers))
    updateFollowersVisibility()
    updateFollowerButtonVisibility()
  }
  
  function showMoreFollowing (step) {
    amountOfVisibleFollowing = Math.min(amountOfVisibleFollowing + step, totalFollowing)
    window.sessionStorage.setItem('visibleFollowing', JSON.stringify(amountOfVisibleFollowing))
    updateFollowingVisibility()
    updateFollowingButtonVisibility()
  }
  
  function showLessFollowing (step) {
    amountOfVisibleFollowing = Math.max(amountOfVisibleFollowing - step,
      minAmountOfVisibleFollowers)
    window.sessionStorage.setItem('visibleFollowing', JSON.stringify(amountOfVisibleFollowing))
    updateFollowingVisibility()
    updateFollowingButtonVisibility()
  }
}
