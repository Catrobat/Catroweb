import { showDefaultTopBarTitle, showCustomTopBarTitle } from '../Layout/TopBar'

require('./ProjectList.scss')

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
    this.apiUrl = this.formatApiUrl(apiUrl)
    this.popStateHandler = this.closeFullView.bind(this)

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
      .then((response) => response.json())
      .then((data) => {
        if (!Array.isArray(data)) {
          console.error(`Data received for ${this.category} is not an array!`)
          this.container.classList.remove('loading')
          return
        }

        if (clear) {
          this.projectsContainer.innerHTML = ''
        }

        data.forEach((project) => {
          const projectElement = this.generateProjectElement(project)
          this.projectsContainer.appendChild(projectElement)
        })

        this.container.classList.remove('loading')
        this.updateChevronVisibility()

        this.projectsLoaded += data.length

        if (this.projectsLoaded === 0 && !this.empty) {
          this.empty = true
          this.displayEmptyMessage()
        }

        this.fetchActive = false
      })
      .catch((error) => {
        console.error(`Failed loading projects in category ${this.category}`, error)
        this.container.classList.remove('loading')
      })
  }

  generateProjectElement(data) {
    const projectUrl = data.project_url.replace('/app/', `/${this.theme}/`)

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
    projectElement.appendChild(propDiv)

    if (data.not_for_kids) {
      this.addNotForKidsElement(projectElement)
    }

    return projectElement
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

  addNotForKidsElement(projectElement) {
    const notForKidsDiv = document.createElement('div')
    notForKidsDiv.className = 'lazyload project-list__project__property__not-for-kids'

    const notForKidsImg = document.createElement('img')
    notForKidsImg.className = 'lazyload project-list__not-for-kids-logo'
    notForKidsImg.src = '/images/default/not_for_kids.svg'
    notForKidsDiv.appendChild(notForKidsImg)

    const notForKidsValueSpan = document.createElement('span')
    notForKidsValueSpan.className = 'project-list__project__property__value'
    notForKidsValueSpan.textContent = 'Not for kids'
    notForKidsDiv.appendChild(notForKidsValueSpan)

    projectElement.appendChild(notForKidsDiv)
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
  }

  handlePopState(event) {
    if (event.state && event.state.type === 'ProjectList' && event.state.full === true) {
      document.querySelector(`#${event.state.id}`).data('list').openFullView()
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
