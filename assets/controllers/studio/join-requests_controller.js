import { Controller } from '@hotwired/stimulus'
import { showSnackbar, SnackbarDuration } from '../../Layout/Snackbar'
import { escapeHtml, escapeAttr } from '../../Components/HtmlEscape'
import { getImageUrl } from '../../Layout/ImageVariants'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static values = {
    joinRequestsUrl: String,
    studioId: String,
    listElementId: String,
  }

  /**
   * Load pending join requests for the studio
   *
   * data-action="click->studio--join-requests#loadJoinRequests"
   */
  async loadJoinRequests() {
    const listElement = document.getElementById(this.listElementIdValue)
    listElement.innerHTML = ''

    try {
      const response = await fetch(this.joinRequestsUrlValue + '?limit=50', {
        credentials: 'same-origin',
      })
      if (!response.ok) {
        return
      }

      const data = await response.json()
      if (data.data && data.data.length > 0) {
        data.data.forEach((request) => {
          listElement.appendChild(this._renderJoinRequest(request))
        })
      } else {
        const emptyItem = document.createElement('li')
        emptyItem.className = 'join-request-empty text-muted text-center py-4'
        emptyItem.textContent = this.element.dataset.transNoRequests || 'No pending join requests.'
        listElement.appendChild(emptyItem)
      }
    } catch (e) {
      console.error('Failed to load join requests:', e)
    }
  }

  _renderJoinRequest(request) {
    const li = document.createElement('li')
    li.className = 'member__list-entry'
    li.id = `join-request-${request.id}`

    const avatarSrc = escapeAttr(
      getImageUrl(request.avatar, 'thumb', '/images/default/avatar_default-thumb@1x.webp'),
    )
    const username = escapeHtml(request.username || 'Unknown')
    const profileUrl = '/app/user/' + escapeAttr(String(request.user_id))
    const transAccept = escapeHtml(this.element.dataset.transAccept || 'Accept')
    const transDecline = escapeHtml(this.element.dataset.transDecline || 'Decline')

    li.innerHTML = `
      <a href="${profileUrl}">
        <img class="member__list-entry__image" src="${avatarSrc}" alt="">
      </a>
      <div class="ps-3 flex-grow-1">
        <a href="${profileUrl}" class="fw-medium text-decoration-none">${username}</a>
      </div>
      <div class="d-flex gap-2 ms-auto">
        <button class="btn btn-sm btn-success join-request-accept-btn"
                data-request-id="${request.id}">
          <span class="material-icons" style="font-size: 18px; vertical-align: middle;">check</span>
          ${transAccept}
        </button>
        <button class="btn btn-sm btn-outline-danger join-request-decline-btn"
                data-request-id="${request.id}">
          <span class="material-icons" style="font-size: 18px; vertical-align: middle;">close</span>
          ${transDecline}
        </button>
      </div>
    `

    li.querySelector('.join-request-accept-btn').addEventListener('click', (e) => {
      this._handleAction(e, request.id, 'accept')
    })

    li.querySelector('.join-request-decline-btn').addEventListener('click', (e) => {
      this._handleAction(e, request.id, 'decline')
    })

    return li
  }

  async _handleAction(event, requestId, action) {
    const btn = event.currentTarget
    btn.disabled = true

    const baseUrl = this.joinRequestsUrlValue
    const url = `${baseUrl}/${requestId}/${action}`

    try {
      const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
      })

      if (response.ok) {
        const item = document.getElementById(`join-request-${requestId}`)
        if (item) {
          item.remove()
        }

        // Update the badge count
        const badge = document.getElementById('join-requests-badge')
        if (badge) {
          const currentCount = parseInt(badge.textContent, 10) || 0
          const newCount = Math.max(0, currentCount - 1)
          badge.textContent = newCount.toString()
          if (newCount === 0) {
            badge.style.display = 'none'
          }
        }

        // Check if the list is empty now
        const listElement = document.getElementById(this.listElementIdValue)
        if (listElement && listElement.children.length === 0) {
          const emptyItem = document.createElement('li')
          emptyItem.className = 'join-request-empty text-muted text-center py-4'
          emptyItem.textContent =
            this.element.dataset.transNoRequests || 'No pending join requests.'
          listElement.appendChild(emptyItem)
        }

        const msgKey = action === 'accept' ? 'transAcceptSuccess' : 'transDeclineSuccess'
        const defaultMsg = action === 'accept' ? 'Join request accepted.' : 'Join request declined.'
        showSnackbar(
          '#share-snackbar',
          this.element.dataset[msgKey] || defaultMsg,
          SnackbarDuration.short,
        )
      } else {
        btn.disabled = false
        showSnackbar(
          '#share-snackbar',
          this.element.dataset.transError || 'Something went wrong. Please try again.',
          SnackbarDuration.error,
        )
      }
    } catch (e) {
      btn.disabled = false
      console.error(`Failed to ${action} join request:`, e)
      showSnackbar(
        '#share-snackbar',
        this.element.dataset.transError || 'Something went wrong. Please try again.',
        SnackbarDuration.error,
      )
    }
  }
}
