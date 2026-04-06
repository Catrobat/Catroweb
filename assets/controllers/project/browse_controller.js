import { Controller } from '@hotwired/stimulus'
import { escapeHtml, escapeAttr } from '../../Components/HtmlEscape'
import { shareOrCopy } from '../../Components/ClipboardHelper'
import AcceptLanguage from '../../Api/AcceptLanguage'
import '../../Components/RetentionTooltip'

export default class extends Controller {
  static values = {
    apiBaseUrl: String,
    projectDetailPath: String,
    uploadPath: String,
    loginPath: String,
    isLoggedIn: Boolean,
    userId: String,
  }

  static targets = [
    'myProjects',
    'myProjectsHeader',
    'myProjectsEmpty',
    'exploreProjects',
    'exploreProjectsHeader',
    'loadMore',
  ]

  connect() {
    this.exploreCursor = null
    this.hasMoreExplore = true
    this.exploreLoading = false
    this.myProjectsLoaded = false

    this._readTranslations()

    if (this.isLoggedInValue) {
      this._fetchMyProjects()
    }

    this._fetchExploreProjects()
  }

  async loadMore() {
    if (!this.exploreLoading && this.hasMoreExplore) {
      await this._fetchExploreProjects()
    }
  }

  _readTranslations() {
    this.translations = {
      myProjects: this.element.dataset.transMyProjects || 'My Projects',
      explore: this.element.dataset.transExplore || 'Explore Projects',
      noProjects: this.element.dataset.transNoProjects || "You haven't created any projects yet",
      loadMore: this.element.dataset.transLoadMore || 'Load More',
      noExplore: this.element.dataset.transNoExplore || 'No projects found',
      downloads: this.element.dataset.transDownloads || '%downloads% downloads',
      views: this.element.dataset.transViews || '%views% views',
    }
  }

  async _fetchMyProjects() {
    const url =
      this.apiBaseUrlValue +
      '/projects/user' +
      '?limit=50&attributes=id,name,project_url,screenshot_small,downloads,uploaded_string,retention_days,retention_expiry'

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
        console.error('Failed to fetch my projects:', response.status, await response.text())
        return
      }

      const projects = await response.json()
      this._renderMyProjects(projects)
    } catch (error) {
      console.error('Error fetching my projects:', error)
    }
  }

  async _fetchExploreProjects() {
    if (this.exploreLoading || !this.hasMoreExplore) {
      return
    }

    this.exploreLoading = true

    const params = new URLSearchParams({
      category: 'random',
      limit: '20',
      attributes:
        'id,name,author,screenshot_small,downloads,views,uploaded_string,retention_days,retention_expiry',
    })
    if (this.exploreCursor) {
      params.set('offset', this.exploreCursor)
    }

    const url = this.apiBaseUrlValue + '/projects?' + params.toString()

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
        console.error('Failed to fetch explore projects:', response.status)
        this.exploreLoading = false
        return
      }

      const projects = await response.json()

      if (projects.length < 20) {
        this.hasMoreExplore = false
      }

      this.exploreCursor = String(parseInt(this.exploreCursor || '0', 10) + projects.length)

      this._renderExploreProjects(projects)
      this._updateLoadMoreButton()
    } catch (error) {
      console.error('Error fetching explore projects:', error)
    } finally {
      this.exploreLoading = false
    }
  }

  _renderMyProjects(projects) {
    if (this.hasMyProjectsHeaderTarget) {
      this.myProjectsHeaderTarget.textContent = this.translations.myProjects
    }

    const container = this.myProjectsTarget

    if (!projects || projects.length === 0) {
      container.innerHTML =
        '<p class="text-muted text-center py-4">' +
        escapeHtml(this.translations.noProjects) +
        '</p>'
      return
    }

    container.innerHTML = ''
    for (const project of projects) {
      container.insertAdjacentHTML('beforeend', this._buildProjectCard(project, true))
    }

    this._bindCardEvents(container)
    this.myProjectsLoaded = true
  }

  _renderExploreProjects(projects) {
    if (this.hasExploreProjectsHeaderTarget) {
      this.exploreProjectsHeaderTarget.textContent = this.translations.explore
    }

    const container = this.exploreProjectsTarget

    if (projects.length === 0 && !this.exploreCursor) {
      container.innerHTML =
        '<p class="text-muted text-center py-4">' + escapeHtml(this.translations.noExplore) + '</p>'
      return
    }

    for (const project of projects) {
      container.insertAdjacentHTML('beforeend', this._buildProjectCard(project, false))
    }

    this._bindCardEvents(container)
  }

  _buildProjectCard(project, isOwned) {
    const id = escapeAttr(String(project.id || ''))
    const name = escapeHtml(project.name || '')
    const author = escapeHtml(project.author || '')
    const screenshotSmall = project.screenshot_small || '/images/default/thumbnail.png'
    const downloads = parseInt(project.downloads, 10) || 0
    const uploadedString = escapeHtml(project.uploaded_string || '')

    const detailUrl = this.projectDetailPathValue.replace('__ID__', id)

    const transOpen = this.element.dataset.transOpen || 'Open'
    const transDownload = this.element.dataset.transDownload || 'Download'
    const transShare = this.element.dataset.transShare || 'Share'
    const transDelete = this.element.dataset.transDelete || 'Delete'
    const transReport = this.element.dataset.transReport || 'Report'

    // Compact one-line metadata with inline icons
    let meta =
      '<span class="projects-meta__item">' +
      '<i class="material-icons">file_download</i>' +
      String(downloads) +
      '</span>'

    if (uploadedString) {
      meta +=
        '<span class="projects-meta__item">' +
        '<i class="material-icons">calendar_today</i>' +
        uploadedString +
        '</span>'
    }

    if (project.retention_days !== undefined) {
      meta += this._buildRetentionMeta(project.retention_days, project.retention_expiry)
    }

    if (!isOwned && author) {
      meta +=
        '<span class="projects-meta__item">' +
        '<i class="material-icons">person</i>' +
        author +
        '</span>'
    }

    // Common menu items: Open, Download, Share
    let menuItems =
      '<a href="' +
      escapeAttr(detailUrl) +
      '" class="projects-list-item--dropdown-item">' +
      '<i class="material-icons">open_in_new</i>' +
      escapeHtml(transOpen) +
      '</a>' +
      '<a href="/api/project/' +
      id +
      '/catrobat" download class="projects-list-item--dropdown-item">' +
      '<i class="material-icons">download</i>' +
      escapeHtml(transDownload) +
      '</a>' +
      '<button class="projects-list-item--dropdown-item" data-action="share" data-project-id="' +
      id +
      '">' +
      '<i class="material-icons">share</i>' +
      escapeHtml(transShare) +
      '</button>'

    if (isOwned) {
      const transToggleVisibility = this.element.dataset.transToggleVisibility || 'Set Private'
      const transNotForKids = this.element.dataset.transNotForKids || 'Mark as not safe for kids'
      menuItems +=
        '<div class="projects-list-item--dropdown-divider"></div>' +
        '<button class="projects-list-item--dropdown-item" data-action="toggle-visibility" data-project-id="' +
        id +
        '">' +
        '<i class="material-icons">lock</i>' +
        escapeHtml(transToggleVisibility) +
        '</button>' +
        '<button class="projects-list-item--dropdown-item" data-action="not-for-kids" data-project-id="' +
        id +
        '">' +
        '<i class="material-icons">child_care</i>' +
        escapeHtml(transNotForKids) +
        '</button>' +
        '<div class="projects-list-item--dropdown-divider"></div>' +
        '<button class="projects-list-item--dropdown-item text-danger" data-action="delete" data-project-id="' +
        id +
        '">' +
        '<i class="material-icons">delete</i>' +
        escapeHtml(transDelete) +
        '</button>'
    } else {
      // Other users' projects: Report action
      menuItems +=
        '<button class="projects-list-item--dropdown-item text-danger" data-action="report" data-project-id="' +
        id +
        '">' +
        '<i class="material-icons">flag</i>' +
        escapeHtml(transReport) +
        '</button>'
    }

    const menuHtml =
      '<div class="projects-list-item--actions">' +
      '<button class="btn projects-list-item--menu-btn" data-project-id="' +
      id +
      '">' +
      '<i class="material-icons">more_vert</i>' +
      '</button>' +
      '<div class="projects-list-item--dropdown" style="display:none;">' +
      menuItems +
      '</div>' +
      '</div>'

    return (
      '<div class="projects-list-item-wrapper" data-project-id="' +
      id +
      '">' +
      '<a href="' +
      escapeAttr(detailUrl) +
      '" class="projects-list-item-link">' +
      '<img src="' +
      escapeAttr(screenshotSmall) +
      '" class="projects-list-item--image" alt="" loading="lazy">' +
      '<div class="projects-list-item--content">' +
      '<h3 class="projects-list-item--name">' +
      name +
      '</h3>' +
      '<div class="projects-meta">' +
      meta +
      '</div>' +
      '</div>' +
      '</a>' +
      menuHtml +
      '</div>'
    )
  }

  _bindCardEvents(container) {
    // Toggle dropdown menus
    container.querySelectorAll('.projects-list-item--menu-btn').forEach((btn) => {
      if (btn.dataset.bound) return
      btn.dataset.bound = 'true'
      btn.addEventListener('click', (e) => {
        e.preventDefault()
        e.stopPropagation()
        const dropdown = btn.nextElementSibling
        const isOpen = dropdown.style.display !== 'none'
        // Close all others first
        container.querySelectorAll('.projects-list-item--dropdown').forEach((d) => {
          d.style.display = 'none'
        })
        dropdown.style.display = isOpen ? 'none' : 'block'
      })
    })

    // Share action
    container.querySelectorAll('[data-action="share"]').forEach((btn) => {
      if (btn.dataset.bound) return
      btn.dataset.bound = 'true'
      btn.addEventListener('click', (e) => {
        e.preventDefault()
        e.stopPropagation()
        const url = window.location.origin + '/app/project/' + btn.dataset.projectId
        btn.closest('.projects-list-item--dropdown').style.display = 'none'
        shareOrCopy(url, () => {
          import('../../Layout/Snackbar').then(({ showSnackbar }) => {
            showSnackbar(
              '#share-snackbar',
              this.element.dataset.transShareSuccess || 'Link copied!',
            )
          })
        })
      })
    })

    // Delete action
    container.querySelectorAll('[data-action="delete"]').forEach((btn) => {
      if (btn.dataset.bound) return
      btn.dataset.bound = 'true'
      btn.addEventListener('click', async (e) => {
        e.preventDefault()
        e.stopPropagation()
        btn.closest('.projects-list-item--dropdown').style.display = 'none'
        const projectId = btn.dataset.projectId
        const { default: Swal } = await import('sweetalert2')
        const result = await Swal.fire({
          title: this.element.dataset.transAreYouSure || 'Are you sure?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: this.element.dataset.transDeleteConfirm || 'Delete',
          cancelButtonText: this.element.dataset.transCancel || 'Cancel',
          customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-outline-primary' },
          buttonsStyling: false,
        })
        if (result.isConfirmed) {
          await fetch(this.apiBaseUrlValue + '/project/' + projectId, {
            method: 'DELETE',
            credentials: 'same-origin',
          })
          const wrapper = container.querySelector(
            '.projects-list-item-wrapper[data-project-id="' + CSS.escape(projectId) + '"]',
          )
          if (wrapper) wrapper.remove()
        }
      })
    })

    // Report action
    container.querySelectorAll('[data-action="report"]').forEach((btn) => {
      if (btn.dataset.bound) return
      btn.dataset.bound = 'true'
      btn.addEventListener('click', async (e) => {
        e.preventDefault()
        e.stopPropagation()
        btn.closest('.projects-list-item--dropdown').style.display = 'none'
        const projectId = btn.dataset.projectId

        const { showReportDialog } = await import('../../Moderation/ReportDialog')
        showReportDialog({
          contentType: 'project',
          contentId: projectId,
          apiUrl: '/api/project/' + projectId + '/report',
          loginUrl: this.loginPathValue,
          isLoggedIn: this.isLoggedInValue,
          translations: {
            title: this.element.dataset.transReportTitle || 'Report',
            submit: this.element.dataset.transReportSubmit || 'Submit',
            cancel: this.element.dataset.transReportCancel || 'Cancel',
            success: this.element.dataset.transReportSuccess || 'Report submitted',
            error: this.element.dataset.transReportError || 'Error submitting report',
            duplicate: this.element.dataset.transReportDuplicate || 'Already reported',
            trustTooLow: this.element.dataset.transReportTrustTooLow || 'Trust too low',
            unverified: this.element.dataset.transReportUnverified || 'Email verification required',
            suspended: this.element.dataset.transReportSuspended || 'Account suspended',
            rateLimited: this.element.dataset.transReportRateLimited || 'Too many reports',
            notePlaceholder:
              this.element.dataset.transReportPlaceholder || 'Please describe the issue...',
          },
        })
      })
    })

    // Toggle visibility action
    container.querySelectorAll('[data-action="toggle-visibility"]').forEach((btn) => {
      if (btn.dataset.bound) return
      btn.dataset.bound = 'true'
      btn.addEventListener('click', async (e) => {
        e.preventDefault()
        e.stopPropagation()
        btn.closest('.projects-list-item--dropdown').style.display = 'none'
        const projectId = btn.dataset.projectId
        const { default: ProjectApi } = await import('../../Api/ProjectApi')
        const api = new ProjectApi()
        api.updateProject(projectId, { private: true }, () => window.location.reload())
      })
    })

    // Not for kids action
    container.querySelectorAll('[data-action="not-for-kids"]').forEach((btn) => {
      if (btn.dataset.bound) return
      btn.dataset.bound = 'true'
      btn.addEventListener('click', async (e) => {
        e.preventDefault()
        e.stopPropagation()
        btn.closest('.projects-list-item--dropdown').style.display = 'none'
        const projectId = btn.dataset.projectId
        const { default: ProjectApi } = await import('../../Api/ProjectApi')
        const api = new ProjectApi()
        api.updateProject(projectId, { not_for_kids: true }, () => window.location.reload())
      })
    })

    // Close dropdown on outside click
    document.addEventListener('click', () => {
      container.querySelectorAll('.projects-list-item--dropdown').forEach((d) => {
        d.style.display = 'none'
      })
    })
  }

  _updateLoadMoreButton() {
    if (this.hasLoadMoreTarget) {
      this.loadMoreTarget.style.display = this.hasMoreExplore ? '' : 'none'
    }
  }

  _buildRetentionMeta(retentionDays, retentionExpiry) {
    const ds = this.element.dataset
    const tooltip = escapeAttr(
      retentionDays === -1
        ? ds.transRetentionTooltipProtected ||
            'This project is protected and will not be automatically deleted.'
        : ds.transRetentionTooltip ||
            'Projects are automatically removed after a period of inactivity.',
    )
    const infoBtn =
      '<span class="retention-info-wrap" onclick="event.preventDefault();event.stopPropagation()">' +
      '<span class="material-icons retention-info-icon">info_outline</span>' +
      '<span class="retention-tooltip">' +
      tooltip +
      '</span>' +
      '</span>'

    if (retentionDays === -1) {
      return (
        '<span class="projects-meta__item projects-meta__item--retention projects-meta__item--protected">' +
        '<i class="material-icons">verified</i>' +
        escapeHtml(ds.transRetentionProtected || 'Protected') +
        infoBtn +
        '</span>'
      )
    }

    let daysLeft = 0
    if (retentionExpiry) {
      daysLeft = Math.max(0, Math.ceil((new Date(retentionExpiry) - Date.now()) / 86400000))
    }

    let icon = 'schedule'
    let cssModifier = ''
    if (daysLeft <= 7) {
      icon = 'warning'
      cssModifier = ' projects-meta__item--critical'
    } else if (daysLeft <= 30) {
      icon = 'hourglass_bottom'
      cssModifier = ' projects-meta__item--warning'
    }

    const label =
      daysLeft === 1
        ? ds.transRetentionDay || '1 day left'
        : (ds.transRetentionDays || '%days% days left').replace('%days%', String(daysLeft))

    return (
      '<span class="projects-meta__item projects-meta__item--retention' +
      cssModifier +
      '">' +
      '<i class="material-icons">' +
      icon +
      '</i>' +
      escapeHtml(label) +
      infoBtn +
      '</span>'
    )
  }
}
