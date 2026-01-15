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
  let fetchActive = false

  const commentUploadDates = document.getElementsByClassName('comment-upload-date')
  for (const element of commentUploadDates) {
    const commentUploadDate = new Date(element.innerHTML)
    element.innerHTML = commentUploadDate.toLocaleString('en-GB')
  }

  amountOfVisibleComments = visibleComments
  restoreAmountOfVisibleCommentsFromSession()
  updateCommentsVisibility()

  // If we are on a comment detail page, ensure the parent comment shows the reply count
  try {
    const parentCommentEl = document.querySelector('.js-project-parentComment')
    if (parentCommentEl) {
      const parentId = parentCommentEl.dataset.parentCommentId
      const commentsData = document.querySelector('.js-project-comments')
      if (parentId && commentsData) {
        const repliesCount = parseInt(commentsData.dataset.totalNumberOfComments || '0', 10)
        if (!Number.isNaN(repliesCount)) {
          setReplyCount(parentId, repliesCount)
        }
      }
    }
  } catch (e) {
    // non-fatal
    console.warn('Failed to initialize parent reply count', e)
  }

  document.querySelector('#comment-post-button')?.addEventListener('click', postComment)

  // Prefer the full project comments container (covers parent-comment-container on detail page).
  // Fallback to comments-wrapper for pages that only render the list.
  // const commentsContainer = document.querySelector('#project-comments') || document.querySelector('#comments-wrapper')

  // Attach to document so clicks on comment detail pages (parent-comment-container)
  // are also captured. We narrow handling by checking closest('.single-comment, .single-reply').
  document.addEventListener('click', function (event) {
    const singleComment = event.target.closest('.single-comment, .single-reply')
    if (!singleComment) return

    // Handle Reply Button
    const replyButton = event.target.closest('.comment-reply-button')
    if (replyButton) {
      event.stopPropagation()
      // If we are on a detail page there is a .js-project-parentComment container
      // and we want to reply to the currently clicked comment. To support replies
      // on the detail page and replies-to-replies, set the parentCommentId
      // on the .js-project-parentComment element from the clicked comment.
      const parentCommentContainer = document.querySelector('.js-project-parentComment')
      if (parentCommentContainer) {
        // Determine the id of the comment we want to reply to. Prefer the
        // explicit data-comment-id attribute, fallback to parsing the element id.
        const targetCommentId =
          singleComment.dataset.commentId ||
          (singleComment.id ? singleComment.id.replace(/^comment-/, '') : null)
        if (targetCommentId) {
          parentCommentContainer.dataset.parentCommentId = targetCommentId
        }
        // Show the reply input
        showAndFocusCommentInput()
      } else {
        // In Overview: navigate to the comment detail page
        const path = singleComment.dataset.pathProjectComment
        if (path) {
          location.href = path
        }
      }
      return
    }

    const commentTranslationActions = event.target.closest('.comment-translation-actions')

    // If it's an action, don't trigger the click on the whole comment
    if (
      event.target.closest('.comment-report-button') ||
      event.target.closest('.comment-delete-button') ||
      commentTranslationActions ||
      event.target.closest('.remove-comment-translation-button') ||
      event.target.tagName === 'A' ||
      event.target.tagName === 'I'
    ) {
      return
    }

    const path = singleComment.dataset.pathProjectComment
    if (path == null) return

    location.href = path
  })

  // Separate listener for report/delete actions (also on document)
  document.addEventListener('click', function (event) {
    const reportButton = event.target.closest('.comment-report-button')
    if (reportButton) {
      event.stopPropagation()
      const commentId = reportButton.id.substring('comment-report-button-'.length)
      askForConfirmation(reportComment, commentId, reportConfirmation, reportIt)
    }

    const deleteButton = event.target.closest('.comment-delete-button')
    if (deleteButton) {
      event.stopPropagation()
      const commentId = deleteButton.id.substring('comment-delete-button-'.length)
      askForConfirmation(deleteComment, commentId, deleteConfirmation, deleteIt)
    }
  })

  const commentMessage = document.querySelector('#comment-message')
  const commentButtonsContainer = document.querySelector('#comment-buttons-container')
  const commentCancelButton = document.querySelector('#comment-cancel-button')

  if (commentMessage) {
    commentMessage.addEventListener('focus', function () {
      commentButtonsContainer.style.display = 'flex'
      commentMessage.style.height = '100px'
    })

    commentMessage.addEventListener('input', function () {
      sessionStorage.setItem('temp_project_comment', commentMessage.value)
    })

    // Hide buttons if focused out and empty
    commentMessage.addEventListener('blur', function () {
      if (commentMessage.value === '') {
        // Use a small timeout to allow click on Cancel/Post buttons to register before hiding
        setTimeout(() => {
          if (
            document.activeElement !== commentCancelButton &&
            document.activeElement !== document.querySelector('#comment-post-button')
          ) {
            commentButtonsContainer.style.display = 'none'
            commentMessage.style.height = '40px'
            if (document.querySelector('#add-comment-button')) {
              document.querySelector('#user-comment-wrapper').style.display = 'none'
              const addCommentInitContainer = document.querySelector('#add-comment-init-container')
              if (addCommentInitContainer) {
                addCommentInitContainer.style.display = 'block'
              }
            }
          }
        }, 200)
      }
    })
  }

  if (commentCancelButton) {
    commentCancelButton.addEventListener('click', function () {
      commentMessage.value = ''
      sessionStorage.setItem('temp_project_comment', '')
      commentButtonsContainer.style.display = 'none'
      commentMessage.style.height = '40px' // Reset height
      if (document.querySelector('#add-comment-button')) {
        document.querySelector('#user-comment-wrapper').style.display = 'none'
        const addCommentInitContainer = document.querySelector('#add-comment-init-container')
        if (addCommentInitContainer) {
          addCommentInitContainer.style.display = 'block'
        }
      } else if (document.querySelector('.js-project-parentComment')) {
        document.querySelector('#user-comment-wrapper').style.display = 'none'
      }
    })
  }

  document.querySelector('#add-comment-button')?.addEventListener('click', function () {
    const userCommentWrapper = document.querySelector('#user-comment-wrapper')
    const addCommentInitContainer = document.querySelector('#add-comment-init-container')
    const commentMessage = document.querySelector('#comment-message')

    userCommentWrapper.style.display = 'flex'
    if (addCommentInitContainer) {
      addCommentInitContainer.style.display = 'none'
    }
    commentMessage.focus()
  })

  window.addEventListener('scroll', function () {
    if (fetchActive) return
    const position = window.scrollY
    const bottom = document.documentElement.scrollHeight - window.innerHeight
    if (bottom <= 0) return
    const pctVertical = position / bottom
    if (pctVertical >= 0.7) {
      if (amountOfVisibleComments < totalAmountOfComments) {
        fetchActive = true
        showMore(showStep)
        // Add a small delay to prevent multiple triggers
        setTimeout(() => {
          fetchActive = false
        }, 100)
      }
    }
  })

  if (
    sessionStorage.getItem('temp_project_comment') != null &&
    sessionStorage.getItem('temp_project_comment') !== ''
  ) {
    document.querySelector('#comment-message').value =
      sessionStorage.getItem('temp_project_comment')
    const commentButtonsContainer = document.querySelector('#comment-buttons-container')
    const userCommentWrapper = document.querySelector('#user-comment-wrapper')
    if (userCommentWrapper) {
      userCommentWrapper.style.display = 'flex'
      const addCommentInitContainer = document.querySelector('#add-comment-init-container')
      if (addCommentInitContainer) {
        addCommentInitContainer.style.display = 'none'
      }
    }
    if (commentButtonsContainer) {
      commentButtonsContainer.style.display = 'flex'
    }
  }

  function postComment() {
    const msg = document.querySelector('#comment-message').value
    if (msg.length === 0) {
      return
    }

    const postCommentUrl = document.querySelector('.js-project-comments').dataset.pathPostCommentUrl
    const parentCommentId =
      document.querySelector('.js-project-parentComment')?.dataset?.parentCommentId ?? 0

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
          return response.json()
        } else if (response.status === 401) {
          redirectToLogin()
        } else {
          throw new Error('Network response was not ok')
        }
      })
      .then((data) => {
        if (!data || !data.rendered) return

        const commentsWrapper = document.querySelector('#comments-wrapper')
        if (commentsWrapper) {
          const tempDiv = document.createElement('div')
          tempDiv.innerHTML = data.rendered
          const newComment = tempDiv.firstChild

          if (parentCommentId > 0) {
            commentsWrapper.appendChild(newComment)
            // Update the parent's visible reply count (+1)
            try {
              updateReplyCount(parentCommentId, 1)
            } catch (e) {
              // Non-fatal — keep posting flow intact
              console.warn('Failed to update reply count in UI', e)
            }
          } else {
            commentsWrapper.prepend(newComment)
            totalAmountOfComments++
            amountOfVisibleComments++
            setVisibleCommentsSessionVar()
          }

          // Format date for the new comment
          const uploadDateElement = newComment.querySelector('.comment-upload-date')
          if (uploadDateElement) {
            const commentUploadDate = new Date(uploadDateElement.innerHTML)
            uploadDateElement.innerHTML = commentUploadDate.toLocaleString('en-GB')
          }
        }

        document.querySelector('#comment-message').value = ''
        sessionStorage.setItem('temp_project_comment', '')
        document.querySelector('#comment-cancel-button').click()
      })
      .catch(() => {
        showErrorPopUp(defaultErrorMessage)
      })
  }

  // Helper to update/create the visible reply count for a parent comment
  function updateReplyCount(parentId, delta) {
    const parentSelector = '#comment-' + parentId
    const parentEl = document.querySelector(parentSelector)
    if (!parentEl) return

    const repliesCountWrapper = parentEl.querySelector('.comment-replies-count')
    if (repliesCountWrapper) {
      const span = repliesCountWrapper.querySelector('span')
      if (span) {
        const current = parseInt(span.textContent || '0', 10)
        span.textContent = Math.max(0, current + delta)
      }
    } else if (delta > 0) {
      // create the replies count element similar to template
      const actionsEl = parentEl.querySelector('.comment-actions')
      if (!actionsEl) return
      const div = document.createElement('div')
      div.className = 'comment-replies-count'
      div.innerHTML = `<i class="material-icons">comment</i><span>${delta}</span>`
      // Insert near the beginning of action area
      actionsEl.insertBefore(div, actionsEl.firstChild)
    }
  }

  // Set absolute count for a parent comment (create element if missing)
  function setReplyCount(parentId, count) {
    const parentSelector = '#comment-' + parentId
    const parentEl = document.querySelector(parentSelector)
    if (!parentEl) return

    let repliesCountWrapper = parentEl.querySelector('.comment-replies-count')
    if (!repliesCountWrapper) {
      const actionsEl = parentEl.querySelector('.comment-actions')
      if (!actionsEl) return
      repliesCountWrapper = document.createElement('div')
      repliesCountWrapper.className = 'comment-replies-count'
      repliesCountWrapper.innerHTML = `<i class="material-icons">comment</i><span>${count}</span>`
      actionsEl.insertBefore(repliesCountWrapper, actionsEl.firstChild)
      return
    }

    const span = repliesCountWrapper.querySelector('span')
    if (span) {
      span.textContent = String(Math.max(0, count))
    }
  }

  function setPopUpDeletedRefresh(refresh) {
    Swal.fire({
      title: popUpDeletedTitle,
      text: popUpDeletedText,
      icon: 'success',
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

  function deleteComment(commentId) {
    const projectComments = document.querySelector('.js-project-comments')
    const deleteCommentUrl = projectComments.dataset.pathDeleteCommentUrl

    fetch(deleteCommentUrl + '/' + commentId, {
      method: 'DELETE',
    })
      .then((response) => {
        if (response.ok) {
          // Instead of removing the comment element from the DOM, switch it to a deleted state
          const commentElement = document.querySelector('#comment-' + commentId)
          if (commentElement) {
            // replace text
            const deletedText =
              document.querySelector('.js-project-comments')?.dataset?.transDeletedComment ||
              'Deleted'
            const textWrapper = commentElement.querySelector('#comment-text-wrapper-' + commentId)
            if (textWrapper) {
              textWrapper.innerHTML = `<p><span class="deleted-comment">${deletedText}</span></p>`
            }

            // hide actions (reply, translate, report, delete) and show nothing or minimal UI
            const actions = commentElement.querySelector('.comment-actions')
            if (actions) {
              // Keep only reply action (.comment-reply-action) and replies count (.comment-replies-count)
              // Remove any other direct children (translation, report, delete buttons, etc.)
              Array.from(actions.children).forEach((child) => {
                const keep =
                  child.classList.contains('comment-reply-action') ||
                  child.classList.contains('comment-replies-count')
                if (!keep) child.remove()
              })
            }

            // mark as deleted for styles/logic
            commentElement.classList.add('deleted-comment-wrapper')
          }
          setPopUpDeletedRefresh(false)
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
      // If the page has a parentComment container, ensure its parentCommentId
      // points to the main comment (use the .js-project-parentComment dataset)
      const parentCommentContainer = document.querySelector('.js-project-parentComment')
      if (parentCommentContainer) {
        // If a data-parent-comment-id already exists in the DOM (e.g. template), keep it.
        // Otherwise try to find the single-comment inside #parent-comment-container.
        if (!parentCommentContainer.dataset.parentCommentId) {
          const parentCommentEl = document.querySelector(
            '#parent-comment-container .single-comment',
          )
          const pcid =
            parentCommentEl?.dataset?.commentId ||
            (parentCommentEl?.id ? parentCommentEl.id.replace(/^comment-/, '') : null)
          if (pcid) parentCommentContainer.dataset.parentCommentId = pcid
        }
      }
      showAndFocusCommentInput()
    })
  })

  function showAndFocusCommentInput() {
    const commentMessage = document.querySelector('#comment-message')
    const commentButtonsContainer = document.querySelector('#comment-buttons-container')
    const userCommentWrapper = document.querySelector('#user-comment-wrapper')
    const addCommentButton = document.querySelector('#add-comment-button')
    if (userCommentWrapper) {
      userCommentWrapper.style.display = 'flex'
      if (addCommentButton) {
        addCommentButton.parentElement.style.display = 'none'
      }
    }
    if (commentButtonsContainer) {
      commentButtonsContainer.style.display = 'flex'
    }
    if (commentMessage) {
      commentMessage.focus()
    }
  }

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
    const commentsClassSelectorElement = document.querySelector('.comments-class-selector')
    if (!commentsClassSelectorElement) return

    const commentsClassSelector = commentsClassSelectorElement.dataset.commentsClassSelector

    document.querySelectorAll(commentsClassSelector).forEach((comment, index) => {
      if (index < amountOfVisibleComments) {
        comment.style.display = 'flex'
      } else {
        comment.style.display = 'none'
      }
    })
    // Ensure parent comment is always visible if it exists
    const parentComment = document.querySelector('#parent-comment-container .single-comment')
    if (parentComment) {
      parentComment.style.display = 'flex'
    }
  }

  function showMore(step) {
    amountOfVisibleComments = Math.min(amountOfVisibleComments + step, totalAmountOfComments)
    setVisibleCommentsSessionVar()
    updateCommentsVisibility()
  }

  function getVisibleCommentsSessionVarName() {
    const sessionVarsNames = document.querySelector('.session-vars-names')
    return sessionVarsNames ? sessionVarsNames.dataset.visibleCommentsSessionVar : 'visibleComments'
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
