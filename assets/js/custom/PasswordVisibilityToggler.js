/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
$(document).ready(function () {
  const toggleButton = $('.show-hide-password .pw-toggler')

  toggleButton.on('click', function (event) {
    event.preventDefault()
    togglePwVisibility(toggleButton)
  })
})

function togglePwVisibility (element) {
  const passwordField = element.closest('.show-hide-password').find('input')
  if (passwordField.attr('type') === 'text') {
    passwordField.attr('type', 'password')
    element.text('visibility')
  } else if (passwordField.attr('type') === 'password') {
    passwordField.attr('type', 'text')
    element.text('visibility_off')
  }
}
