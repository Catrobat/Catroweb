/* eslint-env jquery */

let currentBrickStatBlock = null
let translations = {}

// eslint-disable-next-line no-unused-vars
function initCodeView (showCode, hideCode, showStats, hideStats) {
  translations = {
    showCode: showCode,
    hideCode: hideCode,
    showStats: showStats,
    hideStats: hideStats
  }
  collapseCodeStatistics()
  collapseCodeView()
}

$(document).ready(function () {
  $('.collapse-btn').on('click', function () {
    $(this).next().slideToggle(250, 'linear')
    $(this).find('.arrow').toggleClass('rotate')
  })

  $(document).on('click', '.show-hide-code', function () {
    if ($('.show-hide-code-arrow').hasClass('rotate showing-code')) {
      $('#codeview-wrapper').slideUp(400, function () {
        collapseCodeView()
      })
    } else {
      expandCodeView()
      $('#codeview-wrapper').slideDown()
    }
  })

  $(document).on('click', '.show-hide-code-statistic', function () {
    if ($('.show-hide-code-statistic-arrow').hasClass('rotate showing-code')) {
      $('#codestatistic-wrapper').slideUp(400, function () {
        collapseCodeStatistics()
      })
    } else {
      expandCodeStatistics()
      $('#codestatistic-wrapper').slideDown()
    }
  })

  $(document).on('click', '.brick-statistic-block', function () {
    if (currentBrickStatBlock !== null) {
      currentBrickStatBlock.find('.different-statistic-dropcontent').fadeToggle(150)
      currentBrickStatBlock.toggleClass('active')
    }

    if ($(this).is(currentBrickStatBlock)) {
      currentBrickStatBlock = null
    } else {
      $(this).find('.different-statistic-dropcontent').fadeToggle(150)
      $(this).toggleClass('active')
      currentBrickStatBlock = $(this)
    }
  })
})

function expandCodeStatistics () {
  $('.show-hide-code-statistic-text').text(translations.hideStats)
  $('.show-hide-code-statistic-arrow').addClass('rotate showing-code')
}

function collapseCodeStatistics () {
  $('.show-hide-code-statistic-text').text(translations.showStats)
  $('.show-hide-code-statistic-arrow').removeClass('rotate showing-code')
}

function expandCodeView () {
  $('.show-hide-code-text').text(translations.hideCode)
  $('.show-hide-code-arrow').addClass('rotate showing-code')
}

function collapseCodeView () {
  $('.show-hide-code-text').text(translations.showCode)
  $('.show-hide-code-arrow').removeClass('rotate showing-code')
}
