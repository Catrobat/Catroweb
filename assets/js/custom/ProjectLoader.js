/* global sessionStorage */

import {
  showTopBarSearch,
  controlTopBarSearchClearButton,
} from '../layout/top_bar'
require('../../styles/components/project_list.scss')

/**
 * @deprecated
 */
export class ProjectLoader {
  constructor(container, url) {
    this.container = document.querySelector(container)
    this.url = url

    this.defaultRows = 2
    this.columns = 0
    this.columns_min = 2
    this.columns_max = 9

    this.windowWidth = window.innerWidth

    this.downloadLimit = 0
    this.initialDownloadLimit = this.defaultRows * this.columns_max
    this.numberOfLoadedProjects = 0
    this.numberOfVisibleProjects = 0
    this.defaultNumberOfVisibleProjects = 0
    this.totalNumberOfFoundProjects = 0

    this.show_all_projects = false
    this.query = ''
  }

  async initSearch(query) {
    const oldQuery = sessionStorage.getItem(this.query)
    if (query === oldQuery) {
      this.restoreParamsWithSessionStorage()
    }
    sessionStorage.setItem(this.query, query)
    this.query = query

    try {
      const response = await fetch(
        `${this.url}?q=${query}&limit=${this.initialDownloadLimit}&offset=${this.numberOfLoadedProjects}`,
      )
      const data = await response.json()

      const searchResultsText = document.getElementById('search-results-text')
      if (!data.CatrobatProjects || data.CatrobatProjects.length === 0) {
        document.getElementById('search-progressbar').style.display = 'none'
        searchResultsText.classList.add('no-results')
        searchResultsText.querySelector('span').innerText = 0
        return
      }

      searchResultsText.querySelector('span').innerText =
        data.CatrobatInformation.TotalProjects
      this.totalNumberOfFoundProjects = parseInt(
        data.CatrobatInformation.TotalProjects,
        10,
      )

      await this.setup(data)
    } catch (error) {
      console.error('Error fetching search results:', error)
    }
  }

  async searchResult(q) {
    const searchInput = document.getElementById('top-app-bar__search-input')
    searchInput.innerHTML = q
    await this.initSearch(q)
    document.addEventListener('DOMContentLoaded', () => {
      showTopBarSearch()
      searchInput.value = q
      controlTopBarSearchClearButton()
    })
  }

  async setup(data) {
    if (!this.show_all_projects) {
      await this.initLoaderUI()
    }

    this.showMoreListener()
    this.showLessListener()

    await this.loadProjectsIntoContainer(data)
    document.getElementById('search-progressbar').style.display = 'none'
    await this.initParameters()
    await this.initNumberOfVisibleProjects()
    await this.keepRowsFull()

    await this.updateUIVisibility()
  }

  async loadProjectsIntoContainer(data) {
    const projects = data.CatrobatProjects
    for (const project of projects) {
      const htmlProject = await this.buildProjectInHtml(project, data)
      this.container.querySelector('.projects').append(htmlProject)
      this.container.style.display = 'block'
    }
    this.numberOfLoadedProjects += projects.length
  }

  async setNumberOfColumns() {
    const projectsContainer = this.container.querySelector('.projects')
    const projects = this.container.querySelectorAll('.project')

    const projectsContainerWidth = projectsContainer.offsetWidth
    const projectsOuterWidth = projects[0].offsetWidth

    let columns = Math.floor(projectsContainerWidth / projectsOuterWidth)

    columns = Math.max(this.columns_min, Math.min(columns, this.columns_max))
    this.columns = columns
  }

  async updateInitialDownloadLimit() {
    if (
      this.restored_numberOfVisibleProjects === this.totalNumberOfFoundProjects
    ) {
      this.initialDownloadLimit = this.totalNumberOfFoundProjects
    } else if (this.initialDownloadLimit > this.downloadLimit) {
      this.initialDownloadLimit -=
        this.initialDownloadLimit % this.downloadLimit
    } else {
      this.initialDownloadLimit = this.downloadLimit
    }
  }

  async initNumberOfVisibleProjects() {
    const numberOfProjects =
      this.restored_numberOfVisibleProjects > 0
        ? this.restored_numberOfVisibleProjects
        : this.defaultNumberOfVisibleProjects
    await this.updateNumberOfVisiblePrograms(numberOfProjects)
  }

  async initParameters() {
    await this.setNumberOfColumns()
    this.downloadLimit = this.defaultRows * this.columns
    await this.updateInitialDownloadLimit()
    this.defaultNumberOfVisibleProjects = this.downloadLimit
  }

  async keepRowsFull() {
    if (
      this.numberOfVisibleProjects < this.defaultNumberOfVisibleProjects &&
      this.numberOfVisibleProjects < this.totalNumberOfFoundProjects
    ) {
      await this.showMoreProjects()
    } else if (
      this.numberOfVisibleProjects > this.defaultNumberOfVisibleProjects &&
      this.numberOfVisibleProjects % this.downloadLimit !== 0 &&
      this.numberOfVisibleProjects !== this.totalNumberOfFoundProjects
    ) {
      await this.showLessProjects()
    }
  }

  async updateNumberOfVisiblePrograms(number) {
    this.numberOfVisibleProjects = number
    this.setSessionStorage(this.numberOfVisibleProjects)
  }

  async showMoreProjects() {
    if (this.numberOfVisibleProjects >= this.totalNumberOfFoundProjects) {
      await this.hide(this.container.querySelector('.btn-show-more'))
    } else if (
      this.numberOfLoadedProjects >=
      this.numberOfVisibleProjects + this.downloadLimit
    ) {
      await this.updateNumberOfVisiblePrograms(
        this.numberOfVisibleProjects + this.downloadLimit,
      )
      await this.updateUIVisibility()
    } else if (
      this.totalNumberOfFoundProjects === this.numberOfLoadedProjects
    ) {
      await this.updateNumberOfVisiblePrograms(this.totalNumberOfFoundProjects)
      await this.updateUIVisibility()
    } else {
      const data = await this.fetchProjects()
      await this.loadProjectsIntoContainer(data)
      await this.showMoreProjects()
    }
  }

  async fetchProjects() {
    const response = await fetch(
      `${this.url}?limit=${this.downloadLimit}&offset=${this.numberOfLoadedProjects}`,
    )
    return await response.json()
  }

  async hide(element) {
    if (element) {
      element.classList.add('d-none')
    }
  }

  async show(element) {
    if (element) {
      element.classList.remove('d-none')
    }
  }

  async showLessProjects() {
    await this.updateNumberOfVisiblePrograms(
      this.numberOfVisibleProjects -
        (this.numberOfVisibleProjects % this.downloadLimit),
    )
    await this.updateUIVisibility()
  }

  showMoreListener() {
    const showMoreButton = this.container.querySelector('.btn-show-more')
    if (showMoreButton) {
      showMoreButton.addEventListener('click', () => this.showMoreProjects())
    }
  }

  showLessListener() {
    const showLessButton = this.container.querySelector('.btn-show-less')
    if (showLessButton) {
      showLessButton.addEventListener('click', () => this.showLessProjects())
    }
  }

  async initLoaderUI() {
    const loaderDiv = document.createElement('div')
    loaderDiv.className = 'loader'
    const progressBarDiv = document.createElement('div')
    progressBarDiv.className = 'progress-bar'
    loaderDiv.appendChild(progressBarDiv)
    this.container.appendChild(loaderDiv)
  }

  setSessionStorage(numberOfVisibleProjects) {
    const storageId = window.location.href.split('?')[0]
    sessionStorage.setItem(storageId, numberOfVisibleProjects)
  }

  restoreParamsWithSessionStorage() {
    const storageId = window.location.href.split('?')[0]
    this.restored_numberOfVisibleProjects = parseInt(
      sessionStorage.getItem(storageId),
      10,
    )
  }

  async buildProjectInHtml(project, data) {
    const div = await this.initDivWithCorrectContainerIcon(project)
    const projectLink = await this.getProjectLink(project, data)
    const storedVisits = sessionStorage.getItem('visits')
    const visited = storedVisits
      ? JSON.parse(storedVisits).includes(project.ProjectId.toString())
      : false

    const projectDiv = document.createElement('div')
    projectDiv.className = 'project' + (visited ? ' visited-project' : '')
    projectDiv.id = 'program-' + project.ProjectId

    const link = document.createElement('a')
    link.href = projectLink

    const img = document.createElement('img')
    img.dataset.src = data.CatrobatInformation.BaseUrl + project.ScreenshotSmall
    img.alt = ''
    img.className = 'lazyload'
    link.appendChild(img)

    const projectName = document.createElement('span')
    projectName.className = 'project-name'
    projectName.innerText = project.ProjectName
    link.appendChild(projectName)

    link.innerHTML += div
    projectDiv.appendChild(link)

    return projectDiv
  }

  async getProjectLink(project, data) {
    return data.CatrobatInformation.BaseUrl + project.ProjectUrl
  }

  async initDivWithCorrectContainerIcon(project) {
    const div = document.createElement('div')
    const span = document.createElement('span')
    span.className = 'project-thumb-icon material-icons'
    span.innerText = 'schedule'
    div.appendChild(span)
    div.innerHTML += project.UploadedString

    return div.outerHTML
  }

  async updateUIVisibility() {
    const showMoreButton = this.container.querySelector('.btn-show-more')
    const showLessButton = this.container.querySelector('.btn-show-less')

    if (this.numberOfVisibleProjects >= this.totalNumberOfFoundProjects) {
      await this.hide(showMoreButton)
    } else {
      await this.show(showMoreButton)
    }

    if (
      this.numberOfVisibleProjects <= this.defaultNumberOfVisibleProjects ||
      this.numberOfVisibleProjects === this.totalNumberOfFoundProjects
    ) {
      await this.hide(showLessButton)
    } else {
      await this.show(showLessButton)
    }
  }
}
