import { showDefaultTopBarTitle, showCustomTopBarTitle } from '../Layout/TopBar'
import { escapeHtml, escapeAttr } from '../Components/HtmlEscape'
import { shareOrCopy } from '../Components/ClipboardHelper'

require('./ProjectList.scss')

const projectListRegistry = new Map()

export function getProjectListInstance(containerId) {
  return projectListRegistry.get(containerId) || null
}

export class ProjectList {
  constructor(
    container,
    category,
    apiUrl,
    propertyToShow,
    theme,
    fetchCount = 30,
    emptyMessage = '',
  ) {
    this.container = container
    this.projectsContainer = container.querySelector('.projects-container')
    this.category = category
    this.propertyToShow = propertyToShow
    this.cardType = container.dataset.cardType || 'project'
    this.projectsLoaded = 0
    this.projectFetchCount = fetchCount
    this.empty = false
    this.fetchActive = false
    this.isFullView = false
    this.theme = theme
    this.emptyMessage = emptyMessage
    this.$title = container.querySelector('.project-list__title')
    this.$body = document.body
    this.$chevronLeft = container.querySelector('.project-list__chevrons__left')
    this.$chevronRight = container.querySelector('.project-list__chevrons__right')
    if (this.cardType === 'studio') {
      const sep = apiUrl.includes('?') ? '&' : '?'
      this.apiUrl = apiUrl + sep
    } else {
      this.apiUrl = this.formatApiUrl(apiUrl)
    }
    this.popStateHandler = this.closeFullView.bind(this)

    // Action menu config
    this.showActions = container.dataset.showActions === 'true'
    this.isOwnProfile = container.dataset.isOwnProfile === 'true'
    this.isLoggedIn = container.dataset.isLoggedIn === 'true'
    this.projectDetailPath = container.dataset.projectDetailPath || ''

    if (container.id) {
      projectListRegistry.set(container.id, this)
    }

    this.fetchMore(true)
    this.initListeners()
  }

  formatApiUrl(apiUrl) {
    let attributes =
      'id,name,project_url,screenshot_small,screenshot_large,not_for_kids,uploaded_string,'

    if (this.propertyToShow && !attributes.includes(`,${this.propertyToShow},`)) {
      attributes += this.propertyToShow
    }
    return apiUrl.includes('?')
      ? `${apiUrl}&attributes=${attributes}&`
      : `${apiUrl}?attributes=${attributes}&`
  }

  fetchMore(clear = false) {
    if (this.empty || this.fetchActive) {
      return
    }

    this.fetchActive = true

    fetch(`${this.apiUrl}limit=${this.projectFetchCount}&offset=${this.projectsLoaded}`)
      .then((response) => {
        if (!response.ok) throw new Error('HTTP ' + response.status)
        return response.json()
      })
      .then((data) => {
        const items = Array.isArray(data) ? data : data?.data || []
        if (!Array.isArray(items)) {
          console.error(`Data received for ${this.category} is not an array!`)
          this.container.classList.remove('loading')
          return
        }

        if (clear) {
          this.projectsContainer.innerHTML = ''
        }

        items.forEach((item) => {
          const el =
            this.cardType === 'studio'
              ? this.generateStudioElement(item)
              : this.generateProjectElement(item)
          this.projectsContainer.appendChild(el)
        })

        this.container.classList.remove('loading')
        this.updateChevronVisibility()

        this.projectsLoaded += items.length

        if (this.projectsLoaded === 0 && !this.empty) {
          this.empty = true
          this.displayEmptyMessage()
        }

        this.fetchActive = false
      })
      .catch((error) => {
        console.error(`Failed loading projects in category ${this.category}`, error)
        this.container.classList.remove('loading')
        this.fetchActive = false
      })
  }

  generateProjectElement(data) {
    const projectUrl = data.project_url.replace('/app/', `/${this.theme}/`)

    if (this.showActions) {
      return this._generateProjectElementWithActions(data, projectUrl)
    }

    const projectElement = document.createElement('a')
    projectElement.className = 'project-list__project'
    projectElement.href = projectUrl
    projectElement.dataset.id = data.id

    const img = this.createImageElement(data)
    projectElement.appendChild(img)

    const nameSpan = document.createElement('span')
    nameSpan.className = 'project-list__project__name'
    nameSpan.textContent = data.name
    projectElement.appendChild(nameSpan)

    const propDiv = this.createPropertyElement(data)
    if (data.not_for_kids) {
      this.addNotForKidsIcon(propDiv)
    }
    projectElement.appendChild(propDiv)

    return projectElement
  }

  _generateProjectElementWithActions(data, projectUrl) {
    const id = escapeAttr(String(data.id || ''))
    const name = escapeHtml(data.name || '')
    const screenshotSmall = data.screenshot_small || '/images/default/screenshot.png'
    const uploadedString = escapeHtml(data.uploaded_string || '')

    const detailUrl = this.projectDetailPath
      ? this.projectDetailPath.replace('__ID__', id)
      : projectUrl

    const ds = this.container.dataset
    const transOpen = ds.transOpen || 'Open'
    const transDownload = ds.transDownload || 'Download'
    const transShare = ds.transShare || 'Share'
    const transToggleVisibility = ds.transToggleVisibility || 'Set Private'
    const transDelete = ds.transDelete || 'Delete'
    const transReport = ds.transReport || 'Report'

    // Metadata line
    let meta = ''
    if (uploadedString) {
      meta +=
        '<span class="projects-meta__item">' +
        '<i class="material-icons">schedule</i>' +
        uploadedString +
        '</span>'
    }

    // Common menu items
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

    if (this.isOwnProfile) {
      menuItems +=
        '<button class="projects-list-item--dropdown-item" data-action="toggle-visibility" data-project-id="' +
        id +
        '">' +
        '<i class="material-icons">lock</i>' +
        escapeHtml(transToggleVisibility) +
        '</button>' +
        '<div class="projects-list-item--dropdown-divider"></div>' +
        '<button class="projects-list-item--dropdown-item text-danger" data-action="delete" data-project-id="' +
        id +
        '">' +
        '<i class="material-icons">delete</i>' +
        escapeHtml(transDelete) +
        '</button>'
    } else {
      menuItems +=
        '<button class="projects-list-item--dropdown-item text-danger" data-action="report" data-project-id="' +
        id +
        '">' +
        '<i class="material-icons">flag</i>' +
        escapeHtml(transReport) +
        '</button>'
    }

    const wrapper = document.createElement('div')
    wrapper.className = 'project-list__project projects-list-item-wrapper'
    wrapper.dataset.projectId = id
    wrapper.innerHTML =
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

    this._bindActionsForElement(wrapper)

    return wrapper
  }

  _bindActionsForElement(wrapper) {
    const ds = this.container.dataset

    // Toggle dropdown
    const menuBtn = wrapper.querySelector('.projects-list-item--menu-btn')
    if (menuBtn) {
      menuBtn.addEventListener('click', (e) => {
        e.preventDefault()
        e.stopPropagation()
        const dropdown = menuBtn.nextElementSibling
        const isOpen = dropdown.style.display !== 'none'
        // Close all dropdowns first
        this.projectsContainer.querySelectorAll('.projects-list-item--dropdown').forEach((d) => {
          d.style.display = 'none'
        })
        dropdown.style.display = isOpen ? 'none' : 'block'
      })
    }

    // Share action
    wrapper.querySelectorAll('[data-action="share"]').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.preventDefault()
        e.stopPropagation()
        btn.closest('.projects-list-item--dropdown').style.display = 'none'
        const url = window.location.origin + '/app/project/' + btn.dataset.projectId
        shareOrCopy(url, () => {
          import('../Layout/Snackbar').then(({ showSnackbar }) => {
            showSnackbar('#share-snackbar', ds.transShareSuccess || 'Link copied!')
          })
        })
      })
    })

    // Delete action
    wrapper.querySelectorAll('[data-action="delete"]').forEach((btn) => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault()
        e.stopPropagation()
        btn.closest('.projects-list-item--dropdown').style.display = 'none'
        const projectId = btn.dataset.projectId
        const { default: Swal } = await import('sweetalert2')
        const result = await Swal.fire({
          title: ds.transAreYouSure || 'Are you sure?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: ds.transDeleteConfirm || 'Delete',
          cancelButtonText: ds.transCancel || 'Cancel',
          customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-outline-primary' },
          buttonsStyling: false,
        })
        if (result.isConfirmed) {
          const baseUrl = ds.baseUrl || ''
          const delResp = await fetch(baseUrl + '/api/project/' + projectId, {
            method: 'DELETE',
            credentials: 'same-origin',
          })
          if (delResp.ok) {
            wrapper.remove()
          } else {
            const { showSnackbar, SnackbarDuration } = await import('../Layout/Snackbar')
            showSnackbar(
              '#share-snackbar',
              ds.transDeleteError || 'Failed to delete project.',
              SnackbarDuration.error,
            )
          }
        }
      })
    })

    // Report action
    wrapper.querySelectorAll('[data-action="report"]').forEach((btn) => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault()
        e.stopPropagation()
        btn.closest('.projects-list-item--dropdown').style.display = 'none'
        const projectId = btn.dataset.projectId

        const { showReportDialog } = await import('../Moderation/ReportDialog')
        showReportDialog({
          contentType: 'project',
          contentId: projectId,
          apiUrl: '/api/project/' + projectId + '/report',
          loginUrl: ds.loginUrl || '/app/login',
          isLoggedIn: this.isLoggedIn,
          translations: {
            title: ds.transReportTitle || 'Report',
            submit: ds.transReportSubmit || 'Submit',
            cancel: ds.transReportCancel || 'Cancel',
            success: ds.transReportSuccess || 'Report submitted',
            error: ds.transReportError || 'Error submitting report',
            duplicate: ds.transReportDuplicate || 'Already reported',
            trustTooLow: ds.transReportTrustTooLow || 'Trust too low',
            unverified: ds.transReportUnverified || 'Email verification required',
            suspended: ds.transReportSuspended || 'Account suspended',
            rateLimited: ds.transReportRateLimited || 'Too many reports',
            notePlaceholder: ds.transReportPlaceholder || 'Please describe the issue...',
          },
        })
      })
    })

    // Toggle visibility action
    wrapper.querySelectorAll('[data-action="toggle-visibility"]').forEach((btn) => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault()
        e.stopPropagation()
        btn.closest('.projects-list-item--dropdown').style.display = 'none'
        const projectId = btn.dataset.projectId
        const { default: Swal } = await import('sweetalert2')
        const result = await Swal.fire({
          title: ds.transToggleVisibilityTitle || 'Change visibility?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: ds.transConfirm || 'Confirm',
          cancelButtonText: ds.transCancel || 'Cancel',
          customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-outline-primary',
          },
          buttonsStyling: false,
        })
        if (result.isConfirmed) {
          const { default: ProjectApi } = await import('../Api/ProjectApi')
          const api = new ProjectApi()
          // Fetch current project state to determine toggle direction
          try {
            const projResp = await fetch(
              (ds.baseUrl || '') + '/api/project/' + projectId + '?attributes=private',
            )
            if (projResp.ok) {
              const projData = await projResp.json()
              const currentlyPrivate = projData.private || false
              api.updateProject(projectId, { private: !currentlyPrivate }, () =>
                window.location.reload(),
              )
            }
          } catch {
            // fallback: reload to let user retry
            window.location.reload()
          }
        }
      })
    })
  }

  generateStudioElement(data) {
    const studioUrl = '/app/studio/' + data.id

    const el = document.createElement('a')
    el.className = 'project-list__project'
    el.href = studioUrl
    el.dataset.id = data.id

    const img = document.createElement('img')
    img.setAttribute('data-src', data.image_path || '/images/default/screenshot.png')
    img.className = 'lazyload project-list__project__image'
    img.style.aspectRatio = '1 / 1'
    img.style.objectFit = 'cover'
    el.appendChild(img)

    const nameSpan = document.createElement('span')
    nameSpan.className = 'project-list__project__name'
    nameSpan.textContent = data.name || ''
    el.appendChild(nameSpan)

    const propDiv = document.createElement('div')
    propDiv.className =
      'lazyload project-list__project__property project-list__project__property-members'

    const icon = document.createElement('i')
    icon.className = 'material-icons'
    icon.textContent = 'group'
    propDiv.appendChild(icon)

    const valueSpan = document.createElement('span')
    valueSpan.className = 'project-list__project__property__value'
    const transMembers = this.container.dataset.transMembers || 'members'
    valueSpan.textContent = (data.members_count || 0) + ' ' + transMembers
    propDiv.appendChild(valueSpan)

    el.appendChild(propDiv)

    return el
  }

  createImageElement(data) {
    const img = document.createElement('img')
    img.setAttribute('data-src', data.screenshot_small)
    img.setAttribute('data-srcset', `${data.screenshot_small} 80w, ${data.screenshot_large} 480w`)
    img.setAttribute('data-sizes', '(min-width: 768px) 10vw, 25vw')
    img.className = 'lazyload project-list__project__image'
    if (data.not_for_kids) {
      img.style.filter = 'blur(10px)'
    }
    return img
  }

  createPropertyElement(data) {
    const propDiv = document.createElement('div')
    propDiv.className = `lazyload project-list__project__property project-list__project__property-${this.propertyToShow}`

    const icons = {
      views: 'visibility',
      downloads: 'get_app',
      uploaded: 'schedule',
      author: 'person',
    }

    const propertyValue =
      this.propertyToShow === 'uploaded' ? data.uploaded_string : data[this.propertyToShow]

    const icon = document.createElement('i')
    icon.className = 'material-icons'
    icon.textContent = icons[this.propertyToShow]
    propDiv.appendChild(icon)

    const valueSpan = document.createElement('span')
    valueSpan.className = 'project-list__project__property__value'
    valueSpan.textContent = propertyValue
    propDiv.appendChild(valueSpan)

    return propDiv
  }

  addNotForKidsIcon(propDiv) {
    const notForKidsImg = document.createElement('img')
    notForKidsImg.className = 'project-list__not-for-kids-logo'
    notForKidsImg.src = '/images/default/not_for_kids.svg'
    notForKidsImg.alt = 'Not for kids'
    propDiv.appendChild(notForKidsImg)
  }

  updateChevronVisibility() {
    if (!this.$chevronRight) {
      return
    }
    if (this.projectsContainer.scrollWidth > this.projectsContainer.clientWidth) {
      this.$chevronRight.style.display = 'block'
    } else {
      this.$chevronRight.style.display = 'none'
    }
  }

  displayEmptyMessage() {
    if (this.emptyMessage) {
      this.projectsContainer.innerHTML = this.emptyMessage
      this.container.classList.add('empty-with-text')
    } else {
      this.container.classList.add('empty')
    }
  }

  initListeners() {
    window.addEventListener('popstate', this.handlePopState.bind(this))
    this.projectsContainer?.addEventListener('scroll', this.handleHorizontalScroll.bind(this))
    this.container?.addEventListener('scroll', this.handleVerticalScroll.bind(this))
    this.$title?.addEventListener('click', this.handleTitleClick.bind(this))
    this.$chevronLeft?.addEventListener('click', this.handleChevronLeftClick.bind(this))
    this.$chevronRight?.addEventListener('click', this.handleChevronRightClick.bind(this))

    if (this.showActions) {
      document.addEventListener('click', () => {
        this.projectsContainer.querySelectorAll('.projects-list-item--dropdown').forEach((d) => {
          d.style.display = 'none'
        })
      })
    }
  }

  handlePopState(event) {
    if (event.state && event.state.type === 'ProjectList' && event.state.full === true) {
      const list = getProjectListInstance(event.state.id)
      if (list) {
        list.openFullView()
      }
    }
  }

  handleHorizontalScroll() {
    const pctHorizontal =
      this.projectsContainer.scrollLeft /
      (this.projectsContainer.scrollWidth - this.projectsContainer.clientWidth)
    if (pctHorizontal >= 0.8) {
      this.fetchMore()
    }
    this.$chevronLeft.style.display = pctHorizontal === 0 ? 'none' : 'block'
    this.$chevronRight.style.display = pctHorizontal >= 1 ? 'none' : 'block'
  }

  handleVerticalScroll() {
    const pctVertical =
      this.container.scrollTop / (this.container.scrollHeight - this.container.clientHeight)
    if (pctVertical >= 0.8) {
      this.fetchMore()
    }
  }

  handleTitleClick() {
    if (this.isFullView) {
      window.history.back()
    } else {
      this.openFullView()
      window.history.pushState(
        { type: 'ProjectList', id: this.container.id, full: true },
        this.$title.querySelector('h2').textContent,
        `#${this.container.id}`,
      )
    }
  }

  handleChevronLeftClick() {
    const width = this.projectsContainer.querySelector('.project-list__project').offsetWidth
    this.projectsContainer.scrollLeft -= 2 * width
  }

  handleChevronRightClick() {
    const width = this.projectsContainer.querySelector('.project-list__project').offsetWidth
    this.projectsContainer.scrollLeft += 2 * width
  }

  openFullView() {
    window.addEventListener('popstate', this.popStateHandler)
    showCustomTopBarTitle(this.$title.querySelector('h2').textContent, () => {
      window.history.back()
    })
    this.$title.style.display = 'none'
    this.isFullView = true
    this.container.classList.add('vertical')
    this.container.classList.remove('horizontal')
    this.$body.classList.add('overflow-hidden')
    if (
      this.container.clientHeight === this.container.scrollHeight ||
      this.container.scrollTop / (this.container.scrollHeight - this.container.clientHeight) >= 0.8
    ) {
      this.fetchMore()
    }
  }

  closeFullView() {
    window.removeEventListener('popstate', this.popStateHandler)
    showDefaultTopBarTitle()
    this.$title.style.display = 'block'
    this.isFullView = false
    this.container.classList.add('horizontal')
    this.container.classList.remove('vertical')
    this.$body.classList.remove('overflow-hidden')
    return false
  }
}
