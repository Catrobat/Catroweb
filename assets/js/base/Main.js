/* eslint-env jquery */
/* global TextFill */

// eslint-disable-next-line no-unused-vars
const Main = function () {
  const self = this

  function scrollToHash () {
    if (window.location.hash && $(window.location.hash).offset()) {
      window.scrollTo(0, ($(window.location.hash).offset().top - $('.navbar').outerHeight()))
    }
  }

  scrollToHash()
  window.addEventListener('load', scrollToHash)
  $(window).on('hashchange', scrollToHash)

  $(window).ready(function () {
    self.setClickListener()
    self.initSidebarSwipe()
    self.setLanguageSwitchListener()
  })

  // ---- Language Selection
  self.setLanguageSwitchListener = function () {
    $('#switch-language').on('change', function () {
      document.cookie = 'hl=' + $(this).val() + '; path=/'
      window.location.reload()
    })
  }

  // ---- History State
  window.addEventListener('popstate', function (event) {
    if (event.state != null) {
      if (event.state.type === 'ProjectList' && event.state.full === true) {
        $('#' + event.state.id).data('list').openFullView()
      }
    }
  })

  // ----SideBar
  let sidebar, sidebarToggleBtn
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
    $('body').removeClass('new-nav')
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
    $('body').addClass('new-nav')
    sidebarToggleBtn.attr('aria-expanded', true)
  }

  self.setClickListener = function () {
    sidebar = $('#sidebar')
    sidebarToggleBtn = $('#top-app-bar__btn-sidebar-toggle')

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

    // sidebar.find('a.nav-link').on("click", fnCloseSidebar);
    $('#sidebar-overlay').on('click', fnCloseSidebar)
  }

  self.initSidebarSwipe = function () {
    const sidebar = $('#sidebar')
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

    function refreshSidebar () {
      const left = (curX >= sidebarWidth) ? 0 : curX - sidebarWidth
      sidebar.css('transition', 'none').css('left', left)
      if (!desktop) {
        const opacity = (curX >= sidebarWidth) ? 1 : curX / sidebarWidth
        sidebarOverlay.css('transition', 'all 10ms ease-in-out').css('display', 'block').css('opacity', opacity)
      }
    }

    document.addEventListener('touchstart', function (e) {
      curX = null
      closing = false
      opening = false

      if (e.touches.length === 1) {
        const touch = e.touches[0]

        desktop = $(window).width() >= 768

        const sidebarOpened = (desktop && !sidebar.hasClass('inactive')) || (!desktop && sidebar.hasClass('active'))
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
            (!slow && touchX < sidebarWidth && touchX < startX && Math.abs(startX - touchX) > Math.abs(startY - touchY))
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
            (!slow && touchX > touchThreshold && touchX > startX && Math.abs(startX - touchX) > Math.abs(startY - touchY))
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

    function reset () {
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
}

$(function () {
  // -------------------------------------------------------------------------------------------------------------------
  // Adjust heading font size or break word
  ['h1', '.h1', 'h2', '.h2', 'h3', '.h3'].forEach(function (element) {
    $(element + ':not(.no-textfill)').each(function () {
      textfillDefault(this)
    })
  })

  function textfillDefault (container) {
    const maxFontPixels = parseFloat($(container).css('font-size'))
    const minFontPixels = Math.round(maxFontPixels * 0.7)

    const html = $(container).html()
    $(container).empty()
    const $span = $('<span/>').html(html)
    $(container).append($span)

    TextFill(container, {
      maxFontPixels: maxFontPixels,
      minFontPixels: minFontPixels,
      widthOnly: true,
      innerTag: 'span',
      fail: function () {
        $(container).addClass('force-word-break')
        $(container).html(html)
      },
      success: function () {
        $(container).removeClass('force-word-break')
        const newFontSize = $span.css('font-size')
        $(container).html(html)
        if (parseFloat(newFontSize) < maxFontPixels) {
          $(container).css('font-size', newFontSize)
        }
      }
    })
  }
})
