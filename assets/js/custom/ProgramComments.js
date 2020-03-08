/* eslint-env jquery */
/* global Swal */

// eslint-disable-next-line no-unused-vars
function ProgramComments (programId, visibleComments, showStep, minAmountOfVisibleComments,
  totalAmountOfComments, cancel, deleteIt, reportIt, areYouSure,
  noWayOfReturn, deleteConfirmation, reportConfirmation,
  popUpCommentReportedTitle, popUpCommentReportedText,
  popUpDeletedTitle, popUpDeletedText,
  noAdminRightsMessage, defaultErrorMessage,
  statusCodeOk, statusCodeNotLoggedIn, statusCodeNoAdminRights) {
  let amountOfVisibleComments

  $(function () {
    amountOfVisibleComments = visibleComments
    restoreAmountOfVisibleCommentsFromSession()
    updateCommentsVisibility()
    updateButtonVisibility()
  })

  $(document).on('click', '#comment-post-button', function () {
    postComment()
  })

  $(document).on('click', '.comment-delete-button', function () {
    const commentId = $(this).attr('id').substring('comment-delete-button-'.length)
    askForConfirmation(deleteComment, commentId, deleteConfirmation, deleteIt)
  })

  $(document).on('click', '.comment-report-button', function () {
    const commentId = $(this).attr('id').substring('comment-delete-button-'.length)
    askForConfirmation(reportComment, commentId, reportConfirmation, reportIt)
  })

  $(document).on('change', '#comment-message', function () {
    sessionStorage.setItem('temp_program_comment', $('#comment-message').val())
  })

  $(document).on('click', '.add-comment-button', function () {
    const commentWrapper = $('#user-comment-wrapper')
    const showCommentWrapperButton = $('#show-add-comment-button')
    const hideCommentWrapperButton = $('#hide-add-comment-button')
    if (commentWrapper.is(':visible')) {
      commentWrapper.slideUp()
      hideCommentWrapperButton.hide()
      showCommentWrapperButton.show()
    } else {
      commentWrapper.slideDown()
      showCommentWrapperButton.hide()
      hideCommentWrapperButton.show()
    }
  })

  $(document).on('click', '#show-more-comments-button', function () {
    showMore(showStep)
  })

  $(document).on('click', '#show-less-comments-button', function () {
    showLess(showStep)
  })

  if ((sessionStorage.getItem('temp_program_comment') != null) && (sessionStorage.getItem('temp_program_comment') !== '')) {
    document.getElementById('comment-message').value = sessionStorage.getItem('temp_program_comment')
    const commentWrapper = $('#user-comment-wrapper')
    const showCommentWrapperButton = $('#show-add-comment-button')
    const hideCommentWrapperButton = $('#hide-add-comment-button')
    commentWrapper.slideDown()
    showCommentWrapperButton.hide()
    hideCommentWrapperButton.show()
  }

  function postComment () {
    const msg = $('#comment-message').val()
    if (msg.length === 0) {
      return
    }
    $.ajax({
      url: '../comment',
      type: 'post',
      data: { Message: msg, ProgramId: programId },
      success: function (data) {
        if (data === statusCodeNotLoggedIn) {
          redirectToLogin()
        } else {
          $('#comments-wrapper').load(' #comments-wrapper')
          $('#comment-message').val('')
          sessionStorage.setItem('temp_program_comment', '')
          location.reload()
        }
      },
      error: function () {
        Swal.fire(defaultErrorMessage)
      }
    })
  }

  function deleteComment (commentId) {
    $.ajax({
      url: '../deleteComment',
      type: 'get',
      data: { ProgramId: programId, CommentId: commentId },
      success: function (data) {
        if (data === statusCodeNotLoggedIn) {
          redirectToLogin()
        } else if (data === statusCodeNoAdminRights) {
          Swal.fire(noAdminRightsMessage)
        } else {
          $('#comment-' + commentId).remove()
          showSuccessPopUp(popUpDeletedTitle, popUpDeletedText)
        }
      },
      error: function () {
        Swal.fire(defaultErrorMessage)
      }
    })
  }

  function reportComment (commentId) {
    $.ajax({
      url: '../reportComment',
      type: 'get',
      data: { ProgramId: programId, CommentId: commentId },
      success: function (data) {
        if (data === statusCodeNotLoggedIn) {
          redirectToLogin()
        } else {
          showSuccessPopUp(popUpCommentReportedTitle, popUpCommentReportedText)
        }
      },
      error: function () {
        Swal.fire(defaultErrorMessage)
      }
    })
  }

  function askForConfirmation (continueWithAction, commentId, text, okayText) {
    Swal.fire({
      title: areYouSure,
      html: text + '<br><br>' + noWayOfReturn,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: okayText,
      cancelButtonText: cancel
    }).then((result) => {
      if (result.value) {
        continueWithAction(commentId)
      }
    })
  }

  function showSuccessPopUp (title, text) {
    Swal.fire(
      {
        title: title,
        text: text,
        icon: 'success',
        confirmButtonClass: 'btn btn-success'
      }
    ).then(() => {
      location.reload()
    })
  }

  function redirectToLogin () {
    window.location.href = '../login'
  }

  function restoreAmountOfVisibleCommentsFromSession () {
    const lastSessionAmount = JSON.parse(window.sessionStorage.getItem('visibleComments'))
    if (lastSessionAmount !== null) {
      amountOfVisibleComments = lastSessionAmount
    }
    if (amountOfVisibleComments > totalAmountOfComments) {
      amountOfVisibleComments = totalAmountOfComments
    }
  }

  function updateCommentsVisibility () {
    $('.single-comment').each(function (index, comment) {
      if (index < amountOfVisibleComments) {
        $(comment).show()
      } else {
        $(comment).hide()
      }
    })
  }

  function updateButtonVisibility () {
    if (amountOfVisibleComments > minAmountOfVisibleComments) {
      $('#show-less-comments-button').show()
    } else {
      $('#show-less-comments-button').hide()
    }

    if (amountOfVisibleComments < totalAmountOfComments) {
      $('#show-more-comments-button').show()
    } else {
      $('#show-more-comments-button').hide()
    }
  }

  function showMore (step) {
    amountOfVisibleComments = Math.min(amountOfVisibleComments + step, totalAmountOfComments)
    window.sessionStorage.setItem('visibleComments', JSON.stringify(amountOfVisibleComments))
    updateCommentsVisibility()
    updateButtonVisibility()
  }

  function showLess (step) {
    amountOfVisibleComments = Math.max(amountOfVisibleComments - step, minAmountOfVisibleComments)
    window.sessionStorage.setItem('visibleComments', JSON.stringify(amountOfVisibleComments))
    updateCommentsVisibility()
    updateButtonVisibility()
  }
}
