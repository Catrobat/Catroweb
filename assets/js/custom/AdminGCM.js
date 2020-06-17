/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
function AdminGCM () {
  $('.btn').click(function () {
    $('.resultBox').html('')

    var apikey = $('.apikey').val()
    var message = $('.msg').val()

    if (apikey.length === 0) {
      apikey = $('.apikey').attr('placeholder')
    }

    $.get('send?a=' + apikey + '&m=' + message, function (data) {
      if (data === 'OK') {
        $('.resultBox').switchClass('error', 'success')
      } else {
        $('.resultBox').switchClass('success', 'error')
      }

      $('.resultBox').html(data)
    })
  })
}
