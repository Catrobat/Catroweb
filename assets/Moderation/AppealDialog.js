import Swal from 'sweetalert2'
import { handleAccountState403 } from '../Security/AccountStateErrorHandler'
import { escapeAttr } from '../Components/HtmlEscape'

export function showAppealDialog({ apiUrl, translations }) {
  const placeholder = translations.placeholder || 'Explain why this content should not be hidden...'

  Swal.fire({
    title: translations.title || 'Appeal this decision',
    html: `
      <label class="mdc-text-field mdc-text-field--outlined mdc-text-field--textarea" style="width: 100%;">
        <span class="mdc-text-field__resizer">
          <textarea class="mdc-text-field__input" id="appeal-reason"
            placeholder="${escapeAttr(placeholder)}"
            style="width: 100%; height: 120px"></textarea>
        </span>
        <span class="mdc-notched-outline">
          <span class="mdc-notched-outline__leading"></span>
          <span class="mdc-notched-outline__trailing"></span>
        </span>
      </label>
    `,
    focusConfirm: false,
    showCancelButton: true,
    allowOutsideClick: false,
    customClass: {
      confirmButton: 'btn btn-primary',
      cancelButton: 'btn btn-outline-primary',
    },
    buttonsStyling: false,
    confirmButtonText: translations.submit || 'Submit Appeal',
    cancelButtonText: translations.cancel || 'Cancel',
    preConfirm: () => {
      const reason = document.getElementById('appeal-reason')?.value?.trim()
      if (!reason) {
        Swal.showValidationMessage('Please provide a reason for your appeal')
        return false
      }
      return submitAppeal(apiUrl, reason, translations)
    },
  })
}

function submitAppeal(apiUrl, reason, translations) {
  return fetch(apiUrl, {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ reason }),
  })
    .then((response) => {
      if (response.status === 201) {
        Swal.fire({
          text: translations.success || 'Your appeal has been submitted for review.',
          icon: 'success',
          customClass: { confirmButton: 'btn btn-primary' },
          buttonsStyling: false,
        })
        return true
      } else if (response.status === 409) {
        Swal.showValidationMessage(
          translations.alreadyPending || 'You already have a pending appeal.',
        )
        return false
      } else if (response.status === 403) {
        return handleAccountState403(response, translations).then(() => false)
      } else if (response.status === 429) {
        Swal.showValidationMessage(
          translations.rateLimited ||
            "You're submitting appeals too quickly. Please wait and try again.",
        )
        return false
      } else {
        Swal.showValidationMessage(translations.error || 'Something went wrong.')
        return false
      }
    })
    .catch(() => {
      Swal.showValidationMessage(translations.error || 'Something went wrong.')
      return false
    })
}
