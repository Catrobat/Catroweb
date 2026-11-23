import { Controller } from '@hotwired/stimulus'
import { createPictureElement } from '../../Layout/ImageVariants'
import { SnackbarDuration, showSnackbar } from '../../Layout/Snackbar'
import { handleAccountState403 } from '../../Security/AccountStateErrorHandler'

const PENDING_KEY = 'pendingDocComment'
const PAGE_SIZE = 10

// Comments and tips on a docs page (templates/Docs/Comments.html.twig), through /api/docs/pages/{path}/comments.
// Replies, reporting and deleting use the generic /api/comments/{id}/… endpoints, as on projects.
export default class extends Controller {
  static values = {
    docPath: String,
    listUrl: String,
    commentsUrl: String,
    loginUrl: String,
    isLoggedIn: Boolean,
    isAdmin: Boolean,
    currentUserId: String,
    profileUrl: String,
    defaultAvatarUrl: String,
  }

  static targets = ['form', 'input', 'cancel', 'empty', 'list', 'more']

  cursor = null
  parentId = null

  connect() {
    this.restorePending()
    this.load()
  }

  async load() {
    const data = await this.fetchPage(this.listUrlValue, this.cursor)
    if (!data) return
    for (const comment of data.data) {
      this.listTarget.append(this.render(comment))
    }
    this.cursor = data.next_cursor
    this.moreTarget.hidden = !data.has_more
    this.emptyTarget.hidden = this.listTarget.childElementCount > 0
  }

  async fetchPage(url, cursor) {
    const query = new URLSearchParams({ limit: String(PAGE_SIZE) })
    if (cursor) query.set('cursor', cursor)
    try {
      const response = await fetch(`${url}?${query}`, { credentials: 'same-origin' })
      if (response.ok) return await response.json()
    } catch {
      // fall through to the error message
    }
    this.snack(this.element.dataset.transError)
    return null
  }

  render(comment) {
    const own = this.currentUserIdValue !== '' && comment.user?.id === this.currentUserIdValue
    const el = document.createElement('article')
    el.className = comment.parent_id ? 'single-reply' : 'single-comment'
    el.id = `comment-${comment.id}`
    el.dataset.commentId = comment.id

    const profile = this.profileUrlValue.replace(
      '__USER_ID__',
      encodeURIComponent(comment.user?.id || ''),
    )
    const avatar = document.createElement('a')
    avatar.className = 'comment-avatar'
    avatar.href = profile
    avatar.append(
      createPictureElement(comment.user?.avatar || null, 'thumb', this.defaultAvatarUrlValue, {
        class: 'comment-avatar-img',
        alt: '',
        width: 40,
        height: 40,
      }),
    )

    const body = document.createElement('div')
    body.className = 'comment-payload-wrapper'
    const header = document.createElement('div')
    header.className = 'comment-header'
    const name = document.createElement('a')
    name.className = 'usr-name'
    name.href = profile
    name.textContent = comment.user?.username || ''
    const date = document.createElement('span')
    date.className = 'comment-meta'
    date.textContent = new Date(comment.created_at).toLocaleString()
    const actions = document.createElement('div')
    actions.className = 'comment-actions'
    header.append(name, date, actions)

    const text = document.createElement('p')
    text.className = 'comment-text'
    if (comment.is_deleted) {
      text.classList.add('deleted-comment')
      text.textContent = this.element.dataset.transDeleted
    } else {
      text.textContent = comment.message || ''
    }
    body.append(header, text)

    if (!comment.parent_id) {
      actions.append(this.button('reply', 'reply', this.element.dataset.transReply))
    }
    if (!comment.is_deleted && !own && !comment.user?.approved) {
      actions.append(this.button('report', 'report', this.element.dataset.transReport))
    }
    if (!comment.is_deleted && (own || this.isAdminValue)) {
      actions.append(this.button('delete', 'delete', this.element.dataset.transDelete))
    }
    if (!comment.parent_id) {
      const replies = document.createElement('div')
      replies.className = 'docs-comments__replies'
      if (comment.reply_count > 0) {
        const toggle = this.button('replies', 'forum', '')
        toggle.append(String(comment.reply_count))
        replies.append(toggle)
      }
      body.append(replies)
    }

    el.append(avatar, body)
    return el
  }

  button(action, icon, label) {
    const button = document.createElement('button')
    button.type = 'button'
    button.className = `comment-${action}-button catro-icon-button`
    button.dataset.commentAction = action
    if (label) {
      button.title = label
      button.setAttribute('aria-label', label)
    }
    const i = document.createElement('i')
    i.className = 'material-icons'
    i.setAttribute('aria-hidden', 'true')
    i.textContent = icon
    button.append(i)
    return button
  }

  act(event) {
    const button = event.target.closest('[data-comment-action]')
    const comment = button?.closest('[data-comment-id]')
    if (!comment) return
    const id = comment.dataset.commentId
    const action = button.dataset.commentAction
    if (action === 'reply') this.startReply(id)
    else if (action === 'replies') this.showReplies(comment, button)
    else if (action === 'report') this.report(id)
    else if (action === 'delete') this.confirmDelete(id, comment)
  }

  startReply(id) {
    this.parentId = id
    this.cancelTarget.hidden = false
    document.getElementById(`comment-${id}`)?.after(this.formTarget)
    this.inputTarget.focus()
  }

  cancelReply() {
    this.parentId = null
    this.cancelTarget.hidden = true
    this.element.querySelector('.docs-comments__title').after(this.formTarget)
  }

  async showReplies(comment, toggle) {
    toggle.remove()
    const container = comment.querySelector('.docs-comments__replies')
    let cursor = null
    do {
      const data = await this.fetchPage(
        `${this.commentsUrlValue}/${comment.dataset.commentId}/replies`,
        cursor,
      )
      if (!data) return
      // The API lists newest first; a thread reads oldest first.
      for (const reply of data.data) {
        container.prepend(this.render(reply))
      }
      cursor = data.has_more ? data.next_cursor : null
    } while (cursor)
  }

  async post(event) {
    event.preventDefault()
    const message = this.inputTarget.value.trim()
    if (message === '') return
    const payload = { message, parent_id: this.parentId }

    if (!this.isLoggedInValue) {
      sessionStorage.setItem(
        PENDING_KEY,
        JSON.stringify({ docPath: this.docPathValue, ...payload }),
      )
      window.location.href = this.loginUrlValue
      return
    }

    let response
    try {
      response = await fetch(this.listUrlValue, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      })
    } catch {
      this.snack(this.element.dataset.transError)
      return
    }

    if (response.status === 201) {
      const comment = await response.json()
      if (comment.parent_id) {
        document
          .querySelector(`#comment-${CSS.escape(comment.parent_id)} .docs-comments__replies`)
          ?.append(this.render(comment))
      } else {
        this.listTarget.prepend(this.render(comment))
      }
      this.inputTarget.value = ''
      this.emptyTarget.hidden = true
      if (this.parentId) this.cancelReply()
    } else if (response.status === 401) {
      window.location.href = this.loginUrlValue
    } else if (response.status === 403) {
      handleAccountState403(
        response,
        {
          unverified: this.element.dataset.transUnverified,
          suspended: this.element.dataset.transSuspended,
          error: this.element.dataset.transError,
        },
        this.element.dataset.transError,
      )
    } else if (response.status === 429) {
      this.snack(this.element.dataset.transRateLimited)
    } else {
      this.snack(this.element.dataset.transError)
    }
  }

  // A guest's comment survives the login, for this page only.
  restorePending() {
    const raw = sessionStorage.getItem(PENDING_KEY)
    if (!raw || !this.isLoggedInValue) return
    sessionStorage.removeItem(PENDING_KEY)
    try {
      const pending = JSON.parse(raw)
      if (pending.docPath !== this.docPathValue || typeof pending.message !== 'string') return
      this.inputTarget.value = pending.message
      if (pending.parent_id) {
        this.parentId = String(pending.parent_id)
        this.cancelTarget.hidden = false
      }
      this.element.scrollIntoView()
    } catch {
      // ignore a broken entry
    }
  }

  report(id) {
    const data = this.element.dataset
    import('../../Moderation/ReportDialog').then(({ showReportDialog }) =>
      showReportDialog({
        contentType: 'comment',
        contentId: id,
        apiUrl: `${this.commentsUrlValue}/${encodeURIComponent(id)}/report`,
        loginUrl: this.loginUrlValue,
        isLoggedIn: this.isLoggedInValue,
        translations: {
          title: data.transReportTitle,
          submit: data.transReportSubmit,
          cancel: data.transCancel,
          success: data.transReportSuccess,
          error: data.transReportError,
          duplicate: data.transReportDuplicate,
          trustTooLow: data.transReportTrustTooLow,
          unverified: data.transReportUnverified,
          suspended: data.transSuspended,
          rateLimited: data.transReportRateLimited,
          notePlaceholder: data.transReportPlaceholder,
        },
      }),
    )
  }

  async confirmDelete(id, comment) {
    const data = this.element.dataset
    const { default: Swal } = await import('sweetalert2')
    const result = await Swal.fire({
      title: data.transAreYouSure,
      text: data.transDeleteConfirmation,
      icon: 'warning',
      showCancelButton: true,
      customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-outline-primary' },
      buttonsStyling: false,
      confirmButtonText: data.transDeleteIt,
      cancelButtonText: data.transCancel,
    })
    if (!result.isConfirmed) return

    const response = await fetch(`${this.commentsUrlValue}/${encodeURIComponent(id)}`, {
      method: 'DELETE',
      credentials: 'same-origin',
    }).catch(() => null)
    if (!response?.ok) {
      this.snack(data.transError)
      return
    }
    const text = comment.querySelector('.comment-text')
    text.classList.add('deleted-comment')
    text.textContent = data.transDeleted
    comment
      .querySelectorAll(':scope > .comment-payload-wrapper > .comment-header [data-comment-action]')
      .forEach((button) => {
        if (button.dataset.commentAction !== 'reply') button.remove()
      })
  }

  snack(message) {
    showSnackbar('#share-snackbar', message, SnackbarDuration.error)
  }
}
