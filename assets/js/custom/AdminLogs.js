/* eslint-env jquery */
/* global lineNumber */

// eslint-disable-next-line no-unused-vars
function AdminLogs () {
  $(document).on('click', '.files', function () {
    $('.logs').hide()
    $('.' + $(this).attr('id')).show()
  })

  $(document).on('click', '#search', function () {
    $('#searchIcon').show()
    $.ajax({
      type: 'get',
      data: {
        count: lineNumber.value,
        filter: $('#logLevelSelect').val(),
        greaterThan: $('.greaterThanRB:checked').val()
      },
      success: function (data) {
        $('#searchIcon').hide()
        var innerLogContainerString = $('<div>', { html: data }).find('#innerLogContainer')
        $('#outerLogContainer').html(innerLogContainerString)
      },
      error: function (data) {
        $('#searchIcon').hide()
        alert('something went terribly wrong')
      }
    })
  })
}
