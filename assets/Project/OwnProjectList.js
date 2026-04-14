/* global myProfileConfiguration */

import { normalizeApiResponse } from '../Api/ResponseHelper'
import Swal from 'sweetalert2'
import { ApiDeleteFetch, ApiFetch } from '../Api/ApiHelper'
import ProjectApi from '../Api/ProjectApi'
import { showSnackbar, SnackbarDuration } from '../Layout/Snackbar'
import { escapeHtml, escapeAttr } from '../Components/HtmlEscape'
import { shareOrCopy } from '../Components/ClipboardHelper'
import { getImageUrl } from '../Layout/ImageVariants'
import '../Components/RetentionTooltip'

require('./OwnProjectList.scss')

export class OwnProjectList {
  constructor(container, apiUrl, theme, emptyMessage, baseUrl) {
    this.container = container
    this.projectsContainer = container.getElementsByClassName('projects-container')[0]
    const attributes =
      'attributes=id,project_url,screenshot,name,downloads,views,reactions,comments,private,not_for_kids,retention_days,retention_expiry'
    this.baseUrl = baseUrl
    this.apiUrl = apiUrl.includes('?') ? apiUrl + '&' + attributes : apiUrl + '?' + attributes
    this.projectsLoaded = 0
    this.nextCursor = null
    this.hasMoreFromApi = true
    this.projectsData = {}
    this.projectFetchCount = 20
    this.empty = false
    this.fetchActive = false
    this.theme = theme
    this.emptyMessage = emptyMessage
    this.actionConfiguration = myProfileConfiguration.projectActions
    this.projectInfoConfiguration = myProfileConfiguration.projectInfo
    // Translation strings from data attributes
    const ds = container.dataset
    this.translations = {
      setPrivate: ds.transSetPrivate || 'Set private',
      setPublic: ds.transSetPublic || 'Set public',
      open: ds.transOpen || 'Open',
      download: ds.transDownload || 'Download',
      share: ds.transShare || 'Share',
      markNotForKids: ds.transMarkNotForKids || 'Mark not for kids',
      markSafeForKids: ds.transMarkSafeForKids || 'Mark safe for kids',
      delete: ds.transDelete || 'Delete project',
      retentionProtected: ds.transRetentionProtected || 'Protected',
      retentionDay: ds.transRetentionDay || '1 day left',
      retentionDays: ds.transRetentionDays || '%days% days left',
      retentionTooltip:
        ds.transRetentionTooltip ||
        'Projects are automatically removed after a period of inactivity. Get more downloads or log in regularly to extend retention.',
      retentionTooltipProtected:
        ds.transRetentionTooltipProtected ||
        'This project is protected and will not be automatically deleted.',
    }
  }

  initialize() {
    this.fetchMore(true)
    this._initOutsideClickHandler()
    this.initScrollFetchMoreHandler()

    // remove loading spinners when loading from cache (e.g. browser back button)
    window.addEventListener('pageshow', (ev) => {
      if (ev.persisted) {
        this.projectsContainer
          .querySelectorAll('.loading-spinner-backdrop')
          .forEach((elem) => elem.remove())
      }
    })
  }

  initScrollFetchMoreHandler() {
    window.addEventListener('scroll', () => this.isScrolledToBottom())
  }

  isScrolledToBottom() {
    const docHeight = document.body.scrollHeight
    const scrollHeight = window.scrollY + window.innerHeight

    if (scrollHeight >= docHeight) {
      this.fetchMore()
    }
  }

  _initOutsideClickHandler() {
    document.addEventListener('click', () => {
      this.projectsContainer.querySelectorAll('.projects-list-item--dropdown').forEach((d) => {
        d.style.display = 'none'
      })
    })
  }

  fetchMore(clear = false) {
    if (this.empty === true || this.fetchActive === true || !this.hasMoreFromApi) {
      return
    }

    this.fetchActive = true
    const self = this

    let url = this.apiUrl + '&limit=' + this.projectFetchCount
    if (this.nextCursor) {
      url += '&cursor=' + encodeURIComponent(this.nextCursor)
    } else {
      url += '&offset=' + this.projectsLoaded
    }

    new ApiFetch(url, 'GET', undefined, 'json')
      .run()
      .then(function (response) {
        const envelope = normalizeApiResponse(response)
        const data = envelope.data || []
        if (!Array.isArray(data)) {
          console.error('Data received for own projects is no array!')
          self.container.classList.remove('loading')
          return
        }

        if (envelope.next_cursor !== undefined) {
          self.nextCursor = envelope.next_cursor
        }
        if (envelope.has_more !== undefined) {
          self.hasMoreFromApi = envelope.has_more
        } else {
          self.hasMoreFromApi = data.length >= self.projectFetchCount
        }

        if (clear) {
          Array.prototype.slice.call(self.projectsContainer.childNodes).forEach(function (child) {
            self.projectsContainer.removeChild(child)
          })
        }

        data.forEach(function (project) {
          self.projectsData[project.id] = project
          const projectElement = self._generate(project)
          self.projectsContainer.appendChild(projectElement)
          const projectLink = projectElement.querySelector('a[href]')
          if (projectLink) {
            projectLink.addEventListener('click', function () {
              self._addLoadingSpinner(projectElement)
            })
          }
        })
        self.container.classList.remove('loading')

        self.projectsLoaded += data.length

        if (self.projectsLoaded === 0 && self.empty === false) {
          self.empty = true
          if (self.emptyMessage) {
            self.projectsContainer.appendChild(document.createTextNode(self.emptyMessage))
            self.container.classList.add('empty-with-text')
          } else {
            self.container.classList.add('empty')
          }
        }

        self.fetchActive = false
      })
      .catch(function (reason) {
        console.error('Failed loading own projects', reason)
        self.container.classList.remove('loading')
        self.fetchActive = false
      })
  }

  _generate(data) {
    /*
     * Necessary to support legacy flavoring with URL:
     *   Absolute url always uses new 'app' routing flavor. We have to replace it!
     */
    let projectUrl = data.project_url
    projectUrl = projectUrl.replace('/app/', '/' + this.theme + '/')
    //

    const id = escapeAttr(String(data.id))
    const screenshotSmall = getImageUrl(
      data.screenshot,
      'card',
      '/images/default/screenshot-card@1x.webp',
    )

    const icons = {
      downloads: 'get_app',
      views: 'visibility',
      reactions: 'thumb_up',
      comments: 'chat',
    }

    let metaHtml = ''
    ;['downloads', 'views', 'reactions', 'comments'].forEach(function (key) {
      if (Object.prototype.hasOwnProperty.call(data, key)) {
        metaHtml +=
          '<div class="own-project-list__project__details__properties__property">' +
          '<span class="material-icons">' +
          icons[key] +
          '</span>' +
          '<span class="own-project-list__project__details__properties__property__value">' +
          escapeHtml(String(data[key])) +
          '</span>' +
          '</div>'
      }
    })

    const visibilityIcon = data.private ? 'lock' : 'lock_open'
    const visibilityText = data.private
      ? this.projectInfoConfiguration.visibilityPrivateText
      : this.projectInfoConfiguration.visibilityPublicText

    let retentionHtml = ''
    if (data.retention_days !== undefined) {
      retentionHtml = this._buildRetentionBadge(data.retention_days, data.retention_expiry)
    }

    const transVisibility = data.private
      ? escapeHtml(this.translations.setPublic)
      : escapeHtml(this.translations.setPrivate)
    const nfkValue = data.not_for_kids || 0
    const nfkLabel = nfkValue
      ? escapeHtml(this.translations.markSafeForKids)
      : escapeHtml(this.translations.markNotForKids)

    const menuItems =
      '<a href="' +
      escapeAttr(projectUrl) +
      '" class="projects-list-item--dropdown-item">' +
      '<i class="material-icons">open_in_new</i>' +
      escapeHtml(this.translations.open) +
      '</a>' +
      '<a href="/api/projects/' +
      id +
      '/catrobat" download class="projects-list-item--dropdown-item">' +
      '<i class="material-icons">download</i>' +
      escapeHtml(this.translations.download) +
      '</a>' +
      '<button class="projects-list-item--dropdown-item" data-action="share" data-project-id="' +
      id +
      '">' +
      '<i class="material-icons">share</i>' +
      escapeHtml(this.translations.share) +
      '</button>' +
      '<div class="projects-list-item--dropdown-divider"></div>' +
      '<button class="projects-list-item--dropdown-item" data-action="toggle-visibility" data-project-id="' +
      id +
      '">' +
      '<i class="material-icons">' +
      visibilityIcon +
      '</i>' +
      '<span class="own-project-dropdown-visibility-text">' +
      transVisibility +
      '</span>' +
      '</button>' +
      '<button class="projects-list-item--dropdown-item" data-action="not-for-kids" data-project-id="' +
      id +
      '">' +
      '<i class="material-icons">child_care</i>' +
      '<span class="own-project-dropdown-nfk-text">' +
      nfkLabel +
      '</span>' +
      '</button>' +
      '<div class="projects-list-item--dropdown-divider"></div>' +
      '<button class="projects-list-item--dropdown-item text-danger" data-action="delete" data-project-id="' +
      id +
      '">' +
      '<i class="material-icons">delete</i>' +
      escapeHtml(this.translations.delete) +
      '</button>'

    const wrapper = document.createElement('div')
    wrapper.className = 'own-project-list__project'
    wrapper.dataset.id = data.id
    wrapper.innerHTML =
      '<a href="' +
      escapeAttr(projectUrl) +
      '">' +
      '<img src="' +
      escapeAttr(screenshotSmall) +
      '" class="lazyload own-project-list__project__image" alt="" loading="lazy">' +
      '</a>' +
      '<div class="own-project-list__project__details">' +
      '<div class="own-project-list__project__details__name">' +
      escapeHtml(data.name) +
      '</div>' +
      '<div class="own-project-list__project__details__properties">' +
      metaHtml +
      '</div>' +
      '<div class="own-project-list__project__details__visibility">' +
      '<span class="material-icons own-project-list__project__details__visibility__icon">' +
      visibilityIcon +
      '</span>' +
      '<span class="own-project-list__project__details__visibility__text">' +
      escapeHtml(visibilityText) +
      '</span>' +
      '</div>' +
      retentionHtml +
      '</div>' +
      '<div class="projects-list-item--actions">' +
      '<button class="btn projects-list-item--menu-btn own-project-list__project__action" data-project-id="' +
      id +
      '">' +
      '<i class="material-icons">more_vert</i>' +
      '</button>' +
      '<div class="projects-list-item--dropdown" style="display:none;">' +
      menuItems +
      '</div>' +
      '</div>'

    this._bindDropdownActions(wrapper, data)

    return wrapper
  }

  _bindDropdownActions(wrapper, data) {
    const self = this
    const id = data.id

    // Toggle dropdown
    const menuBtn = wrapper.querySelector('.projects-list-item--menu-btn')
    if (menuBtn) {
      menuBtn.addEventListener('click', function (event) {
        event.preventDefault()
        event.stopPropagation()
        const dropdown = menuBtn.nextElementSibling
        const isOpen = dropdown.style.display !== 'none'
        // Close all dropdowns first
        self.projectsContainer.querySelectorAll('.projects-list-item--dropdown').forEach((d) => {
          d.style.display = 'none'
        })
        if (!isOpen) {
          // Refresh dynamic text before opening
          self._refreshDropdownTexts(wrapper, id)
          dropdown.style.display = 'block'
        }
      })
    }

    // Action handlers via event delegation
    wrapper.querySelectorAll('[data-action]').forEach((btn) => {
      btn.addEventListener('click', function (event) {
        event.preventDefault()
        event.stopPropagation()
        const dropdown = btn.closest('.projects-list-item--dropdown')
        if (dropdown) {
          dropdown.style.display = 'none'
        }
        const action = btn.dataset.action
        const handlers = {
          'toggle-visibility': () => self._actionToggleVisibility(id),
          share: () => self._actionShareProject(id),
          'not-for-kids': () => self._actionToggleNotForKids(id),
          delete: () => self._actionDeleteProject(id),
        }
        if (handlers[action]) {
          handlers[action]()
        }
      })
    })
  }

  _refreshDropdownTexts(wrapper, id) {
    const project = this.projectsData[id]

    // Update visibility toggle text
    const visText = wrapper.querySelector('.own-project-dropdown-visibility-text')
    if (visText) {
      visText.textContent = project.private
        ? this.translations.setPublic
        : this.translations.setPrivate
    }

    // Update visibility icon in dropdown
    const visIcon = wrapper.querySelector('[data-action="toggle-visibility"] .material-icons')
    if (visIcon) {
      visIcon.textContent = project.private ? 'lock' : 'lock_open'
    }

    // Update not-for-kids text
    const nfkText = wrapper.querySelector('.own-project-dropdown-nfk-text')
    if (nfkText) {
      const nfkValue = project.not_for_kids || 0
      nfkText.textContent = nfkValue
        ? this.translations.markSafeForKids
        : this.translations.markNotForKids
    }
  }

  _actionDeleteProject(id) {
    const projectName = escapeHtml(this.projectsData[id].name)
    const msgParts = this.actionConfiguration.delete.confirmationText
      .replace('%programName%', '”' + projectName + '”')
      .split('\n')
    Swal.fire({
      title: msgParts[0],
      html: msgParts[1] + '<br><br>' + msgParts[2],
      icon: 'warning',
      showCancelButton: true,
      allowOutsideClick: false,
      customClass: {
        confirmButton: 'btn btn-danger',
        cancelButton: 'btn btn-outline-primary',
      },
      buttonsStyling: false,
      confirmButtonText: msgParts[3],
      cancelButtonText: msgParts[4],
    }).then((result) => {
      if (result.value) {
        new ApiDeleteFetch(
          this.baseUrl + '/api/projects/' + id,
          'Delete Project',
          myProfileConfiguration.messages.unspecifiedErrorText,
          function () {
            console.info('Project ' + id + ' deleted successfully.')
            window.location.reload()
          },
          {
            404: myProfileConfiguration.messages.deleteProjectNotFoundText,
          },
        ).run()
      }
    })
  }

  _actionShareProject(id) {
    const successMsg = myProfileConfiguration.messages.clipboardSuccessMessage || 'Link copied!'
    const projectUrl = this.projectsData[id].project_url
    shareOrCopy(projectUrl, () => showSnackbar('#share-snackbar', successMsg))
  }

  _actionToggleVisibility(id) {
    const self = this
    const project = this.projectsData[id]
    const msgParts = this.actionConfiguration.visibility.confirmationText
      .replaceAll('%programName%', '”' + escapeHtml(project.name) + '”')
      .split('\n')
    Swal.fire({
      title: msgParts[0],
      html: project.private ? msgParts[3] : msgParts[1] + '<br><br>' + msgParts[2],
      icon: 'warning',
      showCancelButton: true,
      allowOutsideClick: false,
      customClass: {
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-outline-primary',
      },
      buttonsStyling: false,
      confirmButtonText: project.private ? msgParts[4] : msgParts[5],
      cancelButtonText: msgParts[6],
    }).then((result) => {
      if (result.value) {
        const projectElem = document.querySelector(
          '.own-project-list__project[data-id="' + id + '"]',
        )
        self._addLoadingSpinner(projectElem)
        const newValue = !project.private
        const projectApi = new ProjectApi()
        projectApi.updateProject(
          id,
          { private: newValue },
          function () {
            const visibilityElem = projectElem.querySelector(
              '.own-project-list__project__details__visibility',
            )
            if (!newValue) {
              project.private = false
              visibilityElem.querySelector(
                '.own-project-list__project__details__visibility__icon',
              ).innerText = 'lock_open'
              visibilityElem.querySelector(
                '.own-project-list__project__details__visibility__text',
              ).innerText = self.projectInfoConfiguration.visibilityPublicText
            } else {
              project.private = true
              visibilityElem.querySelector(
                '.own-project-list__project__details__visibility__icon',
              ).innerText = 'lock'
              visibilityElem.querySelector(
                '.own-project-list__project__details__visibility__text',
              ).innerText = self.projectInfoConfiguration.visibilityPrivateText
            }
          },
          function () {
            self._removeLoadingSpinner(projectElem)
          },
        )
      }
    })
  }

  _actionToggleNotForKids(id) {
    const self = this
    const project = this.projectsData[id]
    const nfkConfig = this.actionConfiguration.notForKids
    const currentValue = project.not_for_kids || 0

    if (currentValue === 2) {
      showSnackbar('#share-snackbar', nfkConfig.moderatorLocked, SnackbarDuration.error)
      return
    }

    const newValue = currentValue === 0
    Swal.fire({
      title: nfkConfig.confirmTitle,
      html: newValue ? nfkConfig.confirmMark : nfkConfig.confirmUnmark,
      icon: 'warning',
      showCancelButton: true,
      allowOutsideClick: false,
      customClass: {
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-outline-primary',
      },
      buttonsStyling: false,
      confirmButtonText: nfkConfig.confirmYes,
    }).then((result) => {
      if (result.value) {
        const projectElem = document.querySelector(
          '.own-project-list__project[data-id="' + id + '"]',
        )
        self._addLoadingSpinner(projectElem)
        const projectApi = new ProjectApi()
        projectApi.updateProject(
          id,
          { not_for_kids: newValue },
          function () {
            project.not_for_kids = newValue ? 1 : 0
            showSnackbar(
              '#share-snackbar',
              newValue ? nfkConfig.successMarked : nfkConfig.successUnmarked,
            )
          },
          function () {
            self._removeLoadingSpinner(projectElem)
          },
        )
      }
    })
  }

  _addLoadingSpinner(toElement) {
    const newSpinner = document
      .getElementById('profile-loading-spinner-template')
      .content.cloneNode(true)
    toElement.appendChild(newSpinner)
  }

  _removeLoadingSpinner(fromElement) {
    const spinner = fromElement.querySelector('.loading-spinner-backdrop')
    if (spinner) {
      fromElement.removeChild(spinner)
    }
  }

  _buildRetentionBadge(retentionDays, retentionExpiry) {
    const tooltip = escapeAttr(
      retentionDays === -1
        ? this.translations.retentionTooltipProtected ||
            'This project is protected and will not be automatically deleted.'
        : this.translations.retentionTooltip ||
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
        '<div class="own-project-list__project__details__retention own-project-list__project__details__retention--protected">' +
        '<span class="material-icons">verified</span>' +
        '<span>' +
        escapeHtml(this.translations.retentionProtected || 'Protected') +
        '</span>' +
        infoBtn +
        '</div>'
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
      cssModifier = ' own-project-list__project__details__retention--critical'
    } else if (daysLeft <= 30) {
      icon = 'hourglass_bottom'
      cssModifier = ' own-project-list__project__details__retention--warning'
    }

    const label =
      daysLeft === 1
        ? this.translations.retentionDay || '1 day left'
        : (this.translations.retentionDays || '%days% days left').replace(
            '%days%',
            String(daysLeft),
          )

    return (
      '<div class="own-project-list__project__details__retention' +
      cssModifier +
      '">' +
      '<span class="material-icons">' +
      icon +
      '</span>' +
      '<span>' +
      escapeHtml(label) +
      '</span>' +
      infoBtn +
      '</div>'
    )
  }
}
