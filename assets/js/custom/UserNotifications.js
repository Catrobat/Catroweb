/* eslint-env jquery */
/* global FetchNotifications */

// eslint-disable-next-line no-unused-vars
class UserNotifications {
  constructor (markAsReadUrl, markAllSeen, countNotificationsUrl, somethingWentWrongError,
    notificationsClearError, notificationsUnauthorizedError) {
    this.all = true
    this.follower = false
    this.comment = false
    this.reactions = false
    this.remixes = false
    this.markAsReadUrl = markAsReadUrl
    this.markAllSeen = markAllSeen
    this.countNotificationsUrl = countNotificationsUrl
    this.somethingWentWrongError = somethingWentWrongError
    this.notificationsClearError = notificationsClearError
    this.notificationsUnauthorizedError = notificationsUnauthorizedError

    this._initListeners()
  }

  _initListeners () {
    const self = this
    $(document).on('click', '#all-notif', function () {
      if (self.all === false) {
        self.resetChips()
        self.selectChip('all-notif', self.all)
      }
    })

    $(document).on('click', '#follow-notif', function () {
      if (self.follower === false) {
        self.resetChips()
        self.selectChip('follow-notif', self.follower)
      }
    })

    $(document).on('click', '#comment-notif', function () {
      if (self.comment === false) {
        self.resetChips()
        self.selectChip('comment-notif', self.comment)
      }
    })

    $(document).on('click', '#reaction-notif', function () {
      if (self.reactions === false) {
        self.resetChips()
        self.selectChip('reaction-notif', self.reactions)
      }
    })
    $(document).on('click', '#remix-notif', function () {
      if (self.remixes === false) {
        self.resetChips()
        self.selectChip('remix-notif', self.remixes)
      }
    })
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
