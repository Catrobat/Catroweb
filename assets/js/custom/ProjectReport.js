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

  document
    .getElementById('top-app-bar__btn-report-project')
    .addEventListener('click', function () {
      if (!loggedIn) {
        window.location.href = loginUrl
        return
      }

      const oldReportReason =
        sessionStorage.getItem(SESSION_OLD_REPORT_REASON) || ''
      const oldReportCategory =
        sessionStorage.getItem(SESSION_OLD_REPORT_CATEGORY) || ''
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
            document.getElementById('report-reason').value,
            document.querySelector('input[name="report-category"]:checked')
              .value,
          ])
        })
      },
    }).then((result) => {
      if (result.value) {
        handleSubmitProgramReport(result.value)
      }
    })
  }

  document.addEventListener('keyup', function (event) {
    if (event.target && event.target.id === 'report-reason') {
      sessionStorage.setItem(SESSION_OLD_REPORT_REASON, event.target.value)
    }
  })

  document.addEventListener('change', function (event) {
    if (event.target && event.target.id === 'report-reason') {
      sessionStorage.setItem(SESSION_OLD_REPORT_REASON, event.target.value)
    }
    if (event.target && event.target.name === 'report-category') {
      sessionStorage.setItem(
        SESSION_OLD_REPORT_CATEGORY,
        document.querySelector('input[name="report-category"]:checked').value,
      )
    }
  })

  function handleSubmitProgramReport(result) {
    const reason = result[0]
    const category = result[1]

    if (reason === null || reason === '' || category === null) {
      reportProgramDialog(true, reason || '', category || '')
    } else {
      reportProgram(reason, category)
    }
  }

  function reportProgram(reason, category) {
    fetch(reportUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        program: programId,
        category,
        note: reason,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.statusCode === statusCodeOk) {
          Swal.fire({
            text: reportSentText,
            icon: 'success',
            customClass: {
              confirmButton: 'btn btn-primary',
            },
            buttonsStyling: false,
            allowOutsideClick: false,
          }).then(() => {
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
      })
      .catch((error) => {
        Swal.fire({
          title: errorText,
          text: error.message,
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
    const errorClass = error ? 'text-area-empty' : ''
    const reasonPlaceholder = oldReason ? '' : reportDialogReason

    const checkedInappropriate =
      oldCategory === INAPPROPRIATE_VALUE ? CHECKED : ''
    const checkedCopyright = oldCategory === COPYRIGHT_VALUE ? CHECKED : ''
    const checkedSpam = oldCategory === SPAM_VALUE ? CHECKED : ''
    const checkedDislike = oldCategory === DISLIKE_VALUE ? CHECKED : ''

    return `
      <div class="mdc-form-field">
        <div class="mdc-radio">
          <input class="mdc-radio__native-control" type="radio" style="--checked-stroke-color(red)" id="report-inappropriate" name="report-category" value="${INAPPROPRIATE_VALUE}" ${checkedInappropriate}>
          <div class="mdc-radio__background">
            <div class="mdc-radio__outer-circle"></div>
            <div class="mdc-radio__inner-circle"></div>
          </div>
          <div class="mdc-radio__ripple"></div>
        </div>
        <label for="report-inappropriate">${inappropriateLabel}</label>
      </div>
      <div class="mdc-form-field">
        <div class="mdc-radio">
          <input class="mdc-radio__native-control" type="radio" id="report-copyright" name="report-category" value="${COPYRIGHT_VALUE}" ${checkedCopyright}>
          <div class="mdc-radio__background">
            <div class="mdc-radio__outer-circle"></div>
            <div class="mdc-radio__inner-circle"></div>
          </div>
          <div class="mdc-radio__ripple"></div>
        </div>
        <label for="report-copyright">${copyrightLabel}</label>
      </div>
      <div class="mdc-form-field">
        <div class="mdc-radio">
          <input class="mdc-radio__native-control" type="radio" id="report-spam" name="report-category" value="${SPAM_VALUE}" ${checkedSpam}>
          <div class="mdc-radio__background">
            <div class="mdc-radio__outer-circle"></div>
            <div class="mdc-radio__inner-circle"></div>
          </div>
          <div class="mdc-radio__ripple"></div>
        </div>
        <label for="report-spam">${spamLabel}</label>
      </div>
      <div class="mdc-form-field">
        <div class="mdc-radio">
          <input class="mdc-radio__native-control" type="radio" id="report-dislike" name="report-category" value="${DISLIKE_VALUE}" ${checkedDislike}>
          <div class="mdc-radio__background">
            <div class="mdc-radio__outer-circle"></div>
            <div class="mdc-radio__inner-circle"></div>
          </div>
          <div class="mdc-radio__ripple"></div>
        </div>
        <label for="report-dislike">${dislikeLabel}</label>
      </div>
      <label class="mdc-text-field mdc-text-field--outlined mdc-text-field--textarea report-reason">
        <span class="mdc-text-field__resizer">
          <textarea class="mdc-text-field__input ${errorClass}" id="report-reason" placeholder="${reasonPlaceholder}" style="width: 100%; height: 100px" cols="100">${oldReason}</textarea>
        </span>
        <span class="mdc-notched-outline">
          <span class="mdc-notched-outline__leading"></span>
          <span class="mdc-notched-outline__trailing"></span>
        </span>
      </label>
    `
  }
}
