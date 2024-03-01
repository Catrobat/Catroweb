import $ from 'jquery'
import { ApiFetch } from '../api/ApiHelper'

require('../../styles/layout/sidebar.scss')

const sidebar = $('#sidebar')
const sidebarJs = $('.js-sidebar')

const sidebarToggleBtn = $('#top-app-bar__btn-sidebar-toggle')

$(() => {
  initSidebarBadges()
  setClickListener()
  initSidebarSwipe()
})
function initSidebarBadges() {
  if ($('.js-user-state').data('is-user-logged-in')) {
    updateBadge(
      sidebarJs.data('base-url') + '/api/notifications/count',
      'sidebar_badge--unseen-notifications',
      'new',
    )

    updateBadge(
      sidebarJs.data('path-achievements-count'),
      'sidebar_badge--unseen-achievements',
      'old',
      sidebarJs.data('trans-achievements-bade-text'),
    )
  }
}

function updateBadge(
  url,
  badgeID,
  apiToCall = 'old',
  badgeText = null,
  maxAmountToFetch = 99,
  refreshRate = 10000,
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
          badge.innerHTML =
            count <= maxAmountToFetch
              ? count.toString()
              : maxAmountToFetch + '+'
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
  $(window).off('popstate', fnCloseSidebarInternal)
  sidebar.removeClass('active')
  sidebarToggleBtn.attr('aria-expanded', false)
}
const fnCloseSidebarDesktop = function () {
  sidebar.addClass('inactive')
  $('body').removeClass('body-with-sidebar')
  sidebarToggleBtn.attr('aria-expanded', false)
}
const fnOpenSidebar = function () {
  sidebar.addClass('active')
  sidebarToggleBtn.attr('aria-expanded', true)
  window.history.pushState('sidebar-open', null, '')
  $(window).on('popstate', fnCloseSidebarInternal)
}
const fnOpenSidebarDesktop = function () {
  sidebar.removeClass('inactive')
  $('body').addClass('body-with-sidebar')
  sidebarToggleBtn.attr('aria-expanded', true)
}

function setClickListener() {
  if ($(window).width() >= 768) {
    sidebarToggleBtn.attr('aria-expanded', true)
  }

  sidebarToggleBtn.on('click', function () {
    if ($(window).width() < 768) {
      // mobile mode
      if (sidebar.hasClass('active')) {
        fnCloseSidebar()
      } else {
        fnOpenSidebar()
      }
    } else {
      // desktop mode
      if (sidebar.hasClass('inactive')) {
        fnOpenSidebarDesktop()
      } else {
        fnCloseSidebarDesktop()
      }
    }
  })

  $('#sidebar-overlay').on('click', fnCloseSidebar)
}

function initSidebarSwipe() {
  const sidebarWidth = sidebar.width()
  const sidebarOverlay = $('#sidebar-overlay')

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
    sidebar.css('transition', 'none').css('left', left)
    if (!desktop) {
      const opacity = curX >= sidebarWidth ? 1 : curX / sidebarWidth
      sidebarOverlay
        .css('transition', 'all 10ms ease-in-out')
        .css('display', 'block')
        .css('opacity', opacity)
    }
  }

  document.addEventListener('touchstart', function (e) {
    curX = null
    closing = false
    opening = false

    if (e.touches.length === 1) {
      const touch = e.touches[0]

      desktop = $(window).width() >= 768

      const sidebarOpened =
        (desktop && !sidebar.hasClass('inactive')) ||
        (!desktop && sidebar.hasClass('active'))
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
    if (
      e.changedTouches.length === 1 &&
      (closing || opening) &&
      !!curX &&
      startTime
    ) {
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
    sidebar.css('left', '').css('transition', '')
    sidebarOverlay.css('display', '').css('opacity', '').css('transition', '')
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
  const languageMenu = document.querySelector('.language-body')
  const languageMenuOverlay = document.querySelector('.language-body-overlay')

  languageButton.addEventListener('click', function () {
    if (
      languageMenu.style.display === 'none' ||
      languageMenu.style.display === ''
    ) {
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
