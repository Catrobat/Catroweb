import $ from 'jquery'
import Swal from 'sweetalert2'

export function ProjectReport(
  programId,
  reportUrl,
  loginUrl,
  reportSentText,
  errorText,
  reportButtonText,
  cancelText,
  reportDialogTitle,
  reportDialogReason,
  inappropriateLabel,
  copyrightLabel,
  spamLabel,
  dislikeLabel,
  statusCodeOk,
  loggedIn,
) {
  const INAPPROPRIATE_VALUE = 'inappropriate'
  const COPYRIGHT_VALUE = 'copyright infringement'
  const SPAM_VALUE = 'spam'
  const DISLIKE_VALUE = 'dislike'
  const CHECKED = 'checked'
  const SESSION_OLD_REPORT_REASON = 'oldReportReason' + programId
  const SESSION_OLD_REPORT_CATEGORY = 'oldReportCategory' + programId

  $('#top-app-bar__btn-report-project').on('click', function () {
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

  function reportProgramDialog(
    error = false,
    oldReason = '',
    oldCategory = '',
  ) {
    Swal.fire({
      title: reportDialogTitle,
      html: getReportDialogHtml(error, oldReason, oldCategory),
      focusConfirm: false,
      showCancelButton: true,
      allowOutsideClick: false,
      customClass: {
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-outline-primary',
      },
      buttonsStyling: false,
      confirmButtonText: reportButtonText,
      cancelButtonText: cancelText,
      preConfirm: function () {
        return new Promise(function (resolve) {
          resolve([
            $('#report-reason').val(),
            $('input[name=report-category]:checked').val(),
          ])
        })
      },
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
    sessionStorage.setItem(
      SESSION_OLD_REPORT_CATEGORY,
      $('input[name=report-category]:checked').val(),
    )
  })

  function handleSubmitProgramReport(result) {
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

  function reportProgram(reason, category) {
    $.post(
      reportUrl,
      {
        program: programId,
        category,
        note: reason,
      },
      function (data) {
        if (data.statusCode === statusCodeOk) {
          Swal.fire({
            text: reportSentText,
            icon: 'success',
            customClass: {
              confirmButton: 'btn btn-primary',
            },
            buttonsStyling: false,
            allowOutsideClick: false,
          }).then(function () {
            window.location.href = '/'
          })
        } else {
          Swal.fire({
            title: errorText,
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-primary',
            },
            buttonsStyling: false,
            allowOutsideClick: false,
          })
        }
      },
    ).fail(function (XMLHttpRequest, textStatus, errorThrown) {
      Swal.fire({
        title: errorText,
        text: errorThrown,
        icon: 'error',
        customClass: {
          confirmButton: 'btn btn-primary',
        },
        buttonsStyling: false,
        allowOutsideClick: false,
      })
    })
  }

  function getReportDialogHtml(error, oldReason, oldCategory) {
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
      case INAPPROPRIATE_VALUE:
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

    return (
      '<div class="mdc-form-field">' +
      ' <div class="mdc-radio">' +
      '   <input class="mdc-radio__native-control" type="radio" style="--checked-stroke-color(red)" id="report-inappropriate" name="report-category" value="' +
      INAPPROPRIATE_VALUE +
      '" ' +
      checkedInappropriate +
      '>' +
      '   <div class="mdc-radio__background">' +
      '     <div class="mdc-radio__outer-circle"></div>' +
      '     <div class="mdc-radio__inner-circle"></div>' +
      '   </div>' +
      '   <div class="mdc-radio__ripple"></div>' +
      ' </div>' +
      ' <label for="report-inappropriate"">' +
      inappropriateLabel +
      '</label>' +
      '</div>' +
      '<div class="mdc-form-field">' +
      ' <div class="mdc-radio">' +
      '   <input class="mdc-radio__native-control" type="radio" id="report-copyright" name="report-category" value="' +
      COPYRIGHT_VALUE +
      '" ' +
      checkedCopyright +
      '>' +
      '   <div class="mdc-radio__background">' +
      '     <div class="mdc-radio__outer-circle"></div>' +
      '     <div class="mdc-radio__inner-circle"></div>' +
      '   </div>' +
      '   <div class="mdc-radio__ripple"></div>' +
      ' </div>' +
      ' <label for="report-copyright">' +
      copyrightLabel +
      '</label>' +
      '</div>' +
      '<div class="mdc-form-field">' +
      ' <div class="mdc-radio">' +
      '   <input class="mdc-radio__native-control" type="radio" id="report-spam" name="report-category" value="' +
      SPAM_VALUE +
      '" ' +
      checkedSpam +
      '>' +
      '   <div class="mdc-radio__background">' +
      '     <div class="mdc-radio__outer-circle"></div>' +
      '     <div class="mdc-radio__inner-circle"></div>' +
      '   </div>' +
      '   <div class="mdc-radio__ripple"></div>' +
      ' </div>' +
      ' <label for="report-spam">' +
      spamLabel +
      '</label>' +
      '</div>' +
      '<div class="mdc-form-field">' +
      ' <div class="mdc-radio">' +
      '  <input class="mdc-radio__native-control" type="radio" id="report-dislike" name="report-category" value="' +
      DISLIKE_VALUE +
      '" ' +
      checkedDislike +
      '>' +
      '  <div class="mdc-radio__background">' +
      '    <div class="mdc-radio__outer-circle"></div>' +
      '    <div class="mdc-radio__inner-circle"></div>' +
      '  </div>' +
      '  <div class="mdc-radio__ripple"></div>' +
      ' </div>' +
      ' <label for="report-dislike">' +
      dislikeLabel +
      '</label>' +
      '</div>' +
      '<label class="mdc-text-field mdc-text-field--outlined mdc-text-field--textarea report-reason">' +
      ' <span class="mdc-text-field__resizer">' +
      '   <textarea class="mdc-text-field__input ' +
      errorClass +
      '" id="report-reason" placeholder="' +
      reasonPlaceholder +
      '" style="width: 100%; height: 100px" cols="100">' +
      reason +
      '</textarea>' +
      ' </span>' +
      ' <span class="mdc-notched-outline">' +
      '   <span class="mdc-notched-outline__leading"></span>' +
      '   <span class="mdc-notched-outline__trailing"></span>' +
      ' </span>' +
      '</label>'
    )
  }
}
