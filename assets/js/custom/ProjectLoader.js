/* global noProgramsText */
/* global sessionStorage */

import $ from 'jquery'
import {
  showTopBarSearch,
  controlTopBarSearchClearButton,
} from '../layout/top_bar'

require('../../styles/components/project_list.scss')

/**
 * @deprecated
 *
 * @param container
 * @param url
 * @constructor
 */
export const ProjectLoader = function (container, url) {
  const self = this

  // The container where the projects will be appended (must be set!)
  self.container = container

  // the url where the correct projects will be loaded (must be set!)
  self.url = url

  // before changing columns_min, columns_max, have a look at '.projects{.project{width:.%}}' in 'brain.scss' first
  self.defaultRows = 2
  self.columns = 0
  self.columns_min = 2
  self.columns_max = 9

  self.windowWidth = $(window).width()

  self.downloadLimit = 0
  self.initialDownloadLimit = self.defaultRows * self.columns_max // this way, always enough projects will be loaded
  self.numberOfLoadedProjects = 0
  self.numberOfVisibleProjects = 0
  self.defaultNumberOfVisibleProjects = 0
  self.totalNumberOfFoundProjects = 0

  // Setting this variable to true will display all fitting projects of a category
  self.show_all_projects = false

  // ----------------------------------
  // - Default init:
  //
  self.init = function () {
    restoreParamsWithSessionStorage()
    $.get(
      self.url,
      {
        limit: self.initialDownloadLimit,
        offset: self.numberOfLoadedProjects,
      },
      function (data) {
        if (
          data.CatrobatProjects === undefined ||
          data.CatrobatProjects.length === 0
        ) {
          $(self.container).hide()
          return
        }
        $(self.container).show()
        self.totalNumberOfFoundProjects = parseInt(
          data.CatrobatInformation.TotalProjects,
        )
        setup(data)
      },
    )
  }

  // ----------------------------------
  // - More from this user
  //
  self.projectId = undefined // save the id of a project (project detail page)

  self.initMoreFromThisUser = function (userId, projectId) {
    restoreParamsWithSessionStorage()
    $.get(
      self.url,
      {
        limit: self.initialDownloadLimit,
        offset: self.numberOfLoadedProjects,
        user_id: userId,
      },
      function (data) {
        if (
          data.CatrobatProjects === undefined ||
          data.CatrobatProjects.length === 0
        ) {
          $(self.container).hide()
          return
        }
        $(self.container).show()
        self.totalNumberOfFoundProjects = parseInt(
          data.CatrobatInformation.TotalProjects,
        )
        self.projectId = projectId
        setup(data)
        if (self.totalNumberOfFoundProjects <= 1) {
          $(self.container).hide()
        }
      },
    )
  }

  // ----------------------------------
  // - Profile programs
  //
  self.initProfile = function (userId) {
    self.show_all_projects = true // since we show all programs no need to restore a session
    $.get(
      self.url,
      {
        limit: self.initialDownloadLimit,
        offset: self.numberOfLoadedProjects,
        user_id: userId,
      },
      function (data) {
        if (
          data.CatrobatProjects === undefined ||
          data.CatrobatProjects.length === 0
        ) {
          $(self.container)
            .find('.projects')
            .append('<div class="no-projects">' + noProgramsText + '</div>')
          return
        }
        self.totalNumberOfFoundProjects = parseInt(
          data.CatrobatInformation.TotalProjects,
        )
        setup(data)
      },
    )
  }

  self.loadProjects = function (profileId) {
    self.initProfile(profileId)
    $(document).on('click', '.project', function () {
      const clickedProgramId = this.id.replace('program-', '')
      this.className += ' visited-project'
      const storedVisits = sessionStorage.getItem('visits')
      if (!storedVisits) {
        const newVisits = [clickedProgramId]
        sessionStorage.setItem('visits', JSON.stringify(newVisits))
      } else {
        const parsedVisits = JSON.parse(storedVisits)
        if (!($.inArray(clickedProgramId, parsedVisits) >= 0)) {
          parsedVisits.push(clickedProgramId)
          sessionStorage.setItem('visits', JSON.stringify(parsedVisits))
        }
      }
    })
  }

  // ----------------------------------
  // - Search Programs
  //
  self.query = ''

  self.initSearch = function (query) {
    const oldQuery = sessionStorage.getItem(self.query)
    if (query === oldQuery) {
      // same search -> restore old session limits
      restoreParamsWithSessionStorage()
    }
    sessionStorage.setItem(self.query, query)
    self.query = query

    $.get(
      self.url,
      {
        q: query,
        limit: self.initialDownloadLimit,
        offset: self.numberOfLoadedProjects,
      },
      function (data) {
        const searchResultsText = $('#search-results-text')

        if (
          data.CatrobatProjects === undefined ||
          data.CatrobatProjects.length === 0
        ) {
          $('#search-progressbar').hide()
          searchResultsText.addClass('no-results')
          searchResultsText.find('span').text(0)
          return
        }
        searchResultsText
          .find('span')
          .text(data.CatrobatInformation.TotalProjects)
        self.totalNumberOfFoundProjects = parseInt(
          data.CatrobatInformation.TotalProjects,
        )
        setup(data)
      },
    )
  }
  self.searchResult = function (q) {
    const searchInput = $('#top-app-bar__search-input')
    const oldQuery = searchInput.html(q).text()
    self.initSearch(oldQuery)
    $(document).ready(function () {
      // eslint-disable-next-line no-undef
      showTopBarSearch()
      searchInput.val(oldQuery)
      // eslint-disable-next-line no-undef
      controlTopBarSearchClearButton()
    })
  }

  // --------------------------------------------------------------------------------------------------------------------
  //
  async function setup(data) {
    if (!self.show_all_projects) {
      // We need to load all buttons for the show more/less logic if we don't display all projects
      await initLoaderUI()
    }

    showMoreListener()
    showLessListener()

    await loadProjectsIntoContainer(data)
    $('#search-progressbar').hide()
    await initParameters()
    await initNumberOfVisibleProjects()
    await keepRowsFull()

    await updateUIVisibility()
  }

  async function loadProjectsIntoContainer(data) {
    const projects = data.CatrobatProjects
    for (let i = 0; i < projects.length; i++) {
      if (projects[i].ProjectId === self.projectId) {
        // When the user is on a projects detail page no project category should contain the same project
        continue
      }

      const htmlProject = await buildProjectInHtml(projects[i], data)

      $(self.container).find('.projects').append(htmlProject)
      $(self.container).show()
    }
    self.numberOfLoadedProjects += projects.length
  }

  async function setNumberOfColumns() {
    const projectsContainerWidth = $(self.container).find('.projects').width()
    const projectsOuterWidth = $(self.container)
      .find('.project')
      .outerWidth(true)

    let columns = Math.floor(projectsContainerWidth / projectsOuterWidth)

    if (columns < self.columns_min) {
      columns = self.columns_min
    } else if (columns > self.columns_max) {
      columns = self.columns_max
    }
    self.columns = columns
  }

  async function updateInitialDownloadLimit() {
    if (
      self.restored_numberOfVisibleProjects === self.totalNumberOfFoundProjects
    ) {
      self.initialDownloadLimit = self.totalNumberOfFoundProjects
    } else if (self.initialDownloadLimit > self.downloadLimit) {
      self.initialDownloadLimit =
        self.initialDownloadLimit -
        (self.initialDownloadLimit % self.downloadLimit)
    } else {
      self.initialDownloadLimit = self.downloadLimit
    }
  }

  async function initNumberOfVisibleProjects() {
    if (self.restored_numberOfVisibleProjects > 0) {
      await updateNumberOfVisiblePrograms(self.restored_numberOfVisibleProjects)
    } else {
      await updateNumberOfVisiblePrograms(self.defaultNumberOfVisibleProjects)
    }
  }

  async function initParameters() {
    await setNumberOfColumns()
    self.downloadLimit = self.defaultRows * self.columns
    await updateInitialDownloadLimit()
    self.defaultNumberOfVisibleProjects = self.downloadLimit
  }

  async function keepRowsFull() {
    if (
      self.numberOfVisibleProjects < self.defaultNumberOfVisibleProjects &&
      self.numberOfVisibleProjects < self.totalNumberOfFoundProjects
    ) {
      await showMoreProjects()
    } else if (
      self.numberOfVisibleProjects > self.defaultNumberOfVisibleProjects &&
      self.numberOfVisibleProjects % self.downloadLimit !== 0 &&
      self.numberOfVisibleProjects !== self.totalNumberOfFoundProjects
    ) {
      await showLessProjects()
    }
  }

  async function updateNumberOfVisiblePrograms(number) {
    self.numberOfVisibleProjects = number
    setSessionStorage(self.numberOfVisibleProjects)
  }

  async function showMoreProjects() {
    if (self.numberOfVisibleProjects >= self.totalNumberOfFoundProjects) {
      // No projects can be retrieved anymore and they are all already visible
      await hide(showMoreButton)
    } else if (
      self.numberOfLoadedProjects >=
      self.numberOfVisibleProjects + self.downloadLimit
    ) {
      // Enough projects are loaded. Just set the next project rows visible
      await updateNumberOfVisiblePrograms(
        self.numberOfVisibleProjects + self.downloadLimit,
      )
      await updateUIVisibility()
    } else if (
      self.totalNumberOfFoundProjects === self.numberOfLoadedProjects
    ) {
      // All projects are loaded so just set them all visible
      await updateNumberOfVisiblePrograms(self.totalNumberOfFoundProjects)
      await updateUIVisibility()
    } else {
      // We need to load more projects
      await loadMoreProjects()
    }
  }

  async function loadMoreProjects() {
    await hide(showMoreButton)
    await hide(showLessButton)
    await show(ajaxAnimation)
    if (self.query !== '') {
      $.get(
        self.url,
        {
          q: self.query,
          limit: self.downloadLimit,
          offset: self.numberOfLoadedProjects,
        },
        async function (data) {
          if (
            data.CatrobatProjects === undefined ||
            data.CatrobatProjects.length === 0
          ) {
            await hide(ajaxAnimation)
            return
          }
          await loadProjectsIntoContainer(data)
          await showMoreProjects()
          await hide(ajaxAnimation)
        },
      )
    } else {
      $.get(
        self.url,
        {
          limit: self.downloadLimit,
          offset: self.numberOfLoadedProjects,
        },
        async function (data) {
          if (
            data.CatrobatProjects === undefined ||
            data.CatrobatProjects.length === 0
          ) {
            await hide(ajaxAnimation)
            return
          }
          await loadProjectsIntoContainer(data)
          await showMoreProjects()
          await hide(ajaxAnimation)
        },
      )
    }
  }

  async function showLessProjects() {
    if (self.defaultNumberOfVisibleProjects > self.numberOfVisibleProjects) {
      // we already display the minimum number of projects!
      await hide(showLessButton)
      return
    }

    // hides visible projects in a way that all columns are filled for rows that are visible
    if (
      self.numberOfVisibleProjects % self.defaultNumberOfVisibleProjects ===
      0
    ) {
      await updateNumberOfVisiblePrograms(
        self.numberOfVisibleProjects - self.downloadLimit,
      )
    } else {
      await updateNumberOfVisiblePrograms(
        self.numberOfVisibleProjects -
          (self.numberOfVisibleProjects % self.defaultNumberOfVisibleProjects),
      )
    }
    await updateUIVisibility()
  }

  // -------------------------------------------------------------------------------------------------------------------
  // UI elements and helper functions to control the UI
  //
  const showMoreButton = 'button-show-more'
  const showLessButton = 'button-show-less'
  const ajaxAnimation = 'button-show-ajax'

  async function hide(buttonName) {
    $(self.container)
      .find('.' + buttonName)
      .hide()
  }

  async function show(buttonName) {
    $(self.container)
      .find('.' + buttonName)
      .show()
  }

  async function getLoadingSpinner() {
    return (
      '<div class="circular-progress">' +
      '  <div role="progressbar" class="mdc-circular-progress mdc-circular-progress--indeterminate" style="width:48px;height:48px;">' +
      '    <div class="mdc-circular-progress__indeterminate-container">' +
      '      <div class="mdc-circular-progress__spinner-layer">' +
      '        <div class="mdc-circular-progress__circle-clipper mdc-circular-progress__circle-left">' +
      '          <svg class="mdc-circular-progress__indeterminate-circle-graphic" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">' +
      '            <circle cx="24" cy="24" r="18" stroke-dasharray="113.097" stroke-dashoffset="56.549" stroke-width="4"/>' +
      '          </svg>' +
      '        </div>' +
      '        <div class="mdc-circular-progress__gap-patch">' +
      '          <svg class="mdc-circular-progress__indeterminate-circle-graphic" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">' +
      '            <circle cx="24" cy="24" r="18" stroke-dasharray="113.097" stroke-dashoffset="56.549" stroke-width="3.2"/>' +
      '          </svg>' +
      '        </div>' +
      '        <div class="mdc-circular-progress__circle-clipper mdc-circular-progress__circle-right">' +
      '          <svg class="mdc-circular-progress__indeterminate-circle-graphic" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">' +
      '            <circle cx="24" cy="24" r="18" stroke-dasharray="113.097" stroke-dashoffset="56.549" stroke-width="4"/>' +
      '          </svg>' +
      '        </div>' +
      '      </div>' +
      '    </div>' +
      '  </div>' +
      '</div>'
    )
  }

  async function initLoaderUI() {
    $(self.container).append(
      '' +
        '<div class="button-show-placeholder">' +
        '<button class="material-icons mdc-icon-button ' +
        showMoreButton +
        '">' +
        'expand_more' +
        '</button>' +
        '<button class="material-icons mdc-icon-button ' +
        showLessButton +
        '">' +
        'expand_less' +
        '</button>' +
        '<div class=' +
        ajaxAnimation +
        '>' +
        (await getLoadingSpinner()) +
        '</div>',
    )
  }

  async function showVisibleButtons() {
    // As long as not all projects are visible -> show the "show more button"
    if (self.numberOfVisibleProjects < self.totalNumberOfFoundProjects) {
      await show(showMoreButton)
    } else {
      await hide(showMoreButton)
    }

    // As long as there are more than the minimum number of projects displayed
    //   -> give the user the possibility to show less projects
    if (self.numberOfVisibleProjects > self.defaultNumberOfVisibleProjects) {
      await show(showLessButton)
    } else {
      await hide(showLessButton)
    }
  }

  async function showVisibleProjects() {
    const projects = $(self.container).find('.project')
    $(projects).hide()
    for (
      let i = 0;
      i < self.numberOfVisibleProjects && i < self.numberOfLoadedProjects;
      i++
    ) {
      $(projects[i]).show()
    }
  }

  async function updateUIVisibility() {
    if (self.show_all_projects) {
      return
    }

    await showVisibleProjects()

    await showVisibleButtons()
  }

  async function buildProjectInHtml(project, data) {
    const div = await initDivWithCorrectContainerIcon(project)
    const projectLink = await getProjectLink(project, data)
    const storedVisits = sessionStorage.getItem('visits')
    let visited = false
    if (storedVisits) {
      const parsedVisits = JSON.parse(storedVisits)
      const projectId = project.ProjectId.toString()
      visited = $.inArray(projectId, parsedVisits) >= 0
    }

    return $(
      '<div class="project ' +
        (visited ? 'visited-project ' : '') +
        '" id="program-' +
        project.ProjectId +
        '">' +
        '<a href="' +
        projectLink +
        '">' +
        '<img data-src="' +
        data.CatrobatInformation.BaseUrl +
        project.ScreenshotSmall +
        '" alt="" class="lazyload" />' +
        '<span class="project-name">' +
        self.escapeJavaScript(project.ProjectName) +
        '</span>' +
        div +
        '</a></div>',
    )
  }

  async function initDivWithCorrectContainerIcon(project) {
    // ToDo: Refactor to new project_list
    switch (self.container) {
      case '#search-results':
        return (
          '<div><span class="project-thumb-icon material-icons">schedule</span>' +
          project.UploadedString +
          '</div>'
        )

      case '#myprofile-projects':
        return (
          '<div><span class="project-thumb-icon material-icons">schedule</span>' +
          project.UploadedString +
          '</div>'
        )
    }
  }

  async function getProjectLink(project, data) {
    return data.CatrobatInformation.BaseUrl + project.ProjectUrl
  }

  // -------------------------------------------------------------------------------------------------------------------
  // Listeners
  //
  function showMoreListener() {
    $(self.container + ' .' + showMoreButton).click(async function () {
      await showMoreProjects()
    })
  }

  async function showLessListener() {
    $(self.container + ' .' + showLessButton).click(async function () {
      await showLessProjects()
    })
  }

  $(window).resize(async function () {
    if (self.windowWidth === $(window).width()) {
      return
    }
    self.windowWidth = $(window).width()
    await initParameters()
    await updateNumberOfVisiblePrograms(
      Math.min(self.initialDownloadLimit, self.totalNumberOfFoundProjects),
    )
    await keepRowsFull()
    await updateUIVisibility()
  })

  // -------------------------------------------------------------------------------------------------------------------
  // Session Handling - When returning to a page everything should be as it was when it was abandoned
  //
  self.restored_numberOfVisibleProjects = 0

  function restoreParamsWithSessionStorage() {
    self.restored_numberOfVisibleProjects = parseInt(
      sessionStorage.getItem(self.container),
    )
    if (self.restored_numberOfVisibleProjects > self.initialDownloadLimit) {
      self.initialDownloadLimit = self.restored_numberOfVisibleProjects
    }
  }

  function setSessionStorage(value) {
    sessionStorage.setItem(self.container, value)
  }

  // -------------------------------------------------------------------------------------------------------------------
  // prevent JS code execution! (Encoding the < and > chars to their HTML equivalents)
  //
  self.escapeJavaScript = function (html) {
    return html.replace(/</g, '&lt;').replace(/>/g, '&gt;')
  }
}
