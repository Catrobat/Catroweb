/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
$(document).ready(function () {
  const toggleButton = $('.show-hide-password .pw-toggler')
  const passwordField = $('.show-hide-password input')

  toggleButton.on('click', function (event) {
    event.preventDefault()
    if (passwordField.attr('type') === 'text') {
      passwordField.attr('type', 'password')
      $(this).text('visibility')
    } else if (passwordField.attr('type') === 'password') {
      passwordField.attr('type', 'text')
      $(this).text('visibility_off')
    }
  })
})
