import $ from 'jquery'

$('.js-redirect-btn').on('click', (e) => {
  redirect(
    $(e.currentTarget).data('url'),
    $(e.currentTarget).data('spinner'),
    $(e.currentTarget).data('icon')
  )
})

function redirect (url, spinner, icon = null) {
  const buttonSpinner = $(spinner)
  if (icon) {
    const buttonIcon = $(icon)
    buttonIcon.hide()
  }
  buttonSpinner.removeClass('d-none')
  window.location.href = url
}
