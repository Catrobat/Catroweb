import { Controller } from '@hotwired/stimulus'
import { escapeHtml, escapeAttr } from '../../Components/HtmlEscape'
import { getImageUrl } from '../../Layout/ImageVariants'
import { showSnackbar, SnackbarDuration } from '../../Layout/Snackbar'
import Swal from 'sweetalert2'

export default class extends Controller {
  static values = {
    studioId: String,
    commentsUrl: String,
    postUrl: String,
    deleteUrl: String,
    userRole: String,
    userName: String,
    isLoggedIn: Boolean,
    commentsEnabled: Boolean,
    isMinor: Boolean,
  }

  static targets = [
    'container',
    'loadMore',
    'count',
    'noComments',
    'disabledComments',
    'form',
    'messageInput',
    'commentButtons',
  ]

  cursor = null
  hasMore = false

  connect() {
    if (!this.commentsEnabledValue) {
      if (this.hasDisabledCommentsTarget) {
        this.disabledCommentsTarget.style.display = 'block'
      }
      if (this.hasFormTarget) {
        this.formTarget.style.display = 'none'
      }
      return
    }
    this.loadComments()
  }

  async loadComments() {
    const url = new URL(this.commentsUrlValue, window.location.origin)
    url.searchParams.set('limit', '20')
    if (this.cursor) {
      url.searchParams.set('cursor', this.cursor)
    }

    try {
      const response = await fetch(url, { credentials: 'same-origin' })
      if (!response.ok) {
        return
      }

      const data = await response.json()
      this.hasMore = data.has_more
      this.cursor = data.next_cursor

      if (data.data && data.data.length > 0) {
        if (this.hasNoCommentsTarget) {
          this.noCommentsTarget.style.display = 'none'
        }
        data.data.forEach((comment) => this.renderComment(comment))
      } else if (!this.cursor) {
        if (this.hasNoCommentsTarget) {
          this.noCommentsTarget.style.display = 'block'
        }
      }

      if (this.hasLoadMoreTarget) {
        this.loadMoreTarget.style.display = this.hasMore ? 'block' : 'none'
      }
    } catch (e) {
      console.error('Failed to load comments:', e)
    }
  }

  renderComment(comment) {
    const canDelete =
      this.userRoleValue === 'admin' ||
      (this.isLoggedInValue && comment.username === this.userNameValue)

    const avatarSrc = getImageUrl(
      comment.user_avatar,
      'thumb',
      '/images/default/avatar_default-thumb@1x.webp',
    )
    const rawDate = comment.created_at || comment.timestamp
    const dateStr = rawDate ? new Date(rawDate).toLocaleString('en-GB') : ''

    const isOwnComment = this.isLoggedInValue && comment.username === this.userNameValue
    const showReport =
      !isOwnComment && !comment.is_deleted && !comment.user_approved && !this.isMinorValue

    const reportBtn = showReport
      ? `<a id="comment-report-button-${escapeAttr(String(comment.id))}"
            class="comment-report-button"
            data-action="click->studio--comment#reportComment"
            data-comment-id="${escapeAttr(String(comment.id))}"
            data-bs-toggle="tooltip"
            title="${escapeAttr(this.element.dataset.transReport || 'Report')}">
          <i class="material-icons">report</i>
        </a>`
      : ''

    const deleteBtn = canDelete
      ? `<a class="comment-delete-button"
            data-action="click->studio--comment#confirmDeleteComment"
            data-comment-id="${escapeAttr(String(comment.id))}"
            data-bs-toggle="tooltip"
            title="${escapeAttr(this.element.dataset.transRemoveComment || 'Remove comment')}">
          <i class="material-icons text-danger">delete</i>
        </a>`
      : ''

    const replyInfo =
      comment.reply_count > 0
        ? `<div class="comment-replies-count">
            <i class="material-icons">comment</i>
            <span>${comment.reply_count} ${escapeHtml(this.element.dataset.transReplies || 'replies')}</span>
          </div>`
        : ''

    const el = document.createElement('div')
    el.id = `comment-${comment.id}`
    el.className = 'single-comment'
    el.dataset.commentId = comment.id
    el.innerHTML = `
      <div class="comment-avatar">
        <a href="/app/user/${escapeAttr(String(comment.user_id || ''))}">
          <img class="comment-avatar-img" src="${escapeAttr(avatarSrc)}" alt="Avatar" width="48" height="48">
        </a>
      </div>
      <div class="comment-payload-wrapper">
        <div class="comment-header">
          <div class="comment-user-info">
            <a href="/app/user/${escapeAttr(String(comment.user_id || ''))}" class="usr-name no-overflow">
              <span>${escapeHtml(comment.username || '')}</span>
            </a>
            <div class="comment-meta">
              <i class="material-icons">access_time_filled</i>
              <span class="comment-upload-date">${escapeHtml(dateStr)}</span>
            </div>
          </div>
          <div class="comment-actions d-flex align-items-center gap-2">
            ${replyInfo}
            ${reportBtn}
            ${deleteBtn}
          </div>
        </div>
        <div class="comment-text">
          <p>${escapeHtml(comment.message || '')}</p>
        </div>
      </div>
    `

    this.containerTarget.appendChild(el)
  }

  loadMore() {
    if (this.hasMore) {
      this.loadComments()
    }
  }

  showCommentForm() {
    if (this.hasFormTarget) {
      this.formTarget.style.display = 'flex'
    }
    if (this.hasCommentButtonsTarget) {
      this.commentButtonsTarget.style.display = 'flex'
    }
    if (this.hasMessageInputTarget) {
      this.messageInputTarget.style.height = '100px'
      this.messageInputTarget.focus()
    }
  }

  cancelComment() {
    if (this.hasMessageInputTarget) {
      this.messageInputTarget.value = ''
      this.messageInputTarget.style.height = '40px'
    }
    if (this.hasCommentButtonsTarget) {
      this.commentButtonsTarget.style.display = 'none'
    }
  }

  onMessageFocus() {
    if (this.hasCommentButtonsTarget) {
      this.commentButtonsTarget.style.display = 'flex'
    }
    if (this.hasMessageInputTarget) {
      this.messageInputTarget.style.height = '100px'
    }
  }

  async postComment(event) {
    event.preventDefault()

    if (!this.hasMessageInputTarget) {
      return
    }

    const message = this.messageInputTarget.value.trim()
    if (message === '') {
      return
    }

    try {
      const response = await fetch(this.postUrlValue, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: message }),
      })

      if (response.status === 201) {
        const comment = await response.json()
        this.renderComment(comment)
        this.messageInputTarget.value = ''
        this.cancelComment()
        if (this.hasNoCommentsTarget) {
          this.noCommentsTarget.style.display = 'none'
        }
        this.updateCount(1)
      } else if (response.status === 429) {
        showSnackbar(
          '#share-snackbar',
          this.element.dataset.transRateLimited ||
            "You're posting comments too quickly. Please wait a moment.",
          SnackbarDuration.error,
        )
      } else {
        showSnackbar(
          '#share-snackbar',
          this.element.dataset.transPostError || 'Failed to post comment.',
          SnackbarDuration.error,
        )
      }
    } catch (e) {
      console.error('Failed to post comment:', e)
      showSnackbar(
        '#share-snackbar',
        this.element.dataset.transPostError || 'Failed to post comment.',
        SnackbarDuration.error,
      )
    }
  }

  reportComment(event) {
    const commentId =
      event.currentTarget.dataset.commentId ||
      event.target.closest('[data-comment-id]')?.dataset.commentId

    if (!commentId) {
      return
    }

    const apiUrl = `/api/comments/${encodeURIComponent(commentId)}/report`

    import('../../Moderation/ReportDialog').then(({ showReportDialog }) =>
      showReportDialog({
        contentType: 'comment',
        contentId: commentId,
        apiUrl,
        loginUrl: this.element.dataset.pathLoginUrl || '/app/login',
        isLoggedIn: this.isLoggedInValue,
        translations: {
          title: this.element.dataset.transReportTitle || 'Report',
          submit: this.element.dataset.transReportSubmit || 'Submit Report',
          cancel: this.element.dataset.transReportCancel || 'Cancel',
          success: this.element.dataset.transReportSuccess || 'Your report has been submitted.',
          error:
            this.element.dataset.transReportError || 'Oops, that did not work. Please try again!',
          duplicate:
            this.element.dataset.transReportDuplicate || "You've already reported this content.",
          trustTooLow:
            this.element.dataset.transReportTrustTooLow ||
            'Your account is too new to file reports.',
          unverified: this.element.dataset.transReportUnverified || 'Email verification required.',
          suspended:
            this.element.dataset.transReportSuspended || 'Your account has been suspended.',
          rateLimited:
            this.element.dataset.transReportRateLimited ||
            "You're submitting reports too quickly. Please wait and try again.",
          notePlaceholder:
            this.element.dataset.transReportPlaceholder ||
            'Please describe why you are reporting this...',
        },
      }),
    )
  }

  async confirmDeleteComment(event) {
    const commentId =
      event.currentTarget.dataset.commentId ||
      event.target.closest('[data-comment-id]')?.dataset.commentId

    const result = await Swal.fire({
      title: this.element.dataset.transAreYouSure || 'Are you sure?',
      text: this.element.dataset.transNoWayOfReturn || 'This cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      allowOutsideClick: false,
      customClass: {
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-outline-primary',
      },
      buttonsStyling: false,
      confirmButtonText: this.element.dataset.transDeleteIt || 'Delete',
      cancelButtonText: this.element.dataset.transCancel || 'Cancel',
    })

    if (result.isConfirmed) {
      await this.deleteComment(commentId)
    }
  }

  async deleteComment(commentId) {
    const url = this.deleteUrlValue.replace('__COMMENT_ID__', commentId)
    try {
      const response = await fetch(url, {
        method: 'DELETE',
        credentials: 'same-origin',
      })
      if (response.ok) {
        const deletedText = this.element.dataset.transDeletedComment || 'Deleted'
        const commentEl = this.containerTarget.querySelector(`#comment-${CSS.escape(commentId)}`)
        if (commentEl) {
          const textWrapper = commentEl.querySelector('.comment-text')
          if (textWrapper) {
            textWrapper.innerHTML = `<p><span class="deleted-comment">${escapeHtml(deletedText)}</span></p>`
          }
          const actions = commentEl.querySelector('.comment-actions')
          if (actions) {
            Array.from(actions.children).forEach((child) => {
              if (!child.classList.contains('comment-replies-count')) {
                child.remove()
              }
            })
          }
          commentEl.classList.add('deleted-comment-wrapper')
        }
        this.updateCount(-1)
      } else {
        showSnackbar(
          '#share-snackbar',
          this.element.dataset.transRemoveError || 'Failed to remove comment.',
          SnackbarDuration.error,
        )
      }
    } catch (e) {
      console.error('Failed to delete comment:', e)
      showSnackbar(
        '#share-snackbar',
        this.element.dataset.transRemoveError || 'Failed to remove comment.',
        SnackbarDuration.error,
      )
    }
  }

  updateCount(delta) {
    if (this.hasCountTarget) {
      const current = parseInt(this.countTarget.textContent) || 0
      const newCount = current + delta
      this.countTarget.textContent = String(newCount)
      if (newCount <= 0 && this.hasNoCommentsTarget) {
        this.noCommentsTarget.style.display = 'block'
      }
    }
  }
}
