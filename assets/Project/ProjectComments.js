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

  amountOfVisibleComments = visibleComments
  restoreAmountOfVisibleCommentsFromSession()
  updateCommentsVisibility()
  updateButtonVisibility()

  document
    .querySelector('#comment-post-button')
    .addEventListener('click', postComment)

  const singleComments = document.querySelectorAll('.single-comment')
  singleComments.forEach((singleComment) => {
    singleComment.addEventListener('click', function () {
      const path = singleComment.dataset.pathProjectComment
      if (path == null) return

      location.href = path
    })
  })

  const reportButtons = document.querySelectorAll('.comment-report-button')
  reportButtons.forEach((reportButton) => {
    reportButton.addEventListener('click', function (event) {
      event.stopPropagation()
      const commentId = reportButton.id.substring(
        'comment-report-button-'.length,
      )
      askForConfirmation(reportComment, commentId, reportConfirmation, reportIt)
    })
  })

  const deleteButtons = document.querySelectorAll('.comment-delete-button')
  deleteButtons.forEach((deleteButton) => {
    deleteButton.addEventListener('click', function (event) {
      event.stopPropagation()
      const commentId = deleteButton.id.substring(
        'comment-delete-button-'.length,
      )
      askForConfirmation(deleteComment, commentId, deleteConfirmation, deleteIt)
    })
  })

  document
    .querySelector('#comment-message')
    ?.addEventListener('change', function () {
      sessionStorage.setItem(
        'temp_project_comment',
        document.querySelector('#comment-message').value,
      )
    })

  const addCommentButtons = document.querySelectorAll('.add-comment-button')
  addCommentButtons.forEach((addCommentButton) => {
    addCommentButton.addEventListener('click', function () {
      const commentWrapper = document.querySelector('#user-comment-wrapper')
      const showCommentWrapperButton = document.querySelector(
        '#show-add-comment-button',
      )
      const hideCommentWrapperButton = document.querySelector(
        '#hide-add-comment-button',
      )
      if (commentWrapper.style.display !== 'none') {
        commentWrapper.style.display = 'none'
        hideCommentWrapperButton.style.display = 'none'
        showCommentWrapperButton.style.display = 'block'
      } else {
        commentWrapper.style.display = 'block'
        showCommentWrapperButton.style.display = 'none'
        hideCommentWrapperButton.style.display = 'block'
      }
    })
  })

  document
    .querySelector('#show-more-comments-button')
    ?.addEventListener('click', function () {
      showMore(showStep)
    })

  document
    .querySelector('#show-less-comments-button')
    ?.addEventListener('click', function () {
      showLess(showStep)
    })

  if (
    sessionStorage.getItem('temp_project_comment') != null &&
    sessionStorage.getItem('temp_project_comment') !== ''
  ) {
    document.querySelector('#comment-message').value = sessionStorage.getItem(
      'temp_project_comment',
    )
    const commentWrapper = document.querySelector('#user-comment-wrapper')
    const showCommentWrapperButton = document.querySelector(
      '#show-add-comment-button',
    )
    const hideCommentWrapperButton = document.querySelector(
      '#hide-add-comment-button',
    )
    commentWrapper.style.display = 'block'
    if (showCommentWrapperButton) {
      showCommentWrapperButton.style.display = 'none'
    }
    if (hideCommentWrapperButton) {
      hideCommentWrapperButton.style.display = 'block'
    }
  }

  function postComment() {
    const msg = document.querySelector('#comment-message').value
    if (msg.length === 0) {
      return
    }

    const postCommentUrl = document.querySelector('.js-project-comments')
      .dataset.pathPostCommentUrl
    const parentCommentId =
      document.querySelector('.js-project-parentComment')?.dataset
        ?.parentCommentId ?? 0

    fetch(postCommentUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        Message: msg,
        ProgramId: programId,
        ParentCommentId: parentCommentId,
      }),
    })
      .then((response) => {
        if (response.ok) {
          document.querySelector('#comments-wrapper').innerHTML = ''
          document.querySelector('#comment-message').value = ''
          sessionStorage.setItem('temp_project_comment', '')
          location.reload()
        } else if (response.status === 401) {
          redirectToLogin()
        } else {
          throw new Error('Network response was not ok')
        }
      })
      .catch(() => {
        showErrorPopUp(defaultErrorMessage)
      })
  }

  function deleteComment(commentId) {
    const projectComments = document.querySelector('.js-project-comments')
    const deleteCommentUrl = projectComments.dataset.pathDeleteCommentUrl

    fetch(deleteCommentUrl + '/' + commentId, {
      method: 'DELETE',
    })
      .then((response) => {
        if (response.ok) {
          document.querySelector('#comment-' + commentId).remove()
          showSuccessPopUp(popUpDeletedTitle, popUpDeletedText)
        } else if (response.status === 401) {
          redirectToLogin()
        } else if (response.status === 403) {
          showErrorPopUp(noAdminRightsMessage)
        } else {
          throw new Error('Network response was not ok')
        }
      })
      .catch(() => {
        showErrorPopUp(defaultErrorMessage)
      })
  }

  function reportComment(commentId) {
    const projectComments = document.querySelector('.js-project-comments')
    const reportCommentPath = projectComments.dataset.pathReportCommentUrl

    fetch(reportCommentPath + '/' + commentId, {
      method: 'DELETE',
    })
      .then((response) => {
        if (response.ok) {
          showSuccessPopUp(popUpCommentReportedTitle, popUpCommentReportedText)
        } else if (response.status === 401) {
          redirectToLogin()
        } else {
          throw new Error('Network response was not ok')
        }
      })
      .catch(() => {
        showErrorPopUp(defaultErrorMessage)
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

  const replyButtons = document.querySelectorAll('.add-reply-button')
  replyButtons.forEach((replyButton) => {
    replyButton.addEventListener('click', function () {
      const commentWrapper = document.querySelector('#user-comment-wrapper')
      const showCommentWrapperButton = document.querySelector(
        '#show-add-reply-button',
      )
      const hideCommentWrapperButton = document.querySelector(
        '#hide-add-reply-button',
      )
      if (commentWrapper.style.display !== 'none') {
        commentWrapper.style.display = 'none'
        if (showCommentWrapperButton) {
          showCommentWrapperButton.style.display = 'block'
        }
        if (hideCommentWrapperButton) {
          hideCommentWrapperButton.style.display = 'none'
        }
      } else {
        commentWrapper.style.display = 'block'
        if (showCommentWrapperButton) {
          showCommentWrapperButton.style.display = 'none'
        }
        if (hideCommentWrapperButton) {
          hideCommentWrapperButton.style.display = 'block'
        }
      }
      window.location.hash = 'user-comment-wrapper'
    })
  })

  function redirectToLogin() {
    const projectComments = document.querySelector('.js-project-comments')
    window.location.href = projectComments.dataset.pathLoginUrl
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
    const commentsClassSelector = document.querySelector(
      '.comments-class-selector',
    ).dataset.commentsClassSelector

    document
      .querySelectorAll(commentsClassSelector)
      .forEach((comment, index) => {
        if (index < amountOfVisibleComments) {
          comment.style.display = 'block'
        } else {
          comment.style.display = 'none'
        }
      })
  }

  function updateButtonVisibility() {
    const showLessCommentsButton = document.querySelector(
      '#show-less-comments-button',
    )
    const showMoreCommentsButton = document.querySelector(
      '#show-more-comments-button',
    )

    if (amountOfVisibleComments > minAmountOfVisibleComments) {
      showLessCommentsButton.style.display = 'block'
    } else {
      showLessCommentsButton.style.display = 'none'
    }

    if (amountOfVisibleComments < totalAmountOfComments) {
      showMoreCommentsButton.style.display = 'block'
    } else {
      showMoreCommentsButton.style.display = 'none'
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
    return document.querySelector('.session-vars-names').dataset
      .visibleCommentsSessionVar
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
