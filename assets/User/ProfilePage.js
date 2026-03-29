import 'external-svg-loader'
import '../Components/TabBar'
import './FollowerOverview'

import { ProjectList } from '../Project/ProjectList'
import { escapeHtml } from '../Components/HtmlEscape'
import { achievementBadgeHtml } from './AchievementBadge'

import './Profile.scss'
import './Achievements.scss'

initUserProjects()
initProfileAchievements()
initReportUser()

function initReportUser() {
  const reportBtn = document.getElementById('top-app-bar__btn-report-user')
  if (!reportBtn) return

  import('../Moderation/ReportDialog').then(({ showReportDialog }) => {
    const buildReportDialogConfig = () => ({
      contentType: reportBtn.dataset.contentType,
      contentId: reportBtn.dataset.contentId,
      apiUrl: reportBtn.dataset.reportUrl,
      loginUrl: reportBtn.dataset.loginUrl,
      isLoggedIn: reportBtn.dataset.loggedIn === 'true',
      translations: {
        title: reportBtn.dataset.transReportTitle,
        submit: reportBtn.dataset.transReportSubmit,
        cancel: reportBtn.dataset.transReportCancel,
        success: reportBtn.dataset.transReportSuccess,
        error: reportBtn.dataset.transReportError,
        duplicate: reportBtn.dataset.transReportDuplicate,
        trustTooLow: reportBtn.dataset.transReportTrustTooLow,
        unverified: reportBtn.dataset.transReportUnverified,
        suspended: reportBtn.dataset.transReportSuspended,
        rateLimited: reportBtn.dataset.transReportRateLimited,
        notePlaceholder: reportBtn.dataset.transReportPlaceholder,
      },
    })

    reportBtn.addEventListener('click', () => {
      showReportDialog(buildReportDialogConfig())
    })

    if (reportBtn.dataset.loggedIn === 'true') {
      const pending = sessionStorage.getItem('pendingAction')
      if (pending) {
        try {
          const pendingAction = JSON.parse(pending)
          const isMatchingReportHandoff =
            pendingAction?.actionType === 'report' &&
            String(pendingAction?.contentType || '') ===
              String(reportBtn.dataset.contentType || '') &&
            String(pendingAction?.contentId || '') === String(reportBtn.dataset.contentId || '')

          if (isMatchingReportHandoff) {
            sessionStorage.removeItem('pendingAction')
            showReportDialog(buildReportDialogConfig())
          }
        } catch (e) {
          console.error('Failed to parse pending report handoff', e)
          sessionStorage.removeItem('pendingAction')
        }
      }
    }
  })
}

function initUserProjects() {
  const userProjects = document.querySelector('#projects-section')
  const projectLists = userProjects.querySelectorAll('.project-list')

  projectLists.forEach((projectList) => {
    const property = projectList.dataset.property
    const theme = projectList.dataset.theme
    const baseUrl = projectList.dataset.baseUrl
    const userId = projectList.dataset.userId
    const emptyMessage = projectList.dataset.emptyMessage

    const url = `${baseUrl}/api/projects/user/${userId}`

    new ProjectList(projectList, 'user-projects', url, property, theme, 999, emptyMessage)
  })
}

function initProfileAchievements() {
  const container = document.querySelector('.js-profile-achievements')
  if (!container) {
    return
  }

  const baseUrl = container.dataset.baseUrl
  const userId = container.dataset.userId
  const title = container.dataset.transTitle

  fetch(baseUrl + '/api/user/' + userId + '/achievements', {
    headers: { Accept: 'application/json' },
  })
    .then((r) => r.json())
    .then((achievements) => {
      if (!achievements || achievements.length === 0) {
        return
      }

      const badgesHtml = achievements
        .map(
          (achievement) =>
            '<div class="achievement__badge achievement__badge--profile">' +
            achievementBadgeHtml(achievement, 'profile') +
            '</div>',
        )
        .join('')

      container.innerHTML =
        '<hr>' +
        '<h3>' +
        escapeHtml(title) +
        '</h3>' +
        '<div class="horizontal-scrolling-wrapper">' +
        badgesHtml +
        '</div>' +
        '<hr>'

      container.classList.remove('d-none')
    })
    .catch((error) => {
      console.error('Failed to load profile achievements:', error)
    })
}
