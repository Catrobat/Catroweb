import Swal from 'sweetalert2'
import { handleAccountState403 } from '../Security/AccountStateErrorHandler'
import { escapeHtml, escapeAttr } from '../Components/HtmlEscape'
import { REPORT_CATEGORIES } from './ReportCategories'

const SESSION_KEY_PREFIX = 'pendingReport_'

export function showReportDialog({
  contentType,
  contentId,
  apiUrl,
  loginUrl,
  isLoggedIn,
  translations,
}) {
  if (!isLoggedIn) {
    sessionStorage.setItem(
      'pendingAction',
      JSON.stringify({
        contentType,
        contentId,
        actionType: 'report',
      }),
    )
    window.location.href = loginUrl
    return
  }

  const categories = REPORT_CATEGORIES[contentType] || []
  const sessionKey = SESSION_KEY_PREFIX + contentType + '_' + contentId
  const oldData = JSON.parse(sessionStorage.getItem(sessionKey) || '{}')

  Swal.fire({
    title: translations.title || 'Report',
    html: buildReportHtml(categories, translations, oldData),
    focusConfirm: false,
    showCancelButton: true,
    allowOutsideClick: false,
    customClass: {
      confirmButton: 'btn btn-primary',
      cancelButton: 'btn btn-outline-primary',
    },
    buttonsStyling: false,
    confirmButtonText: translations.submit || 'Submit Report',
    cancelButtonText: translations.cancel || 'Cancel',
    preConfirm: () => {
      const checked = document.querySelector('input[name="report-category"]:checked')
      const note = document.getElementById('report-note')?.value || ''

      if (!checked) {
        Swal.showValidationMessage('Please select a category')
        return false
      }

      return submitReport(apiUrl, { category: checked.value, note }, translations, sessionKey)
    },
    didOpen: (popup) => {
      // Persist form state in session (scoped to popup to avoid listener leak)
      popup.addEventListener('input', (e) => {
        if (e.target.id === 'report-note' || e.target.name === 'report-category') {
          const checked = popup.querySelector('input[name="report-category"]:checked')
          sessionStorage.setItem(
            sessionKey,
            JSON.stringify({
              category: checked?.value || '',
              note: popup.querySelector('#report-note')?.value || '',
            }),
          )
        }
      })
    },
  })
}

function submitReport(apiUrl, { category, note }, translations, sessionKey) {
  return fetch(apiUrl, {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ category, note: note || null }),
  })
    .then((response) => {
      if (response.status === 204) {
        sessionStorage.removeItem(sessionKey)
        Swal.fire({
          text: translations.success || 'Your report has been submitted.',
          icon: 'success',
          customClass: { confirmButton: 'btn btn-primary' },
          buttonsStyling: false,
        })
        return true
      } else if (response.status === 409) {
        Swal.showValidationMessage(
          translations.duplicate || "You've already reported this content.",
        )
        return false
      } else if (response.status === 429) {
        Swal.showValidationMessage(
          translations.rateLimited ||
            "You're submitting reports too quickly. Please wait and try again.",
        )
        return false
      } else if (response.status === 403) {
        return handleAccountState403(
          response,
          translations,
          translations.trustTooLow || 'Your account is too new to file reports.',
        ).then(() => false)
      } else {
        Swal.showValidationMessage(
          translations.error || 'Oops, that did not work. Please try again!',
        )
        return false
      }
    })
    .catch(() => {
      Swal.showValidationMessage(translations.error || 'Oops, that did not work. Please try again!')
      return false
    })
}

function buildReportHtml(categories, translations, oldData) {
  const categoryHtml = categories
    .map((cat) => {
      const checked = oldData.category === cat.value ? 'checked' : ''
      const label = translations['category_' + cat.labelKey] || cat.value
      return `
      <div class="mdc-form-field">
        <div class="mdc-radio">
          <input class="mdc-radio__native-control" type="radio"
            id="report-cat-${escapeAttr(cat.value)}"
            name="report-category"
            value="${escapeAttr(cat.value)}" ${checked}>
          <div class="mdc-radio__background">
            <div class="mdc-radio__outer-circle"></div>
            <div class="mdc-radio__inner-circle"></div>
          </div>
          <div class="mdc-radio__ripple"></div>
        </div>
        <label for="report-cat-${escapeAttr(cat.value)}">${escapeHtml(label)}</label>
      </div>`
    })
    .join('')

  const placeholder =
    translations.notePlaceholder || 'Please describe why you are reporting this...'
  const noteValue = oldData.note || ''

  return `
    ${categoryHtml}
    <label class="mdc-text-field mdc-text-field--outlined mdc-text-field--textarea report-reason" style="margin-top: 1rem; width: 100%;">
      <span class="mdc-text-field__resizer">
        <textarea class="mdc-text-field__input" id="report-note"
          placeholder="${escapeAttr(placeholder)}"
          style="width: 100%; height: 80px">${escapeHtml(noteValue)}</textarea>
      </span>
      <span class="mdc-notched-outline">
        <span class="mdc-notched-outline__leading"></span>
        <span class="mdc-notched-outline__trailing"></span>
      </span>
    </label>
  `
}
