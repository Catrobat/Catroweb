var ProgramLoader = function (container, url, column_max, recommended_by_program_id, recommended_by_page_id) {
  var self = this;
  self.container = container;
  self.url = url;
  self.recommended_by_program_id = (typeof recommended_by_program_id === "undefined") ? null : recommended_by_program_id;
  self.recommended_by_page_id = (typeof recommended_by_page_id === "undefined") ? null : recommended_by_page_id;
  self.default_rows = 2;
  self.columns_min = 3; // before changing these values, have a look at '.programs{.program{width:.%}}' in 'brain.less' first
  self.columns_max = (typeof column_max === "undefined") ? 9 : column_max; // before changing these values, have a look at '.programs{.program{width:.%}}' in 'brain.less' first
  self.download_limit = self.default_rows * self.columns_max;
  self.initial_download_limit = self.download_limit;
  self.prev_visible = 0; // set if dynamically loaded
  self.prev_step = 0; // set when button-load-more clicked
  self.loaded = 0;
  self.visible = 0;
  self.visible_steps = 0;
  self.showAllPrograms = false;
  self.windowWidth = $(window).width();
  self.query = "";
  self.searchTerms = Object(null);
  self.programsFound = 0;

  self.init = function() {
    self.setParamsWithSessionStorage(); // sets self.prev_visible and self.initial_download_liit
    $.get(self.url, { limit: self.initial_download_limit, offset: self.loaded}, function(data) {
      if(data.CatrobatProjects.length == 0 || data.CatrobatProjects == undefined) {
        $(self.container).find('.programs').append('<div class="no-programs">There are currently no programs.</div>'); //todo: translate
        return;
      }
      self.setup(data);
    });
  };

  self.initRecsys = function() {

    if(!($(self.container).length > 0))
      return;

    self.setParamsWithSessionStorage(); // sets self.prev_visible and self.initial_download_limit
    $.get(self.url, { program_id: self.recommended_by_program_id,}, function(data) {
      if(data.CatrobatProjects.length == 0 || data.CatrobatProjects == undefined) {
        $(self.container).hide();
        // $(self.container).find('.programs').append('<div class="no-programs">There are currently no programs.</div>');
        return;
      }
      self.setup(data);
    });
  };

  self.initSpecificRecsys = function() {
    $(self.container).hide();
    self.init();
  };

  self.initProfile = function(user_id) {
    self.showAllPrograms = true;
    $.get(self.url, { limit: self.download_limit, offset: self.loaded, user_id: user_id }, function(data) {
      if(data.CatrobatProjects.length == 0 || data.CatrobatProjects == undefined) {
        $(self.container).find('.programs').append('<div class="no-programs">There are currently no programs.</div>'); //todo: translate
        return;
      }
      self.setup(data);
    });
  };

  self.initSearch = function(query) {
    self.query = query;
    $.get(self.url, { q: query, limit: self.download_limit*2, offset: self.loaded }, function(data) {
      var searchResultsText = $('#search-results-text');
      if(data.CatrobatProjects.length == 0 || data.CatrobatProjects == undefined) {
        searchResultsText.addClass('no-results');
        searchResultsText.find('span').text(0);
        return;
      }
      console.log(data);
      searchResultsText.find('span').text(data.CatrobatInformation.TotalProjects);
      self.programsFound = data.CatrobatInformation.TotalProjects;
      self.setup(data);
      self.showMorePrograms();
      self.searchPageLoadDone = true; // fix for search.feature: 'I press enter "#searchbar"'
    });
  };

  self.repeatableSearch = function(search_term) {
    self.query = search_term;
    var current_loaded = 0;
    if (search_term in self.searchTerms)
    {
      current_loaded = self.searchTerms[search_term];
    }
    else
    {
      self.searchTerms[search_term] = 0;
    }
    $.get(self.url, { q: search_term, limit: self.download_limit*2, offset: current_loaded }, function(data) {
        var searchResultsText = $('#search-results-text');
        if(data.CatrobatProjects.length == 0 || data.CatrobatProjects == undefined) {
          if (self.programsFound == 0)
          {
              searchResultsText.addClass('no-results');
              searchResultsText.find('span').text(0);
              return;
          }
          return;
        }
        self.programsFound += data.CatrobatInformation.TotalProjects;
        self.searchTerms[search_term] += data.CatrobatInformation.TotalProjects;
        searchResultsText.find('span').text(self.programsFound);
        self.setup(data);
        self.showMorePrograms();
        self.searchPageLoadDone = true; // fix for search.feature: 'I press enter "#searchbar"'
    });
  };

  self.setup = function(data) {
    if(!self.showAllPrograms) {
      $(self.container).append('' +
        '<div class="button-show-placeholder">' +
          '<div class="button-show-more img-load-more"></div>' +
          '<div class="button-show-ajax img-load-ajax"></div>' +
          '<div class="button-show-less img-load-less"></div>' +
        '</div>');

      if (self.initial_download_limit > self.download_limit)
        $(self.container).find('.button-show-less').show();

    }
    self.loadProgramsIntoContainer(data);
    self.showMoreListener();
    self.showLessListener();
    self.setDefaultVisibility();
    $(window).resize(function() {
      if(self.windowWidth == $(window).width())
        return;
      self.resetParamsInSessionStorage();
      self.setDefaultVisibility();
      self.windowWidth = $(window).width();
    });
  };

  self.loadProgramsIntoContainer = function(data) {
    var programs = data.CatrobatProjects;
    for(var i=0; i < programs.length; i++) {
      var div = null;
      var additionalLinkCssClass = null;

      // Extend this for new containers...
      switch (self.container) {
        case '#newest':
        case '#search-results':
        case "#random":
          div = '<div><div class="img-time-small"></div>' + programs[i].UploadedString + '</div>';
          break;
        case '#myprofile-programs':
        case '#user-programs':
          div = '<div>' + programs[i].UploadedString + '</div>';
          break;
        case '#mostDownloaded':
          div = '<div><div class="img-download-small"></div>' + programs[i].Downloads + '</div>';
          break;
        case '#mostViewed':
          div = '<div><div class="img-view-small"></div>' + programs[i].Views + '</div>';
          break;
        case '#recommendations':
          div = '<div><div class="img-view-small"></div>' + programs[i].Views + '</div>';
          break;
        case '#recommended':
          div = '<div><div class="img-view-small"></div>' + programs[i].Views + '</div>';
          additionalLinkCssClass = "homepage-recommended-programs";
          break;
        case '#specific-programs-recommendations':
            div = '<div><div class="img-download-small"></div>' + programs[i].Downloads + '</div>';
            break;
        default:
          if($(self.container).hasClass('starterDownloads'))
            div = '<div><div class="img-download-small"></div>' + programs[i].Downloads + '</div>';
          else
            div = '<div>' + programs[i].Author + '</div>';
      }

      if (self.container == "#recommendations") {
        var program_link = data.CatrobatInformation.BaseUrl + programs[i].ProjectUrl + "?rec_from=" + self.recommended_by_program_id;
      } else if ((self.container == "#recommended") || (self.container == "#specific-programs-recommendations")) {
        var program_link = data.CatrobatInformation.BaseUrl + programs[i].ProjectUrl + "?rec_by_page_id=" + self.recommended_by_page_id;
        program_link += (self.recommended_by_program_id != null) ? "&rec_by_program_id=" + self.recommended_by_program_id : "";
        program_link += "&rec_user_specific=" + (("isUserSpecificRecommendation" in data) && data.isUserSpecificRecommendation ? 1 : 0);
      } else {
        var program_link = data.CatrobatInformation.BaseUrl + programs[i].ProjectUrl;
      }

      var stored_visits = sessionStorage.getItem("visits");
      var linkCssClasses = "rec-programs" + ((additionalLinkCssClass != null) ? (" " + additionalLinkCssClass) : "");
      if(!stored_visits){
        var program = $(
            '<div class="program" id="program-'+ programs[i].ProjectId +'">'+
            '<a class="' + linkCssClasses + '" href = \''+ program_link + '\'>'+
            '<div><img src="' + data.CatrobatInformation.BaseUrl + programs[i].ScreenshotSmall +'"></div>'+
            '<div class="program-name"><b>'+ programs[i].ProjectName +'</b></div>'+
            div +
            '</a>'+
            '</div>'
        );
      }
      else{
        var parsed_visits = JSON.parse(stored_visits);
        var program_id = programs[i].ProjectId.toString();
        if($.inArray(program_id, parsed_visits)>=0) {
          var program = $(
            '<div class="program visited-program" id="program-'+ programs[i].ProjectId +'">'+
              '<a class="' + linkCssClasses + '" href = \''+ program_link + '\' >'+
                '<div><img src="' + data.CatrobatInformation.BaseUrl + programs[i].ScreenshotSmall +'"></div>'+
                '<div class="program-name"><b>'+ programs[i].ProjectName +'</b></div>'+
                div +
              '</a>'+
            '</div>'
          );
        }
        else{
          var program = $(
              '<div class="program" id="program-'+ programs[i].ProjectId +'">'+
              '<a class="' + linkCssClasses + '" href = \''+ program_link + '\'>'+
              '<div><img src="' + data.CatrobatInformation.BaseUrl + programs[i].ScreenshotSmall +'"></div>'+
              '<div class="program-name"><b>'+ programs[i].ProjectName +'</b></div>'+
              div +
              '</a>'+
              '</div>'
          );
        }
      }

      $(self.container).find('.programs').append(program);
      $(self.container).show();

      if(self.container == '#myprofile-programs') {
        $(program).prepend('<div id="delete-' + programs[i].ProjectId + '" class="img-delete" onclick="profile.deleteProgram(' + programs[i].ProjectId + ')"></div>');
        $(program).prepend('<div id="visibility-' + programs[i].ProjectId + '" class="' + (programs[i].Private ? 'img-visibility-hidden' : 'img-visibility-visible') + '" onclick="profile.toggleVisibility(' + programs[i].ProjectId + ')"></div>');
      }
    }
    self.loaded += programs.length;
  };

  self.showMorePrograms = function() {
    var programs_in_container = $(self.container).find('.program');

    $(programs_in_container).hide();
    for(var i = 0; i < self.visible + self.visible_steps; i++) {
      if(programs_in_container[i] == undefined) {
        $(self.container).find('.button-show-more').hide();
        break;
      }
      $(programs_in_container[i]).show();
    }

    if(self.loaded < self.visible + self.visible_steps)
      $(self.container).find('.button-show-more').hide();
    else
      $(self.container).find('.button-show-more').show();

    self.prev_step = i - self.visible;
    self.visible = i;
    self.setSessionStorage(self.visible);

    // Show button-show-less if more than initially visible programs are visible
    if (self.visible <= self.visible_steps)
      $(self.container).find('.button-show-less').hide();
    else
      $(self.container).find('.button-show-less').show();
  };

  self.showLessPrograms = function() {
    var programs_in_container = $(self.container).find('.program');

    $(programs_in_container).hide();
    for (var i = 0; i < self.visible - self.prev_step; i++) {
      $(programs_in_container[i]).show();
    }

    $(self.container).find('.button-show-more').show();

    self.prev_step = self.visible_steps; // reset prev_step in case button-show-less is clicked more than once in a row
    self.visible = i;
    self.setSessionStorage(self.visible);

    // Show button-show-less if more than initially visible programs are visible
    if (self.visible <= self.visible_steps)
      $(self.container).find('.button-show-less').hide();
    else
      $(self.container).find('.button-show-less').show();
  };

  self.resetParamsInSessionStorage = function() {
    self.initial_download_limit = self.default_rows * self.columns_max;
    self.prev_visible = 0;
    self.setSessionStorage(0);
  };

  self.setParamsWithSessionStorage = function() {
    var stored_visible = sessionStorage.getItem(self.container);
    if (stored_visible) {
      self.prev_visible = stored_visible;
      if (stored_visible > 0)
        self.initial_download_limit = stored_visible;
      else
        self.initial_download_limit = self.download_limit;
    }
  };

  self.setSessionStorage = function(value) {
    sessionStorage.setItem(self.container, value);
  };

  self.setDefaultVisibility = function() {
    if(self.showAllPrograms)
      return;

    var programs_in_row = parseInt($(window).width() / $('.program').width());
    if(programs_in_row < self.columns_min) programs_in_row = self.columns_min;
    if(programs_in_row > self.columns_max) programs_in_row = self.columns_max;

    var programs_in_container = $(self.container).find('.program');
    var show_programs_count = 0;

    if(self.prev_visible == 0)
      show_programs_count = self.default_rows * programs_in_row;
    else
      show_programs_count = self.prev_visible;

    $(programs_in_container).hide();

    for(var i=0; i < show_programs_count; i++) {
      $(programs_in_container[i]).show();
    }

    self.visible = i;
    self.visible_steps = self.default_rows * programs_in_row;

    if(self.loaded < self.visible)
      $(self.container).find('.button-show-more').hide();
    else
      $(self.container).find('.button-show-more').show();

    // Show button-show-less if more than initially visible programs are visible
    if (self.visible <= self.visible_steps)
      $(self.container).find('.button-show-less').hide();
    else
      $(self.container).find('.button-show-less').show();
  };

  self.showMoreListener = function() {
    $(self.container + ' .button-show-more').click(function() {

      if(self.visible + self.visible_steps <= self.loaded)
        self.showMorePrograms();
      else {
        $(self.container).find('.button-show-more').hide();
        $(self.container).find('.button-show-ajax').show();
        // on loadUserPrograms... set user_id as parameter

        if (self.query != "")
        {
          $.get(self.url, { q: self.query, limit: self.download_limit, offset: self.loaded }, function(data) {
            if((data.CatrobatProjects.length == 0 || data.CatrobatProjects == undefined) && self.loaded <= self.visible) {
              $(self.container).find('.button-show-ajax').hide();
              return;
            }

            self.loadProgramsIntoContainer(data);
            self.showMorePrograms();

            $(self.container).find('.button-show-ajax').hide();
          });

        }
        else {
          $.get(self.url, { limit: self.download_limit, offset: self.loaded }, function(data) {
            if((data.CatrobatProjects.length == 0 || data.CatrobatProjects == undefined) && self.loaded <= self.visible) {
              $(self.container).find('.button-show-ajax').hide();
              return;
            }

            self.loadProgramsIntoContainer(data);
            self.showMorePrograms();

            $(self.container).find('.button-show-ajax').hide();
          });
        }
      }

    });
  };

  self.showLessListener = function() {
    $(self.container + ' .button-show-less').on('click', function() {

      self.showLessPrograms();

    });
  };
};



