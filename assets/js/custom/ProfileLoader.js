let ProfileLoader = function (profile_id, url, profile_url, container, default_profile) {
  let self = this
  self.id = profile_id
  self.loaded = 0
  self.url = url
  self.profileUrl = profile_url
  self.pageSize = 10
  self.page = 0
  self.container = container
  self.defaultProfile = default_profile
  self.maximum = 0
  
  self.init = function () {
    $(self.container).append('<div class="profile-container row text-center"></div>')
    $(self.container).append(
      '<div class="row">' +
      '<div class="col-xs-1 col-xs-offset-5"><a role="button" class="button-load-profiles img-load-more pull-right"></a></div>' +
      '<div class="col-xs-1"><a role="button" class="button-remove-profiles img-load-less pull-left"></a></div>' +
      '</div>'
    )
    self.profileContainer = $(self.container).find('.profile-container')
    self.loadMoreButton = $(self.container).find('.button-load-profiles')
    self.removeButton = $(self.container).find('.button-remove-profiles')
    self.loadMoreButton.click(self.showMoreProfiles)
    self.removeButton.click(self.showLessProfiles)
    self.showMoreProfiles()
  }
  
  self.showMoreProfiles = function () {
    $.post(self.url, {id: self.id, pageSize: self.pageSize, page: self.page}).done(function (data) {
      self.maximum = data['maximum']
      if (data['profiles'] instanceof Array)
      {
        data['profiles'].forEach(function (profile) {
            self.addProfile(profile)
          }
        )
        self.page += 1
        self.checkForHiddenButton()
      }
    })
  }
  
  self.addProfile = function (profile) {
    let profilePic = self.defaultProfile
    if (profile['avatar'] != null)
    {
      profilePic = profile['avatar']
    }
    self.profileContainer.append(
      '<div class="follow col-xs-4 col-sm-2 col-md-1 col-lg-1" id="profile-' + profile['id'] + '">' +
      '<a class="rec-profile" href="' + self.profileUrl + '/' + profile['id'] + '">' +
      '<img src="' + profilePic + '">' +
      '<div class="profile-name"><b>' + profile['username'] + '</b></div>' +
      '</a>' +
      '</div>'
    )
    self.loaded++
  }
  
  self.showLessProfiles = function () {
    let removeCount = (self.loaded % self.pageSize) === 0 ? self.pageSize : self.loaded % self.pageSize
    self.profileContainer.find('.follow:nth-last-child(-n+' + removeCount + ')').remove()
    self.loaded -= removeCount
    self.page--
    self.checkForHiddenButton()
  }
  
  self.checkForHiddenButton = function () {
    if (self.loaded <= self.pageSize)
    {
      self.removeButton.addClass('hidden')
    }
    else
    {
      self.removeButton.removeClass('hidden')
    }
    
    if (self.loaded % self.pageSize || self.loaded === 0 || self.maximum === self.loaded)
    {
      self.loadMoreButton.addClass('hidden')
      if (self.loaded === 0)
      {
        self.addNothingFoundMessage()
      }
    }
    else
    {
      self.loadMoreButton.removeClass('hidden')
    }
  }
  
  self.addNothingFoundMessage = function () {
    let url = Routing.generate('translate_word', {
      'word'  : 'profileLoader.noProfiles',
      'domain': 'catroweb'
    })
    $.get(url, function (data) {
      $(self.container).append('<div class="no-programs text-center">' + data + '</div>')
    })

  }
  
}