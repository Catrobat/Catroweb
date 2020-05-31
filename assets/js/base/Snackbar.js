/* eslint-env jquery */

const SnackbarDuration = {
  short: 2000,
  long: 3500
}

// eslint-disable-next-line no-unused-vars
function showSnackbar (id, text = '', duration = SnackbarDuration.short) {
  const snackbar = $(id)
  const snackbarLabel = $(id + '-label')

  // When multiple snackbar updates are necessary, they should appear one at a time
  const visibleSnacks = $('.mdc-snackbar:visible').length
  if (visibleSnacks > 0) {
    window.setTimeout(function () {
      showSnackbar(id, text)
    }, 250)
    return
  }

  snackbarLabel.text(text)
  snackbar.show()
  snackbar.css('opacity', '1')
  snackbar.children().css('opacity', '1')
  snackbar.delay(duration).fadeOut(400)
}
