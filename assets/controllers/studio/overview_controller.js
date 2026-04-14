import { Controller } from '@hotwired/stimulus'
import { escapeAttr, escapeHtml } from '../../Components/HtmlEscape'
import { shareOrCopy } from '../../Components/ClipboardHelper'
import { getImageUrl } from '../../Layout/ImageVariants'
import { showSnackbar, SnackbarDuration } from '../../Layout/Snackbar'
import AcceptLanguage from '../../Api/AcceptLanguage'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static values = {
    apiBaseUrl: String,
    studioDetailsPath: String,
    createStudioPath: String,
    loginPath: String,
    isLoggedIn: Boolean,
    userId: String,
  }

  static targets = [
    'studiosList',
    'myStudios',
    'exploreStudios',
    'myStudiosHeader',
    'exploreStudiosHeader',
    'loadMore',
    'loadMoreMy',
  ]

  connect() {
    this.exploreCursor = null
    this.hasMoreExplore = true
    this.exploreLoading = false

    this.myCursor = null
    this.hasMoreMy = true
    this.myLoading = false

    this._readTranslations()

    if (this.isLoggedInValue && this.userIdValue) {
      this._fetchMyStudios()
    }
    this._fetchExploreStudios()

    this._onDocumentClick = () => {
      this.element.querySelectorAll('.projects-list-item--dropdown').forEach((d) => {
        d.style.display = 'none'
      })
    }
    document.addEventListener('click', this._onDocumentClick)
  }

  disconnect() {
    document.removeEventListener('click', this._onDocumentClick)
  }

  async loadMore() {
    if (!this.exploreLoading && this.hasMoreExplore) {
      await this._fetchExploreStudios()
    }
  }

  async loadMoreMy() {
    if (!this.myLoading && this.hasMoreMy) {
      await this._fetchMyStudios()
    }
  }

  _readTranslations() {
    this.translations = {
      publicStudios: this.element.dataset.transPublicStudios || 'Public studios',
      joinedStudios: this.element.dataset.transJoinedStudios || 'Joined studios',
      privateStudios: this.element.dataset.transPrivateStudios || 'Private studios',
      join: this.element.dataset.transJoin || 'Join',
      leave: this.element.dataset.transLeave || 'Leave',
      pending: this.element.dataset.transPending || 'Pending',
      declined: this.element.dataset.transDeclined || 'Declined',
      loadMore: this.element.dataset.transLoadMore || 'Load More',
      noStudios: this.element.dataset.transNoStudios || 'No studios found',
      myStudios: this.element.dataset.transMyStudios || 'My Studios',
      explore: this.element.dataset.transExplore || 'Explore Studios',
      noJoined: this.element.dataset.transNoJoined || "You haven't joined any studios yet",
      joined: this.element.dataset.transJoined || 'Joined',
      open: this.element.dataset.transOpen || 'Open',
      share: this.element.dataset.transShare || 'Share',
      shareSuccess: this.element.dataset.transShareSuccess || 'Link copied to clipboard!',
      leaveConfirmTitle: this.element.dataset.transLeaveConfirmTitle || 'Leave this studio?',
      cancel: this.element.dataset.transCancel || 'Cancel',
      cancelRequest: this.element.dataset.transCancelRequest || 'Cancel Request',
      cancelRequestConfirmTitle:
        this.element.dataset.transCancelRequestConfirmTitle || 'Cancel join request?',
      admin: this.element.dataset.transAdmin || 'Admin',
    }
  }

  // BEARER cookie is HttpOnly; credentials: 'same-origin' sends it automatically
  _buildHeaders() {
    return {
      Accept: 'application/json',
      'Accept-Language': new AcceptLanguage().get(),
    }
  }

  _emptyMessageHtml(message) {
    return (
      '<p class="text-muted text-center py-4 studios-empty-message">' + escapeHtml(message) + '</p>'
    )
  }

  async _fetchMyStudios() {
    if (this.myLoading || !this.hasMoreMy) {
      return
    }

    this.myLoading = true

    const params = new URLSearchParams({ limit: '20' })
    if (this.myCursor) {
      params.set('cursor', this.myCursor)
    }

    const url =
      this.apiBaseUrlValue +
      '/users/' +
      encodeURIComponent(this.userIdValue) +
      '/studios?' +
      params.toString()

    try {
      const response = await fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        headers: this._buildHeaders(),
      })

      if (!response.ok) {
        console.error('Failed to fetch my studios:', response.status)
        this.myLoading = false
        return
      }

      const json = await response.json()
      const studios = json.data || []
      this.hasMoreMy = json.has_more || false
      this.myCursor = json.next_cursor || null

      this._appendToSection(this.myStudiosTarget, studios, this.translations.noJoined, true)
      this._updateLoadMoreMyButton()
    } catch (error) {
      console.error('Error fetching my studios:', error)
    } finally {
      this.myLoading = false
      this._removeSkeletons(this.myStudiosTarget)
    }
  }

  async _fetchExploreStudios() {
    if (this.exploreLoading || !this.hasMoreExplore) {
      return
    }

    this.exploreLoading = true

    const params = new URLSearchParams({ limit: '20' })
    if (this.exploreCursor) {
      params.set('cursor', this.exploreCursor)
    }

    const container = this.isLoggedInValue ? this.exploreStudiosTarget : this.studiosListTarget
    const url = this.apiBaseUrlValue + '/studios?' + params.toString()

    try {
      const response = await fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        headers: this._buildHeaders(),
      })

      if (!response.ok) {
        console.error('Failed to fetch studios:', response.status)
        this.exploreLoading = false
        return
      }

      const json = await response.json()
      let studios = json.data || []
      this.hasMoreExplore = json.has_more || false
      this.exploreCursor = json.next_cursor || null

      if (this.isLoggedInValue) {
        studios = studios.filter((s) => s.is_member !== true && s.join_request_status !== 'pending')
      }

      this._appendToSection(container, studios, this.translations.noStudios, false)
      this._updateLoadMoreButton()
    } catch (error) {
      console.error('Error fetching studios:', error)
    } finally {
      this.exploreLoading = false
      this._removeSkeletons(container)
    }
  }

  _removeSkeletons(container) {
    if (container) {
      container.querySelectorAll('.js-skeleton').forEach((el) => el.remove())
    }
  }

  _appendToSection(container, studios, emptyMessage, isMyStudios) {
    const existingEmpty = container.querySelector('.studios-empty-message')
    if (existingEmpty) {
      existingEmpty.remove()
    }

    if (
      studios.length === 0 &&
      container.querySelectorAll('.studios-list-item-wrapper').length === 0
    ) {
      container.insertAdjacentHTML('beforeend', this._emptyMessageHtml(emptyMessage))
      return
    }

    for (const studio of studios) {
      container.insertAdjacentHTML('beforeend', this._buildStudioCard(studio, isMyStudios))
    }

    this._bindCardEvents()
  }

  _buildStudioCard(studio, isMyStudios) {
    const id = escapeAttr(studio.id || '')
    const name = escapeHtml(studio.name || '')
    const description = escapeHtml(this._truncate(studio.description || '', 100))
    const imagePath = getImageUrl(studio.cover, 'card', '/images/default/thumbnail-card@1x.webp')
    const membersCount = parseInt(studio.members_count, 10) || 0
    const projectsCount = parseInt(studio.projects_count, 10) || 0
    const isPublic = studio.is_public !== false
    const isMember = studio.is_member === true
    const userRole = studio.user_role || null
    const joinRequestStatus = studio.join_request_status || null

    const detailUrl = this.studioDetailsPathValue.replace('__ID__', id)

    let pills = ''
    if (isMyStudios && userRole === 'admin') {
      pills +=
        '<span class="studios-list-item--pill studios-list-item--pill-admin">' +
        escapeHtml(this.translations.admin) +
        '</span>'
    }
    if (joinRequestStatus === 'pending') {
      pills +=
        '<span class="studios-list-item--pill studios-list-item--pill-pending">' +
        escapeHtml(this.translations.pending) +
        '</span>'
    }

    const actionArea = this._buildActionArea(
      id,
      detailUrl,
      isMyStudios,
      isMember,
      userRole,
      joinRequestStatus,
    )

    return (
      '<div class="studios-list-item-wrapper" data-studio-id="' +
      id +
      '">' +
      '<a href="' +
      escapeAttr(detailUrl) +
      '" class="studios-list-item-link">' +
      '<div class="studios-list-item">' +
      '<img src="' +
      escapeAttr(imagePath) +
      '" class="img-fluid studios-list-item--image" alt="">' +
      '<div class="studios-list-item--content">' +
      '<div class="studios-list-item--heading">' +
      '<h3>' +
      name +
      '</h3>' +
      (!isPublic
        ? '<div class="studios-list-item--badge"><span class="material-icons">lock</span></div>'
        : '') +
      pills +
      '</div>' +
      '<div class="studios-list-item--icons">' +
      '<div class="studios-list-item--icon-wrapper">' +
      '<span class="material-icons">person</span>' +
      '<span id="studios-user-count-' +
      id +
      '" class="studios-list-item--icons-text ms-2">' +
      membersCount +
      '</span>' +
      '</div>' +
      '<div class="studios-list-item--icon-wrapper">' +
      '<span class="material-icons">app_shortcut</span>' +
      '<span class="studios-list-item--icon-text ms-2">' +
      projectsCount +
      '</span>' +
      '</div>' +
      '</div>' +
      (description ? '<p class="text-muted small mb-0 mt-1">' + description + '</p>' : '') +
      '</div>' +
      '</div>' +
      '</a>' +
      actionArea +
      '</div>'
    )
  }

  _buildActionArea(studioId, detailUrl, isMyStudios, isMember, userRole, joinRequestStatus) {
    let menuItems =
      '<a href="' +
      escapeAttr(detailUrl) +
      '" class="projects-list-item--dropdown-item">' +
      '<i class="material-icons">open_in_new</i>' +
      escapeHtml(this.translations.open) +
      '</a>' +
      '<button class="projects-list-item--dropdown-item" data-action="share" data-studio-id="' +
      escapeAttr(studioId) +
      '">' +
      '<i class="material-icons">share</i>' +
      escapeHtml(this.translations.share) +
      '</button>'

    if (this.isLoggedInValue && isMyStudios && isMember && userRole !== 'admin') {
      menuItems +=
        '<div class="projects-list-item--dropdown-divider"></div>' +
        '<button class="projects-list-item--dropdown-item text-danger" data-action="leave" data-studio-id="' +
        escapeAttr(studioId) +
        '">' +
        '<i class="material-icons">logout</i>' +
        escapeHtml(this.translations.leave) +
        '</button>'
    }

    if (this.isLoggedInValue && joinRequestStatus === 'pending' && !isMember) {
      menuItems +=
        '<div class="projects-list-item--dropdown-divider"></div>' +
        '<button class="projects-list-item--dropdown-item text-danger" data-action="cancel-request" data-studio-id="' +
        escapeAttr(studioId) +
        '">' +
        '<i class="material-icons">close</i>' +
        escapeHtml(this.translations.cancelRequest) +
        '</button>'
    }

    // Join button on explore cards only (not in dropdown menu)
    let joinButton = ''
    if (this.isLoggedInValue && !isMyStudios && !isMember) {
      joinButton =
        '<button class="btn btn-primary btn-sm studio-join-btn" data-studio-id="' +
        escapeAttr(studioId) +
        '">' +
        escapeHtml(this.translations.join) +
        '</button>'
    }

    return (
      '<div class="studios-list-item--buttons">' +
      joinButton +
      '<div class="projects-list-item--actions">' +
      '<button class="btn projects-list-item--menu-btn" data-studio-id="' +
      escapeAttr(studioId) +
      '">' +
      '<i class="material-icons">more_vert</i>' +
      '</button>' +
      '<div class="projects-list-item--dropdown" style="display:none;">' +
      menuItems +
      '</div>' +
      '</div>' +
      '</div>'
    )
  }

  _bindCardEvents() {
    const containers = []
    if (this.isLoggedInValue) {
      if (this.hasMyStudiosTarget) containers.push(this.myStudiosTarget)
      if (this.hasExploreStudiosTarget) containers.push(this.exploreStudiosTarget)
    } else {
      if (this.hasStudiosListTarget) containers.push(this.studiosListTarget)
    }

    for (const container of containers) {
      container.querySelectorAll('.projects-list-item--menu-btn').forEach((btn) => {
        if (!btn.dataset.bound) {
          btn.dataset.bound = 'true'
          btn.addEventListener('click', (e) => {
            e.preventDefault()
            e.stopPropagation()
            const dropdown = btn.nextElementSibling
            const isOpen = dropdown.style.display !== 'none'
            this.element.querySelectorAll('.projects-list-item--dropdown').forEach((d) => {
              d.style.display = 'none'
            })
            dropdown.style.display = isOpen ? 'none' : 'block'
          })
        }
      })

      container.querySelectorAll('[data-action="share"]').forEach((btn) => {
        if (!btn.dataset.bound) {
          btn.dataset.bound = 'true'
          btn.addEventListener('click', (e) => {
            e.preventDefault()
            e.stopPropagation()
            btn.closest('.projects-list-item--dropdown').style.display = 'none'
            const studioId = btn.dataset.studioId
            const url =
              window.location.origin + this.studioDetailsPathValue.replace('__ID__', studioId)
            shareOrCopy(url, () => {
              showSnackbar(
                '#share-snackbar',
                this.translations.shareSuccess,
                SnackbarDuration.short,
              )
            })
          })
        }
      })

      container.querySelectorAll('[data-action="leave"]').forEach((btn) => {
        if (!btn.dataset.bound) {
          btn.dataset.bound = 'true'
          btn.addEventListener('click', async (e) => {
            e.preventDefault()
            e.stopPropagation()
            btn.closest('.projects-list-item--dropdown').style.display = 'none'
            await this._confirmAndLeaveStudio(btn.dataset.studioId)
          })
        }
      })

      container.querySelectorAll('[data-action="cancel-request"]').forEach((btn) => {
        if (!btn.dataset.bound) {
          btn.dataset.bound = 'true'
          btn.addEventListener('click', async (e) => {
            e.preventDefault()
            e.stopPropagation()
            btn.closest('.projects-list-item--dropdown').style.display = 'none'
            await this._confirmAndCancelRequest(btn.dataset.studioId)
          })
        }
      })

      container.querySelectorAll('.studio-join-btn').forEach((btn) => {
        if (!btn.dataset.bound) {
          btn.dataset.bound = 'true'
          btn.addEventListener('click', (e) => {
            e.preventDefault()
            e.stopPropagation()
            this._joinStudio(btn.dataset.studioId)
          })
        }
      })
    }
  }

  async _confirmAndLeaveStudio(studioId) {
    const { default: Swal } = await import('sweetalert2')
    const result = await Swal.fire({
      title: this.translations.leaveConfirmTitle,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      confirmButtonText: this.translations.leave,
      cancelButtonText: this.translations.cancel,
    })

    if (result.isConfirmed) {
      await this._removeMembership(studioId, true)
    }
  }

  async _confirmAndCancelRequest(studioId) {
    const { default: Swal } = await import('sweetalert2')
    const result = await Swal.fire({
      title: this.translations.cancelRequestConfirmTitle,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      confirmButtonText: this.translations.cancelRequest,
      cancelButtonText: this.translations.cancel,
    })

    if (result.isConfirmed) {
      await this._removeMembership(studioId, false)
    }
  }

  async _joinStudio(studioId) {
    if (!this.isLoggedInValue) {
      window.location.href = this.loginPathValue
      return
    }

    try {
      const response = await fetch(
        this.apiBaseUrlValue + '/studios/' + encodeURIComponent(studioId) + '/join',
        {
          method: 'POST',
          credentials: 'same-origin',
          headers: this._buildHeaders(),
        },
      )

      if (response.ok) {
        const wrapper = this.element.querySelector(
          '.studios-list-item-wrapper[data-studio-id="' + CSS.escape(studioId) + '"]',
        )
        if (wrapper) {
          wrapper.remove()
        }

        if (this.hasMyStudiosTarget) {
          const emptyMsg = this.myStudiosTarget.querySelector('.studios-empty-message')
          if (emptyMsg) {
            emptyMsg.remove()
          }
        }

        // Re-fetch to get the card with correct user_role and membership state
        this.myCursor = null
        this.hasMoreMy = true
        if (this.hasMyStudiosTarget) {
          this.myStudiosTarget.innerHTML = ''
        }
        await this._fetchMyStudios()

        this._showEmptyExploreIfNeeded()
      } else if (response.status === 409) {
        // Already a member
      } else {
        console.error('Join failed:', response.status)
      }
    } catch (error) {
      console.error('Join error:', error)
    }
  }

  /**
   * Shared handler for both "leave studio" and "cancel join request" — both
   * DELETE to the same endpoint, remove the card from My Studios, and refresh Explore.
   */
  async _removeMembership(studioId, isLeave) {
    if (!this.isLoggedInValue) {
      window.location.href = this.loginPathValue
      return
    }

    try {
      const response = await fetch(
        this.apiBaseUrlValue + '/studios/' + encodeURIComponent(studioId) + '/leave',
        {
          method: 'DELETE',
          credentials: 'same-origin',
          headers: this._buildHeaders(),
        },
      )

      if (response.ok) {
        this._removeCardFromMyStudios(studioId)
        await this._resetAndRefetchExplore()
      } else if (isLeave && response.status === 422) {
        showSnackbar('#share-snackbar', 'Admins cannot leave the studio', SnackbarDuration.error)
      } else {
        console.error(isLeave ? 'Leave failed:' : 'Cancel request failed:', response.status)
      }
    } catch (error) {
      console.error(isLeave ? 'Leave error:' : 'Cancel request error:', error)
    }
  }

  _removeCardFromMyStudios(studioId) {
    if (!this.hasMyStudiosTarget) return

    const wrapper = this.myStudiosTarget.querySelector(
      '.studios-list-item-wrapper[data-studio-id="' + CSS.escape(studioId) + '"]',
    )
    if (wrapper) {
      wrapper.remove()
    }

    if (this.myStudiosTarget.querySelectorAll('.studios-list-item-wrapper').length === 0) {
      this.myStudiosTarget.insertAdjacentHTML(
        'beforeend',
        this._emptyMessageHtml(this.translations.noJoined),
      )
    }
  }

  async _resetAndRefetchExplore() {
    this.exploreCursor = null
    this.hasMoreExplore = true
    if (this.hasExploreStudiosTarget) {
      this.exploreStudiosTarget.innerHTML = ''
    }
    await this._fetchExploreStudios()
  }

  _showEmptyExploreIfNeeded() {
    if (!this.isLoggedInValue || !this.hasExploreStudiosTarget) return

    if (this.exploreStudiosTarget.querySelectorAll('.studios-list-item-wrapper').length === 0) {
      this.exploreStudiosTarget.insertAdjacentHTML(
        'beforeend',
        this._emptyMessageHtml(this.translations.noStudios),
      )
    }
  }

  _updateLoadMoreButton() {
    if (this.hasLoadMoreTarget) {
      this.loadMoreTarget.style.display = this.hasMoreExplore ? '' : 'none'
    }
  }

  _updateLoadMoreMyButton() {
    if (this.hasLoadMoreMyTarget) {
      this.loadMoreMyTarget.style.display = this.hasMoreMy ? '' : 'none'
    }
  }

  _truncate(str, maxLen) {
    if (str.length <= maxLen) {
      return str
    }
    return str.substring(0, maxLen) + '...'
  }
}
