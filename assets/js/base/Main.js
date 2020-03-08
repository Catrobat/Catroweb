/* eslint-env jquery */
/* global TextFill */

// eslint-disable-next-line no-unused-vars
const Main = function (searchUrl) {
  const self = this
  self.searchUrl = searchUrl.replace(0, '')

  $(window).ready(function () {
    self.setClickListener()
    self.setWindowResizeListener()
    self.initSidebarSwipe()
  })

  let sidebar, sidebarToggleBtn
  const fnCloseSidebar = function () {
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
  }
  const fnOpenSidebarDesktop = function () {
    sidebar.removeClass('inactive')
    $('body').addClass('new-nav')
    sidebarToggleBtn.attr('aria-expanded', true)
  }

  self.setClickListener = function () {
    sidebar = $('#sidebar')
    sidebarToggleBtn = $('#btn-sidebar-toggle')

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

    self.setSearchBtnListener()
    self.setLanguageSwitchListener()
  }

  self.setWindowResizeListener = function () {
    $(window).resize(function () {
      $('#nav-dropdown').hide()
    })
  }

  self.setSearchBtnListener = function () {
    // search enter pressed
    $('input.input-search').keypress(function (event) {
      if (event.which === 13) {
        const searchTerm = $(this).val()
        if (!searchTerm) {
          $(this).tooltip('show')
          return
        }
        self.searchPrograms(searchTerm)
      }
    })

    // search button clicked (header)
    $('.btn-search').click(function () {
      const searchField = $(this).parent().parent().find('input.input-search')
      const searchTerm = searchField.val()
      if (!searchTerm) {
        searchField.tooltip('show')
        return
      }
      self.searchPrograms(searchTerm)
    })
  }

  self.searchPrograms = function (string) {
    window.location.href = self.searchUrl + encodeURIComponent(string.trim())
  }

  self.setLanguageSwitchListener = function () {
    const select = $('#switch-language')
    select.change(function () {
      document.cookie = 'hl=' + $(this).val() + '; path=/'
      location.reload()
    })
  }

  self.getCookie = function (cname) {
    const name = cname + '='
    const ca = document.cookie.split(';')
    for (let i = 0; i < ca.length; i++) {
      let c = ca[i]
      while (c.charAt(0) === ' ') {
        c = c.substring(1)
      }
      if (c.indexOf(name) !== -1) {
        return c.substring(name.length, c.length)
      }
    }
    return ''
  }

  self.initSidebarSwipe = function () {
    const sidebar = $('#sidebar')
    const sidebarWidth = sidebar.width()
    const sidebarOverlay = $('#sidebar-overlay')

    let curX = null
    let startTime = null
    let startX = null; let startY = null

    let opening = false
    let closing = false

    let desktop = false

    const touchThreshold = 25 // area where touch is possible

    function refrehSidebar () {
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
            refrehSidebar()
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
            refrehSidebar()
          } else {
            reset()
          }
        } else {
          refrehSidebar()
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

  // -------------------------------------------------------------------------------------------------------------------
  // Search field
  const searchInput = $('.input-search')

  searchInput.tooltip({
    trigger: 'manual',
    placement: 'bottom'
  })

  searchInput.on('shown.bs.tooltip', function () {
    setTimeout(function () {
      searchInput.tooltip('hide')
    }, 1000)
  })
})
