import { showSnackbar } from '../Layout/Snackbar'
import { MDCChipSet } from '@material/chips'
import { ApiFetch } from '../Api/ApiHelper'
import { escapeHtml, escapeAttr } from '../Components/HtmlEscape'
import './ReportsPage.scss'

const TAB_CONFIG = [
  { chipId: 'all-reports', paneId: 'reports-all', status: 'all' },
  { chipId: 'pending-reports', paneId: 'reports-pending', status: 'pending' },
  { chipId: 'accepted-reports', paneId: 'reports-accepted', status: 'accepted' },
  { chipId: 'rejected-reports', paneId: 'reports-rejected', status: 'rejected' },
]

const CONTENT_TYPE_ICONS = {
  project: 'apps',
  comment: 'comment',
  user: 'person',
  studio: 'groups',
}

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

  const reportsElement = document.querySelector('.js-reports')
  const userReports = new UserReports(
    reportsElement.dataset.baseUrl,
    reportsElement.dataset.errorMessage,
    {
      project: reportsElement.dataset.transProject,
      comment: reportsElement.dataset.transComment,
      user: reportsElement.dataset.transUser,
      studio: reportsElement.dataset.transStudio,
    },
    {
      pending: reportsElement.dataset.transPending,
      accepted: reportsElement.dataset.transAccepted,
      rejected: reportsElement.dataset.transRejected,
    },
  )

  userReports.fetchMore()

  for (const tab of TAB_CONFIG) {
    document.getElementById(tab.chipId).addEventListener('click', function () {
      if (userReports.activeTab !== tab.status) {
        userReports.resetChips()
        userReports.selectChip(tab.chipId, tab.paneId)
        userReports.activeTab = tab.status
        if (tab.status === 'all') {
          userReports.fetchMore()
        } else {
          userReports.renderFilteredTab(tab.status)
        }
      }
    })
  }

  let scrollTicking = false
  window.addEventListener('scroll', function () {
    if (scrollTicking) return
    scrollTicking = true
    requestAnimationFrame(function () {
      const bottom = document.documentElement.scrollHeight - window.innerHeight
      if (bottom > 0 && window.scrollY / bottom >= 0.7) {
        userReports.fetchMore()
      }
      scrollTicking = false
    })
  })
})

class UserReports {
  constructor(baseUrl, errorMessage, contentTypeLabels, statusLabels) {
    this.activeTab = 'all'
    this.baseUrl = baseUrl
    this.errorMessage = errorMessage
    this.contentTypeLabels = contentTypeLabels
    this.statusLabels = statusLabels

    this.cursor = null
    this.hasMore = true
    this.fetching = false
    this.items = []

    this.containers = {}
    for (const tab of TAB_CONFIG) {
      this.containers[tab.status] = document.getElementById(tab.paneId)
    }
  }

  fetchMore() {
    if (!this.hasMore || this.fetching) return
    this.fetching = true

    const params = new URLSearchParams({ limit: 20 })
    if (this.cursor) params.set('cursor', this.cursor)

    new ApiFetch(`${this.baseUrl}/api/users/me/reports?${params}`, 'GET', undefined, 'json')
      .run()
      .then((data) => {
        this._removeSkeletons()
        data.data.forEach((report) => {
          this.items.push(report)
          this._renderCard(report, this.containers.all)
        })
        this.cursor = data.next_cursor
        this.hasMore = data.has_more
        this._updatePlaceholder('all', this.items.length)
        this.fetching = false
      })
      .catch((error) => {
        this._removeSkeletons()
        this.fetching = false
        this._handleError(error)
      })
  }

  renderFilteredTab(status) {
    const container = this.containers[status]
    container.querySelectorAll('.report-item').forEach((el) => el.remove())

    const filtered = this.items.filter((r) => r.status === status)
    filtered.forEach((report) => this._renderCard(report, container))
    this._updatePlaceholder(status, filtered.length)
  }

  _renderCard(report, container) {
    const contentType = report.content_type || ''
    const contentTypeIcon = CONTENT_TYPE_ICONS[contentType] || 'flag'
    const contentTypeLabel = this.contentTypeLabels[contentType] || escapeHtml(contentType)
    const category = escapeHtml(report.category || '')
    const status = report.status || 'pending'
    const statusLabel = escapeHtml(this.statusLabels[status] || status)
    const createdAt = this._formatDate(report.created_at)
    const resolvedHtml = report.resolved_at
      ? `<div class="report-resolved">Resolved: ${escapeHtml(this._formatDate(report.resolved_at))}</div>`
      : ''

    container.insertAdjacentHTML(
      'beforeend',
      `<div id="report-${escapeAttr(String(report.id))}" class="report-item">
        <div class="report-card">
          <div class="report-icon">
            <span class="material-icons">${escapeHtml(contentTypeIcon)}</span>
          </div>
          <div class="report-content">
            <div class="report-title">${escapeHtml(contentTypeLabel)} report</div>
            <div class="report-category">Category: ${category}</div>
            <div class="report-date">${escapeHtml(createdAt)}</div>
            ${resolvedHtml}
          </div>
          <div class="report-status">
            <span class="report-badge report-badge-${escapeAttr(status)}">${statusLabel}</span>
          </div>
        </div>
      </div>`,
    )
  }

  _formatDate(dateString) {
    if (!dateString) return ''
    try {
      return new Intl.DateTimeFormat(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      }).format(new Date(dateString))
    } catch {
      return dateString
    }
  }

  _removeSkeletons() {
    for (const tab of TAB_CONFIG) {
      this.containers[tab.status]?.querySelectorAll('.js-skeleton').forEach((el) => el.remove())
    }
  }

  _updatePlaceholder(status, count) {
    const el = document.getElementById(`no-reports-${status}`)
    if (el) {
      if (count > 0) {
        el.parentElement.classList.remove('d-block')
        el.parentElement.classList.add('d-none')
      } else {
        el.parentElement.classList.remove('d-none')
        el.parentElement.classList.add('d-block')
      }
    }
  }

  resetChips() {
    for (const tab of TAB_CONFIG) {
      document.getElementById(tab.chipId).classList.replace('chip-selected', 'chip-default')
      document.getElementById(tab.paneId).classList.remove('show', 'active')
    }
  }

  selectChip(elementId, paneId) {
    document.getElementById(elementId).classList.replace('chip-default', 'chip-selected')
    document.getElementById(paneId).classList.add('show', 'active')
  }

  _handleError(error) {
    const status = error?.status || (error?.message && parseInt(error.message.match(/\d+/)?.[0]))
    if (status === 401) {
      window.location.href = '/login'
      return
    }
    showSnackbar('#share-snackbar', this.errorMessage)
  }
}
