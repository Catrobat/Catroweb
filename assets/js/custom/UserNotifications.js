/* eslint-env jquery */
/* global FetchNotifications */

// eslint-disable-next-line no-unused-vars
class UserNotifications {
  constructor (markAsReadUrl, markAllSeen, countNotificationsUrl, fetchNotificationsUrl, somethingWentWrongError,
    notificationsClearError, notificationsUnauthorizedError, allNotificationsCount,
    followNotificationCount, reactionNotificationCount, commentNotificationCount, remixNotificationCount,
    profilePath, programPath, imgAsset) {
    this.all = true
    this.follower = false
    this.comment = false
    this.reactions = false
    this.remixes = false
    this.markAsReadUrl = markAsReadUrl
    this.markAllSeen = markAllSeen
    this.countNotificationsUrl = countNotificationsUrl
    this.fetchNotificationsUrl = fetchNotificationsUrl
    this.somethingWentWrongError = somethingWentWrongError
    this.notificationsClearError = notificationsClearError
    this.notificationsUnauthorizedError = notificationsUnauthorizedError
    this.notificationsFetchCount = 20
    this.allNotificationsLoaded = document.getElementById('notifications').childElementCount
    this.followerNotificationsLoaded = document.getElementById('follow-notifications').childElementCount
    this.reactionNotificationsLoaded = document.getElementById('reaction-notifications').childElementCount
    this.commentNotificationsLoaded = document.getElementById('comment-notifications').childElementCount
    this.remixNotificationsLoaded = document.getElementById('remix-notifications').childElementCount
    this.empty = false
    this.fetchActive = false
    this.profilePath = profilePath
    this.programPath = programPath
    this.imgAsset = imgAsset

    this._initListeners()
  }

  _initListeners () {
    const self = this
    $(document).on('click', '#all-notif', function () {
      if (self.all === false) {
        self.resetChips()
        self.selectChip('all-notif', self.all)
        self.all = true
      }
    })

    $(document).on('click', '#follow-notif', function () {
      if (self.follower === false) {
        self.resetChips()
        self.selectChip('follow-notif', self.follower)
        self.follower = true
        if (self.followerNotificationsLoaded < self.notificationsFetchCount) {
          self.followerNotificationsLoaded = document.getElementById('follow-notifications').childElementCount
          self.fetchMoreNotifications(self.notificationsFetchCount, self.followerNotificationsLoaded,
            'follow', 'follow-notification-', $('#follow-notifications'))
        }
      }
    })

    $(document).on('click', '#comment-notif', function () {
      if (self.comment === false) {
        self.resetChips()
        self.selectChip('comment-notif', self.comment)
        self.comment = true
        if (self.commentNotificationsLoaded < self.notificationsFetchCount) {
          self.commentNotificationsLoaded = document.getElementById('comment-notifications').childElementCount
          self.fetchMoreNotifications(self.notificationsFetchCount, self.commentNotificationsLoaded,
            'comment', 'comment-notification-', $('#comment-notifications'))
        }
      }
    })

    $(document).on('click', '#reaction-notif', function () {
      if (self.reactions === false) {
        self.resetChips()
        self.selectChip('reaction-notif', self.reactions)
        self.reactions = true
        if (self.reactionNotificationsLoaded < self.notificationsFetchCount) {
          self.reactionNotificationsLoaded = document.getElementById('reaction-notifications').childElementCount
          self.fetchMoreNotifications(self.notificationsFetchCount, self.reactionNotificationsLoaded,
            'reaction', 'reaction-notification-', $('#reaction-notifications'))
        }
      }
    })
    $(document).on('click', '#remix-notif', function () {
      if (self.remixes === false) {
        self.resetChips()
        self.selectChip('remix-notif', self.remixes)
        self.remixes = true
        if (self.remixNotificationsLoaded < self.notificationsFetchCount) {
          self.remixNotificationsLoaded = document.getElementById('remix-notifications').childElementCount
          self.fetchMoreNotifications(self.notificationsFetchCount, self.remixNotificationsLoaded,
            'remix', 'remix-notification-', $('#remix-notifications'))
        }
      }
    })

    $(document).on('scroll', function () {
      const position = $(window).scrollTop()
      const bottom = $(document).height() - $(window).height()
      const pctVertical = position / bottom
      if (pctVertical >= 0.7) {
        if (self.all === true) {
          self.allNotificationsLoaded = document.getElementById('notifications').childElementCount
          self.fetchMoreNotifications(self.notificationsFetchCount, self.allNotificationsLoaded,
            'all', 'catro-notification-', $('#notifications'))
        } else if (self.follower === true) {
          self.followerNotificationsLoaded = document.getElementById('follow-notifications').childElementCount
          self.fetchMoreNotifications(self.notificationsFetchCount, self.followerNotificationsLoaded,
            'follow', 'follow-notification-', $('#follow-notifications'))
        } else if (self.comment === true) {
          self.commentNotificationsLoaded = document.getElementById('comment-notifications').childElementCount
          self.fetchMoreNotifications(self.notificationsFetchCount, self.commentNotificationsLoaded,
            'comment', 'comment-notification-', $('#comment-notifications'))
        } else if (self.reactions === true) {
          self.reactionNotificationsLoaded = document.getElementById('reaction-notifications').childElementCount
          self.fetchMoreNotifications(self.notificationsFetchCount, self.reactionNotificationsLoaded,
            'reaction', 'reaction-notification-', $('#reaction-notifications'))
        } else if (self.remixes === true) {
          self.remixNotificationsLoaded = document.getElementById('remix-notifications').childElementCount
          self.fetchMoreNotifications(self.notificationsFetchCount, self.remixNotificationsLoaded,
            'remix', 'remix-notification-', $('#remix-notifications'))
        }
      }
    })
  }

  fetchMoreNotifications (limit, loadedCount, type, idPrefix, $container) {
    const self = this
    if (this.empty === true || this.fetchActive === true) {
      return
    }
    this.fetchActive = true
    $.ajax({
      url: self.fetchNotificationsUrl + '/' + limit + '/' + loadedCount + '/' + type,
      type: 'get',
      success: function (data) {
        data['fetched-notifications'].forEach(function (fetched) {
          self.generateNotificationBody(fetched, idPrefix, $container)
        })
        self.updateNoNotificationsPlaceholder(type, data['fetched-notifications'].length)
        self.fetchActive = false
      },
      error: function (xhr) {
        self.handleError(xhr)
      }
    })
  }

  updateNoNotificationsPlaceholder (type, fetchedAmount) {
    if (fetchedAmount > 0) {
      if (type === 'all') {
        document.getElementById('no-notif-all').style.display = 'none'
      }
      if (type === 'follow') {
        document.getElementById('no-notif-follow').style.display = 'none'
      }
      if (type === 'comment') {
        document.getElementById('no-notif-comment').style.display = 'none'
      }
      if (type === 'reaction') {
        document.getElementById('no-notif-reaction').style.display = 'none'
      }
      if (type === 'remix') {
        document.getElementById('no-notif-remix').style.display = 'none'
      }
    }
  }

  generateNotificationBody (fetched, idPrefix, $container) {
    const self = this
    const imgLeft = self.generateNotificationImage(fetched)
    const msg = self.generateNotificationMessage(fetched)
    const notificationId = idPrefix + fetched.id
    let notificationDot = ''
    if (!fetched.seen) {
      notificationDot = '<div class="my-auto mark-as-read">' +
        '<span class="dot">' + '</span>' + '</div>'
    }
    const notificationBody = '<div onclick="' + 'notification.handleNotificationInteract(' + "'" + fetched.id +
      "'" + ',' + "'" + fetched.type + "'" + ',' + "'" + fetched.seen +
      "'" + ',' + "'" + fetched.program + "'" + ')' +
      '" id="' + notificationId + '" class="row my-3 no-gutters ripple notif">' +
      '<div class="col-2 col-sm-1 my-auto">' + imgLeft + '</div>' +
      '<div class="col-8 col-sm-8 pl-3 my-auto">' + msg + '</div>' + notificationDot + '</div>'
    $container.append(notificationBody)
  }

  generateNotificationImage (fetched) {
    const self = this
    if (fetched.type !== 'other') {
      let imgLeft = self.imgAsset
      if (fetched.avatar) {
        imgLeft = fetched.avatar
      }
      imgLeft = '<a href="' + self.profilePath + '/' + fetched.from + '">' +
        '<img class="img-fluid notification-avatar-round" src="' + imgLeft + '" alt="">' + '</a>'
      return imgLeft
    } else {
      let imgLeft = '<span class="material-icons broadcast-icon">' + 'notifications_active' + '</span>'
      if (fetched.prize) {
        imgLeft = '<span class="material-icons broadcast-icon">' + 'cake' + '</span>'
      }
      return imgLeft
    }
  }

  generateNotificationMessage (fetched) {
    const self = this
    let msg = fetched.message
    if (msg.includes('%user_link%')) {
      msg = msg.replace('%user_link%', '<a href="' + self.profilePath + '/' + fetched.from + '">' +
        fetched.from_name + '</a>')
    }
    if (msg.includes('%program_link%')) {
      msg = msg.replace('%program_link%', '<a href="' + self.programPath + '/' + fetched.program +
        '">' + fetched.program_name + '</a>')
    }
    if (msg.includes('%remixed_program_link%')) {
      msg = msg.replace('%remixed_program_link%', '<a href="' + self.programPath + '/' +
        fetched.remixed_program + '">' + fetched.remixed_program_name + '</a>')
    }
    if (fetched.prize) {
      msg = '<div class="message">' + fetched.message + '</div>' +
        '<div class="prize">' + fetched.prize + '</div>'
    }
    return msg
  }

  resetChips () {
    const self = this
    self.remixes = false
    self.all = false
    self.follower = false
    self.comment = false
    self.reactions = false
    self.resetColor()
  }

  resetColor () {
    document.getElementById('all-notif').classList.replace('chip-selected', 'chip-default')
    document.getElementById('follow-notif').classList.replace('chip-selected', 'chip-default')
    document.getElementById('comment-notif').classList.replace('chip-selected', 'chip-default')
    document.getElementById('reaction-notif').classList.replace('chip-selected', 'chip-default')
    document.getElementById('remix-notif').classList.replace('chip-selected', 'chip-default')
  }

  selectChip (elementId, notificationType) {
    notificationType = true
    document.getElementById(elementId).classList.replace('chip-default', 'chip-selected')
  }

  handleNotificationInteract (notificationId, type, seen, id) {
    const self = this
    if (!seen) {
      self.markAsRead(notificationId)
    }
    self.redirectUser(type, id)
  }

  markAsRead (notificationId) {
    const self = this
    $.ajax({
      url: self.markAsReadUrl + '/' + notificationId,
      type: 'get',
      success: function () {
        self.updateBadgeNumber()
        self.reloadResources(notificationId)
      },
      error: function (xhr) {
        self.handleError(xhr)
      }
    })
  }

  redirectUser (type, id) {
    if (type === 'follow') {
      window.location.assign('follower')
    }
    if (type === 'comment' || type === 'reaction' || type === 'remix' || type === 'program') {
      window.location.assign('project/' + id)
    }
  }

  markAllRead () {
    const self = this
    $.ajax({
      url: self.markAllSeen,
      type: 'get',
      success: function () {
        self.updateBadgeNumber()
      },
      error: function (xhr) {
        self.handleError(xhr)
      }
    })
  }

  updateBadgeNumber () {
    const self = this
    const fetchNotifications = new FetchNotifications(self.countNotificationsUrl, 99, 10000)
    fetchNotifications.run('markAsRead')
  }

  reloadResources (id) {
    $('#follow-notification-' + id).load(window.location.href + ' #follow-notification-' + id + '>*')
    $('#comment-notification-' + id).load(window.location.href + ' #comment-notification-' + id + '>*')
    $('#reaction-notification-' + id).load(window.location.href + ' #reaction-notification-' + id + '>*')
    $('#remix-notification-' + id).load(window.location.href + ' #remix-notification-' + id + '>*')
    $('#catro-notification-' + id).load(window.location.href + ' #catro-notification-' + id + '>*')
  }

  handleError (xhr) {
    const self = this
    if (xhr.status === 401) {
      // eslint-disable-next-line no-undef
      showSnackbar('#share-snackbar', self.notificationsUnauthorizedError)
      return
    }
    if (xhr.status === 404) {
      // eslint-disable-next-line no-undef
      showSnackbar('#share-snackbar', self.notificationsClearError)
    }
  }
}
