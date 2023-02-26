/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
function AdminMail () {
  $('.btn-send').click(function () {
    const username = $('#username').val()
    const subject = $('#subject').val()
    const titel = $('titel').val()
    const message = $('#content').val()
    const resultBox = $('.resultBox')

    // calls _/Controller/Admin/Tools/SendMailToUserController::sendAction
    $.get('send', { username, subject, titel, message }, function (data) {
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

  // calls _/Controller/Admin/Tools/SendMailToUserController::previewAction
  $('.btn-preview').click(function () {
    const username = $('#username').val()
    const subject = $('#subject').val()
    const titel = $('#titel').val()
    const message = $('#content').val()
    const template = $('#template-select').val()
    const url = 'preview?username=' + username + '&subject=' + subject + '&titel=' + titel + '&message=' + message + '&template=' + template
    window.open(url, '_blank')
  })
}
