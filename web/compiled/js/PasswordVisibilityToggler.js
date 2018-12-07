/*
  Generated File by Grunt
  Sourcepath: web/js
*/
$(document).ready(function () {
  
  let toggleButton = $('.show-hide-password a')
  let toggleButtonIcon = $('.show-hide-password i')
  let passwordField = $('.show-hide-password input')
  
  toggleButton.on('click', function (event) {
    event.preventDefault()
    if (passwordField.attr('type') === 'text')
    {
      passwordField.attr('type', 'password')
      toggleButtonIcon.addClass('fa-eye-slash')
      toggleButtonIcon.removeClass('fa-eye')
    }
    else if (passwordField.attr('type') === 'password')
    {
      passwordField.attr('type', 'text')
      toggleButtonIcon.removeClass('fa-eye-slash')
      toggleButtonIcon.addClass('fa-eye')
    }
  })
})
