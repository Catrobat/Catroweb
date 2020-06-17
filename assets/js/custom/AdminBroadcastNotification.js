/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
function AdminBroadcastNotification () {
  $('.btn').click(function () {
    $('.resultBox').html('')

    var message = $('#msg').val()
    var title = $('#title').val()

    $.ajax({
      url: 'send',
      type: 'get', // send it through get method
      data: { Title: title, Message: message },
      success: function (data) {
        if (data === 'OK') {
          $('.resultBox').switchClass('error', 'success')
        } else {
          $('.resultBox').switchClass('success', 'error')
        }
        $('.resultBox').html(data)
        // Do Something
      }
    })
  })
}
