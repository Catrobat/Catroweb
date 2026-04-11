import 'external-svg-loader'
import '../Components/TabBar'
import './FollowerOverview'

import { ProjectList } from '../Project/ProjectList'
import { escapeHtml } from '../Components/HtmlEscape'
import { achievementBadgeHtml } from './AchievementBadge'
import './Profile.scss'
import './Achievements.scss'
import '../Project/ProjectsBrowse.scss'

initProfileHeader()
initUserProjects()
initProfileAchievements()
initReportUser()

function initProfileHeader() {
  const container = document.querySelector('.js-profile')
  if (!container) return

  const userId = container.dataset.userId
  const baseUrl = container.dataset.baseUrl || ''
  const viewerId = container.dataset.viewerId || ''
  const isLoggedIn = container.dataset.isLoggedIn === 'true'
  const viewerIsMinor = container.dataset.viewerIsMinor === 'true'
  const profileIsMinor = container.dataset.profileIsMinor === 'true'
  const followerContainer = document.querySelector('.js-follower-overview')
  const transFollow = followerContainer?.dataset.transFollow || ''
  const transFollows = followerContainer?.dataset.transFollows || ''

  fetch(baseUrl + '/api/users/' + userId, {
    headers: { Accept: 'application/json' },
    credentials: 'same-origin',
  })
    .then((r) => {
      if (!r.ok) throw new Error('HTTP ' + r.status)
      return r.json()
    })
    .then((profile) => {
      if (profile.is_verified) {
        const badge = document.getElementById('verification-badge')
        if (badge) badge.classList.remove('d-none')
      }

      const projectsCount = document.getElementById('projects-count')
      if (projectsCount) projectsCount.textContent = profile.projects ?? '-'

      if (profile.scratch_username) {
        const scratchLink = document.getElementById('scratch-link')
        if (scratchLink) {
          scratchLink.href =
            'https://scratch.mit.edu/users/' + encodeURIComponent(profile.scratch_username)
          scratchLink.classList.remove('d-none')
        }
      }

      const buttonContainer = document.querySelector('.button-container')
      if (buttonContainer) {
        renderFollowButtons(buttonContainer, {
          userId,
          viewerId,
          isLoggedIn,
          viewerIsMinor,
          profileIsMinor,
          isFollowedByViewer: profile.is_followed_by_viewer,
          transFollow,
          transFollows,
          username: profile.username,
        })
      }

      if (viewerIsMinor || profileIsMinor) {
        document.querySelectorAll('.js-follower-tab').forEach((tab) => {
          tab.style.display = 'none'
        })
        const followerSection = document.getElementById('follower-section')
        const followingSection = document.getElementById('following-section')
        if (followerSection) followerSection.remove()
        if (followingSection) followingSection.remove()
      }
    })
    .catch((error) => {
      console.error('Failed to load profile data:', error)
    })
    .finally(() => {
      document.querySelectorAll('.js-skeleton').forEach((el) => el.remove())
    })
}

function renderFollowButtons(buttonContainer, opts) {
  const {
    userId,
    viewerId,
    isLoggedIn,
    viewerIsMinor,
    profileIsMinor,
    isFollowedByViewer,
    transFollow,
    transFollows,
    username,
  } = opts

  const isOwnProfile = isLoggedIn && viewerId === userId

  // No follow buttons for own profile or minor users
  if (isOwnProfile || viewerIsMinor || profileIsMinor) return

  if (isLoggedIn && isFollowedByViewer !== null) {
    // Authenticated user — show follow or unfollow based on state
    const unfollowBtn = document.createElement('button')
    unfollowBtn.className = 'btn btn-outline-primary profile-follows unfollow-btn mt-0 ms-auto'
    unfollowBtn.style.display = isFollowedByViewer ? 'block' : 'none'
    unfollowBtn.dataset.userId = userId
    unfollowBtn.dataset.userName = username
    unfollowBtn.textContent = transFollows
    buttonContainer.appendChild(unfollowBtn)

    const followBtn = document.createElement('button')
    followBtn.className = 'btn btn-primary profile-follow follow-btn mt-0 ms-auto'
    followBtn.style.display = isFollowedByViewer ? 'none' : 'block'
    followBtn.dataset.userId = userId
    followBtn.textContent = transFollow
    buttonContainer.appendChild(followBtn)
  } else {
    // Guest user — show follow button (redirects to login)
    const followBtn = document.createElement('button')
    followBtn.className = 'btn btn-primary profile-follow follow-btn mt-0 ms-auto'
    followBtn.dataset.userId = userId
    followBtn.textContent = transFollow
    buttonContainer.appendChild(followBtn)
  }
}

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
    const isOwnProfile = projectList.dataset.isOwnProfile === 'true'

    const url = isOwnProfile
      ? `${baseUrl}/api/projects/user`
      : `${baseUrl}/api/projects/user/${userId}`

    const authHeaders = {}

    new ProjectList(
      projectList,
      'user-projects',
      url,
      property,
      theme,
      999,
      emptyMessage,
      authHeaders,
    )
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

  fetch(baseUrl + '/api/users/' + userId + '/achievements', {
    headers: { Accept: 'application/json' },
  })
    .then((r) => r.json())
    .then((response) => {
      const achievements = Array.isArray(response) ? response : response?.data || []
      const skeleton = container.querySelector('.js-achievements-skeleton')

      if (!achievements || achievements.length === 0) {
        collapseContainer(container)
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

      if (skeleton) skeleton.remove()
      container.innerHTML =
        '<hr>' +
        '<h3>' +
        escapeHtml(title) +
        '</h3>' +
        '<div class="horizontal-scrolling-wrapper">' +
        badgesHtml +
        '</div>' +
        '<hr>'
    })
    .catch((error) => {
      console.error('Failed to load profile achievements:', error)
      collapseContainer(container)
    })
}

function collapseContainer(container) {
  container.style.height = container.offsetHeight + 'px'
  container.style.transition = 'height 0.2s ease, opacity 0.2s ease'
  container.style.overflow = 'hidden'
  requestAnimationFrame(() => {
    container.style.height = '0'
    container.style.opacity = '0'
    container.style.margin = '0'
    container.style.padding = '0'
  })
}
