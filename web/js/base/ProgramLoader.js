let ProgramLoader = function(container, url, column_max, recommended_by_program_id, recommended_by_page_id) {
  let self = this;
  self.container = container;
  self.url = url;
  self.recommended_by_program_id =
    (typeof recommended_by_program_id === "undefined") ? null : recommended_by_program_id;
  self.recommended_by_page_id = (typeof recommended_by_page_id === "undefined") ? null : recommended_by_page_id;
  self.query = "";
  self.default_rows = 2;
  // before changing columns_min, columns_max, have a look at '.programs{.program{width:.%}}' in 'brain.scss' first
  self.columns = 0;
  self.columns_min = 3;
  self.columns_max = 9;
  self.windowWidth = $(window).width();
  self.download_limit = 0;
  self.initial_download_limit = self.default_rows * self.columns_max; // this way, always enough programs will be loaded
  self.show_all_programs = false;
  self.amount_of_loaded_programs = 0;
  self.amount_of_visible_programs = 0;
  self.total_amount_of_found_programs = 0;
  self.default_amount_of_visible_programs = 3;
  
  
  self.init = function() {
    self.restoreParamsWithSessionStorage();
    $.get(self.url, {limit: self.initial_download_limit, offset: self.amount_of_loaded_programs}, function(data) {
      if (data.CatrobatProjects === undefined || data.CatrobatProjects.length === 0)
      {
        let url = Routing.generate('translate_word', {
          'word': 'programs.noPrograms',
          'domain': 'catroweb'
        });
        $.get(url, function(data) {
          $(self.container).find('.programs').append('<div class="no-programs">' + data + '</div>');
        });
        return;
      }
      self.total_amount_of_found_programs = data.CatrobatInformation.TotalProjects;
      self.setup(data);
    });
  };
  
  
  self.initRecsys = function() {
    
    if (($(self.container).length <= 0))
    {
      return;
    }
    
    self.restoreParamsWithSessionStorage(); // sets self.amount_of_visible_programsOfStoredSession and self.initial_download_limit
    $.get(self.url, {program_id: self.recommended_by_program_id,}, function(data) {
      if (data.CatrobatProjects === undefined || data.CatrobatProjects.length === 0)
      {
        $(self.container).hide();
        return;
      }
      self.total_amount_of_found_programs = data.CatrobatInformation.TotalProjects;
      self.setup(data);
    });
  };
  
  
  self.initSpecificRecsys = function() {
    $(self.container).hide();
    self.init();
  };
  
  
  self.initProfile = function(user_id) {
    self.show_all_programs = false;
    self.restoreParamsWithSessionStorage();
    $.get(self.url, {
      limit  : self.initial_download_limit,
      offset : self.amount_of_loaded_programs,
      user_id: user_id
    }, function(data) {
      if (data.CatrobatProjects === undefined || data.CatrobatProjects.length === 0)
      {
        let url = Routing.generate('translate_word', {
          'word': 'programs.noPrograms',
          'domain': 'catroweb'
        });
        $.get(url, function(data) {
          $(self.container).find('.programs').append('<div class="no-programs">' + data + '</div>');
        });
        return;
      }
      self.total_amount_of_found_programs = data.CatrobatInformation.TotalProjects;
      self.setup(data);
    });
  };
  
  
  self.restoreParamsWithSessionStorage = function() {
    let amountOfStoredVisiblePrograms = sessionStorage.getItem(self.container);
    if (amountOfStoredVisiblePrograms > self.initial_download_limit)
    {
      self.initial_download_limit = amountOfStoredVisiblePrograms;
    }
  };
  
  
  self.setSessionStorage = function(value) {
    sessionStorage.setItem(self.container, value);
  };
  
  
  self.initSearch = function(query) {
    let old_query = sessionStorage.getItem(self.query);
    if (query === old_query)
    { // same search -> restore old session limits
      self.restoreParamsWithSessionStorage();
    }
    sessionStorage.setItem(self.query, query);
    self.query = query;
    
    $.get(self.url, {q: query, limit: self.initial_download_limit, offset: self.amount_of_loaded_programs},
      function(data) {
        let search_results_text = $('#search-results-text');
        
        if (data.CatrobatProjects === undefined || data.CatrobatProjects.length === 0)
        {
          search_results_text.addClass('no-results');
          search_results_text.find('span').text(0);
          return;
        }
        search_results_text.find('span').text(data.CatrobatInformation.TotalProjects);
        self.total_amount_of_found_programs = data.CatrobatInformation.TotalProjects;
        
        self.setup(data);
      });
  };
  
  
  self.setup = function(data) {
    if (!self.show_all_programs)
    {
      $(self.container).append('' +
        '<div class="button-show-placeholder">' +
        '<div class="button-show-more"><i class="fa fa-chevron-circle-down catro-icon-button"></i></div>' +
        '<div class="button-show-ajax"><i class="fa fa-spinner fa-pulse fa-2x fa-fw" aria-hidden="true"></i></div>' +
        '<div class="button-show-less"><i class="fa fa-chevron-circle-up catro-icon-button"></i></div>' +
        '</div>');
    }
    self.loadProgramsIntoContainer(data);
    
    self.showMoreListener();
    self.showLessListener();
    
    self.updateParameterBasedOnScreenSize();
    
    $(window).resize(function() {
      if (self.windowWidth === $(window).width())
      {
        return;
      }
      self.windowWidth = $(window).width();
      self.updateParameterBasedOnScreenSize();
    });
  };
  
  
  self.updateParameterBasedOnScreenSize = function() {
    let columns = Math.round(($('.programs').width()) / $('.program').outerWidth());
    if (columns < self.columns_min)
    {
      columns = self.columns_min;
    }
    else if (columns > self.columns_max)
    {
      columns = self.columns_max;
    }
    self.columns = columns;
    self.download_limit = self.default_rows * self.columns;
    if (self.initial_download_limit > self.download_limit)
    {
      self.initial_download_limit = self.initial_download_limit - (self.initial_download_limit % self.download_limit);
    }
    else
    {
      self.initial_download_limit = self.download_limit;
    }
    self.default_amount_of_visible_programs = self.download_limit;
    self.amount_of_visible_programs = Math.min(self.initial_download_limit, self.total_amount_of_found_programs);
    
    if (self.amount_of_visible_programs < self.default_amount_of_visible_programs &&
      self.amount_of_visible_programs < self.total_amount_of_found_programs)
    {
      self.showMorePrograms();
    }
    else if (self.amount_of_visible_programs > self.default_amount_of_visible_programs &&
      self.amount_of_visible_programs % self.download_limit !== 0)
    {
      self.showLessPrograms();
    }
    else
    {
      self.updateProgramVisibility();
    }
  };
  
  
  self.loadProgramsIntoContainer = function(data) {
    let programs = data.CatrobatProjects;
    for (let i = 0; i < programs.length; i++)
    {
      let div = null;
      let additional_link_css_class = null;
      
      // Extend this for new containers...
      switch (self.container)
      {
        case '#newest':
        case '#search-results':
        case "#random":
          div = '<div><i class="fas fa-clock program-small-icon"></i>' + programs[i].UploadedString + '</div>';
          break;
        case '#myprofile-programs':
        case '#user-programs':
          div = '<div><i class="fas fa-clock program-small-icon"></i>' + programs[i].UploadedString + '</div>';
          break;
        case '#mostDownamount_of_loaded_programs':
          div = '<div><i class="fas fa-download program-small-icon"></i>' + programs[i].Downloads + '</div>';
          break;
        case '#mostViewed':
          div = '<div><i class="fas fa-eye program-small-icon"></i>' + programs[i].Views + '</div>';
          break;
        case '#recommendations':
          div = '<div><i class="fas fa-eye program-small-icon"></i>' + programs[i].Views + '</div>';
          break;
        case '#recommended':
          div = '<div><i class="fas fa-eye program-small-icon"></i>' + programs[i].Views + '</div>';
          additional_link_css_class = "homepage-recommended-programs";
          break;
        case '#specific-programs-recommendations':
          div = '<div><i class="fas fa-download program-small-icon"></i>' + programs[i].Downloads + '</div>';
          break;
        default:
          if ($(self.container).hasClass('starterDownloads'))
          {
            div = '<div><i class="fas fa-download program-small-icon"></i>' + programs[i].Downloads + '</div>';
          }
          else
          {
            div = '<div><i class="fas fa-user program-small-icon"></i>' + programs[i].Author + '</div>';
          }
      }
      
      let program_link = undefined;
      if (self.container === "#recommendations")
      {
        program_link = data.CatrobatInformation.BaseUrl + programs[i].ProjectUrl +
          "?rec_from=" + self.recommended_by_program_id;
      }
      else if ((self.container === "#recommended") || (self.container === "#specific-programs-recommendations"))
      {
        program_link = data.CatrobatInformation.BaseUrl + programs[i].ProjectUrl + "?rec_by_page_id=" +
          self.recommended_by_page_id;
        program_link += (self.recommended_by_program_id != null) ? "&rec_by_program_id=" +
          self.recommended_by_program_id : "";
        program_link += "&rec_user_specific=" + (("isUserSpecificRecommendation" in data) &&
        data.isUserSpecificRecommendation ? 1 : 0);
      }
      else
      {
        program_link = data.CatrobatInformation.BaseUrl + programs[i].ProjectUrl;
      }
      
      let stored_visits = sessionStorage.getItem("visits");
      let link_css_classes = "rec-programs" + ((additional_link_css_class != null) ?
        (" " + additional_link_css_class) : "");
      let program = undefined;
      if (!stored_visits)
      {
        program = $(
          '<div class="program" id="program-' + programs[i].ProjectId + '">' +
          '<a class="' + link_css_classes + '" href = \'' + program_link + '\'>' +
          '<div><img src="' + data.CatrobatInformation.BaseUrl + programs[i].ScreenshotSmall + '"></div>' +
          '<div class="program-name"><b>' + programs[i].ProjectName + '</b></div>' +
          div +
          '</a>' +
          '</div>'
        );
      }
      else
      {
        let parsed_visits = JSON.parse(stored_visits);
        let program_id = programs[i].ProjectId.toString();
        if ($.inArray(program_id, parsed_visits) >= 0)
        {
          program = $(
            '<div class="program visited-program" id="program-' + programs[i].ProjectId + '">' +
            '<a class="' + link_css_classes + '" href = \'' + program_link + '\' >' +
            '<div><img src="' + data.CatrobatInformation.BaseUrl + programs[i].ScreenshotSmall + '"></div>' +
            '<div class="program-name"><b>' + programs[i].ProjectName + '</b></div>' +
            div +
            '</a>' +
            '</div>'
          );
        }
        else
        {
          program = $(
            '<div class="program" id="program-' + programs[i].ProjectId + '">' +
            '<a class="' + link_css_classes + '" href = \'' + program_link + '\'>' +
            '<div><img src="' + data.CatrobatInformation.BaseUrl + programs[i].ScreenshotSmall + '"></div>' +
            '<div class="program-name"><b>' + programs[i].ProjectName + '</b></div>' +
            div +
            '</a>' +
            '</div>'
          );
        }
      }
      
      $(self.container).find('.programs').append(program);
      $(self.container).show();
      
      if (self.container === '#myprofile-programs')
      {
        $(program).prepend('<div id="delete-' + programs[i].ProjectId + '" class="img-delete" ' +
          'onclick="profile.deleteProgram(' + programs[i].ProjectId + ')">' +
          '<i class="fas fa-times-circle catro-icon-button"></i></div>');
        
        $(program).prepend('<div id="visibility-lock-open-' + programs[i].ProjectId + '" class="img-lock-open" ' +
          (programs[i].Private ? 'style="display: none;"' : '') +
          ' onclick="profile.toggleVisibility(' + programs[i].ProjectId + ')">' +
            '<i class="fas fa-lock-open catro-icon-button"></i></div>');
  
        $(program).prepend('<div id="visibility-lock-' + programs[i].ProjectId + '" class="img-lock" ' +
          (programs[i].Private ? '' : 'style="display: none;"') +
          ' onclick="profile.toggleVisibility(' + programs[i].ProjectId + ')">' +
          '<i class="fas fa-lock catro-icon-button"></i></div>');
      }
    }
    self.amount_of_loaded_programs += programs.length;
  };
  
  
  self.updateProgramVisibility = function() {
    if (self.show_all_programs)
    {
      return;
    }
    
    self.showVisiblePrograms();
    
    self.setSessionStorage(self.amount_of_visible_programs);
    
    self.showVisibleButtons();
  };
  
  
  self.showVisiblePrograms = function() {
    let programs_in_container = $(self.container).find('.program');
    $(programs_in_container).hide();
    for (let i = 0; i < self.amount_of_visible_programs && i < self.amount_of_loaded_programs; i++)
    {
      $(programs_in_container[i]).show();
    }
  };
  
  
  self.showVisibleButtons = function() {
    if (self.amount_of_visible_programs < self.total_amount_of_found_programs)
    {
      $(self.container).find('.button-show-more').show();
    }
    else
    {
      $(self.container).find('.button-show-more').hide();
    }
    
    
    if (self.amount_of_visible_programs > self.default_amount_of_visible_programs)
    {
      $(self.container).find('.button-show-less').show();
    }
    else
    {
      $(self.container).find('.button-show-less').hide();
    }
  };
  
  
  self.showMoreListener = function() {
    $(self.container + ' .button-show-more').click(function() {
      self.showMorePrograms();
    });
  };
  
  
  self.showMorePrograms = function() {
    
    if (self.total_amount_of_found_programs <= self.amount_of_visible_programs)
    {
      $(self.container).find('.button-show-more').hide();
      return;
    }
    
    if (self.amount_of_visible_programs + self.download_limit <= self.amount_of_loaded_programs)
    {
      self.amount_of_visible_programs += self.download_limit;
      self.updateProgramVisibility();
    }
    else if (self.total_amount_of_found_programs === self.amount_of_loaded_programs)
    {
      self.amount_of_visible_programs = self.total_amount_of_found_programs;
    }
    else
    {
      $(self.container).find('.button-show-more').hide();
      $(self.container).find('.button-show-ajax').show();
      self.loadMorePrograms();
    }
  };
  
  
  self.loadMorePrograms = function() {
    if (self.query !== "")
    {
      $.get(self.url, {
        q     : self.query,
        limit : self.download_limit,
        offset: self.amount_of_loaded_programs
      }, function(data) {
        if ((data.CatrobatProjects === undefined || data.CatrobatProjects.length === 0))
        {
          $(self.container).find('.button-show-ajax').hide();
          return;
        }
        
        self.loadProgramsIntoContainer(data);
        self.showMorePrograms();
        
        $(self.container).find('.button-show-ajax').hide();
      });
      
    }
    else
    {
      $.get(self.url, {limit: self.download_limit, offset: self.amount_of_loaded_programs}, function(data) {
        if ((data.CatrobatProjects.length === 0 || data.CatrobatProjects === undefined))
        {
          $(self.container).find('.button-show-ajax').hide();
          return;
        }
        
        self.loadProgramsIntoContainer(data);
        self.showMorePrograms();
        
        $(self.container).find('.button-show-ajax').hide();
      });
    }
  };
  
  
  self.showLessListener = function() {
    $(self.container + ' .button-show-less').click(function() {
      self.showLessPrograms();
    });
  };
  
  
  self.showLessPrograms = function() {
    
    if (self.default_amount_of_visible_programs > self.amount_of_visible_programs)
    {
      $(self.container).find('.button-show-less').hide();
      return;
    }
    
    // hides visible programs in a way that all columns are filled for rows that are visible
    if (self.amount_of_visible_programs % self.default_amount_of_visible_programs === 0)
    {
      self.amount_of_visible_programs -= self.download_limit;
    }
    else
    {
      self.amount_of_visible_programs -= self.amount_of_visible_programs % self.default_amount_of_visible_programs;
    }
    self.updateProgramVisibility();
  };
};



