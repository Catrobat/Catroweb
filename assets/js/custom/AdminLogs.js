/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
function AdminLogs () {
  $(document).on('click', '.line-head', function () {
    $(this).parent('.panel-heading').siblings('.panel-collapse').toggleClass('hide')
  })

  $(document).on('click', '#search', function () {
    loadFileContent($('#currentFile').val(), $('#logLevelSelect').val(), $('#lineNumber').val(), $('.greaterThanRB:checked').val())
  })
  $(document).on('click', '.files', function () {
    loadFileContent($(this).val(), $('#logLevelSelect').val(), $('#lineNumber').val(), $('.greaterThanRB:checked').val())
    $('#currentFile').val($(this).val())
  })
}

function loadFileContent (file, filter, count, greaterThan) {
  $('#loading-spinner').show()
  $('#innerLogContainer').html('')
  $.ajax({
    type: 'get',
    data: {
      file,
      filter,
      count,
      greaterThan
    },
    success: function (data) {
      $('#loading-spinner').hide()
      const innerLogContainerString = $('<div>', { html: data }).find('#innerLogContainer')
      $('#outerLogContainer').html(innerLogContainerString)
    },
    error: function () {
      $('#loading-spinner').hide()
      alert('something went terribly wrong')
    }
  })
}
