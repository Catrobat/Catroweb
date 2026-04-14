import { showSnackbar, SnackbarDuration } from '../Layout/Snackbar'
import { MDCChipSet } from '@material/chips'
import { ApiFetch } from '../Api/ApiHelper'
import { escapeHtml, escapeAttr } from '../Components/HtmlEscape'
import { getImageUrl } from '../Layout/ImageVariants'
import './NotificationsPage.scss'

const TAB_CONFIG = [
  { chipId: 'all-notif', paneId: 'notifications', type: 'all', prefix: 'catro-notification-' },
  {
    chipId: 'follow-notif',
    paneId: 'follow-notifications',
    type: 'follow',
    prefix: 'follow-notification-',
  },
  {
    chipId: 'comment-notif',
    paneId: 'comment-notifications',
    type: 'comment',
    prefix: 'comment-notification-',
  },
  {
    chipId: 'reaction-notif',
    paneId: 'reaction-notifications',
    type: 'reaction',
    prefix: 'reaction-notification-',
  },
  {
    chipId: 'remix-notif',
    paneId: 'remix-notifications',
    type: 'remix',
    prefix: 'remix-notification-',
  },
  {
    chipId: 'studio-notif',
    paneId: 'studio-notifications',
    type: 'studio',
    prefix: 'studio-notification-',
  },
  {
    chipId: 'project-notif',
    paneId: 'project-notifications',
    type: 'project',
    prefix: 'project-notification-',
  },
]

document.addEventListener('DOMContentLoaded', () => {
  const chipsetRoot = document.querySelector('.mdc-chip-set')
  const chipset = chipsetRoot ? new MDCChipSet(chipsetRoot) : null
  const tabPaneElements = document.querySelectorAll('.tab-pane')

  if (chipset) {
    chipset.listen('MDCChip:interaction', function (event) {
      document.querySelector('.show.active').classList.remove('show', 'active')
      tabPaneElements[event.detail.index].classList.add('show', 'active')
    })
  }

  const notificationsElement = document.querySelector('.js-notifications')
  const userNotifications = new UserNotifications(
    notificationsElement.dataset.baseUrl,
    notificationsElement.dataset.somethingWentWrongError,
    notificationsElement.dataset.notificationsClearError,
    notificationsElement.dataset.notificationsUnauthorizedError,
    notificationsElement.dataset.profilePath,
    notificationsElement.dataset.projectPath,
    notificationsElement.dataset.imgAsset,
  )

  userNotifications.markAllRead()

  // Fetch initial "all" tab data via API
  const allTab = TAB_CONFIG[0]
  userNotifications.fetchMoreNotifications(
    userNotifications.notificationsFetchCount,
    allTab.type,
    allTab.prefix,
    userNotifications.containers[allTab.type],
  )

  // Event delegation for click handling on API-rendered notifications
  document.querySelector('.tab-content').addEventListener('click', (event) => {
    const item = event.target.closest('.notification-item')
    if (item) {
      userNotifications.redirectUser(
        item.getAttribute('data-notification-instance'),
        item.getAttribute('data-notification-redirect'),
      )
    }
  })
})

class UserNotifications {
  constructor(
    baseUrl,
    somethingWentWrongError,
    notificationsClearError,
    notificationsUnauthorizedError,
    profilePath,
    projectPath,
    imgAsset,
  ) {
    this.activeTab = 'all'
    this.baseUrl = baseUrl
    this.markAllSeenUrl = baseUrl + '/api/notifications/read'
    this.somethingWentWrongError = somethingWentWrongError
    this.notificationsClearError = notificationsClearError
    this.notificationsUnauthorizedError = notificationsUnauthorizedError
    this.notificationsFetchCount = 20
    this.profilePath = profilePath
    this.projectPath = projectPath
    this.imgAsset = imgAsset

    this.cursors = {
      all: null,
      follow: null,
      comment: null,
      reaction: null,
      remix: null,
      studio: null,
    }
    this.hasMore = {
      all: true,
      follow: true,
      comment: true,
      reaction: true,
      remix: true,
      studio: true,
    }
    this.fetchActive = {
      all: false,
      follow: false,
      comment: false,
      reaction: false,
      remix: false,
      studio: false,
    }

    this.containers = {}
    for (const tab of TAB_CONFIG) {
      this.containers[tab.type] = document.getElementById(tab.paneId)
    }

    this._initListeners()
  }

  _initListeners() {
    const self = this

    for (const tab of TAB_CONFIG) {
      document.getElementById(tab.chipId).addEventListener('click', function () {
        if (self.activeTab !== tab.type) {
          self.resetChips()
          self.selectChip(tab.chipId, tab.paneId)
          self.activeTab = tab.type
          self.fetchMoreNotifications(
            self.notificationsFetchCount,
            tab.type,
            tab.prefix,
            self.containers[tab.type],
          )
        }
      })
    }

    let scrollTicking = false
    window.addEventListener('scroll', function () {
      if (scrollTicking) return
      scrollTicking = true
      requestAnimationFrame(function () {
        const position = window.scrollY
        const bottom = document.documentElement.scrollHeight - window.innerHeight
        const pctVertical = position / bottom
        if (pctVertical >= 0.7 && self.activeTab) {
          const tab = TAB_CONFIG.find((t) => t.type === self.activeTab)
          if (tab) {
            self.fetchMoreNotifications(
              self.notificationsFetchCount,
              tab.type,
              tab.prefix,
              self.containers[tab.type],
            )
          }
        }
        scrollTicking = false
      })
    })
  }

  fetchMoreNotifications(limit, type, idPrefix, container) {
    const self = this
    if (!this.hasMore[type] || this.fetchActive[type]) {
      return
    }
    this.fetchActive[type] = true

    const params = new URLSearchParams({ limit, type })
    if (self.cursors[type]) {
      params.set('cursor', self.cursors[type])
    }

    new ApiFetch(`${self.baseUrl}/api/notifications?${params}`, 'GET', undefined, 'json')
      .run()
      .then((data) => {
        self._removeSkeletons(container)
        data.data.forEach((fetched) => {
          self.generateNotificationBody(fetched, idPrefix, container)
        })
        self.cursors[type] = data.next_cursor
        self.hasMore[type] = data.has_more
        self.updateNoNotificationsPlaceholder(type, data.data.length)
        self.fetchActive[type] = false
      })
      .catch((error) => {
        self._removeSkeletons(container)
        self.fetchActive[type] = false
        self.handleError(error)
      })
  }

  updateNoNotificationsPlaceholder(type, fetchedAmount) {
    const emptyElement = document.getElementById(`no-notif-${type}`)
    if (!emptyElement) return
    if (fetchedAmount > 0) {
      emptyElement.parentElement.classList.remove('d-block')
      emptyElement.parentElement.classList.add('d-none')
    } else if (!this.cursors[type]) {
      emptyElement.parentElement.classList.remove('d-none')
      emptyElement.parentElement.classList.add('d-block')
    }
  }

  generateNotificationBody(fetched, idPrefix, container) {
    const self = this
    const imgLeft = self.generateNotificationImage(fetched)
    const msg = self.generateNotificationMessage(fetched)
    const notificationId = escapeAttr(idPrefix + fetched.id)
    const unreadClass = !fetched.seen ? ' notification-unread' : ''
    const notificationDot = !fetched.seen ? '<span class="dot"></span>' : ''
    const instanceType = escapeAttr(self.getInstanceType(fetched))
    const redirectTarget = escapeAttr(String(self.getRedirectTarget(fetched)))

    const notificationBody = `<div id="${notificationId}" class="notification-item"
          data-notification-instance="${instanceType}"
          data-notification-redirect="${redirectTarget}">
        <div class="notification-card${unreadClass}">
          <div class="notification-avatar">${imgLeft}</div>
          <div class="notification-content">${msg}</div>
          <div class="notification-indicator">${notificationDot}</div>
        </div>
      </div>`
    container.insertAdjacentHTML('beforeend', notificationBody)
  }

  getInstanceType(fetched) {
    if (fetched.type === 'follow' && fetched.project) return 'program'
    if (fetched.type === 'moderation' && fetched.project) return 'program'
    if (fetched.type === 'studio') return 'studio'
    if (fetched.type === 'project' && fetched.project) return 'program'
    if (fetched.type === 'project') return 'other'
    return fetched.type
  }

  getRedirectTarget(fetched) {
    if (fetched.type === 'studio' && fetched.studio) return fetched.studio
    if (fetched.project) return fetched.project
    if (fetched.type === 'follow' && fetched.from) return fetched.from
    return ''
  }

  generateNotificationImage(fetched) {
    const self = this
    if (fetched.type === 'moderation') {
      return '<span class="material-icons notification-broadcast-icon">flag</span>'
    }
    if (fetched.type !== 'other') {
      let imgLeft = self.imgAsset
      const avatarUrl = getImageUrl(fetched.avatar, 'thumb', null)
      if (avatarUrl) {
        imgLeft = avatarUrl
      }
      const safeFrom = encodeURIComponent(fetched.from)
      const safeName = escapeAttr(fetched.from_name || '')
      return `<a href="${self.profilePath}/${safeFrom}">
        <img class="notification-avatar-img" src="${escapeAttr(imgLeft)}" alt="${safeName}">
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
    let msg = escapeHtml(fetched.message)
    if (msg.includes('%user_link%')) {
      const safeFrom = encodeURIComponent(fetched.from)
      msg = msg.replace(
        '%user_link%',
        `<a href="${self.profilePath}/${safeFrom}">${escapeHtml(fetched.from_name)}</a>`,
      )
    }
    if (msg.includes('%program_link%')) {
      const safeProject = encodeURIComponent(fetched.project)
      msg = msg.replace(
        '%program_link%',
        `<a href="${self.projectPath}/${safeProject}">${escapeHtml(fetched.project_name)}</a>`,
      )
    }
    if (msg.includes('%remix_program_link%')) {
      const safeRemixed = encodeURIComponent(fetched.remixed_project)
      msg = msg.replace(
        '%remix_program_link%',
        `<a href="${self.projectPath}/${safeRemixed}">${escapeHtml(fetched.remixed_project_name)}</a>`,
      )
    }
    if (fetched.prize) {
      msg = `<div class="message">${escapeHtml(fetched.message)}</div><div class="prize">${escapeHtml(fetched.prize)}</div>`
    }
    return msg
  }

  resetChips() {
    for (const tab of TAB_CONFIG) {
      document.getElementById(tab.chipId).classList.replace('chip-selected', 'chip-default')
      document.getElementById(tab.paneId).classList.remove('show', 'active')
    }
  }

  selectChip(elementId, paneID) {
    document.getElementById(elementId).classList.replace('chip-default', 'chip-selected')
    document.getElementById(paneID).classList.add('show', 'active')
  }

  redirectUser(type, id) {
    if (type === 'follow') {
      window.location.assign('follower')
      return
    }
    if (['comment', 'reaction', 'remix', 'program'].includes(type)) {
      const safeId =
        typeof id === 'string' ? encodeURIComponent(id.replace(/[^A-Za-z0-9_-]/g, '')) : ''
      window.location.assign(`project/${safeId}`)
      return
    }
    if (type === 'studio' && id) {
      const safeId =
        typeof id === 'string' ? encodeURIComponent(id.replace(/[^A-Za-z0-9_-]/g, '')) : ''
      window.location.assign(`studio/${safeId}`)
    }
  }

  markAllRead() {
    const self = this

    // We delay marking all as read by 2 seconds to allow the user to see what's new
    setTimeout(() => {
      const badge = document.getElementById('sidebar_badge--unseen-notifications')
      if (badge && badge.style.display !== 'none') {
        new ApiFetch(self.markAllSeenUrl, 'PUT')
          .run()
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

  _removeSkeletons(container) {
    container.querySelectorAll('.js-skeleton').forEach((el) => el.remove())
  }

  handleError(error) {
    const self = this
    const status = error?.status || (error?.message && parseInt(error.message.match(/\d+/)?.[0]))
    if (status === 401) {
      showSnackbar('#share-snackbar', self.notificationsUnauthorizedError, SnackbarDuration.error)
      return
    }
    if (status === 404) {
      showSnackbar('#share-snackbar', self.notificationsClearError, SnackbarDuration.error)
    }
  }
}
