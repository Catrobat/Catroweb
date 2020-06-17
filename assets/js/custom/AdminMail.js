/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
function AdminMail () {
  $('.btn').click(function () {
    const username = $('#username').val()
    const subject = $('#subject').val()
    const message = $('#content').val()
    const resultBox = $('.resultBox')

    // calls _/Controller/Admin/EmailUserMessageController::sendAction
    $.get('send', { username: username, subject: subject, message: message }, function (data) {
      if (data && data.length >= 2 && data.substring(0, 2) === 'OK') {
        resultBox.switchClass('error', 'success')
      } else {
        resultBox.switchClass('success', 'error')
      }
      resultBox.html(data)
    })
  })
}
