import { normalizeApiResponse } from '../Api/ResponseHelper'
import { showCustomTopBarTitle, showDefaultTopBarTitle } from '../Layout/TopBar'
import { escapeAttr, escapeHtml } from '../Components/HtmlEscape'
import { shareOrCopy } from '../Components/ClipboardHelper'
import { buildPictureHTML, createPictureElement } from '../Layout/ImageVariants'
import '../Components/RetentionTooltip'

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
    extraHeaders = {},
  ) {
    this.container = container
    this.projectsContainer = container.querySelector('.projects-container')
    this.category = category
    this.propertyToShow = propertyToShow
    this.cardType = container.dataset.cardType || 'project'
    this.projectsLoaded = 0
    this.nextCursor = null
    this.hasMoreFromApi = true
    this.projectFetchCount = fetchCount
    this.empty = false
    this.fetchActive = false
    this.isFullView = false
    this.theme = theme
    this.emptyMessage = emptyMessage
    this.extraHeaders = extraHeaders
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
    this.projectsData = {}

    if (container.id) {
      projectListRegistry.set(container.id, this)
    }

    this.fetchMore(true)
    this.initListeners()
  }

  formatApiUrl(apiUrl) {
    let attributes =
      'id,name,project_url,screenshot,not_for_kids,uploaded_string,retention_days,retention_expiry,private,views,downloads'

    if (this.propertyToShow && !attributes.split(',').includes(this.propertyToShow)) {
      attributes += ',' + this.propertyToShow
    }
    return apiUrl.includes('?')
      ? `${apiUrl}&attributes=${attributes}&`
      : `${apiUrl}?attributes=${attributes}&`
  }

  fetchMore(clear = false) {
    if (this.empty || this.fetchActive || !this.hasMoreFromApi) {
      return
    }

    this.fetchActive = true

    let fetchUrl = `${this.apiUrl}limit=${this.projectFetchCount}`
    if (this.nextCursor) {
      fetchUrl += `&cursor=${encodeURIComponent(this.nextCursor)}`
    }

    fetch(fetchUrl, {
      headers: this.extraHeaders,
      credentials: 'same-origin',
    })
      .then((response) => {
        if (!response.ok) throw new Error('HTTP ' + response.status)
        return response.json()
      })
      .then((data) => {
        const envelope = normalizeApiResponse(data)
        const items = envelope.data || []
        if (!Array.isArray(items)) {
          console.error(`Data received for ${this.category} is not an array!`)
          this.container.classList.remove('loading')
          return
        }

        if (envelope.next_cursor !== undefined) {
          this.nextCursor = envelope.next_cursor
        }
        if (envelope.has_more !== undefined) {
          this.hasMoreFromApi = envelope.has_more
        } else {
          this.hasMoreFromApi = items.length >= this.projectFetchCount
        }

        if (clear) {
          this.projectsContainer.innerHTML = ''
        }

        items.forEach((item) => {
          if (item.id) this.projectsData[item.id] = item
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

    const imgWrap = document.createElement('div')
    imgWrap.className = 'project-list__project__image-wrap'
    const img = this.createImageElement(data)
    imgWrap.appendChild(img)
    if (data.private) {
      const lockIcon = document.createElement('i')
      lockIcon.className = 'material-icons project-list__lock-badge'
      lockIcon.textContent = 'lock'
      imgWrap.appendChild(lockIcon)
    }
    if (data.not_for_kids) {
      const nfkIcon = document.createElement('i')
      nfkIcon.className = 'material-icons project-list__lock-badge project-list__lock-badge--nfk'
      nfkIcon.textContent = 'no_accounts'
      imgWrap.appendChild(nfkIcon)
    }
    projectElement.appendChild(imgWrap)

    const nameSpan = document.createElement('span')
    nameSpan.className = 'project-list__project__name'
    nameSpan.textContent = data.name
    projectElement.appendChild(nameSpan)

    const propDiv = this.createPropertyElement(data)
    projectElement.appendChild(propDiv)

    const secondaryMeta = this._buildSecondaryMeta(data)
    if (secondaryMeta) {
      projectElement.appendChild(secondaryMeta)
    }

    if (data.retention_days !== undefined) {
      const retDiv = document.createElement('div')
      retDiv.innerHTML = this._buildRetentionMeta(
        data.retention_days,
        data.retention_expiry,
        this.container.dataset,
      )
      if (retDiv.firstChild) {
        projectElement.appendChild(retDiv.firstChild)
      }
    }

    return projectElement
  }

  _generateProjectElementWithActions(data, projectUrl) {
    const id = escapeAttr(String(data.id || ''))
    const name = escapeHtml(data.name || '')
    const uploadedString = escapeHtml(data.uploaded_string || '')

    const detailUrl = this.projectDetailPath
      ? this.projectDetailPath.replace('__ID__', id)
      : projectUrl

    const ds = this.container.dataset
    const transOpen = ds.transOpen || 'Open'
    const transDownload = ds.transDownload || 'Download'
    const transShare = ds.transShare || 'Share'
    const transSetPrivate = ds.transSetPrivate || 'Set private'
    const transSetPublic = ds.transSetPublic || 'Set public'
    const transDelete = ds.transDelete || 'Delete'
    const transReport = ds.transReport || 'Report'
    const transMarkNotForKids = ds.transMarkNotForKids || 'Mark not for kids'
    const transMarkSafeForKids = ds.transMarkSafeForKids || 'Mark safe for kids'
    const isPrivate = data.private || false
    const isNfk = data.not_for_kids || false
    const isNotForKids = data.not_for_kids || 0

    // Metadata line
    let meta = ''
    if (uploadedString) {
      meta +=
        '<span class="projects-meta__item">' +
        '<i class="material-icons">calendar_today</i>' +
        uploadedString +
        '</span>'
    }
    if (data.views !== undefined || data.downloads !== undefined) {
      meta += '<span class="project-list__meta-secondary">'
      if (data.views !== undefined) {
        meta +=
          '<span class="project-list__meta-secondary__item">' +
          '<i class="material-icons">visibility</i>' +
          escapeHtml(String(data.views)) +
          '</span>'
      }
      if (data.downloads !== undefined) {
        meta +=
          '<span class="project-list__meta-secondary__item">' +
          '<i class="material-icons">get_app</i>' +
          escapeHtml(String(data.downloads)) +
          '</span>'
      }
      meta += '</span>'
    }
    if (data.retention_days !== undefined) {
      meta += this._buildRetentionMeta(data.retention_days, data.retention_expiry, ds)
    }

    // Common menu items
    let menuItems =
      '<a href="' +
      escapeAttr(detailUrl) +
      '" class="projects-list-item--dropdown-item">' +
      '<i class="material-icons">open_in_new</i>' +
      escapeHtml(transOpen) +
      '</a>' +
      '<a href="/api/projects/' +
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
        '<div class="projects-list-item--dropdown-divider"></div>' +
        '<button class="projects-list-item--dropdown-item" data-action="toggle-visibility" data-project-id="' +
        id +
        '">' +
        '<i class="material-icons">' +
        (isPrivate ? 'lock_open' : 'lock') +
        '</i>' +
        '<span class="js-visibility-text">' +
        escapeHtml(isPrivate ? transSetPublic : transSetPrivate) +
        '</span>' +
        '</button>' +
        '<button class="projects-list-item--dropdown-item" data-action="toggle-not-for-kids" data-project-id="' +
        id +
        '">' +
        '<i class="material-icons">child_care</i>' +
        '<span class="js-nfk-text">' +
        escapeHtml(isNotForKids ? transMarkSafeForKids : transMarkNotForKids) +
        '</span>' +
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
      '<span class="projects-list-item--image-wrap">' +
      buildPictureHTML(
        data.screenshot,
        'card',
        '/images/default/screenshot-card@1x.webp',
        'class="projects-list-item--image" alt="' +
          escapeAttr(data.name || '') +
          '" width="360" height="360" loading="lazy"',
      ) +
      (isPrivate ? '<i class="material-icons projects-list-item--lock-badge">lock</i>' : '') +
      (isNfk
        ? '<i class="material-icons projects-list-item--lock-badge projects-list-item--nfk-badge">no_accounts</i>'
        : '') +
      '</span>' +
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
          const delResp = await fetch(baseUrl + '/api/projects/' + projectId, {
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
          apiUrl: '/api/projects/' + projectId + '/report',
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
        const project = this.projectsData[projectId]
        const currentlyPrivate = project?.private || false
        const { default: Swal } = await import('sweetalert2')
        const result = await Swal.fire({
          title: currentlyPrivate
            ? ds.transSetPublicConfirm || 'Make this project public?'
            : ds.transSetPrivateConfirm || 'Make this project private?',
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
          api.updateProject(projectId, { private: !currentlyPrivate }, () => {
            if (project) project.private = !currentlyPrivate
            // Update button text/icon in dropdown
            const icon = btn.querySelector('.material-icons')
            const text = btn.querySelector('.js-visibility-text')
            if (icon) icon.textContent = !currentlyPrivate ? 'lock_open' : 'lock'
            if (text) {
              text.textContent = !currentlyPrivate
                ? ds.transSetPublic || 'Set public'
                : ds.transSetPrivate || 'Set private'
            }
            // Update lock badge on thumbnail
            const lockBadge = wrapper.querySelector('.projects-list-item--lock-badge')
            if (!currentlyPrivate) {
              // Now private — add badge if missing
              if (!lockBadge) {
                const imgWrap = wrapper.querySelector('.projects-list-item--image-wrap')
                if (imgWrap) {
                  const icon = document.createElement('i')
                  icon.className = 'material-icons projects-list-item--lock-badge'
                  icon.textContent = 'lock'
                  imgWrap.appendChild(icon)
                }
              }
            } else {
              // Now public — remove badge
              if (lockBadge) lockBadge.remove()
            }
          })
        }
      })
    })

    // Toggle not-for-kids action
    wrapper.querySelectorAll('[data-action="toggle-not-for-kids"]').forEach((btn) => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault()
        e.stopPropagation()
        btn.closest('.projects-list-item--dropdown').style.display = 'none'
        const projectId = btn.dataset.projectId
        const project = this.projectsData[projectId]
        const currentNfk = project?.not_for_kids || 0
        if (currentNfk === 2) {
          const { showSnackbar, SnackbarDuration } = await import('../Layout/Snackbar')
          showSnackbar(
            '#share-snackbar',
            ds.transNfkModeratorLocked || 'Locked by moderator',
            SnackbarDuration.error,
          )
          return
        }
        const newValue = currentNfk === 0
        const { default: Swal } = await import('sweetalert2')
        const result = await Swal.fire({
          title: newValue
            ? ds.transMarkNotForKidsConfirm || 'Mark as not for kids?'
            : ds.transMarkSafeForKidsConfirm || 'Mark as safe for kids?',
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
          api.updateProject(projectId, { not_for_kids: newValue }, () => {
            if (project) project.not_for_kids = newValue ? 1 : 0
            const text = btn.querySelector('.js-nfk-text')
            if (text) {
              text.textContent = newValue
                ? ds.transMarkSafeForKids || 'Mark safe for kids'
                : ds.transMarkNotForKids || 'Mark not for kids'
            }
          })
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

    const picture = createPictureElement(
      data.cover,
      'card',
      '/images/default/screenshot-card@1x.webp',
      {
        class: 'project-list__project__image',
        alt: data.name || '',
        width: 360,
        height: 360,
        loading: 'lazy',
      },
    )
    el.appendChild(picture)

    const nameSpan = document.createElement('span')
    nameSpan.className = 'project-list__project__name'
    nameSpan.textContent = data.name || ''
    el.appendChild(nameSpan)

    const propDiv = document.createElement('div')
    propDiv.className = 'project-list__project__property project-list__project__property-members'

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
    const attrs = {
      class: 'project-list__project__image',
      alt: data.name || '',
      width: 360,
      height: 360,
      loading: 'lazy',
    }
    if (data.not_for_kids) {
      attrs.style = 'filter: blur(10px)'
    }
    return createPictureElement(
      data.screenshot,
      'card',
      '/images/default/screenshot-card@1x.webp',
      attrs,
    )
  }

  createPropertyElement(data) {
    const propDiv = document.createElement('div')
    propDiv.className = `lazyload project-list__project__property project-list__project__property-${this.propertyToShow}`

    const icons = {
      views: 'visibility',
      downloads: 'get_app',
      uploaded: 'calendar_today',
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
    this.$title.style.removeProperty('display')
    this.isFullView = false
    this.container.classList.add('horizontal')
    this.container.classList.remove('vertical')
    this.$body.classList.remove('overflow-hidden')
    return false
  }

  _buildSecondaryMeta(data) {
    const items = []
    if (data.views !== undefined && this.propertyToShow !== 'views') {
      items.push({ icon: 'visibility', value: data.views })
    }
    if (data.downloads !== undefined && this.propertyToShow !== 'downloads') {
      items.push({ icon: 'get_app', value: data.downloads })
    }
    if (items.length === 0) return null

    const wrapper = document.createElement('div')
    wrapper.className = 'project-list__meta-secondary'
    items.forEach((item) => {
      const span = document.createElement('span')
      span.className = 'project-list__meta-secondary__item'
      const icon = document.createElement('i')
      icon.className = 'material-icons'
      icon.textContent = item.icon
      span.appendChild(icon)
      span.appendChild(document.createTextNode(String(item.value)))
      wrapper.appendChild(span)
    })
    return wrapper
  }

  _buildRetentionMeta(retentionDays, retentionExpiry, ds) {
    const tooltip = escapeAttr(
      retentionDays === -1
        ? ds.transRetentionTooltipProtected ||
            'This project is protected and will not be automatically deleted.'
        : ds.transRetentionTooltip ||
            'Projects are automatically removed after a period of inactivity. Get more downloads or log in regularly to extend retention.',
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
