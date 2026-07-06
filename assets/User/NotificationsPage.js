import { MDCChipSet } from '@material/chips'
import { ApiFetch } from '../Api/ApiHelper'
import { escapeAttr, escapeHtml } from '../Components/HtmlEscape'
import { buildPictureHTML } from '../Layout/ImageVariants'
import { SnackbarDuration, showSnackbar } from '../Layout/Snackbar'
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
    chipset.listen('MDCChip:interaction', (event) => {
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
    this.markAllSeenUrl = `${baseUrl}/api/notifications/read`
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
    for (const tab of TAB_CONFIG) {
      document.getElementById(tab.chipId).addEventListener('click', () => {
        if (this.activeTab !== tab.type) {
          this.resetChips()
          this.selectChip(tab.chipId, tab.paneId)
          this.activeTab = tab.type
          this.fetchMoreNotifications(
            this.notificationsFetchCount,
            tab.type,
            tab.prefix,
            this.containers[tab.type],
          )
        }
      })
    }

    let scrollTicking = false
    window.addEventListener('scroll', () => {
      if (scrollTicking) return
      scrollTicking = true
      requestAnimationFrame(() => {
        const position = window.scrollY
        const bottom = document.documentElement.scrollHeight - window.innerHeight
        const pctVertical = position / bottom
        if (pctVertical >= 0.7 && this.activeTab) {
          const tab = TAB_CONFIG.find((t) => t.type === this.activeTab)
          if (tab) {
            this.fetchMoreNotifications(
              this.notificationsFetchCount,
              tab.type,
              tab.prefix,
              this.containers[tab.type],
            )
          }
        }
        scrollTicking = false
      })
    })
  }

  fetchMoreNotifications(limit, type, idPrefix, container) {
    if (!this.hasMore[type] || this.fetchActive[type]) {
      return
    }
    this.fetchActive[type] = true

    const params = new URLSearchParams({ limit, type })
    if (this.cursors[type]) {
      params.set('cursor', this.cursors[type])
    }

    new ApiFetch(`${this.baseUrl}/api/notifications?${params}`, 'GET', undefined, 'json')
      .run()
      .then((data) => {
        this._removeSkeletons(container)
        data.data.forEach((fetched) => {
          this.generateNotificationBody(fetched, idPrefix, container)
        })
        this.cursors[type] = data.next_cursor
        this.hasMore[type] = data.has_more
        this.updateNoNotificationsPlaceholder(type, data.data.length)
        this.fetchActive[type] = false
      })
      .catch((error) => {
        this._removeSkeletons(container)
        this.fetchActive[type] = false
        this.handleError(error)
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
    const imgLeft = this.generateNotificationImage(fetched)
    const msg = this.generateNotificationMessage(fetched)
    const notificationId = escapeAttr(idPrefix + fetched.id)
    const unreadClass = !fetched.seen ? ' notification-unread' : ''
    const notificationDot = !fetched.seen ? '<span class="dot"></span>' : ''
    const instanceType = escapeAttr(this.getInstanceType(fetched))
    const redirectTarget = escapeAttr(String(this.getRedirectTarget(fetched)))

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
    if (fetched.type === 'moderation') {
      return '<span class="material-icons notification-broadcast-icon">flag</span>'
    }
    if (fetched.type === 'project' && !fetched.from) {
      return '<span class="material-icons notification-broadcast-icon">auto_delete</span>'
    }
    if (fetched.type !== 'other') {
      const safeFrom = encodeURIComponent(fetched.from)
      const safeName = escapeAttr(fetched.from_name || '')
      return `<a href="${this.profilePath}/${safeFrom}">
        ${buildPictureHTML(fetched.avatar, 'thumb', this.imgAsset, `class="notification-avatar-img" alt="${safeName}"`)}
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
    let msg = escapeHtml(fetched.message)
    if (msg.includes('%user_link%')) {
      const safeFrom = encodeURIComponent(fetched.from)
      msg = msg.replace(
        '%user_link%',
        `<a href="${this.profilePath}/${safeFrom}">${escapeHtml(fetched.from_name)}</a>`,
      )
    }
    if (msg.includes('%program_link%')) {
      const safeProject = encodeURIComponent(fetched.project)
      msg = msg.replace(
        '%program_link%',
        `<a href="${this.projectPath}/${safeProject}">${escapeHtml(fetched.project_name)}</a>`,
      )
    }
    if (msg.includes('%remix_program_link%')) {
      const safeRemixed = encodeURIComponent(fetched.remixed_project)
      msg = msg.replace(
        '%remix_program_link%',
        `<a href="${this.projectPath}/${safeRemixed}">${escapeHtml(fetched.remixed_project_name)}</a>`,
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
    // We delay marking all as read by 2 seconds to allow the user to see what's new
    setTimeout(() => {
      const badge = document.getElementById('sidebar_badge--unseen-notifications')
      if (badge && badge.style.display !== 'none') {
        new ApiFetch(this.markAllSeenUrl, 'PUT')
          .run()
          .then(() => this.hideBadge())
          .catch((error) => {
            this.handleError(error)
          })
      }
    }, 2000)
  }

  hideBadge() {
    const badge = document.getElementById('sidebar_badge--unseen-notifications')
    badge.style.display = 'none'
  }

  _removeSkeletons(container) {
    container.querySelectorAll('.js-skeleton').forEach((el) => {
      el.remove()
    })
  }

  handleError(error) {
    const status =
      error?.status || (error?.message && parseInt(error.message.match(/\d+/)?.[0], 10))
    if (status === 401) {
      showSnackbar('#share-snackbar', this.notificationsUnauthorizedError, SnackbarDuration.error)
      return
    }
    if (status === 404) {
      showSnackbar('#share-snackbar', this.notificationsClearError, SnackbarDuration.error)
    }
  }
}
