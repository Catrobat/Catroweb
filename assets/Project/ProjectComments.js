import Swal from 'sweetalert2'

export function ProjectComments(config) {
  const {
    showStep,
    minAmountOfVisibleComments,
    cancel,
    deleteIt,
    areYouSure,
    noWayOfReturn,
    deleteConfirmation,
    popUpDeletedTitle,
    popUpDeletedText,
    noAdminRightsMessage,
    defaultErrorMessage,
  } = config
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
  const isLoggedIn = projectComments?.dataset.isLoggedIn === 'true'
  const isAdmin = projectComments?.dataset.isAdmin === 'true'
  const currentUserId = projectComments?.dataset.currentUserId || ''
  const isMinor = projectComments?.dataset.currentUserIsMinor === 'true'
  const defaultAvatarUrl = projectComments?.dataset.defaultAvatarUrl || ''
  const profileUrlTemplate = projectComments?.dataset.profileUrlTemplate || ''
  const commentDetailUrlTemplate = projectComments?.dataset.commentDetailUrlTemplate || ''
  const translateUrlTemplate = projectComments?.dataset.translateUrlTemplate || ''
  const transReply = projectComments?.dataset.transReply || ''
  const transShowTranslation = projectComments?.dataset.transShowTranslation || ''
  const transHideTranslation = projectComments?.dataset.transHideTranslation || ''
  const transReport = projectComments?.dataset.transReport || ''
  const transDelete = projectComments?.dataset.transDelete || ''
  const transDeletedComment = projectComments?.dataset.transDeletedComment || 'Deleted'

  // Format any server-rendered comment dates (e.g. parent comment on detail page)
  const commentUploadDates = document.getElementsByClassName('comment-upload-date')
  for (const element of commentUploadDates) {
    const commentUploadDate = new Date(element.textContent)
    element.textContent = commentUploadDate.toLocaleString('en-GB')
  }

  loadMoreComments()

  document.querySelector('#comment-post-button')?.addEventListener('click', postComment)

  // Attach to document so clicks on comment detail pages (parent-comment-container)
  // are also captured. We narrow handling by checking closest('.single-comment, .single-reply').
  document.addEventListener('click', function (event) {
    const singleComment = event.target.closest('.single-comment, .single-reply')
    if (!singleComment) return

    // Handle Reply Button
    const replyButton = event.target.closest('.comment-reply-button')
    if (replyButton) {
      event.stopPropagation()
      const parentCommentContainer = document.querySelector('.js-project-parentComment')
      if (parentCommentContainer) {
        const targetCommentId =
          singleComment.dataset.commentId ||
          (singleComment.id ? singleComment.id.replace(/^comment-/, '') : null)
        if (targetCommentId) {
          parentCommentContainer.dataset.parentCommentId = targetCommentId
        }
        showAndFocusCommentInput()
      } else {
        const path = singleComment.dataset.pathProjectComment
        if (path) {
          location.href = path
        }
      }
      return
    }

    // If it's an action, don't trigger the click on the whole comment
    if (
      event.target.closest('.comment-report-button') ||
      event.target.closest('.comment-delete-button') ||
      event.target.closest('.comment-translation-actions') ||
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
      commentMessage.style.height = '40px'
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
          const newComment = buildCommentElement(data, parentCommentId > 0)

          if (parentCommentId > 0) {
            commentsWrapper.appendChild(newComment)
            try {
              updateReplyCount(parentCommentId, 1)
            } catch (e) {
              console.warn('Failed to update reply count in UI', e)
            }
          } else {
            commentsWrapper.prepend(newComment)
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
        const fragment = document.createDocumentFragment()
        comments.forEach((comment) => {
          if (!comment) return
          fragment.appendChild(buildCommentElement(comment, isRepliesPage))
        })
        commentsWrapper.appendChild(fragment)

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

  const spinnerTemplate = buildSmallSpinner()

  function buildCommentElement(comment, isReply) {
    const commentId = comment.id
    const userId = comment.user?.id || ''
    const username = comment.user?.username || ''
    const avatarUrl = comment.user?.avatar || defaultAvatarUrl
    const userApproved = comment.user?.approved || false
    const message = comment.message
    const createdAt = comment.created_at
    const replyCount = comment.reply_count || 0
    const isDeleted = comment.is_deleted || false
    const isOwnComment = currentUserId && userId === currentUserId

    const profileUrl = profileUrlTemplate.replace('__USER_ID__', userId)

    const el = document.createElement('div')
    el.id = `comment-${commentId}`
    el.dataset.pathProjectComment = commentDetailUrlTemplate.replace('__COMMENT_ID__', commentId)
    el.dataset.commentId = String(commentId)
    el.className = isReply ? 'single-reply' : 'single-comment'

    // Avatar
    const avatarDiv = document.createElement('div')
    avatarDiv.className = 'comment-avatar'
    const avatarLink = document.createElement('a')
    avatarLink.href = profileUrl
    const avatarImg = document.createElement('img')
    avatarImg.className = 'comment-avatar-img'
    avatarImg.src = avatarUrl
    avatarImg.alt = 'Avatar'
    avatarImg.width = 48
    avatarImg.height = 48
    avatarLink.appendChild(avatarImg)
    avatarDiv.appendChild(avatarLink)
    el.appendChild(avatarDiv)

    // Payload wrapper
    const payloadWrapper = document.createElement('div')
    payloadWrapper.className = 'comment-payload-wrapper'

    // Header
    const header = document.createElement('div')
    header.className = 'comment-header'

    // User info
    const userInfo = document.createElement('div')
    userInfo.className = 'comment-user-info'

    const usernameLink = document.createElement('a')
    usernameLink.href = profileUrl
    usernameLink.className = 'usr-name no-overflow'
    const usernameSpan = document.createElement('span')
    usernameSpan.id = `profile-comment-user-id-${userId}`
    usernameSpan.textContent = username
    usernameLink.appendChild(usernameSpan)
    userInfo.appendChild(usernameLink)

    const metaDiv = document.createElement('div')
    metaDiv.className = 'comment-meta'
    metaDiv.appendChild(materialIcon('access_time_filled'))
    const dateSpan = document.createElement('span')
    dateSpan.className = 'comment-upload-date'
    dateSpan.textContent = new Date(createdAt).toLocaleString('en-GB')
    metaDiv.appendChild(dateSpan)
    userInfo.appendChild(metaDiv)

    header.appendChild(userInfo)

    // Actions
    const actions = document.createElement('div')
    actions.className = 'comment-actions d-flex align-items-center gap-2'

    // Reply button
    const replyAction = document.createElement('div')
    replyAction.className = 'comment-reply-action'
    const replyBtn = document.createElement('span')
    replyBtn.className = 'comment-reply-button catro-icon-button'
    replyBtn.dataset.bsToggle = 'tooltip'
    replyBtn.title = transReply
    replyBtn.appendChild(materialIcon('reply'))
    replyAction.appendChild(replyBtn)
    actions.appendChild(replyAction)

    // Reply count (always show for top-level comments, even when 0)
    if (!isReply) {
      const repliesCount = document.createElement('div')
      repliesCount.className = 'comment-replies-count'
      repliesCount.appendChild(materialIcon('comment'))
      const countSpan = document.createElement('span')
      countSpan.textContent = String(replyCount)
      repliesCount.appendChild(countSpan)
      actions.appendChild(repliesCount)
    }

    // Translation actions (not for own comments or deleted comments)
    if (!isOwnComment && !isDeleted) {
      const translationActions = document.createElement('div')
      translationActions.className = 'comment-translation-actions'

      const translateBtn = document.createElement('span')
      translateBtn.id = `comment-translation-button-${commentId}`
      translateBtn.className = 'comment-translation-button catro-icon-button'
      translateBtn.dataset.bsToggle = 'tooltip'
      translateBtn.title = transShowTranslation
      translateBtn.appendChild(materialIcon('translate'))
      translationActions.appendChild(translateBtn)

      const spinner = document.createElement('span')
      spinner.id = `comment-translation-loading-spinner-${commentId}`
      spinner.className = 'comment-translation-loading-spinner'
      spinner.style.display = 'none'
      spinner.appendChild(spinnerTemplate.cloneNode(true))
      translationActions.appendChild(spinner)

      const removeTranslateBtn = document.createElement('span')
      removeTranslateBtn.id = `remove-comment-translation-button-${commentId}`
      removeTranslateBtn.className = 'remove-comment-translation-button catro-icon-button'
      removeTranslateBtn.style.display = 'none'
      removeTranslateBtn.dataset.bsToggle = 'tooltip'
      removeTranslateBtn.title = transHideTranslation
      removeTranslateBtn.appendChild(materialIcon('close'))
      translationActions.appendChild(removeTranslateBtn)

      actions.appendChild(translationActions)
    }

    // Report button
    const showReport =
      (isAdmin || !isLoggedIn || !isOwnComment) && !isDeleted && !userApproved && !isMinor
    if (showReport) {
      const reportBtn = document.createElement('a')
      reportBtn.id = `comment-report-button-${commentId}`
      reportBtn.className = 'comment-report-button'
      reportBtn.dataset.contentType = 'comment'
      reportBtn.dataset.contentId = String(commentId)
      reportBtn.dataset.reportUrl = `${commentsBaseUrl}/${commentId}/report`
      reportBtn.dataset.bsToggle = 'tooltip'
      reportBtn.title = transReport
      reportBtn.appendChild(materialIcon('report'))
      actions.appendChild(reportBtn)
    }

    // Delete button
    if ((isAdmin || isOwnComment) && !isDeleted) {
      const deleteBtn = document.createElement('a')
      deleteBtn.id = `comment-delete-button-${commentId}`
      deleteBtn.className = 'comment-delete-button'
      deleteBtn.dataset.bsToggle = 'tooltip'
      deleteBtn.title = transDelete
      const deleteIcon = materialIcon('delete')
      deleteIcon.classList.add('text-danger')
      deleteBtn.appendChild(deleteIcon)
      actions.appendChild(deleteBtn)
    }

    header.appendChild(actions)
    payloadWrapper.appendChild(header)

    // Comment text
    const textWrapper = document.createElement('div')
    textWrapper.id = `comment-text-wrapper-${commentId}`
    textWrapper.className = 'comment-text'

    const textP = document.createElement('p')
    if (isDeleted) {
      const deletedSpan = document.createElement('span')
      deletedSpan.className = 'deleted-comment'
      deletedSpan.textContent = transDeletedComment
      textP.appendChild(deletedSpan)
    } else {
      textP.id = `comment-text-${commentId}`
      textP.textContent = message || ''
    }
    textWrapper.appendChild(textP)
    payloadWrapper.appendChild(textWrapper)

    // Translation container
    const translationContainer = document.createElement('div')
    translationContainer.className = 'comment-translation-container'

    if (!isOwnComment && !isDeleted) {
      const translationDiv = document.createElement('div')
      translationDiv.className = 'comment-translation'
      translationDiv.dataset.translateCommentId = `translate-comment-${commentId}`
      translationDiv.dataset.pathTranslateComment = `${translateUrlTemplate}/${commentId}/translation`
      translationContainer.appendChild(translationDiv)
    }

    const translationWrapper = document.createElement('div')
    translationWrapper.id = `comment-translation-wrapper-${commentId}`
    translationWrapper.className = 'comment-translation-wrapper'
    translationWrapper.style.display = 'none'

    const creditWrapper = document.createElement('div')
    creditWrapper.id = `comment-translation-credit-wrapper-${commentId}`
    creditWrapper.className = 'translation-credit-wrapper'

    const creditParts = [
      'before-languages',
      'first-language',
      'between-languages',
      'second-language',
      'after-languages',
    ]
    creditParts.forEach((part) => {
      const span = document.createElement('span')
      span.id = `comment-translation-${part}-${commentId}`
      span.className = 'translation-credit'
      creditWrapper.appendChild(span)
    })

    translationWrapper.appendChild(creditWrapper)

    const translatedText = document.createElement('p')
    translatedText.id = `comment-text-translation-${commentId}`
    translatedText.className = 'comment-text-translation'
    translatedText.setAttribute('lang', '')
    translationWrapper.appendChild(translatedText)

    translationContainer.appendChild(translationWrapper)
    payloadWrapper.appendChild(translationContainer)

    el.appendChild(payloadWrapper)

    return el
  }

  function materialIcon(name) {
    const i = document.createElement('i')
    i.className = 'material-icons'
    i.textContent = name
    return i
  }

  function buildSmallSpinner() {
    const wrapper = document.createElement('div')
    wrapper.className = 'circular-progress circular-progress--small'

    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg')
    svg.setAttribute('viewBox', '0 0 24 24')
    svg.setAttribute('xmlns', 'http://www.w3.org/2000/svg')

    const bgCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle')
    bgCircle.setAttribute('class', 'circular-progress__background')
    bgCircle.setAttribute('cx', '12')
    bgCircle.setAttribute('cy', '12')
    bgCircle.setAttribute('r', '10')
    svg.appendChild(bgCircle)

    const fgCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle')
    fgCircle.setAttribute('class', 'circular-progress__foreground')
    fgCircle.setAttribute('cx', '12')
    fgCircle.setAttribute('cy', '12')
    fgCircle.setAttribute('r', '10')
    svg.appendChild(fgCircle)

    wrapper.appendChild(svg)
    return wrapper
  }

  function updateReplyCount(parentId, delta) {
    const parentEl = document.querySelector('#comment-' + parentId)
    if (!parentEl) return

    const repliesCountWrapper = parentEl.querySelector('.comment-replies-count')
    if (repliesCountWrapper) {
      const span = repliesCountWrapper.querySelector('span')
      if (span) {
        const current = parseInt(span.textContent || '0', 10)
        span.textContent = Math.max(0, current + delta)
      }
    } else if (delta > 0) {
      const actionsEl = parentEl.querySelector('.comment-actions')
      if (!actionsEl) return
      const div = document.createElement('div')
      div.className = 'comment-replies-count'
      div.appendChild(materialIcon('comment'))
      const countSpan = document.createElement('span')
      countSpan.textContent = String(delta)
      div.appendChild(countSpan)
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
          const commentElement = document.querySelector('#comment-' + commentId)
          if (commentElement) {
            const textWrapper = commentElement.querySelector('#comment-text-wrapper-' + commentId)
            if (textWrapper) {
              textWrapper.replaceChildren()
              const p = document.createElement('p')
              const span = document.createElement('span')
              span.className = 'deleted-comment'
              span.textContent = transDeletedComment
              p.appendChild(span)
              textWrapper.appendChild(p)
            }

            const actions = commentElement.querySelector('.comment-actions')
            if (actions) {
              Array.from(actions.children).forEach((child) => {
                const keep =
                  child.classList.contains('comment-reply-action') ||
                  child.classList.contains('comment-replies-count')
                if (!keep) child.remove()
              })
            }

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
      const parentCommentContainer = document.querySelector('.js-project-parentComment')
      if (parentCommentContainer) {
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
