let sound_map = {}
let current_brick_stat_block = null
let translations = {}

function initCodeView(showCode, hideCode, showStats, hideStats)
{
  translations = {
    'showCode' : showCode,
    'hideCode' : hideCode,
    'showStats': showStats,
    'hideStats': hideStats
  }
}

$(document).ready(function() {
  
  $('.collapse-btn').on('click', function() {
    $(this).next().slideToggle(250, 'linear')
    $(this).find('.arrow').toggleClass('rotate')
  })
  
  $(document).on('click', '.show-hide-code', function() {
    let text = $('.show-hide-code-text')
    
    if (text.text() === translations['showCode'])
    {
      $('.show-hide-code-arrow').addClass('rotate showing-code')
      text.text(translations['hideCode'])
      $('#codeview-wrapper').slideDown()
    }
    else
    {
      $('#codeview-wrapper').slideUp(400, function() {
        $('.show-hide-code-arrow').removeClass('rotate showing-code')
        text.text(translations['showCode'])
      })
    }
  })
  
  $(document).on('click', '.show-hide-code-statistic', function() {
    let text = $('.show-hide-code-statistic-text')
    if (text.text() === translations['showStats'])
    {
      $('.show-hide-code-statistic-arrow').addClass('rotate showing-code')
      text.text(translations['hideStats'])
      $('#codestatistic-wrapper').slideDown()
    }
    else
    {
      $('#codestatistic-wrapper').slideUp(400, function() {
        $('.show-hide-code-statistic-arrow').removeClass('rotate showing-code')
        text.text(translations['showStats'])
      })
    }
  })
  
  $(document).on('click', '.brick-statistic-block', function() {
    if (current_brick_stat_block !== null)
    {
      current_brick_stat_block.find('.different-statistic-dropcontent').fadeToggle(150)
      current_brick_stat_block.toggleClass('active')
    }
    
    if ($(this).is(current_brick_stat_block))
    {
      current_brick_stat_block = null
    }
    else
    {
      $(this).find('.different-statistic-dropcontent').fadeToggle(150)
      $(this).toggleClass('active')
      current_brick_stat_block = $(this)
    }
  })
})

function initialSound(file_name, id)
{
  let audio = new Audio(file_name)
  audio.play()
  audio.addEventListener('ended', function() {
    $('#soundStop-' + id).hide()
    $('#sound-' + id).show()
  })
  sound_map[id] = audio
  $('#sound-' + id).hide()
  $('#soundStop-' + id).show()
}

function stopSound(id)
{
  sound_map[id].pause()
  sound_map[id].currentTime = 0
  $('#soundStop-' + id).hide()
  $('#sound-' + id).show()
}

