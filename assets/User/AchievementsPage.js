import Swal from 'sweetalert2'
import 'external-svg-loader'
import { ApiFetch } from '../Api/ApiHelper'
import { escapeHtml, escapeAttr } from '../Components/HtmlEscape'
import { achievementBadgeHtml } from './AchievementBadge'

import '../Components/TabBar'

import './Achievements.scss'

let baseUrl
let popupBackgroundSvg
let trans

document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('.js-achievements')
  if (!container) {
    return
  }

  baseUrl = container.dataset.baseUrl
  popupBackgroundSvg = container.dataset.popupBackgroundSvg
  trans = {
    popupTitle: container.dataset.transPopupTitle,
    popupSubtitle: container.dataset.transPopupSubtitle,
    popupConfirm: container.dataset.transPopupConfirm,
    mostRecentTitle: container.dataset.transMostRecentTitle,
    xOutOfY: container.dataset.transXOutOfY,
    unlockedEmpty: container.dataset.transUnlockedEmpty,
    lockedEmpty: container.dataset.transLockedEmpty,
  }

  fetchAchievements()
})

function fetchAchievements() {
  new ApiFetch(baseUrl + '/api/achievements', 'GET', undefined, 'json')
    .run()
    .then((data) => {
      renderUnlockedAchievements(data.unlocked || [])
      renderLockedAchievements(data.locked || [])
      renderMostRecentSection(data)
      updateEmptyStates(data)

      if (data.show_animation && data.most_recent) {
        showNewAchievementAnimation(data.most_recent)
      }
    })
    .catch((error) => {
      console.error('Failed to load achievements:', error)
    })
}

function renderUnlockedAchievements(achievements) {
  const wrapper = document.getElementById('unlocked-achievements')
  if (!wrapper) {
    return
  }

  const fragment = document.createDocumentFragment()
  achievements.forEach((achievement) => {
    fragment.appendChild(createUnlockedAchievementElement(achievement))
  })
  wrapper.appendChild(fragment)
}

function renderLockedAchievements(achievements) {
  const wrapper = document.getElementById('locked-achievements')
  if (!wrapper) {
    return
  }

  const fragment = document.createDocumentFragment()
  achievements.forEach((achievement) => {
    fragment.appendChild(createLockedAchievementElement(achievement))
  })
  wrapper.appendChild(fragment)
}

function createUnlockedAchievementElement(achievement) {
  const div = document.createElement('div')
  div.className = 'achievement'
  div.innerHTML =
    '<div class="achievement__badge">' +
    achievementBadgeHtml(achievement, 'tab') +
    '</div>' +
    '<p class="achievement__badge__text">' +
    escapeHtml(achievement.description) +
    '</p>'
  return div
}

function createLockedAchievementElement(achievement) {
  const div = document.createElement('div')
  div.className = 'achievement'
  div.innerHTML =
    '<div class="achievement__badge">' +
    '<svg class="achievement__badge__coin achievement__badge__coin--tab"' +
    ' data-src="' +
    escapeAttr(achievement.badge_locked_svg_path) +
    '" data-unique-ids="disabled"></svg>' +
    '</div>' +
    '<p class="achievement__badge__text">' +
    escapeHtml(achievement.description) +
    '</p>'
  return div
}

function renderMostRecentSection(data) {
  if (!data.most_recent) {
    return
  }

  const achievement = data.most_recent
  const tabContent = document.querySelector('.tab-content')
  if (!tabContent) {
    return
  }

  const section = document.createElement('div')
  section.id = 'most-recent-achievement'

  const xOutOfYText = trans.xOutOfY
    .replace('__UNLOCKED__', data.unlocked_count)
    .replace('__TOTAL__', data.total_count)

  section.innerHTML =
    '<h2>' +
    escapeHtml(trans.mostRecentTitle) +
    '</h2>' +
    '<div class="mt-4 mb-4 achievement-top__wrapper">' +
    '<div class="achievement__badge">' +
    achievementBadgeHtml(achievement, 'top') +
    '</div>' +
    '<div class="achievement-top__text-wrapper">' +
    '<div>' +
    escapeHtml(achievement.description) +
    '</div>' +
    '<div>' +
    escapeHtml(data.most_recent_unlocked_at || '') +
    '</div>' +
    '<div>' +
    escapeHtml(xOutOfYText) +
    '</div>' +
    '</div>' +
    '</div>'

  tabContent.parentNode.insertBefore(section, tabContent)
}

function updateEmptyStates(data) {
  const noUnlocked = document.getElementById('no-unlocked-achievements')
  if (noUnlocked) {
    if (!data.unlocked || data.unlocked.length === 0) {
      noUnlocked.textContent = trans.unlockedEmpty
      noUnlocked.classList.remove('d-none')
      noUnlocked.classList.add('d-block')
    }
  }

  const noLocked = document.getElementById('no-locked-achievements')
  if (noLocked) {
    if (!data.locked || data.locked.length === 0) {
      noLocked.textContent = trans.lockedEmpty
      noLocked.classList.remove('d-none')
      noLocked.classList.add('d-block')
    }
  }
}

function showNewAchievementAnimation(achievement) {
  Swal.fire({
    html: getNewAchievementAnimationHtml(achievement),
    customClass: {
      htmlContainer: 'popup__new-achievement',
      confirmButton: 'btn btn-primary',
    },
    confirmButtonText: trans.popupConfirm,
    buttonsStyling: false,
    allowOutsideClick: false,
  }).then((result) => {
    if (result.value) {
      markAchievementsAsRead()
      hideAchievementsSidebarBadge()
    }
  })
}

function getNewAchievementAnimationHtml(achievement) {
  return (
    '<svg class="popup__new-achievement__background" ' +
    'data-src="' +
    escapeAttr(popupBackgroundSvg) +
    '" data-unique-ids="disabled">' +
    '<h2 class="h1">' +
    escapeHtml(trans.popupTitle) +
    '</h2>' +
    '<h3>' +
    escapeHtml(trans.popupSubtitle) +
    '</h3>' +
    '<div class="achievement__badge achievement__badge--popup">' +
    achievementBadgeHtml(achievement, 'popup') +
    '</div>'
  )
}

function hideAchievementsSidebarBadge() {
  const badge = document.getElementById('sidebar_badge--unseen-achievements')
  if (badge) {
    badge.style.display = 'none'
  }
}

function markAchievementsAsRead() {
  new ApiFetch(baseUrl + '/api/achievements/read', 'PUT').run().catch((error) => {
    console.error('Failed to mark achievements as read:', error)
  })
}
