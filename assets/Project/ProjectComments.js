import Swal from 'sweetalert2'

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
  const commentDetailUrlTemplate = projectComments?.dataset.commentDetailUrlTemplate
  const profileUrlTemplate = projectComments?.dataset.profileUrlTemplate
  const translateCommentUrlTemplate = projectComments?.dataset.translateCommentUrlTemplate
  const currentUserId = projectComments?.dataset.currentUserId ?? ''
  const isAdmin = projectComments?.dataset.isAdmin === 'true'
  const parentCommentContainer = document.querySelector('.js-project-parentComment')
  const repliesParentId = parentCommentContainer?.dataset.parentCommentId
  const isRepliesPage = Boolean(parentCommentContainer && repliesParentId)
  const isLoggedIn = projectComments?.dataset.isLoggedIn === 'true'

  const commentUploadDates = document.getElementsByClassName('comment-upload-date')
  for (const element of commentUploadDates) {
    const commentUploadDate = new Date(element.textContent || '')
    element.textContent = commentUploadDate.toLocaleString('en-GB')
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
          isLoggedIn,
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
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
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
              if (body?.error?.message === 'Email verification required.') {
                const msg =
                  projectComments?.dataset.transAccountNotVerified ||
                  'Please make sure you are logged in and your account\u2019s email is verified.'
                showPopUp('warning', msg)
              } else if (body?.error?.message === 'Your account has been suspended.') {
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
        if (!data) return

        const commentsWrapper = document.querySelector('#comments-wrapper')
        if (commentsWrapper) {
          const newComment = createCommentElement(data, parentCommentId > 0)
          if (!newComment) return

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
            uploadDateElement.textContent = formatCommentDate(data.created_at)
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
    fetch(listUrl, { credentials: 'same-origin' })
      .then((response) => {
        if (response.ok) {
          return response.json()
        }
        throw new Error('Network response was not ok')
      })
      .then((data) => {
        const comments = data?.data || []
        comments.forEach((comment) => {
          appendRenderedComment(comment, isRepliesPage || comment?.parent_id > 0)
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

  function appendRenderedComment(comment, isReply) {
    const newComment = createCommentElement(comment, isReply)
    if (!newComment) return
    commentsWrapper.appendChild(newComment)
  }

  function createCommentElement(comment, isReply) {
    if (!comment?.id || !comment?.user) return null

    const commentElement = document.createElement('div')
    commentElement.id = `comment-${comment.id}`
    commentElement.dataset.commentId = String(comment.id)
    commentElement.className = isReply ? 'single-reply' : 'single-comment'

    const commentDetailUrl = buildCommentDetailUrl(comment.id)
    if (commentDetailUrl) {
      commentElement.dataset.pathProjectComment = commentDetailUrl
    }

    const commentAvatar = document.createElement('div')
    commentAvatar.className = 'comment-avatar'

    const avatarLink = document.createElement('a')
    avatarLink.href = buildProfileUrl(comment.user.id)

    const avatarImage = document.createElement('img')
    avatarImage.className = 'comment-avatar-img'
    avatarImage.src = comment.user.avatar || '/images/default/avatar_default.png'
    avatarImage.alt = 'Avatar'
    avatarLink.appendChild(avatarImage)
    commentAvatar.appendChild(avatarLink)

    const payloadWrapper = document.createElement('div')
    payloadWrapper.className = 'comment-payload-wrapper'

    const commentHeader = document.createElement('div')
    commentHeader.className = 'comment-header'

    const userInfo = document.createElement('div')
    userInfo.className = 'comment-user-info'

    const userLink = document.createElement('a')
    userLink.href = buildProfileUrl(comment.user.id)
    userLink.className = 'usr-name no-overflow'

    const userName = document.createElement('span')
    userName.id = `profile-comment-user-id-${comment.user.id}`
    userName.textContent = comment.user.username || ''
    userLink.appendChild(userName)

    const commentMeta = document.createElement('div')
    commentMeta.className = 'comment-meta'

    const commentIcon = document.createElement('i')
    commentIcon.className = 'material-icons'
    commentIcon.textContent = 'access_time_filled'

    const uploadDate = document.createElement('span')
    uploadDate.className = 'comment-upload-date'
    uploadDate.textContent = formatCommentDate(comment.created_at)

    commentMeta.appendChild(commentIcon)
    commentMeta.appendChild(uploadDate)
    userInfo.appendChild(userLink)
    userInfo.appendChild(commentMeta)

    const commentActions = document.createElement('div')
    commentActions.className = 'comment-actions d-flex align-items-center gap-2'

    const replyAction = document.createElement('div')
    replyAction.className = 'comment-reply-action'

    const replyButton = document.createElement('span')
    replyButton.className = 'comment-reply-button catro-icon-button'
    replyButton.dataset.bsToggle = 'tooltip'
    replyButton.title = projectComments?.dataset.transReply || 'Reply'

    const replyIcon = document.createElement('i')
    replyIcon.className = 'material-icons'
    replyIcon.textContent = 'reply'
    replyButton.appendChild(replyIcon)
    replyAction.appendChild(replyButton)
    commentActions.appendChild(replyAction)

    if (comment.reply_count !== undefined && comment.reply_count !== null) {
      const repliesCountWrapper = document.createElement('div')
      repliesCountWrapper.className = 'comment-replies-count'

      const repliesIcon = document.createElement('i')
      repliesIcon.className = 'material-icons'
      repliesIcon.textContent = 'comment'

      const repliesCount = document.createElement('span')
      repliesCount.textContent = String(comment.reply_count)

      repliesCountWrapper.appendChild(repliesIcon)
      repliesCountWrapper.appendChild(repliesCount)
      commentActions.appendChild(repliesCountWrapper)
    }

    const isOwnComment = String(comment.user.id) === String(currentUserId)
    const isDeleted = Boolean(comment.is_deleted)

    if (!isDeleted && !isOwnComment) {
      const translationActions = document.createElement('div')
      translationActions.className = 'comment-translation-actions'

      const translationButton = document.createElement('span')
      translationButton.id = `comment-translation-button-${comment.id}`
      translationButton.className = 'comment-translation-button catro-icon-button'
      translationButton.dataset.bsToggle = 'tooltip'
      translationButton.title = projectComments?.dataset.transShowTranslation || 'Show translation'

      const translationIcon = document.createElement('i')
      translationIcon.className = 'material-icons'
      translationIcon.textContent = 'translate'
      translationButton.appendChild(translationIcon)

      const loadingSpinner = createLoadingSpinner(`comment-translation-loading-spinner-${comment.id}`)

      const removeTranslationButton = document.createElement('span')
      removeTranslationButton.id = `remove-comment-translation-button-${comment.id}`
      removeTranslationButton.className = 'remove-comment-translation-button catro-icon-button'
      removeTranslationButton.dataset.bsToggle = 'tooltip'
      removeTranslationButton.title = projectComments?.dataset.transHideTranslation || 'Hide translation'
      removeTranslationButton.style.display = 'none'

      const removeTranslationIcon = document.createElement('i')
      removeTranslationIcon.className = 'material-icons'
      removeTranslationIcon.textContent = 'close'
      removeTranslationButton.appendChild(removeTranslationIcon)

      translationActions.appendChild(translationButton)
      translationActions.appendChild(loadingSpinner)
      translationActions.appendChild(removeTranslationButton)
      commentActions.appendChild(translationActions)
    }

    if (!isDeleted && (isAdmin || !isOwnComment || !isLoggedIn)) {
      const reportButton = document.createElement('a')
      reportButton.id = `comment-report-button-${comment.id}`
      reportButton.className = 'comment-report-button'
      reportButton.dataset.contentType = 'comment'
      reportButton.dataset.contentId = String(comment.id)
      reportButton.dataset.reportUrl = `/api/comments/${comment.id}/report`
      reportButton.dataset.bsToggle = 'tooltip'
      reportButton.title = projectComments?.dataset.transReport || 'Report'

      const reportIcon = document.createElement('i')
      reportIcon.className = 'material-icons'
      reportIcon.textContent = 'report'
      reportButton.appendChild(reportIcon)
      commentActions.appendChild(reportButton)
    }

    if (!isDeleted && (isAdmin || isOwnComment)) {
      const deleteButton = document.createElement('a')
      deleteButton.id = `comment-delete-button-${comment.id}`
      deleteButton.className = 'comment-delete-button'
      deleteButton.dataset.bsToggle = 'tooltip'
      deleteButton.title = projectComments?.dataset.transDeleteComment || 'Delete'

      const deleteIcon = document.createElement('i')
      deleteIcon.className = 'material-icons text-danger'
      deleteIcon.textContent = 'delete'
      deleteButton.appendChild(deleteIcon)
      commentActions.appendChild(deleteButton)
    }

    commentHeader.appendChild(userInfo)
    commentHeader.appendChild(commentActions)

    const commentTextWrapper = document.createElement('div')
    commentTextWrapper.id = `comment-text-wrapper-${comment.id}`
    commentTextWrapper.className = 'comment-text'

    if (isDeleted) {
      const deletedParagraph = document.createElement('p')
      const deletedLabel = document.createElement('span')
      deletedLabel.className = 'deleted-comment'
      deletedLabel.textContent = projectComments?.dataset.transDeletedComment || 'Deleted'
      deletedParagraph.appendChild(deletedLabel)
      commentTextWrapper.appendChild(deletedParagraph)
    } else {
      const commentParagraph = document.createElement('p')
      commentParagraph.id = `comment-text-${comment.id}`
      commentParagraph.textContent = comment.message || ''
      commentTextWrapper.appendChild(commentParagraph)
    }

    const translationContainer = document.createElement('div')
    translationContainer.className = 'comment-translation-container'

    if (!isDeleted && (!isOwnComment || !isLoggedIn)) {
      const translationPlaceholder = document.createElement('div')
      translationPlaceholder.className = 'comment-translation'
      translationPlaceholder.dataset.translateCommentId = `translate-comment-${comment.id}`
      const translateCommentUrl = buildTranslateCommentUrl(comment.id)
      if (translateCommentUrl) {
        translationPlaceholder.dataset.pathTranslateComment = translateCommentUrl
      }
      translationContainer.appendChild(translationPlaceholder)
    }

    const translationWrapper = document.createElement('div')
    translationWrapper.id = `comment-translation-wrapper-${comment.id}`
    translationWrapper.className = 'comment-translation-wrapper'
    translationWrapper.style.display = 'none'

    const creditWrapper = document.createElement('div')
    creditWrapper.id = `comment-translation-credit-wrapper-${comment.id}`
    creditWrapper.className = 'translation-credit-wrapper'

    const beforeLanguages = document.createElement('span')
    beforeLanguages.id = `comment-translation-before-languages-${comment.id}`
    beforeLanguages.className = 'translation-credit'

    const firstLanguage = document.createElement('span')
    firstLanguage.id = `comment-translation-first-language-${comment.id}`
    firstLanguage.className = 'translation-credit'

    const betweenLanguages = document.createElement('span')
    betweenLanguages.id = `comment-translation-between-languages-${comment.id}`
    betweenLanguages.className = 'translation-credit'

    const secondLanguage = document.createElement('span')
    secondLanguage.id = `comment-translation-second-language-${comment.id}`
    secondLanguage.className = 'translation-credit'

    const afterLanguages = document.createElement('span')
    afterLanguages.id = `comment-translation-after-languages-${comment.id}`
    afterLanguages.className = 'translation-credit'

    creditWrapper.appendChild(beforeLanguages)
    creditWrapper.appendChild(firstLanguage)
    creditWrapper.appendChild(betweenLanguages)
    creditWrapper.appendChild(secondLanguage)
    creditWrapper.appendChild(afterLanguages)

    const translatedParagraph = document.createElement('p')
    translatedParagraph.id = `comment-text-translation-${comment.id}`
    translatedParagraph.className = 'comment-text-translation'
    translatedParagraph.lang = ''

    translationWrapper.appendChild(creditWrapper)
    translationWrapper.appendChild(translatedParagraph)
    translationContainer.appendChild(translationWrapper)

    payloadWrapper.appendChild(commentHeader)
    payloadWrapper.appendChild(commentTextWrapper)
    payloadWrapper.appendChild(translationContainer)

    commentElement.appendChild(commentAvatar)
    commentElement.appendChild(payloadWrapper)

    return commentElement
  }

  function createLoadingSpinner(spinnerId) {
    const spinnerWrapper = document.createElement('span')
    spinnerWrapper.className = 'comment-translation-loading-spinner'
    spinnerWrapper.id = spinnerId
    spinnerWrapper.style.display = 'none'

    spinnerWrapper.innerHTML = `
      <div class="circular-progress">
        <div class="mdc-circular-progress mdc-circular-progress--indeterminate" style="width:24px;height:24px;" role="progressbar" aria-label="Circular Progress Bar" aria-valuemin="0" aria-valuemax="1">
          <div class="mdc-circular-progress__indeterminate-container">
            <div class="mdc-circular-progress__spinner-layer">
              <div class="mdc-circular-progress__circle-clipper mdc-circular-progress__circle-left">
                <svg class="mdc-circular-progress__indeterminate-circle-graphic" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="12" cy="12" r="8.75" stroke-dasharray="54.978" stroke-dashoffset="27.489" stroke-width="2.5"></circle>
                </svg>
              </div>
              <div class="mdc-circular-progress__gap-patch">
                <svg class="mdc-circular-progress__indeterminate-circle-graphic" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="12" cy="12" r="8.75" stroke-dasharray="54.978" stroke-dashoffset="27.489" stroke-width="2"></circle>
                </svg>
              </div>
              <div class="mdc-circular-progress__circle-clipper mdc-circular-progress__circle-right">
                <svg class="mdc-circular-progress__indeterminate-circle-graphic" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="12" cy="12" r="8.75" stroke-dasharray="54.978" stroke-dashoffset="27.489" stroke-width="2.5"></circle>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>
    `

    return spinnerWrapper
  }

  function buildCommentDetailUrl(commentId) {
    if (!commentDetailUrlTemplate) return null

    return commentDetailUrlTemplate.replace('__COMMENT_ID__', String(commentId))
  }

  function buildProfileUrl(userId) {
    if (!profileUrlTemplate) return '#'

    return profileUrlTemplate.replace('__USER_ID__', String(userId))
  }

  function buildTranslateCommentUrl(commentId) {
    if (!translateCommentUrlTemplate) return null

    return translateCommentUrlTemplate.replace('__COMMENT_ID__', String(commentId))
  }

  function formatCommentDate(commentDate) {
    if (!commentDate) return ''

    const date = new Date(commentDate)
    return Number.isNaN(date.getTime()) ? String(commentDate) : date.toLocaleString('en-GB')
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
      const icon = document.createElement('i')
      icon.className = 'material-icons'
      icon.textContent = 'comment'
      const span = document.createElement('span')
      span.textContent = String(delta)
      div.appendChild(icon)
      div.appendChild(span)
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
      credentials: 'same-origin',
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
              textWrapper.replaceChildren()
              const deletedParagraph = document.createElement('p')
              const deletedSpan = document.createElement('span')
              deletedSpan.className = 'deleted-comment'
              deletedSpan.textContent = deletedText
              deletedParagraph.appendChild(deletedSpan)
              textWrapper.appendChild(deletedParagraph)
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
