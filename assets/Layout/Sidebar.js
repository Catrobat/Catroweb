import { ApiFetch } from '../Api/ApiHelper'

import './Sidebar.scss'

const sidebar = document.getElementById('sidebar')
const sidebarJs = document.querySelector('.js-sidebar')

const sidebarToggleBtn = document.getElementById('top-app-bar__btn-sidebar-toggle')

document.addEventListener('DOMContentLoaded', () => {
  initSidebarBadges()
  setClickListener()
  initSidebarSwipe()
})

function initSidebarBadges() {
  if (document.querySelector('.js-user-state').dataset.isUserLoggedIn === 'true') {
    updateBadge(
      sidebarJs.dataset.baseUrl + '/api/notifications/count',
      'sidebar_badge--unseen-notifications',
      'new',
    )

    updateBadge(
      sidebarJs.dataset.pathAchievementsCount,
      'sidebar_badge--unseen-achievements',
      'old',
      sidebarJs.dataset.transAchievementsBadeText,
    )
  }
}

function updateBadge(
  url,
  badgeID,
  apiToCall = 'old',
  badgeText = null,
  maxAmountToFetch = 99,
  refreshRate = 300000, // 5 minutes
) {
  const badge = document.getElementById(badgeID)
  if (!badge) {
    return
  }
  new ApiFetch(url)
    .generateAuthenticatedFetch()
    .then((response) => response.json())
    .then((data) => {
      const count = apiToCall === 'new' ? data.total : data.count
      if (count > 0) {
        if (badgeText === null) {
          badge.innerHTML = count <= maxAmountToFetch ? count.toString() : maxAmountToFetch + '+'
        } else {
          badge.innerHTML = badgeText
        }
        badge.style.display = 'block'
      } else {
        badge.innerHTML = ''
        badge.style.display = 'none'
      }
      setTimeout(
        updateBadge,
        refreshRate,
        url,
        badgeID,
        apiToCall,
        badgeText,
        maxAmountToFetch,
        refreshRate,
      )
    })
    .catch((error) => {
      console.error('Unable to update sidebar badge! Error: ', error)
    })
}

const fnCloseSidebar = function () {
  window.history.back() // to remove pushed state
}
const fnCloseSidebarInternal = function () {
  window.removeEventListener('popstate', fnCloseSidebarInternal)
  sidebar.classList.remove('active')
  sidebarToggleBtn?.setAttribute('aria-expanded', 'false')
}
const fnCloseSidebarDesktop = function () {
  sidebar.classList.add('inactive')
  document.body.classList.remove('body-with-sidebar')
  sidebarToggleBtn?.setAttribute('aria-expanded', 'false')
}
const fnOpenSidebar = function () {
  sidebar.classList.add('active')
  sidebarToggleBtn?.setAttribute('aria-expanded', 'true')
  window.history.pushState('sidebar-open', null, '')
  window.addEventListener('popstate', fnCloseSidebarInternal)
}
const fnOpenSidebarDesktop = function () {
  sidebar.classList.remove('inactive')
  document.body.classList.add('body-with-sidebar')
  sidebarToggleBtn?.setAttribute('aria-expanded', 'true')
}

function setClickListener() {
  if (window.innerWidth >= 768) {
    sidebarToggleBtn?.setAttribute('aria-expanded', 'true')
  }

  sidebarToggleBtn?.addEventListener('click', function () {
    if (window.innerWidth < 768) {
      // mobile mode
      if (sidebar.classList.contains('active')) {
        fnCloseSidebar()
      } else {
        fnOpenSidebar()
      }
    } else {
      // desktop mode
      if (sidebar.classList.contains('inactive')) {
        fnOpenSidebarDesktop()
      } else {
        fnCloseSidebarDesktop()
      }
    }
  })

  document.getElementById('sidebar-overlay').addEventListener('click', fnCloseSidebar)
}

function initSidebarSwipe() {
  const sidebarWidth = sidebar.offsetWidth
  const sidebarOverlay = document.querySelector('#sidebar-overlay')

  let curX = null
  let startTime = null
  let startX = null
  let startY = null

  let opening = false
  let closing = false

  let desktop = false

  const touchThreshold = 25 // area where touch is possible

  function refreshSidebar() {
    const left = curX >= sidebarWidth ? 0 : curX - sidebarWidth
    sidebar.style.transition = 'none'
    sidebar.style.left = `${left}px`
    if (!desktop) {
      const opacity = curX >= sidebarWidth ? 1 : curX / sidebarWidth
      sidebarOverlay.style.transition = 'all 10ms ease-in-out'
      sidebarOverlay.style.display = 'block'
      sidebarOverlay.style.opacity = opacity.toString()
    }
  }

  document.addEventListener('touchstart', function (e) {
    curX = null
    closing = false
    opening = false

    if (e.touches.length === 1) {
      const touch = e.touches[0]

      desktop = window.innerWidth >= 768

      const sidebarOpened =
        (desktop && !sidebar.classList.contains('inactive')) ||
        (!desktop && sidebar.classList.contains('active'))
      if (sidebarOpened) {
        curX = touch.pageX
        startX = touch.pageX
        startY = touch.pageY
        startTime = Date.now()
        closing = true
      } else {
        if (touch.pageX < touchThreshold) {
          curX = touch.pageX
          startX = touch.pageX
          startY = touch.pageY
          startTime = Date.now()
          opening = true
          refreshSidebar()
        }
      }
    }
  })

  document.addEventListener('touchmove', function (e) {
    if (e.touches.length === 1 && (closing || opening) && !!curX) {
      curX = e.touches[0].pageX

      if (closing) {
        const touchY = e.touches[0].pageY
        const yDiff = Math.abs(touchY - startY)
        const xDiff = Math.abs(curX - startX)

        if (xDiff > yDiff * 1.25) {
          refreshSidebar()
        } else {
          reset()
        }
      } else {
        refreshSidebar()
      }
    }
  })

  document.addEventListener('touchend', function (e) {
    if (e.changedTouches.length === 1 && (closing || opening) && !!curX && startTime) {
      const touchX = e.changedTouches[0].pageX
      const touchY = e.changedTouches[0].pageY
      const timeDiff = Date.now() - startTime
      const slow = timeDiff > 100 // 100 ms

      if (closing) {
        if (
          (slow && touchX < sidebarWidth / 2) ||
          (!slow &&
            touchX < sidebarWidth &&
            touchX < startX &&
            Math.abs(startX - touchX) > Math.abs(startY - touchY))
        ) {
          if (desktop) {
            fnCloseSidebarDesktop()
          } else {
            fnCloseSidebar()
          }
        }
      } else if (opening) {
        if (
          (slow && touchX > sidebarWidth / 2) ||
          (!slow &&
            touchX > touchThreshold &&
            touchX > startX &&
            Math.abs(startX - touchX) > Math.abs(startY - touchY))
        ) {
          if (desktop) {
            fnOpenSidebarDesktop()
          } else {
            fnOpenSidebar()
          }
        }
      }
    }

    reset()
  })

  function reset() {
    sidebar.style.left = ''
    sidebar.style.transition = ''
    sidebarOverlay.style.display = ''
    sidebarOverlay.style.opacity = ''
    sidebarOverlay.style.transition = ''
    curX = null
    startTime = null
    startX = null
    startY = null

    opening = false
    closing = false

    desktop = false
  }
}

document.addEventListener('DOMContentLoaded', function () {
  const languageButton = document.querySelector('#btn-language')
  if (!languageButton) {
    return
  }
  const languageMenu = document.querySelector('.language-body')
  const languageMenuOverlay = document.querySelector('.language-body-overlay')

  languageButton.addEventListener('click', function () {
    if (languageMenu.style.display === 'none' || languageMenu.style.display === '') {
      languageMenu.style.display = 'block'
      languageMenuOverlay.style.display = 'block'
      document.body.style.overflow = 'hidden'
    } else {
      languageMenu.style.display = 'none'
      languageMenuOverlay.style.display = 'none'
      document.body.style.overflow = 'auto'
    }
  })
})
