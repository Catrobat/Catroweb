import { Controller } from '@hotwired/stimulus'
import { escapeAttr, escapeHtml } from '../../Components/HtmlEscape'
import { shareOrCopy } from '../../Components/ClipboardHelper'
import { showSnackbar, SnackbarDuration } from '../../Layout/Snackbar'
import AcceptLanguage from '../../Api/AcceptLanguage'

export default class extends Controller {
  static values = {
    apiBaseUrl: String,
    studioDetailsPath: String,
    createStudioPath: String,
    loginPath: String,
    isLoggedIn: Boolean,
  }

  static targets = [
    'studiosList',
    'myStudios',
    'exploreStudios',
    'myStudiosHeader',
    'exploreStudiosHeader',
    'loadMore',
  ]

  connect() {
    this.cursor = null
    this.hasMoreStudios = true
    this.loading = false
    this.allStudios = []

    this._readTranslations()
    this._fetchAllStudios()

    // Close dropdowns when clicking outside
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
    if (!this.loading && this.hasMoreStudios) {
      await this._fetchAllStudios()
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

  async _fetchAllStudios() {
    if (this.loading || !this.hasMoreStudios) {
      return
    }

    this.loading = true

    const params = new URLSearchParams({ limit: '20' })
    if (this.cursor) {
      params.set('cursor', this.cursor)
    }

    const url = this.apiBaseUrlValue + '/studios?' + params.toString()

    try {
      const response = await fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'Accept-Language': new AcceptLanguage().get(),
        },
      })

      if (!response.ok) {
        console.error('Failed to fetch studios:', response.status)
        this.loading = false
        return
      }

      const json = await response.json()
      const studios = json.data || []
      this.hasMoreStudios = json.has_more || false
      this.cursor = json.next_cursor || null

      this.allStudios = this.allStudios.concat(studios)

      if (this.isLoggedInValue) {
        this._renderTwoSections()
      } else {
        this._renderSingleList(studios)
      }

      this._updateLoadMoreButton()
    } catch (error) {
      console.error('Error fetching studios:', error)
    } finally {
      this.loading = false
      this._removeSkeletons()
    }
  }

  _removeSkeletons() {
    this.element.querySelectorAll('.js-skeleton').forEach((el) => el.remove())
  }

  _renderTwoSections() {
    const myStudios = this.allStudios.filter(
      (s) => s.is_member === true || s.join_request_status === 'pending',
    )
    const exploreStudios = this.allStudios.filter(
      (s) => s.is_member !== true && s.join_request_status !== 'pending',
    )

    // Sort explore studios by member count descending
    exploreStudios.sort((a, b) => {
      const countA = parseInt(a.members_count, 10) || 0
      const countB = parseInt(b.members_count, 10) || 0
      return countB - countA
    })

    if (this.hasMyStudiosHeaderTarget) {
      this.myStudiosHeaderTarget.textContent = this.translations.myStudios
    }
    if (this.hasExploreStudiosHeaderTarget) {
      this.exploreStudiosHeaderTarget.textContent = this.translations.explore
    }

    this._renderSection(this.myStudiosTarget, myStudios, this.translations.noJoined, true)
    this._renderSection(
      this.exploreStudiosTarget,
      exploreStudios,
      this.translations.noStudios,
      false,
    )

    this._bindCardEvents()
  }

  _renderSection(container, studios, emptyMessage, isMyStudios) {
    container.innerHTML = ''

    if (studios.length === 0) {
      container.innerHTML =
        '<p class="text-muted text-center py-4">' + escapeHtml(emptyMessage) + '</p>'
      return
    }

    for (const studio of studios) {
      container.insertAdjacentHTML('beforeend', this._buildStudioCard(studio, isMyStudios))
    }
  }

  _renderSingleList(studios) {
    const container = this.studiosListTarget

    if (studios.length === 0 && !this.cursor) {
      container.innerHTML =
        '<p class="text-muted text-center py-4">' + escapeHtml(this.translations.noStudios) + '</p>'
      return
    }

    for (const studio of studios) {
      container.insertAdjacentHTML('beforeend', this._buildStudioCard(studio, false))
    }

    this._bindCardEvents()
  }

  _buildStudioCard(studio, isMyStudios) {
    const id = escapeAttr(studio.id || '')
    const name = escapeHtml(studio.name || '')
    const description = escapeHtml(this._truncate(studio.description || '', 100))
    const imagePath = studio.image_path || '/images/default/thumbnail.png'
    const membersCount = parseInt(studio.members_count, 10) || 0
    const projectsCount = parseInt(studio.projects_count, 10) || 0
    const isPublic = studio.is_public !== false
    const isMember = studio.is_member === true
    const userRole = studio.user_role || null
    const joinRequestStatus = studio.join_request_status || null

    const detailUrl = this.studioDetailsPathValue.replace('__ID__', id)

    // Build pill badges
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

    // Build action area (menu + optional join button)
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
    // Build dropdown menu items
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

    // Join button shown directly on explore cards (not in menu)
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
      // Dropdown toggle
      container.querySelectorAll('.projects-list-item--menu-btn').forEach((btn) => {
        if (!btn.dataset.bound) {
          btn.dataset.bound = 'true'
          btn.addEventListener('click', (e) => {
            e.preventDefault()
            e.stopPropagation()
            const dropdown = btn.nextElementSibling
            const isOpen = dropdown.style.display !== 'none'
            // Close all dropdowns first
            this.element.querySelectorAll('.projects-list-item--dropdown').forEach((d) => {
              d.style.display = 'none'
            })
            dropdown.style.display = isOpen ? 'none' : 'block'
          })
        }
      })

      // Share buttons inside dropdown
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

      // Leave buttons inside dropdown
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

      // Cancel request buttons inside dropdown
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

      // Join buttons (directly on card, not in dropdown)
      container.querySelectorAll('.studio-join-btn').forEach((btn) => {
        if (!btn.dataset.bound) {
          btn.dataset.bound = 'true'
          btn.addEventListener('click', (e) => {
            e.preventDefault()
            e.stopPropagation()
            this._joinStudio(btn.dataset.studioId, btn)
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
      await this._leaveStudio(studioId)
    }
  }

  async _joinStudio(studioId, btn) {
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
          headers: {
            Accept: 'application/json',
            'Accept-Language': new AcceptLanguage().get(),
          },
        },
      )

      if (response.ok) {
        const studio = this.allStudios.find((s) => String(s.id) === String(studioId))

        if (studio) {
          if (studio.is_public !== false) {
            studio.is_member = true
            studio.members_count = (parseInt(studio.members_count, 10) || 0) + 1
          } else {
            studio.join_request_status = 'pending'
          }
        }

        if (this.isLoggedInValue) {
          this._renderTwoSections()
        } else {
          const wrapper = btn.closest('.studios-list-item-wrapper')
          const countEl = wrapper?.querySelector('#studios-user-count-' + CSS.escape(studioId))
          if (countEl) {
            countEl.textContent = parseInt(countEl.textContent, 10) + 1
          }
          // Re-render the card without join button
          this._bindCardEvents()
        }
      } else if (response.status === 409) {
        // Already a member
      } else {
        console.error('Join failed:', response.status)
      }
    } catch (error) {
      console.error('Join error:', error)
    }
  }

  async _leaveStudio(studioId) {
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
          headers: {
            Accept: 'application/json',
            'Accept-Language': new AcceptLanguage().get(),
          },
        },
      )

      if (response.ok || response.status === 204) {
        const studio = this.allStudios.find((s) => String(s.id) === String(studioId))
        if (studio) {
          studio.is_member = false
          studio.members_count = Math.max(0, (parseInt(studio.members_count, 10) || 0) - 1)
        }

        if (this.isLoggedInValue) {
          this._renderTwoSections()
        }
      } else if (response.status === 422) {
        showSnackbar('#share-snackbar', 'Admins cannot leave the studio', SnackbarDuration.error)
      } else {
        console.error('Leave failed:', response.status)
      }
    } catch (error) {
      console.error('Leave error:', error)
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
      await this._cancelRequest(studioId)
    }
  }

  async _cancelRequest(studioId) {
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
          headers: {
            Accept: 'application/json',
            'Accept-Language': new AcceptLanguage().get(),
          },
        },
      )

      if (response.ok || response.status === 204) {
        const studio = this.allStudios.find((s) => String(s.id) === String(studioId))
        if (studio) {
          studio.join_request_status = null
        }

        if (this.isLoggedInValue) {
          this._renderTwoSections()
        }
      } else {
        console.error('Cancel request failed:', response.status)
      }
    } catch (error) {
      console.error('Cancel request error:', error)
    }
  }

  _updateLoadMoreButton() {
    if (this.hasLoadMoreTarget) {
      this.loadMoreTarget.style.display = this.hasMoreStudios ? '' : 'none'
    }
  }

  _truncate(str, maxLen) {
    if (str.length <= maxLen) {
      return str
    }
    return str.substring(0, maxLen) + '...'
  }
}
