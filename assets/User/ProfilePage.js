import 'external-svg-loader'
import '../Components/TabBar'
import './FollowerOverview'

import { shareLink } from '../Components/ShareLink'
import { ProjectList } from '../Project/ProjectList'
import { escapeHtml } from '../Components/HtmlEscape'
import { achievementBadgeHtml } from './AchievementBadge'

import './Profile.scss'
import './Achievements.scss'

const userShare = document.querySelector('.js-user-share')

shareLink(
  userShare.dataset.themeDisplayName,
  userShare.dataset.transCheckOutUser,
  userShare.dataset.userUrl,
  userShare.dataset.transShareSuccess,
  userShare.dataset.transShareError,
  userShare.dataset.transCopy,
  userShare.dataset.transClipboardSuccess,
  userShare.dataset.transClipboardFail,
)

initUserProjects()
initProfileAchievements()

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

    projectList.dataset.list = JSON.stringify(
      new ProjectList(projectList, 'user-projects', url, property, theme, 999, emptyMessage),
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
