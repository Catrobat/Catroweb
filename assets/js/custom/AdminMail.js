/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
function AdminMail () {
  $('.btn').click(function () {
    const username = $('#username').val()
    const subject = $('#subject').val()
    const message = $('#content').val()
    const resultBox = $('.resultBox')

    // calls _/Controller/Admin/Tools/SendMailToUserController::sendAction
    $.get('send', { username, subject, message }, function (data) {
      if (data && data.length >= 2 && data.substring(0, 2) === 'OK') {
        resultBox.removeClass('error')
        resultBox.addClass('success')
      } else {
        resultBox.removeClass('success')
        resultBox.addClass('error')
      }
      resultBox.html(data)
    })
  })
}
