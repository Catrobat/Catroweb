import { showSnackbar } from '../Layout/Snackbar'
import { MDCChipSet } from '@material/chips'
import { ApiFetch } from '../Api/ApiHelper'
import './NotificationsPage.scss'

document.addEventListener('DOMContentLoaded', () => {
  const chipset = new MDCChipSet(document.querySelector('.mdc-chip-set'))
  const tabPaneElements = document.querySelectorAll('.tab-pane')

  chipset.listen('MDCChip:interaction', function (event) {
    document.querySelector('.show.active').classList.remove('show', 'active')
    tabPaneElements[event.detail.index].classList.add('show', 'active')
  })

  const notificationsElement = document.querySelector('.js-notifications')
  const userNotifications = new UserNotifications(
    notificationsElement.dataset.baseUrl + '/api/notifications/read',
    notificationsElement.dataset.fetchUrl,
    notificationsElement.dataset.somethingWentWrongError,
    notificationsElement.dataset.notificationsClearError,
    notificationsElement.dataset.notificationsUnauthorizedError,
    notificationsElement.dataset.allNotificationsCount,
    notificationsElement.dataset.followNotificationCount,
    notificationsElement.dataset.reactionNotificationCount,
    notificationsElement.dataset.commentNotificationCount,
    notificationsElement.dataset.remixNotificationCount,
    notificationsElement.dataset.profilePath,
    notificationsElement.dataset.projectPath,
    notificationsElement.dataset.imgAsset,
  )

  userNotifications.markAllRead()

  document.querySelectorAll('.js-notification-interaction').forEach((element) => {
    element.addEventListener('click', () => {
      userNotifications.redirectUser(
        element.getAttribute('data-notification-instance'),
        element.getAttribute('data-notification-redirect'),
      )
    })
  })
})

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
    this.allNotificationsLoaded = document.getElementById('notifications').childElementCount
    this.followerNotificationsLoaded =
      document.getElementById('follow-notifications').childElementCount
    this.reactionNotificationsLoaded =
      document.getElementById('reaction-notifications').childElementCount
    this.commentNotificationsLoaded =
      document.getElementById('comment-notifications').childElementCount
    this.remixNotificationsLoaded = document.getElementById('remix-notifications').childElementCount
    this.empty = false
    this.fetchActive = false
    this.profilePath = profilePath
    this.projectPath = projectPath
    this.imgAsset = imgAsset

    this._initListeners()
  }

  _initListeners() {
    const self = this
    document.getElementById('all-notif').addEventListener('click', function () {
      if (!self.all) {
        self.resetChips()
        self.selectChip('all-notif', 'notifications')
        self.all = true
      }
    })

    document.getElementById('follow-notif').addEventListener('click', function () {
      if (!self.follower) {
        self.resetChips()
        self.selectChip('follow-notif', 'follow-notifications')
        self.follower = true
        if (self.followerNotificationsLoaded < self.notificationsFetchCount) {
          self.followerNotificationsLoaded =
            document.getElementById('follow-notifications').childElementCount
          self.fetchMoreNotifications(
            self.notificationsFetchCount,
            self.followerNotificationsLoaded,
            'follow',
            'follow-notification-',
            document.getElementById('follow-notifications'),
          )
        }
      }
    })

    document.getElementById('comment-notif').addEventListener('click', function () {
      if (!self.comment) {
        self.resetChips()
        self.selectChip('comment-notif', 'comment-notifications')
        self.comment = true
        if (self.commentNotificationsLoaded < self.notificationsFetchCount) {
          self.commentNotificationsLoaded =
            document.getElementById('comment-notifications').childElementCount
          self.fetchMoreNotifications(
            self.notificationsFetchCount,
            self.commentNotificationsLoaded,
            'comment',
            'comment-notification-',
            document.getElementById('comment-notifications'),
          )
        }
      }
    })

    document.getElementById('reaction-notif').addEventListener('click', function () {
      if (!self.reactions) {
        self.resetChips()
        self.selectChip('reaction-notif', 'reaction-notifications')
        self.reactions = true
        if (self.reactionNotificationsLoaded < self.notificationsFetchCount) {
          self.reactionNotificationsLoaded =
            document.getElementById('reaction-notifications').childElementCount
          self.fetchMoreNotifications(
            self.notificationsFetchCount,
            self.reactionNotificationsLoaded,
            'reaction',
            'reaction-notification-',
            document.getElementById('reaction-notifications'),
          )
        }
      }
    })

    document.getElementById('remix-notif').addEventListener('click', function () {
      if (!self.remixes) {
        self.resetChips()
        self.selectChip('remix-notif', 'remix-notifications')
        self.remixes = true
        if (self.remixNotificationsLoaded < self.notificationsFetchCount) {
          self.remixNotificationsLoaded =
            document.getElementById('remix-notifications').childElementCount
          self.fetchMoreNotifications(
            self.notificationsFetchCount,
            self.remixNotificationsLoaded,
            'remix',
            'remix-notification-',
            document.getElementById('remix-notifications'),
          )
        }
      }
    })

    window.addEventListener('scroll', function () {
      const position = window.scrollY
      const bottom = document.documentElement.scrollHeight - window.innerHeight
      const pctVertical = position / bottom
      if (pctVertical >= 0.7) {
        if (self.all) {
          self.allNotificationsLoaded = document.getElementById('notifications').childElementCount
          self.fetchMoreNotifications(
            self.notificationsFetchCount,
            self.allNotificationsLoaded,
            'all',
            'catro-notification-',
            document.getElementById('notifications'),
          )
        } else if (self.follower) {
          self.followerNotificationsLoaded =
            document.getElementById('follow-notifications').childElementCount
          self.fetchMoreNotifications(
            self.notificationsFetchCount,
            self.followerNotificationsLoaded,
            'follow',
            'follow-notification-',
            document.getElementById('follow-notifications'),
          )
        } else if (self.comment) {
          self.commentNotificationsLoaded =
            document.getElementById('comment-notifications').childElementCount
          self.fetchMoreNotifications(
            self.notificationsFetchCount,
            self.commentNotificationsLoaded,
            'comment',
            'comment-notification-',
            document.getElementById('comment-notifications'),
          )
        } else if (self.reactions) {
          self.reactionNotificationsLoaded =
            document.getElementById('reaction-notifications').childElementCount
          self.fetchMoreNotifications(
            self.notificationsFetchCount,
            self.reactionNotificationsLoaded,
            'reaction',
            'reaction-notification-',
            document.getElementById('reaction-notifications'),
          )
        } else if (self.remixes) {
          self.remixNotificationsLoaded =
            document.getElementById('remix-notifications').childElementCount
          self.fetchMoreNotifications(
            self.notificationsFetchCount,
            self.remixNotificationsLoaded,
            'remix',
            'remix-notification-',
            document.getElementById('remix-notifications'),
          )
        }
      }
    })
  }

  fetchMoreNotifications(limit, loadedCount, type, idPrefix, container) {
    const self = this
    if (this.empty || this.fetchActive) {
      return
    }
    this.fetchActive = true

    fetch(`${self.fetchNotificationsUrl}/${limit}/${loadedCount}/${type}`)
      .then((response) => response.json())
      .then((data) => {
        data['fetched-notifications'].forEach((fetched) => {
          self.generateNotificationBody(fetched, idPrefix, container)
        })
        self.updateNoNotificationsPlaceholder(type, data['fetched-notifications'].length)
        self.fetchActive = false
      })
      .catch((xhr) => {
        self.handleError(xhr)
      })
  }

  updateNoNotificationsPlaceholder(type, fetchedAmount) {
    if (fetchedAmount > 0) {
      let emptyId = ''
      if (type === 'all') emptyId = 'no-notif-all'
      if (type === 'follow') emptyId = 'no-notif-follow'
      if (type === 'comment') emptyId = 'no-notif-comment'
      if (type === 'reaction') emptyId = 'no-notif-reaction'
      if (type === 'remix') emptyId = 'no-notif-remix'

      if (emptyId) {
        const emptyElement = document.getElementById(emptyId)
        if (emptyElement) {
          emptyElement.parentElement.classList.replace('d-block', 'd-none')
        }
      }
    }
  }

  generateNotificationBody(fetched, idPrefix, container) {
    const self = this
    const imgLeft = self.generateNotificationImage(fetched)
    const msg = self.generateNotificationMessage(fetched)
    const notificationId = idPrefix + fetched.id
    const unreadClass = !fetched.seen ? ' notification-unread' : ''
    const notificationDot = !fetched.seen ? '<span class="dot"></span>' : ''

    const notificationBody = `<div id="${notificationId}" class="notification-item">
        <div class="notification-card${unreadClass}">
          <div class="notification-avatar">${imgLeft}</div>
          <div class="notification-content">${msg}</div>
          <div class="notification-indicator">${notificationDot}</div>
        </div>
      </div>`
    container.insertAdjacentHTML('beforeend', notificationBody)
  }

  generateNotificationImage(fetched) {
    const self = this
    if (fetched.type !== 'other') {
      let imgLeft = self.imgAsset
      if (fetched.avatar) {
        imgLeft = fetched.avatar
      }
      return `<a href="${self.profilePath}/${fetched.from}">
        <img class="notification-avatar-img" src="${imgLeft}" alt="${fetched.from_name || ''}">
      </a>`
    } else {
      let iconName = 'notifications_active'
      if (fetched.prize) {
        iconName = 'cake'
      }
      return `<span class="material-icons notification-broadcast-icon">${iconName}</span>`
    }
  }

  generateNotificationMessage(fetched) {
    const self = this
    let msg = fetched.message
    if (msg.includes('%user_link%')) {
      msg = msg.replace(
        '%user_link%',
        `<a href="${self.profilePath}/${fetched.from}">${fetched.from_name}</a>`,
      )
    }
    if (msg.includes('%program_link%')) {
      msg = msg.replace(
        '%program_link%',
        `<a href="${self.projectPath}/${fetched.program}">${fetched.program_name}</a>`,
      )
    }
    if (msg.includes('%remix_program_link%')) {
      msg = msg.replace(
        '%remix_program_link%',
        `<a href="${self.projectPath}/${fetched.remixed_program}">${fetched.remixed_program_name}</a>`,
      )
    }
    if (fetched.prize) {
      msg = `<div class="message">${fetched.message}</div><div class="prize">${fetched.prize}</div>`
    }
    return msg
  }

  resetChips() {
    this.remixes = false
    this.all = false
    this.follower = false
    this.comment = false
    this.reactions = false
    this.resetColor()
  }

  resetColor() {
    document.getElementById('all-notif').classList.replace('chip-selected', 'chip-default')
    document.getElementById('follow-notif').classList.replace('chip-selected', 'chip-default')
    document.getElementById('comment-notif').classList.replace('chip-selected', 'chip-default')
    document.getElementById('reaction-notif').classList.replace('chip-selected', 'chip-default')
    document.getElementById('remix-notif').classList.replace('chip-selected', 'chip-default')
    document.getElementById('notifications').classList.remove('show', 'active')
    document.getElementById('follow-notifications').classList.remove('show', 'active')
    document.getElementById('comment-notifications').classList.remove('show', 'active')
    document.getElementById('reaction-notifications').classList.remove('show', 'active')
    document.getElementById('remix-notifications').classList.remove('show', 'active')
  }

  selectChip(elementId, paneID) {
    document.getElementById(elementId).classList.replace('chip-default', 'chip-selected')
    document.getElementById(paneID).classList.add('show', 'active')
  }

  redirectUser(type, id) {
    if (type === 'follow') {
      window.location.assign('follower')
    }
    if (['comment', 'reaction', 'remix', 'program'].includes(type)) {
      window.location.assign(`project/${id}`)
    }
  }

  markAllRead() {
    const self = this

    // We delay marking all as read by 2 seconds to allow the user to see what's new
    setTimeout(() => {
      const badge = document.getElementById('sidebar_badge--unseen-notifications')
      if (badge && badge.style.display !== 'none') {
        new ApiFetch(self.markAllSeen, 'PUT')
          .generateAuthenticatedFetch()
          .then(() => self.hideBadge())
          .catch((error) => {
            self.handleError(error)
          })
      }
    }, 2000)
  }

  hideBadge() {
    const badge = document.getElementById('sidebar_badge--unseen-notifications')
    badge.style.display = 'none'
  }

  handleError(xhr) {
    const self = this
    if (xhr.status === 401) {
      showSnackbar('#share-snackbar', self.notificationsUnauthorizedError)
      return
    }
    if (xhr.status === 404) {
      showSnackbar('#share-snackbar', self.notificationsClearError)
    }
  }
}
