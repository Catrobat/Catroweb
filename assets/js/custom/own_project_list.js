/* global myProfileConfiguration */

import { Corner, MDCMenu } from '@material/menu'
import { MDCMenuSurfaceFoundation } from '@material/menu-surface'
import Swal from 'sweetalert2'
import { ApiDeleteFetch, ApiFetch } from '../api/ApiHelper'
import ProjectApi from '../api/ProjectApi'

require('../../styles/components/own_project_list.scss')

export class OwnProjectList {
  constructor (container, apiUrl, theme, emptyMessage = '') {
    this.container = container
    this.projectsContainer = container.getElementsByClassName('projects-container')[0]
    const attributes = 'attributes=id,project_url,screenshot_small,screenshot_large,name,downloads,views,reactions,comments,private'
    this.apiUrl = apiUrl.includes('?') ? (apiUrl + '&' + attributes) : (apiUrl + '?' + attributes)
    this.projectsLoaded = 0
    this.projectsData = {}
    this.projectFetchCount = 99999
    this.empty = false
    this.fetchActive = false
    this.theme = theme
    this.emptyMessage = emptyMessage
    this.projectActionMenu = undefined
    this.actionConfiguration = myProfileConfiguration.projectActions
    this.projectInfoConfiguration = myProfileConfiguration.projectInfo
  }

  initialize () {
    this.fetchMore(true)
    this._initActionMenu()

    // remove loading spinners when loading from cache (e.g. browser back button)
    window.addEventListener('pageshow', ev => {
      if (ev.persisted) {
        this.projectsContainer.querySelectorAll('.loading-spinner-backdrop').forEach(elem => elem.remove())
      }
    })
  }

  _initActionMenu () {
    const self = this
    this.projectActionMenu = new MDCMenu(document.getElementById('project-action-menu'))
    this.projectActionMenu.listen('MDCMenu:selected', function (event) {
      if (event.detail.index === 0) { // Set public/private
        self._actionToggleVisibility(self.projectActionMenu.projectId)
      } else if (event.detail.index === 1) { // Delete
        self._actionDeleteProject(self.projectActionMenu.projectId)
      } else {
        console.error('Invalid menu item selected')
      }
    })
    this.projectActionMenu.setAnchorCorner(Corner.TOP_END)
    this.projectActionMenu.setAbsolutePosition(0, 0)
  }

  fetchMore (clear = false) {
    if (this.empty === true || this.fetchActive === true) {
      return
    }

    this.fetchActive = true
    const self = this

    const url = this.apiUrl + '&limit=' + this.projectFetchCount + '&offset=' + this.projectsLoaded

    new ApiFetch(url, 'GET', undefined, 'json').run().then(function (data) {
      if (!Array.isArray(data)) {
        console.error('Data received for own projects is no array!')
        self.container.classList.remove('loading')
        return
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
        projectElement.addEventListener('click', function () {
          self._addLoadingSpinner(projectElement)
        }, false)
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
    }
    ).catch(function (reason) {
      console.error('Failed loading own projects', reason)
      self.container.classList.remove('loading')
    })
  }

  _generate (data) {
    const self = this
    /*
    * Necessary to support legacy flavoring with URL:
    *   Absolute url always uses new 'app' routing flavor. We have to replace it!
    */
    let projectUrl = data.project_url
    projectUrl = projectUrl.replace('/app/', '/' + this.theme + '/')
    //

    const proj = document.createElement('a')
    proj.className = 'own-project-list__project'
    proj.setAttribute('href', projectUrl)
    proj.dataset.id = data.id

    const img = document.createElement('img')
    img.className = 'lazyload own-project-list__project__image'
    img.dataset.src = data.screenshot_small
    // TODO: generate larger thumbnails and adapt here (change 80w to width of thumbs)
    img.dataset.srcset = data.screenshot_small + ' 80w, ' + data.screenshot_large + ' 480w'
    img.dataset.sizes = '(min-width: 768px) 10vw, 25vw'

    proj.appendChild(img)

    const details = document.createElement('div')
    details.className = 'own-project-list__project__details'
    proj.appendChild(details)

    const name = document.createElement('div')
    name.className = 'own-project-list__project__details__name'
    name.appendChild(document.createTextNode(data.name))
    details.appendChild(name)

    const properties = document.createElement('div')
    properties.className = 'own-project-list__project__details__properties'
    details.appendChild(properties)

    const icons = {
      downloads: 'get_app',
      views: 'visibility',
      reactions: 'thumb_up',
      comments: 'chat'
    }

    // eslint-disable-next-line no-array-constructor
    Array('downloads', 'views', 'reactions', 'comments').forEach(function (propertyKey) {
      if (Object.prototype.hasOwnProperty.call(data, propertyKey)) {
        const propEl = document.createElement('div')
        propEl.className = 'own-project-list__project__details__properties__property'

        const iconEl = document.createElement('span')
        iconEl.className = 'material-icons'
        iconEl.appendChild(document.createTextNode(icons[propertyKey]))
        propEl.appendChild(iconEl)

        const valueEl = document.createElement('span')
        valueEl.className = 'own-project-list__project__details__properties__property__value'
        valueEl.appendChild(document.createTextNode(data[propertyKey]))
        propEl.appendChild(valueEl)

        properties.appendChild(propEl)
      }
    })

    const visibility = document.createElement('div')
    visibility.className = 'own-project-list__project__details__visibility'
    details.appendChild(visibility)

    const visibilityIcon = document.createElement('span')
    visibilityIcon.className = 'material-icons own-project-list__project__details__visibility__icon'
    visibilityIcon.appendChild(document.createTextNode(data.private ? 'lock' : 'lock_open'))
    visibility.appendChild(visibilityIcon)

    const visibilityText = document.createElement('span')
    visibilityText.className = 'own-project-list__project__details__visibility__text'
    visibilityText.appendChild(document.createTextNode(data.private ? this.projectInfoConfiguration.visibilityPrivateText : this.projectInfoConfiguration.visibilityPublicText))
    visibility.appendChild(visibilityText)

    const action = document.createElement('div')
    action.className = 'own-project-list__project__action'
    action.addEventListener('click', function (event) {
      event.preventDefault()
      event.stopPropagation()

      const refreshAndOpenMenu = function () {
        const visibilityItem = self.projectActionMenu.items[0].getElementsByClassName('mdc-list-item__text')[0]
        if (self.projectsData[data.id].private) { // private project
          visibilityItem.innerText = visibilityItem.dataset.textPublic
        } else {
          visibilityItem.innerText = visibilityItem.dataset.textPrivate
        }

        self.projectActionMenu.setAnchorElement(event.target)
        self.projectActionMenu.projectId = data.id
        self.projectActionMenu.open = true
      }

      if (self.projectActionMenu.root.classList.contains(MDCMenuSurfaceFoundation.cssClasses.ANIMATING_CLOSED)) {
        setTimeout(refreshAndOpenMenu, MDCMenuSurfaceFoundation.numbers.TRANSITION_CLOSE_DURATION + 25)
      } else {
        refreshAndOpenMenu()
      }
    }, false)
    proj.appendChild(action)

    const actionIcon = document.createElement('span')
    actionIcon.className = 'material-icons'
    actionIcon.appendChild(document.createTextNode('more_vert'))
    action.appendChild(actionIcon)

    return proj
  }

  _actionDeleteProject (id) {
    const projectName = this.projectsData[id].name
    const msgParts = this.actionConfiguration.delete.confirmationText
      .replace('%programName%', '“' + projectName + '”').split('\n')
    Swal.fire({
      title: msgParts[0],
      html: msgParts[1] + '<br><br>' + msgParts[2],
      icon: 'warning',
      showCancelButton: true,
      allowOutsideClick: false,
      customClass: {
        confirmButton: 'btn btn-danger',
        cancelButton: 'btn btn-outline-primary'
      },
      buttonsStyling: false,
      confirmButtonText: msgParts[3],
      cancelButtonText: msgParts[4]
    }).then((result) => {
      if (result.value) {
        new ApiDeleteFetch('/api/project/' + id, 'Delete Project',
          myProfileConfiguration.messages.unspecifiedErrorText, function () {
            console.info('Project ' + id + ' deleted successfully.')
            window.location.reload()
          }, {
            404: myProfileConfiguration.messages.deleteProjectNotFoundText
          }).run()
      }
    })
  }

  _actionToggleVisibility (id) {
    const self = this
    const project = this.projectsData[id]
    const msgParts = this.actionConfiguration.visibility.confirmationText
      .replaceAll('%programName%', '“' + project.name + '”').split('\n')
    Swal.fire({
      title: msgParts[0],
      html: (project.private) ? msgParts[3] : msgParts[1] + '<br><br>' + msgParts[2],
      icon: 'warning',
      showCancelButton: true,
      allowOutsideClick: false,
      customClass: {
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-outline-primary'
      },
      buttonsStyling: false,
      confirmButtonText: (project.private) ? msgParts[4] : msgParts[5],
      cancelButtonText: msgParts[6]
    }).then((result) => {
      if (result.value) {
        const projectElem = document.querySelector('.own-project-list__project[data-id="' + id + '"]')
        self._addLoadingSpinner(projectElem)
        const newValue = !project.private
        ProjectApi.update(id, { private: newValue }, function () {
          const visibilityElem = projectElem.querySelector('.own-project-list__project__details__visibility')
          if (!newValue) {
            project.private = false
            visibilityElem.querySelector('.own-project-list__project__details__visibility__icon').innerText = 'lock_open'
            visibilityElem.querySelector('.own-project-list__project__details__visibility__text').innerText = self.projectInfoConfiguration.visibilityPublicText
          } else {
            project.private = true
            visibilityElem.querySelector('.own-project-list__project__details__visibility__icon').innerText = 'lock'
            visibilityElem.querySelector('.own-project-list__project__details__visibility__text').innerText = self.projectInfoConfiguration.visibilityPrivateText
          }
        }, function () {
          self._removeLoadingSpinner(projectElem)
        })
      }
    })
  }

  _addLoadingSpinner (toElement) {
    const newSpinner = document.getElementById('profile-loading-spinner-template').content.cloneNode(true)
    toElement.appendChild(newSpinner)
  }

  _removeLoadingSpinner (fromElement) {
    const spinner = fromElement.querySelector('.loading-spinner-backdrop')
    if (spinner) {
      fromElement.removeChild(spinner)
    }
  }
}
