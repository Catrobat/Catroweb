/* eslint-env jquery */

$(document).ready(function () {
  $('.collapse-btn').on('click', function () {
    $(this).next().slideToggle(250, 'linear')
    $(this).find('.arrow').toggleClass('rotate')
  })
})
