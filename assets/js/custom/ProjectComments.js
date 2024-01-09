import $ from 'jquery'
import Swal from 'sweetalert2'

export function ProjectComments(
  programId,
  visibleComments,
  showStep,
  minAmountOfVisibleComments,
  totalAmountOfComments,
  cancel,
  deleteIt,
  reportIt,
  areYouSure,
  noWayOfReturn,
  deleteConfirmation,
  reportConfirmation,
  popUpCommentReportedTitle,
  popUpCommentReportedText,
  popUpDeletedTitle,
  popUpDeletedText,
  noAdminRightsMessage,
  defaultErrorMessage,
) {
  let amountOfVisibleComments

  const commentUploadDates = document.getElementsByClassName(
    'comment-upload-date',
  )
  for (const element of commentUploadDates) {
    const commentUploadDate = new Date(element.innerHTML)
    element.innerHTML = commentUploadDate.toLocaleString('en-GB')
  }

  $(function () {
    amountOfVisibleComments = visibleComments
    restoreAmountOfVisibleCommentsFromSession()
    updateCommentsVisibility()
    updateButtonVisibility()
  })

  $(document).on('click', '#comment-post-button', function () {
    postComment()
  })

  $('.single-comment').on('click', function () {
    const path = $(this).data('path-project-comment')
    if (path == null) return

    location.href = path
  })

  $('.comment-report-button').on('click', function (event) {
    event.stopPropagation()
    const commentId = $(this)
      .attr('id')
      .substring('comment-report-button-'.length)
    askForConfirmation(reportComment, commentId, reportConfirmation, reportIt)
  })

  $('.comment-delete-button').on('click', function (event) {
    event.stopPropagation()
    const commentId = $(this)
      .attr('id')
      .substring('comment-delete-button-'.length)
    askForConfirmation(deleteComment, commentId, deleteConfirmation, deleteIt)
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

  if (
    sessionStorage.getItem('temp_program_comment') != null &&
    sessionStorage.getItem('temp_program_comment') !== ''
  ) {
    document.getElementById('comment-message').value = sessionStorage.getItem(
      'temp_program_comment',
    )
    const commentWrapper = $('#user-comment-wrapper')
    const showCommentWrapperButton = $('#show-add-comment-button')
    const hideCommentWrapperButton = $('#hide-add-comment-button')
    commentWrapper.slideDown()
    showCommentWrapperButton.hide()
    hideCommentWrapperButton.show()
  }

  function postComment() {
    const msg = $('#comment-message').val()
    if (msg.length === 0) {
      return
    }

    const postCommentUrl = $('.js-project-comments').data(
      'path-post-comment-url',
    )
    const parentCommentId = $('.js-project-parentComment').data(
      'parent-comment-id',
    )
    $.ajax({
      url: postCommentUrl,
      type: 'post',
      data: {
        Message: msg,
        ProgramId: programId,
        ParentCommentId: parentCommentId,
      },
      success: function () {
        $('#comments-wrapper').load(' #comments-wrapper')
        $('#comment-message').val('')
        sessionStorage.setItem('temp_program_comment', '')
        location.reload()
      },
      error: function (data) {
        if (data.status === 401) {
          redirectToLogin()
        } else {
          showErrorPopUp(defaultErrorMessage)
        }
      },
    })
  }

  function deleteComment(commentId) {
    const $projectComments = $('.js-project-comments')
    const deleteCommentUrl = $projectComments.data('path-delete-comment-url')
    $.ajax({
      url: deleteCommentUrl,
      type: 'get',
      data: { ProgramId: programId, CommentId: commentId },
      success: function () {
        $('#comment-' + commentId).remove()
        showSuccessPopUp(popUpDeletedTitle, popUpDeletedText)
      },
      error: function (data) {
        if (data.status === 401) {
          redirectToLogin()
        } else if (data.status === 403) {
          showErrorPopUp(noAdminRightsMessage)
        } else {
          showErrorPopUp(defaultErrorMessage)
        }
      },
    })
  }

  function reportComment(commentId) {
    const $projectComments = $('.js-project-comments')
    const reportCommentPath = $projectComments.data('path-report-comment-url')
    $.ajax({
      url: reportCommentPath,
      type: 'get',
      data: { ProgramId: programId, CommentId: commentId },
      success: function () {
        showSuccessPopUp(popUpCommentReportedTitle, popUpCommentReportedText)
      },
      error: function (data) {
        if (data.status === 401) {
          redirectToLogin()
        } else {
          showErrorPopUp(defaultErrorMessage)
        }
      },
    })
  }

  function askForConfirmation(continueWithAction, commentId, text, okayText) {
    Swal.fire({
      title: areYouSure,
      html: text + '<br><br>' + noWayOfReturn,
      icon: 'warning',
      showCancelButton: true,
      allowOutsideClick: false,
      customClass: {
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-outline-primary',
      },
      buttonsStyling: false,
      confirmButtonText: okayText,
      cancelButtonText: cancel,
    }).then((result) => {
      if (result.value) {
        continueWithAction(commentId)
      }
    })
  }

  function showSuccessPopUp(title, text) {
    showPopUp('success', title, text, true)
  }

  function showErrorPopUp(title, text) {
    showPopUp('error', title, text)
  }

  function showPopUp(type, title, text, refresh = false) {
    Swal.fire({
      title,
      text,
      icon: type,
      customClass: {
        confirmButton: 'btn btn-primary',
      },
      buttonsStyling: false,
      allowOutsideClick: false,
    }).then(() => {
      if (refresh) {
        location.reload()
      }
    })
  }

  $(document).on('click', '.add-reply-button', function () {
    const commentWrapper = $('#user-comment-wrapper')
    const showCommentWrapperButton = $('#show-add-reply-button')
    const hideCommentWrapperButton = $('#hide-add-reply-button')
    if (!commentWrapper.is(':visible')) {
      commentWrapper.slideDown()
      showCommentWrapperButton.hide()
      hideCommentWrapperButton.show()
    }
    window.location = '#user-comment-wrapper'
  })

  function redirectToLogin() {
    const $projectComments = $('.js-project-comments')
    window.location.href = $projectComments.data('path-login-url')
  }

  function restoreAmountOfVisibleCommentsFromSession() {
    const lastSessionAmount = getVisibleCommentsSessionVar()
    if (lastSessionAmount !== null) {
      amountOfVisibleComments = lastSessionAmount
    }
    if (amountOfVisibleComments > totalAmountOfComments) {
      amountOfVisibleComments = totalAmountOfComments
    }
  }

  function updateCommentsVisibility() {
    const $commentsClassSelector = $('.comments-class-selector').data(
      'comments-class-selector',
    )

    $($commentsClassSelector).each(function (index, comment) {
      if (index < amountOfVisibleComments) {
        $(comment).show()
      } else {
        $(comment).hide()
      }
    })
  }

  function updateButtonVisibility() {
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

  function showMore(step) {
    amountOfVisibleComments = Math.min(
      amountOfVisibleComments + step,
      totalAmountOfComments,
    )
    setVisibleCommentsSessionVar()
    updateCommentsVisibility()
    updateButtonVisibility()
  }

  function showLess(step) {
    amountOfVisibleComments = Math.max(
      amountOfVisibleComments - step,
      minAmountOfVisibleComments,
    )
    setVisibleCommentsSessionVar()
    updateCommentsVisibility()
    updateButtonVisibility()
  }

  function getVisibleCommentsSessionVarName() {
    return $('.session-vars-names').data('visible-comments-session-var')
  }

  function setVisibleCommentsSessionVar() {
    const visibleCommentSessionVarName = getVisibleCommentsSessionVarName()
    window.sessionStorage.setItem(
      visibleCommentSessionVarName,
      JSON.stringify(amountOfVisibleComments),
    )
  }

  function getVisibleCommentsSessionVar() {
    const visibleCommentSessionVarName = getVisibleCommentsSessionVarName()
    return JSON.parse(
      window.sessionStorage.getItem(
        visibleCommentSessionVarName,
        JSON.stringify(amountOfVisibleComments),
      ),
    )
  }
}
