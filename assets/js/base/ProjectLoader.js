let ProjectLoader = function(container, url, recommended_by_project_id, recommended_by_page_id) {
  
  let self = this
  
  // The container where the projects will be appended (must be set!)
  self.container = container
  
  // the url where the correct projects will be loaded (must be set!)
  self.url = url
  
  self.recommended_by_project_id = (typeof recommended_by_project_id === 'undefined') ? null : recommended_by_project_id
  
  self.recommended_by_page_id = (typeof recommended_by_page_id === 'undefined') ? null : recommended_by_page_id
  
  // before changing columns_min, columns_max, have a look at '.projects{.project{width:.%}}' in 'brain.scss' first
  self.default_rows = 2
  self.columns = 0
  self.columns_min = 2
  self.columns_max = 9
  
  self.windowWidth = $(window).width()
  
  self.download_limit = 0
  self.initial_download_limit = self.default_rows * self.columns_max // this way, always enough projects will be loaded
  self.number_of_loaded_projects = 0
  self.number_of_visible_projects = 0
  self.default_number_of_visible_projects = 0
  self.total_number_of_found_projects = 0
  
  // Setting this variable to true will display all fitting projects of a category
  self.show_all_projects = false
  
  
  // ----------------------------------
  // - Default init:
  //
  self.init = function() {
    restoreParamsWithSessionStorage()
    $.get(self.url, {limit: self.initial_download_limit, offset: self.number_of_loaded_projects}, function (data) {
      if (data.CatrobatProjects === undefined || data.CatrobatProjects.length === 0)
      {
        $(self.container).hide()
        return
      }
      $(self.container).show()
      self.total_number_of_found_projects = parseInt(data.CatrobatInformation.TotalProjects)
      setup(data)
    })
  }
  
  // ----------------------------------
  // - Recommended Programs
  //
  self.initRecsys = function() {
    
    if (($(self.container).length <= 0))
    {
      return
    }
    
    restoreParamsWithSessionStorage()
    $.get(self.url, {program_id: self.recommended_by_project_id,}, function (data) {
      if (data.CatrobatProjects === undefined || data.CatrobatProjects.length === 0)
      {
        $(self.container).hide()
        return
      }
      $(self.container).show()
      self.total_number_of_found_projects = parseInt(data.CatrobatInformation.TotalProjects)
      setup(data)
    })
  }
  
  // ----------------------------------
  // - More from this user
  //
  self.project_id = undefined // save the id of a project (project detail page)
  
  self.initMoreFromThisUser = function(user_id, project_id) {
    restoreParamsWithSessionStorage()
    $.get(self.url, {
      limit  : self.initial_download_limit,
      offset : self.number_of_loaded_projects,
      user_id: user_id
    }, function (data) {
      if (data.CatrobatProjects === undefined || data.CatrobatProjects.length === 0)
      {
        $(self.container).hide()
        return
      }
      $(self.container).show()
      self.total_number_of_found_projects = parseInt(data.CatrobatInformation.TotalProjects)
      self.project_id = project_id
      setup(data)
      if (self.total_number_of_found_projects <= 1) {
        $(self.container).hide()
      }
    })
  }
  
  // ----------------------------------
  // - Profile programs
  //
  self.initProfile = function(user_id) {
    self.show_all_projects = true // since we show all programs no need to restore a session
    $.get(self.url, {
      limit  : self.initial_download_limit,
      offset : self.number_of_loaded_projects,
      user_id: user_id
    }, function (data) {
      if (data.CatrobatProjects === undefined || data.CatrobatProjects.length === 0)
      {
        let url = Routing.generate('translate_word', {
          'word'  : 'programs.noPrograms',
          'domain': 'catroweb'
        })
        $.get(url, function (data) {
          $(self.container).find('.programs').append('<div class="no-programs">' + data + '</div>')
        })
        return
      }
      self.total_number_of_found_projects = parseInt(data.CatrobatInformation.TotalProjects)
      setup(data)
    })
  }
  
  // ----------------------------------
  // - Search Programs
  //
  self.query = ''
  
  self.initSearch = function(query) {
    let old_query = sessionStorage.getItem(self.query)
    if (query === old_query)
    { // same search -> restore old session limits
      restoreParamsWithSessionStorage()
    }
    sessionStorage.setItem(self.query, query)
    self.query = query
    
    $.get(self.url, {q: query, limit: self.initial_download_limit, offset: self.number_of_loaded_projects},
      function (data) {
        let search_results_text = $('#search-results-text')
        
        if (data.CatrobatProjects === undefined || data.CatrobatProjects.length === 0)
        {
          search_results_text.addClass('no-results')
          search_results_text.find('span').text(0)
          return
        }
        search_results_text.find('span').text(data.CatrobatInformation.TotalProjects)
        self.total_number_of_found_projects = parseInt(data.CatrobatInformation.TotalProjects)
        setup(data)
      })
  }
  
  //--------------------------------------------------------------------------------------------------------------------
  //
  async function setup(data) {
    
    if (!self.show_all_projects)
    {
      // We need to load all buttons for the show more/less logic if we don't display all projects
      await initLoaderUI();
    }
    
    showMoreListener()
    showLessListener()
    
    await loadProjectsIntoContainer(data)
    await initParameters()
    await initNumberOfVisibleProjects()
    await keepRowsFull()

    await updateUIVisibility()
  }
  
  async function loadProjectsIntoContainer(data) {
    let projects = data.CatrobatProjects
    for (let i = 0; i < projects.length; i++)
    {
      if (projects[i].ProjectId === self.project_id)
      {
        // When the user is on a projects detail page no recommendations etc. should contain the same project
        continue;
      }
      
      const html_project = await buildProjectInHtml(projects[i], data)
      
      $(self.container).find('.programs').append(html_project)
      $(self.container).show()
      
      if (isMyProject())
      {
        await addMyProfileProgramButtons(html_project, projects[i]);
      }
    }
    self.number_of_loaded_projects += projects.length
  }
  
  async function setNumberOfColumns() {
    
    let programs_container_width =$(self.container).find('.programs').width()
    let program_outer_width = $(self.container).find('.program').outerWidth(true)
    
    let columns = Math.floor(programs_container_width / program_outer_width)
    
    if (columns < self.columns_min)
    {
      columns = self.columns_min
    }
    else if (columns > self.columns_max)
    {
      columns = self.columns_max
    }
    self.columns = columns
  }
  
  async function updateInitialDownloadLimit() {
    if (self.restored_number_of_visible_projects === self.total_number_of_found_projects) {
      self.initial_download_limit = self.total_number_of_found_projects
    }
    else if (self.initial_download_limit > self.download_limit)
    {
      self.initial_download_limit = self.initial_download_limit - (self.initial_download_limit % self.download_limit)
    }
    else
    {
      self.initial_download_limit = self.download_limit
    }
  }
  
  async function initNumberOfVisibleProjects() {
    if (self.restored_number_of_visible_projects > 0) {
      await updateNumberOfVisiblePrograms(self.restored_number_of_visible_projects)
    }
    else {
      await updateNumberOfVisiblePrograms(self.default_number_of_visible_projects)
    }
  }
  
  async function initParameters() {
    await setNumberOfColumns()
    self.download_limit = self.default_rows * self.columns
    await updateInitialDownloadLimit()
    self.default_number_of_visible_projects = self.download_limit
  }
  
  async function keepRowsFull() {
    
    if (self.number_of_visible_projects < self.default_number_of_visible_projects &&
      self.number_of_visible_projects < self.total_number_of_found_projects)
    {
      await showMoreProjects()
    }
    else if (self.number_of_visible_projects > self.default_number_of_visible_projects &&
      self.number_of_visible_projects % self.download_limit !== 0 &&
      self.number_of_visible_projects !== self.total_number_of_found_projects)
    {
      await showLessProjects()
    }
  }
  
  async function updateNumberOfVisiblePrograms(number) {
    self.number_of_visible_projects = number
    setSessionStorage(self.number_of_visible_projects)
  }
  
  async function showMoreProjects() {
    
    if (self.number_of_visible_projects >= self.total_number_of_found_projects)
    {
      // No projects can be retrieved anymore and they are all already visible
      await hide(show_more_button)
    }
    else if (self.number_of_loaded_projects >= self.number_of_visible_projects + self.download_limit)
    {
      // Enough projects are loaded. Just set the next project rows visible
      await updateNumberOfVisiblePrograms(self.number_of_visible_projects + self.download_limit)
      await updateUIVisibility()
    }
    else if (self.total_number_of_found_projects === self.number_of_loaded_projects)
    {
      // All projects are loaded so just set them all visible
      await updateNumberOfVisiblePrograms(self.total_number_of_found_projects)
      await updateUIVisibility()
    }
    else
    {
      // We need to load more projects
      await loadMoreProjects()
    }
  }
  
  async function loadMoreProjects() {
    await hide(show_more_button)
    await hide(show_less_button)
    await show(ajax_animation)
    if (self.query !== '')
    {
      $.get(self.url, {
        q     : self.query,
        limit : self.download_limit,
        offset: self.number_of_loaded_projects
      }, async function (data) {
        if ((data.CatrobatProjects === undefined || data.CatrobatProjects.length === 0))
        {
          await hide(ajax_animation)
          return
        }
        await loadProjectsIntoContainer(data)
        await showMoreProjects()
        await hide(ajax_animation)
      })
      
    }
    else
    {
      $.get(self.url, {limit: self.download_limit, offset: self.number_of_loaded_projects}, async function (data) {
        if (data.CatrobatProjects === undefined || data.CatrobatProjects.length === 0)
        {
          await hide(ajax_animation)
          return
        }
        await loadProjectsIntoContainer(data)
        await showMoreProjects()
        await hide(ajax_animation)
      })
    }
  }
  
  async function showLessProjects() {
    
    if (self.default_number_of_visible_projects > self.number_of_visible_projects)
    {
      // we already display the minimum number of projects!
      await hide(show_less_button)
      return
    }
    
    // hides visible projects in a way that all columns are filled for rows that are visible
    if (self.number_of_visible_projects % self.default_number_of_visible_projects === 0)
    {
      await updateNumberOfVisiblePrograms(self.number_of_visible_projects - self.download_limit)
    }
    else
    {
      await updateNumberOfVisiblePrograms(self.number_of_visible_projects -
        self.number_of_visible_projects % self.default_number_of_visible_projects)
    }
    await updateUIVisibility();
  }
  // -------------------------------------------------------------------------------------------------------------------
  // UI elements and helper functions to control the UI
  //
  const show_more_button = 'button-show-more';
  const show_less_button = 'button-show-less';
  const ajax_animation = 'button-show-ajax';
  
  async function hide(button_name) {
    $(self.container).find('.' + button_name).hide()
  }
  
  async function show(button_name) {
    $(self.container).find('.' + button_name).show()
  }
  
  async function initLoaderUI() {
    $(self.container).append('' +
      '<div class="button-show-placeholder">' +
      '<div class=' + show_more_button + '>' +
      '  <i class="fa fa-chevron-circle-down catro-icon-button"></i>' +
      '</div>' +
      '<div class=' + ajax_animation + '>' +
      '  <i class="fa fa-spinner fa-pulse fa-2x fa-fw" aria-hidden="true"></i>' +
      '</div>' +
      '<div class=' + show_less_button + '>' +
      '  <i class="fa fa-chevron-circle-up catro-icon-button"></i></div>' +
      '</div>')
  }
  
  async function showVisibleButtons() {
    
    // As long as not all projects are visible -> show the "show more button"
    if (self.number_of_visible_projects < self.total_number_of_found_projects)
    {
      await show(show_more_button)
    }
    else
    {
      await hide(show_more_button)
    }
    
    // As long as there are more than the minimum number of projects displayed
    //   -> give the user the possibility to show less projects
    if (self.number_of_visible_projects > self.default_number_of_visible_projects)
    {
      await show(show_less_button)
    }
    else
    {
      await hide(show_less_button)
    }
  }
  
  async function showVisibleProjects() {
    let projects = $(self.container).find('.program')
    $(projects).hide()
    for (let i = 0; i < self.number_of_visible_projects && i < self.number_of_loaded_projects; i++)
    {
      $(projects[i]).show()
    }
  }
  
  async function updateUIVisibility() {
    if (self.show_all_projects)
    {
      return
    }
    
    await showVisibleProjects()
    
    await showVisibleButtons()
  }
  
  async function buildProjectInHtml(project, data) {
    
    let div = await initDivWithCorrectContainerIcon(project)
    let link_css_classes = await getLinkCssClasses()
    let project_link = await getProjectLink(project, data)
    let stored_visits = sessionStorage.getItem('visits')
    let visited = false
    if (stored_visits)
    {
      let parsed_visits = JSON.parse(stored_visits)
      let project_id = project.ProjectId.toString()
      visited = $.inArray(project_id, parsed_visits) >= 0
    }
    
    return $(
      '<div class="program ' + (visited ? 'visited-program ' : '') + '" id="program-' + project.ProjectId + '">' +
      '<a href="' + project_link + '" class="' + link_css_classes + '">' +
      '<img src="' + data.CatrobatInformation.BaseUrl + project.ScreenshotSmall + '" alt="" />' +
      '<span class="program-name">' + self.escapeJavaScript(project.ProjectName) + '</span>' +
      div +
      '</a></div>'
    )
  }
  
  async function initDivWithCorrectContainerIcon(project) {
    // Extend this for new containers...
    switch (self.container)
    {
      case '#newest':
      case '#search-results':
      case '#random':
        return '<div><i class="fas fa-clock program-small-icon"></i>' + project.UploadedString + '</div>'
      
      case '#myprofile-programs':
      case '#user-programs':
        return '<div><i class="fas fa-clock program-small-icon"></i>' + project.UploadedString + '</div>'
      
      case '#mostDownloaded':
        return  '<div><i class="fas fa-download program-small-icon"></i>' + project.Downloads + '</div>'
      
      case '#mostViewed':
        return  '<div><i class="fas fa-eye program-small-icon"></i>' + project.Views + '</div>'
      
      case '#recommendations':
      case '#more-from-this-user-recommendations':
        return  '<div><i class="fas fa-eye program-small-icon"></i>' + project.Views + '</div>'
      
      case '#recommended':
        return '<div><i class="fas fa-eye program-small-icon"></i>' + project.Views + '</div>'
      
      case '#specific-programs-recommendations':
        return '<div><i class="fas fa-download program-small-icon"></i>' + project.Downloads + '</div>'
      
      default:
        if ($(self.container).hasClass('starterDownloads'))
        {
          return '<div><i class="fas fa-download program-small-icon"></i>' + project.Downloads + '</div>'
        }
        else
        {
          div = '<div><i class="fas fa-user program-small-icon"></i>' + self.escapeJavaScript(project.Author) + '</div>'
        }
    }
  }
  
  async function getLinkCssClasses() {
    let additional_link_css_class = '';
    if (self.container === '#recommended')
    {
      additional_link_css_class = 'homepage-recommended-programs'
    }
    return 'rec-programs' + ' ' + additional_link_css_class + ' '
  }
  
  async function getProjectLink(project, data) {
    switch (self.container)
    {
      case '#recommendations':
        return data.CatrobatInformation.BaseUrl + project.ProjectUrl + '?rec_from=' + self.recommended_by_project_id
      
      case '#recommended':
      case '#specific-programs-recommendations':
        return data.CatrobatInformation.BaseUrl + project.ProjectUrl +
        '?rec_by_page_id=' + self.recommended_by_page_id +
        (self.recommended_by_project_id != null) ?
          '&rec_by_program_id=' + self.recommended_by_project_id :
          '' + '&rec_user_specific=' + (('isUserSpecificRecommendation' in data) &&
          data.isUserSpecificRecommendation ? 1 : 0)
    }
    return data.CatrobatInformation.BaseUrl + project.ProjectUrl
  }
  
  function isMyProject() {
    return self.container === '#myprofile-programs'
  }
  
  async function addMyProfileProgramButtons(html_project, project) {
    $(html_project).prepend('<div id="delete-' + project.ProjectId + '" class="img-delete" ' +
      'onclick="profile.deleteProgram(' + project.ProjectId + ')">' +
      '<i class="fas fa-times-circle catro-icon-button"></i></div>')
    
    $(html_project).prepend('<div id="visibility-lock-open-' + project.ProjectId + '" class="img-lock-open" ' +
      (project.Private ? 'style="display: none;"' : '') +
      ' onclick="profile.toggleVisibility(' + project.ProjectId + ')">' +
      '<i class="fas fa-lock-open catro-icon-button"></i></div>')
    
    $(html_project).prepend('<div id="visibility-lock-' + project.ProjectId + '" class="img-lock" ' +
      (project.Private ? '' : 'style="display: none;"') +
      ' onclick="profile.toggleVisibility(' + project.ProjectId + ')">' +
      '<i class="fas fa-lock catro-icon-button"></i></div>')
  }
  
  // -------------------------------------------------------------------------------------------------------------------
  // Listeners
  //
  function showMoreListener() {
    $(self.container + ' .' + show_more_button).click(async function () {
      await showMoreProjects()
    })
  }
  
  async function showLessListener() {
    $(self.container + ' .' + show_less_button).click(async function () {
      await showLessProjects()
    })
  }
  
  $(window).resize(async function () {
    if (self.windowWidth === $(window).width())
    {
      return
    }
    self.windowWidth = $(window).width()
    await initParameters()
    await updateNumberOfVisiblePrograms(Math.min(self.initial_download_limit, self.total_number_of_found_projects))
    await keepRowsFull()
    await updateUIVisibility()
  })
  
  // -------------------------------------------------------------------------------------------------------------------
  // Session Handling - When returning to a page everything should be as it was when it was abandoned
  //
  self.restored_number_of_visible_projects = 0;
  
  function restoreParamsWithSessionStorage() {
    self.restored_number_of_visible_projects = parseInt(sessionStorage.getItem(self.container))
    if (self.restored_number_of_visible_projects > self.initial_download_limit) {
      self.initial_download_limit = self.restored_number_of_visible_projects;
    }
  }
  
  function setSessionStorage(value) {
    sessionStorage.setItem(self.container, value)
  }
  
  // -------------------------------------------------------------------------------------------------------------------
  // prevent JS code execution! (Encoding the < and > chars to their HTML equivalents)
  //
  self.escapeJavaScript = function(html) {
    return html.replace(/</g, "&lt;").replace(/>/g, "&gt;");
  }
}