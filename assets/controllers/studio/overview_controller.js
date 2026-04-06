import { Controller } from '@hotwired/stimulus'
import { escapeHtml, escapeAttr } from '../../Components/HtmlEscape'
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

    const url = this.apiBaseUrlValue + '/studio?' + params.toString()

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
    }
  }

  _renderTwoSections() {
    const myStudios = this.allStudios.filter((s) => s.is_member === true)
    const exploreStudios = this.allStudios.filter((s) => s.is_member !== true)

    // Sort my studios by recent activity (use updated_at or id as proxy)
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

    this._renderSection(this.myStudiosTarget, myStudios, this.translations.noJoined)
    this._renderSection(this.exploreStudiosTarget, exploreStudios, this.translations.noStudios)

    this._bindCardEvents()
  }

  _renderSection(container, studios, emptyMessage) {
    container.innerHTML = ''

    if (studios.length === 0) {
      container.innerHTML =
        '<p class="text-muted text-center py-4">' + escapeHtml(emptyMessage) + '</p>'
      return
    }

    for (const studio of studios) {
      container.insertAdjacentHTML('beforeend', this._buildStudioCard(studio))
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
      container.insertAdjacentHTML('beforeend', this._buildStudioCard(studio))
    }

    this._bindCardEvents()
  }

  _buildStudioCard(studio) {
    const id = escapeAttr(studio.id || '')
    const name = escapeHtml(studio.name || '')
    const description = escapeHtml(this._truncate(studio.description || '', 100))
    const imagePath = studio.image_path || '/images/default/thumbnail.png'
    const membersCount = parseInt(studio.members_count, 10) || 0
    const projectsCount = parseInt(studio.projects_count, 10) || 0
    const isPublic = studio.is_public !== false
    const isMember = studio.is_member === true
    const joinRequestStatus = studio.join_request_status || null

    const detailUrl = this.studioDetailsPathValue.replace('__ID__', id)

    let actionButton = ''
    if (this.isLoggedInValue) {
      actionButton = this._buildActionButton(id, isMember, joinRequestStatus)
    }

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
      actionButton +
      '</div>'
    )
  }

  _buildActionButton(studioId, isMember, joinRequestStatus) {
    const buttonHtml = this._buildMembershipButton(studioId, isMember, joinRequestStatus)

    return (
      '<div class="studios-list-item--buttons d-flex align-items-center">' + buttonHtml + '</div>'
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

      container.querySelectorAll('.studio-leave-btn').forEach((btn) => {
        if (!btn.dataset.bound) {
          btn.dataset.bound = 'true'
          btn.addEventListener('click', (e) => {
            e.preventDefault()
            e.stopPropagation()
            this._leaveStudio(btn.dataset.studioId, btn)
          })
        }
      })
    }
  }

  async _joinStudio(studioId, btn) {
    if (!this.isLoggedInValue) {
      window.location.href = this.loginPathValue
      return
    }

    try {
      const response = await fetch(
        this.apiBaseUrlValue + '/studio/' + encodeURIComponent(studioId) + '/join',
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
            // Public studio: joined immediately
            studio.is_member = true
            studio.members_count = (parseInt(studio.members_count, 10) || 0) + 1
          } else {
            // Private studio: join request created, pending approval
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
          const buttonsContainer = btn.closest('.studios-list-item--buttons')
          if (buttonsContainer) {
            buttonsContainer.innerHTML = this._buildMembershipButton(studioId, true, null)
            this._bindCardEvents()
          }
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

  async _leaveStudio(studioId, btn) {
    if (!this.isLoggedInValue) {
      window.location.href = this.loginPathValue
      return
    }

    try {
      const response = await fetch(
        this.apiBaseUrlValue + '/studio/' + encodeURIComponent(studioId) + '/leave',
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
        // Update the studio in allStudios
        const studio = this.allStudios.find((s) => String(s.id) === String(studioId))
        if (studio) {
          studio.is_member = false
          studio.members_count = Math.max(0, (parseInt(studio.members_count, 10) || 0) - 1)
        }

        if (this.isLoggedInValue) {
          this._renderTwoSections()
        } else {
          const wrapper = btn.closest('.studios-list-item-wrapper')
          const countEl = wrapper?.querySelector('#studios-user-count-' + CSS.escape(studioId))
          if (countEl) {
            countEl.textContent = Math.max(0, parseInt(countEl.textContent, 10) - 1)
          }
          const buttonsContainer = btn.closest('.studios-list-item--buttons')
          if (buttonsContainer) {
            buttonsContainer.innerHTML = this._buildMembershipButton(studioId, false, null)
            this._bindCardEvents()
          }
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

  _buildMembershipButton(studioId, isMember, joinRequestStatus) {
    if (isMember) {
      return (
        '<button class="btn btn-sm btn-outline-secondary studio-action-btn studio-leave-btn" ' +
        'data-studio-id="' +
        escapeAttr(studioId) +
        '" data-action-type="leave">' +
        escapeHtml(this.translations.joined) +
        '</button>'
      )
    }
    if (joinRequestStatus === 'pending') {
      return (
        '<span class="btn btn-sm btn-outline-warning disabled studio-action-btn">' +
        escapeHtml(this.translations.pending) +
        '</span>'
      )
    }
    if (joinRequestStatus === 'declined') {
      return (
        '<span class="btn btn-sm btn-outline-danger disabled studio-action-btn">' +
        escapeHtml(this.translations.declined) +
        '</span>'
      )
    }
    return (
      '<button class="btn btn-sm btn-primary studio-action-btn studio-join-btn" ' +
      'data-studio-id="' +
      escapeAttr(studioId) +
      '" data-action-type="join">' +
      escapeHtml(this.translations.join) +
      '</button>'
    )
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
