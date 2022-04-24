/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
function AdminBroadcastNotification () {
  $('.btn').click(function () {
    const resultBox = $('.resultBox')
    resultBox.html('')

    const message = $('#msg').val()
    $.ajax({
      url: 'send',
      type: 'get', // send it through get method
      data: { Message: message },
      success: function (data) {
        if (data === 'OK') {
          resultBox.removeClass('error')
          resultBox.addClass('success')
        } else {
          resultBox.removeClass('success')
          resultBox.addClass('error')
        }
        $('.resultBox').html(data)
        // Do Something
      }
    })
  })
}
