import $ from 'jquery'
import { showSnackbar } from './components/snackbar'

import { MDCChipSet } from '@material/chips'
import { ApiFetch } from './api/ApiHelper'
const chipset = new MDCChipSet(document.querySelector('.mdc-chip-set'))
const tabPaneElements = document.querySelectorAll('.tab-pane')

chipset.listen('MDCChip:interaction', function (event) {
  document.querySelector('.show.active').classList.remove('show', 'active')
  tabPaneElements[event.detail.index].classList.add('show', 'active')
})

require('../styles/notifications_overview.scss')

$(() => {
  const $notifications = $('.js-notifications')
  const userNotifications = new UserNotifications(
    $notifications.data('base-url') + '/api/notifications/read',
    $notifications.data('fetch-url'),
    $notifications.data('something-went-wrong-error'),
    $notifications.data('notifications-clear-error'),
    $notifications.data('notifications-unauthorized-error'),
    $notifications.data('all-notifications-count'),
    $notifications.data('follow-notification-count'),
    $notifications.data('reaction-notification-count'),
    $notifications.data('comment-notification-count'),
    $notifications.data('remix-notification-count'),
    $notifications.data('profile-path'),
    $notifications.data('project-path'),
    $notifications.data('img-asset'),
  )

  userNotifications.markAllRead()

  $('.js-notification-interaction').on('click', function () {
    userNotifications.redirectUser(
      $(this).attr('data-notification-instance'),
      $(this).attr('data-notification-redirect'),
    )
  })
})

// eslint-disable-next-line no-unused-vars
class UserNotifications {
  constructor(
    markAllSeen,
    fetchNotificationsUrl,
    somethingWentWrongError,
    notificationsClearError,
    notificationsUnauthorizedError,
    allNotificationsCount,
    followNotificationCount,
    reactionNotificationCount,
    commentNotificationCount,
    remixNotificationCount,
    profilePath,
    projectPath,
    imgAsset,
  ) {
    this.all = true
    this.follower = false
    this.comment = false
    this.reactions = false
    this.remixes = false
    this.markAllSeen = markAllSeen
    this.fetchNotificationsUrl = fetchNotificationsUrl
    this.somethingWentWrongError = somethingWentWrongError
    this.notificationsClearError = notificationsClearError
    this.notificationsUnauthorizedError = notificationsUnauthorizedError
    this.notificationsFetchCount = 20
    this.allNotificationsLoaded =
      document.getElementById('notifications').childElementCount
    this.followerNotificationsLoaded = document.getElementById(
      'follow-notifications',
    ).childElementCount
    this.reactionNotificationsLoaded = document.getElementById(
      'reaction-notifications',
    ).childElementCount
    this.commentNotificationsLoaded = document.getElementById(
      'comment-notifications',
    ).childElementCount
    this.remixNotificationsLoaded = document.getElementById(
      'remix-notifications',
    ).childElementCount
    this.empty = false
    this.fetchActive = false
    this.profilePath = profilePath
    this.projectPath = projectPath
    this.imgAsset = imgAsset

    this._initListeners()
  }

  _initListeners() {
    const self = this
    $(document).on('click', '#all-notif', function () {
      if (self.all === false) {
        self.resetChips()
        self.selectChip('all-notif', 'notifications')
        self.all = true
      }
    })

    $(document).on('click', '#follow-notif', function () {
      if (self.follower === false) {
        self.resetChips()
        self.selectChip('follow-notif', 'follow-notifications')
        self.follower = true
        if (self.followerNotificationsLoaded < self.notificationsFetchCount) {
          self.followerNotificationsLoaded = document.getElementById(
            'follow-notifications',
          ).childElementCount
          self.fetchMoreNotifications(
            self.notificationsFetchCount,
            self.followerNotificationsLoaded,
            'follow',
            'follow-notification-',
            $('#follow-notifications'),
          )
        }
      }
    })

    $(document).on('click', '#comment-notif', function () {
      if (self.comment === false) {
        self.resetChips()
        self.selectChip('comment-notif', 'comment-notifications')
        self.comment = true
        if (self.commentNotificationsLoaded < self.notificationsFetchCount) {
          self.commentNotificationsLoaded = document.getElementById(
            'comment-notifications',
          ).childElementCount
          self.fetchMoreNotifications(
            self.notificationsFetchCount,
            self.commentNotificationsLoaded,
            'comment',
            'comment-notification-',
            $('#comment-notifications'),
          )
        }
      }
    })

    $(document).on('click', '#reaction-notif', function () {
      if (self.reactions === false) {
        self.resetChips()
        self.selectChip('reaction-notif', 'reaction-notifications')
        self.reactions = true
        if (self.reactionNotificationsLoaded < self.notificationsFetchCount) {
          self.reactionNotificationsLoaded = document.getElementById(
            'reaction-notifications',
          ).childElementCount
          self.fetchMoreNotifications(
            self.notificationsFetchCount,
            self.reactionNotificationsLoaded,
            'reaction',
            'reaction-notification-',
            $('#reaction-notifications'),
          )
        }
      }
    })

    $(document).on('click', '#remix-notif', function () {
      if (self.remixes === false) {
        self.resetChips()
        self.selectChip('remix-notif', 'remix-notifications')
        self.remixes = true
        if (self.remixNotificationsLoaded < self.notificationsFetchCount) {
          self.remixNotificationsLoaded = document.getElementById(
            'remix-notifications',
          ).childElementCount
          self.fetchMoreNotifications(
            self.notificationsFetchCount,
            self.remixNotificationsLoaded,
            'remix',
            'remix-notification-',
            $('#remix-notifications'),
          )
        }
      }
    })

    $(document).on('scroll', function () {
      const position = $(window).scrollTop()
      const bottom = $(document).height() - $(window).height()
      const pctVertical = position / bottom
      if (pctVertical >= 0.7) {
        if (self.all === true) {
          self.allNotificationsLoaded =
            document.getElementById('notifications').childElementCount
          self.fetchMoreNotifications(
            self.notificationsFetchCount,
            self.allNotificationsLoaded,
            'all',
            'catro-notification-',
            $('#notifications'),
          )
        } else if (self.follower === true) {
          self.followerNotificationsLoaded = document.getElementById(
            'follow-notifications',
          ).childElementCount
          self.fetchMoreNotifications(
            self.notificationsFetchCount,
            self.followerNotificationsLoaded,
            'follow',
            'follow-notification-',
            $('#follow-notifications'),
          )
        } else if (self.comment === true) {
          self.commentNotificationsLoaded = document.getElementById(
            'comment-notifications',
          ).childElementCount
          self.fetchMoreNotifications(
            self.notificationsFetchCount,
            self.commentNotificationsLoaded,
            'comment',
            'comment-notification-',
            $('#comment-notifications'),
          )
        } else if (self.reactions === true) {
          self.reactionNotificationsLoaded = document.getElementById(
            'reaction-notifications',
          ).childElementCount
          self.fetchMoreNotifications(
            self.notificationsFetchCount,
            self.reactionNotificationsLoaded,
            'reaction',
            'reaction-notification-',
            $('#reaction-notifications'),
          )
        } else if (self.remixes === true) {
          self.remixNotificationsLoaded = document.getElementById(
            'remix-notifications',
          ).childElementCount
          self.fetchMoreNotifications(
            self.notificationsFetchCount,
            self.remixNotificationsLoaded,
            'remix',
            'remix-notification-',
            $('#remix-notifications'),
          )
        }
      }
    })
  }

  fetchMoreNotifications(limit, loadedCount, type, idPrefix, $container) {
    const self = this
    if (this.empty === true || this.fetchActive === true) {
      return
    }
    this.fetchActive = true
    $.ajax({
      url:
        self.fetchNotificationsUrl +
        '/' +
        limit +
        '/' +
        loadedCount +
        '/' +
        type,
      type: 'get',
      success: function (data) {
        data['fetched-notifications'].forEach(function (fetched) {
          self.generateNotificationBody(fetched, idPrefix, $container)
        })
        self.updateNoNotificationsPlaceholder(
          type,
          data['fetched-notifications'].length,
        )
        self.fetchActive = false
      },
      error: function (xhr) {
        self.handleError(xhr)
      },
    })
  }

  updateNoNotificationsPlaceholder(type, fetchedAmount) {
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

  generateNotificationBody(fetched, idPrefix, $container) {
    const self = this
    const imgLeft = self.generateNotificationImage(fetched)
    const msg = self.generateNotificationMessage(fetched)
    const notificationId = idPrefix + fetched.id
    let notificationDot = ''
    if (!fetched.seen) {
      notificationDot =
        '<div class="col-2 my-auto mark-as-read">' +
        '<span class="dot">' +
        '</span>' +
        '</div>'
    }
    const notificationBody =
      '<div id="' +
      notificationId +
      '" class="row my-3 no-gutters ripple notif">' +
      '<div class="col-2 my-auto">' +
      imgLeft +
      '</div>' +
      '<div class="col-8 ps-3 my-auto">' +
      msg +
      '</div>' +
      notificationDot +
      '</div>'
    $container.append(notificationBody)
  }

  generateNotificationImage(fetched) {
    const self = this
    if (fetched.type !== 'other') {
      let imgLeft = self.imgAsset
      if (fetched.avatar) {
        imgLeft = fetched.avatar
      }
      imgLeft =
        '<a href="' +
        self.profilePath +
        '/' +
        fetched.from +
        '">' +
        '<img class="img-fluid notification-avatar-round" src="' +
        imgLeft +
        '" alt="">' +
        '</a>'
      return imgLeft
    } else {
      let imgLeft =
        '<span class="material-icons broadcast-icon">' +
        'notifications_active' +
        '</span>'
      if (fetched.prize) {
        imgLeft =
          '<span class="material-icons broadcast-icon">' + 'cake' + '</span>'
      }
      return imgLeft
    }
  }

  generateNotificationMessage(fetched) {
    const self = this
    let msg = fetched.message
    if (msg.includes('%user_link%')) {
      msg = msg.replace(
        '%user_link%',
        '<a href="' +
          self.profilePath +
          '/' +
          fetched.from +
          '">' +
          fetched.from_name +
          '</a>',
      )
    }
    if (msg.includes('%program_link%')) {
      msg = msg.replace(
        '%program_link%',
        '<a href="' +
          self.projectPath +
          '/' +
          fetched.program +
          '">' +
          fetched.program_name +
          '</a>',
      )
    }
    if (msg.includes('%remix_program_link%')) {
      msg = msg.replace(
        '%remix_program_link%',
        '<a href="' +
          self.projectPath +
          '/' +
          fetched.remixed_program +
          '">' +
          fetched.remixed_program_name +
          '</a>',
      )
    }
    if (fetched.prize) {
      msg =
        '<div class="message">' +
        fetched.message +
        '</div>' +
        '<div class="prize">' +
        fetched.prize +
        '</div>'
    }
    return msg
  }

  resetChips() {
    const self = this
    self.remixes = false
    self.all = false
    self.follower = false
    self.comment = false
    self.reactions = false
    self.resetColor()
  }

  resetColor() {
    document
      .getElementById('all-notif')
      .classList.replace('chip-selected', 'chip-default')
    document
      .getElementById('follow-notif')
      .classList.replace('chip-selected', 'chip-default')
    document
      .getElementById('comment-notif')
      .classList.replace('chip-selected', 'chip-default')
    document
      .getElementById('reaction-notif')
      .classList.replace('chip-selected', 'chip-default')
    document
      .getElementById('remix-notif')
      .classList.replace('chip-selected', 'chip-default')
    document.getElementById('notifications').classList.remove('show')
    document.getElementById('notifications').classList.remove('active')
    document.getElementById('follow-notifications').classList.remove('show')
    document.getElementById('follow-notifications').classList.remove('active')
    document.getElementById('comment-notifications').classList.remove('show')
    document.getElementById('comment-notifications').classList.remove('active')
    document.getElementById('reaction-notifications').classList.remove('show')
    document.getElementById('reaction-notifications').classList.remove('active')
    document.getElementById('remix-notifications').classList.remove('show')
    document.getElementById('remix-notifications').classList.remove('active')
  }

  selectChip(elementId, paneID) {
    document
      .getElementById(elementId)
      .classList.replace('chip-default', 'chip-selected')
    document.getElementById(paneID).classList.add('show')
    document.getElementById(paneID).classList.add('active')
  }

  redirectUser(type, id) {
    if (type === 'follow') {
      window.location.assign('follower')
    }
    if (
      type === 'comment' ||
      type === 'reaction' ||
      type === 'remix' ||
      type === 'program'
    ) {
      window.location.assign('project/' + id)
    }
  }

  markAllRead() {
    const self = this

    new ApiFetch(self.markAllSeen, 'PUT')
      .generateAuthenticatedFetch()
      .then(() => self.hideBadge())
      .catch((error) => {
        self.handleError(error)
      })
  }

  hideBadge() {
    const badge = document.getElementById('sidebar_badge--unseen-notifications')
    badge.style.display = 'none'
  }

  handleError(xhr) {
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
