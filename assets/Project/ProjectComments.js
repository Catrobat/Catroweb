import Swal from 'sweetalert2'
import { getCookie } from '../Security/CookieHelper'

export function ProjectComments(
  programId,
  visibleComments,
  showStep,
  minAmountOfVisibleComments,
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
  let fetchActive = false
  let nextCursor = null
  let hasMore = true

  const projectComments = document.querySelector('.js-project-comments')
  const commentsWrapper = document.querySelector('#comments-wrapper')
  const commentsListUrl = projectComments?.dataset.pathCommentsListUrl
  const commentsBaseUrl = projectComments?.dataset.pathCommentsBaseUrl
  const parentCommentContainer = document.querySelector('.js-project-parentComment')
  const repliesParentId = parentCommentContainer?.dataset.parentCommentId
  const isRepliesPage = Boolean(parentCommentContainer && repliesParentId)

  const commentUploadDates = document.getElementsByClassName('comment-upload-date')
  for (const element of commentUploadDates) {
    const commentUploadDate = new Date(element.innerHTML)
    element.innerHTML = commentUploadDate.toLocaleString('en-GB')
  }

  loadMoreComments()

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
      import('../Moderation/ReportDialog').then(({ showReportDialog }) => {
        showReportDialog({
          contentType: reportButton.dataset.contentType || 'comment',
          contentId: reportButton.dataset.contentId,
          apiUrl: reportButton.dataset.reportUrl,
          loginUrl: projectComments?.dataset.pathLoginUrl,
          isLoggedIn: Boolean(getCookie('BEARER')),
          translations: {
            title: projectComments?.dataset.transReportTitle,
            submit: projectComments?.dataset.transReportSubmit,
            cancel: projectComments?.dataset.transReportCancel,
            success: projectComments?.dataset.transReportSuccess,
            error: projectComments?.dataset.transReportError,
            duplicate: projectComments?.dataset.transReportDuplicate,
            trustTooLow: projectComments?.dataset.transReportTrustTooLow,
            unverified: projectComments?.dataset.transReportUnverified,
            suspended: projectComments?.dataset.transReportSuspended,
            rateLimited: projectComments?.dataset.transReportRateLimited,
            notePlaceholder: projectComments?.dataset.transReportPlaceholder,
          },
        })
      })
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
      loadMoreComments()
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

    const postCommentUrl = projectComments?.dataset.pathCommentsListUrl
    if (!postCommentUrl) {
      showErrorPopUp(defaultErrorMessage)
      return
    }
    const parentCommentId =
      document.querySelector('.js-project-parentComment')?.dataset?.parentCommentId ?? 0

    const payload = {
      message: msg,
    }

    if (parentCommentId > 0) {
      payload.parent_id = Number(parentCommentId)
    }

    fetch(postCommentUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: 'Bearer ' + getCookie('BEARER'),
      },
      body: JSON.stringify(payload),
    })
      .then((response) => {
        if (response.ok) {
          return response.json()
        } else if (response.status === 401) {
          redirectToLogin()
          return undefined
        } else if (response.status === 429) {
          const msg =
            projectComments?.dataset.transRateLimited ||
            "You're posting comments too quickly. Please wait a moment."
          showPopUp('warning', msg)
          return undefined
        } else if (response.status === 403) {
          return response
            .json()
            .then((body) => {
              if (body?.error === 'Email verification required.') {
                const msg =
                  projectComments?.dataset.transAccountNotVerified ||
                  'Please make sure you are logged in and your account\u2019s email is verified.'
                showPopUp('warning', msg)
              } else if (body?.error === 'Your account has been suspended.') {
                const msg =
                  projectComments?.dataset.transAccountSuspended ||
                  'Your account has been suspended due to community reports.'
                showPopUp('warning', msg)
              } else {
                showErrorPopUp(defaultErrorMessage)
              }
            })
            .catch(() => showErrorPopUp(defaultErrorMessage))
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

  function buildListUrl() {
    const baseUrl = isRepliesPage
      ? `${commentsBaseUrl}/${repliesParentId}/replies`
      : commentsListUrl

    if (!baseUrl) return null

    const params = new URLSearchParams()
    params.set('limit', String(showStep || minAmountOfVisibleComments || 20))
    if (nextCursor) {
      params.set('cursor', nextCursor)
    }

    return `${baseUrl}?${params.toString()}`
  }

  function loadMoreComments() {
    if (fetchActive || !hasMore || !commentsWrapper) return

    const listUrl = buildListUrl()
    if (!listUrl) return

    fetchActive = true
    const loadHeaders = {}
    const bearerToken = getCookie('BEARER')
    if (bearerToken) {
      loadHeaders['Authorization'] = 'Bearer ' + bearerToken
    }
    fetch(listUrl, { headers: loadHeaders })
      .then((response) => {
        if (response.ok) {
          return response.json()
        }
        throw new Error('Network response was not ok')
      })
      .then((data) => {
        const comments = data?.data || []
        comments.forEach((comment) => {
          if (!comment?.rendered) return
          appendRenderedComment(comment.rendered)
        })

        nextCursor = data?.next_cursor || null
        hasMore = Boolean(data?.has_more)
      })
      .catch(() => {
        showErrorPopUp(defaultErrorMessage)
      })
      .finally(() => {
        fetchActive = false
      })
  }

  function appendRenderedComment(rendered) {
    const tempDiv = document.createElement('div')
    tempDiv.innerHTML = rendered
    const newComment = tempDiv.firstChild
    if (!newComment) return
    commentsWrapper.appendChild(newComment)
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
    if (!commentsBaseUrl) {
      showErrorPopUp(defaultErrorMessage)
      return
    }

    fetch(`${commentsBaseUrl}/${commentId}`, {
      method: 'DELETE',
      headers: {
        Authorization: 'Bearer ' + getCookie('BEARER'),
      },
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
}
