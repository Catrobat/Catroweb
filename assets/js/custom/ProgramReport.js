/* eslint-env jquery */
/* global Swal */

// eslint-disable-next-line no-unused-vars
function ProgramReport (programId, reportUrl, loginUrl, reportSentText, errorText,
  reportButtonText, cancelText, reportDialogTitle, reportDialogReason,
  inappropriateLabel, copyrightLabel, spamLabel, dislikeLabel,
  statusCodeOk, loggedIn) {
  const INAPPROPRIATE_VALUE = 'inappropriate'
  const COPYRIGHT_VALUE = 'copyright infringement'
  const SPAM_VALUE = 'spam'
  const DISLIKE_VALUE = 'dislike'
  const CHECKED = 'checked'
  const SESSION_OLD_REPORT_REASON = 'oldReportReason' + programId
  const SESSION_OLD_REPORT_CATEGORY = 'oldReportCategory' + programId

  $('#top-app-bar__btn-report-project').click(function () {
    if (!loggedIn) {
      window.location.href = loginUrl
      return
    }

    let oldReportReason = sessionStorage.getItem(SESSION_OLD_REPORT_REASON)
    if (oldReportReason === null) {
      oldReportReason = ''
    }
    let oldReportCategory = sessionStorage.getItem(SESSION_OLD_REPORT_CATEGORY)
    if (oldReportCategory === null) {
      oldReportCategory = ''
    }
    reportProgramDialog(false, oldReportReason, oldReportCategory)
  })

  function reportProgramDialog (error = false, oldReason = '', oldCategory = '') {
    Swal.fire({
      title: reportDialogTitle,
      html: getReportDialogHtml(error, oldReason, oldCategory),
      focusConfirm: false,
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: reportButtonText,
      cancelButtonText: cancelText,
      preConfirm: function () {
        return new Promise(function (resolve) {
          resolve([
            $('#report-reason').val(),
            $('input[name=report-category]:checked').val()
          ])
        })
      }
    }).then((result) => {
      if (result.value) {
        handleSubmitProgramReport(result.value)
      }
    })
  }

  $(document).on('keyup', '#report-reason', function () {
    sessionStorage.setItem(SESSION_OLD_REPORT_REASON, $('#report-reason').val())
  })
  $(document).on('change', '#report-reason', function () {
    sessionStorage.setItem(SESSION_OLD_REPORT_REASON, $('#report-reason').val())
  })

  $(document).on('change', 'input[name=report-category]', function () {
    sessionStorage.setItem(SESSION_OLD_REPORT_CATEGORY, $('input[name=report-category]:checked').val())
  })

  function handleSubmitProgramReport (result) {
    let reason = result[0]
    let category = result[1]

    if (reason === null || reason === '' || category === null) {
      if (reason === null) {
        reason = ''
      }
      if (category === null) {
        category = ''
      }
      reportProgramDialog(true, reason, category)
    } else {
      reportProgram(reason, category)
    }
  }

  function reportProgram (reason, category) {
    $.post(reportUrl, {
      program: programId,
      category: category,
      note: reason
    }, function (data) {
      if (data.statusCode === statusCodeOk) {
        Swal.fire({
          text: reportSentText,
          icon: 'success',
          confirmButtonClass: 'btn btn-success'
        }).then(function () {
          window.location.href = '/'
        })
      } else {
        Swal.fire({
          title: errorText,
          icon: 'error'
        })
      }
    }).fail(function (XMLHttpRequest, textStatus, errorThrown) {
      Swal.fire({
        title: errorText,
        text: errorThrown,
        icon: 'error'
      })
    })
  }

  function getReportDialogHtml (error, oldReason, oldCategory) {
    let errorClass = ''
    if (error) {
      errorClass = 'text-area-empty'
    }

    let reasonPlaceholder = reportDialogReason
    let reason = ''
    if (oldReason !== '') {
      reasonPlaceholder = ''
      reason = oldReason
    }

    let checkedInappropriate = ''
    let checkedCopyright = ''
    let checkedSpam = ''
    let checkedDislike = ''

    switch (oldCategory) {
      case INAPPROPRIATE_VALUE :
        checkedInappropriate = CHECKED
        break
      case COPYRIGHT_VALUE:
        checkedCopyright = CHECKED
        break
      case SPAM_VALUE:
        checkedSpam = CHECKED
        break
      case DISLIKE_VALUE:
        checkedDislike = CHECKED
        break
      default:
        checkedInappropriate = CHECKED
        break
    }

    return '<div class="text-left">' +
      '<div class="radio-item">' +
      '<input type="radio" id="report-inappropriate" name="report-category" value="' +
      INAPPROPRIATE_VALUE + '" ' + checkedInappropriate + '>' +
      '<label for="report-inappropriate">' + inappropriateLabel + '</label>' +
      '</div>' +
      '<div class="radio-item">' +
      '<input type="radio" id="report-copyright" name="report-category" value="' +
      COPYRIGHT_VALUE + '" ' + checkedCopyright + '>' +
      '<label for="report-copyright">' + copyrightLabel + '</label>' +
      '</div>' +
      '<div class="radio-item">' +
      '<input type="radio" id="report-spam" name="report-category" value="' +
      SPAM_VALUE + '" ' + checkedSpam + '>' +
      '<label for="report-spam">' + spamLabel + '</label>' +
      '</div>' +
      '<div class="radio-item">' +
      '<input type="radio" id="report-dislike" name="report-category" value="' +
      DISLIKE_VALUE + '" ' + checkedDislike + '>' +
      '<label for="report-dislike">' + dislikeLabel + '</label>' +
      '</div>' +
      '</div>' +
      '<textarea class="swal2-textarea mt-4 mb-0 ' + errorClass + '" ' +
      'id="report-reason" placeholder="' + reasonPlaceholder + '">' + reason + '</textarea>'
  }
}
