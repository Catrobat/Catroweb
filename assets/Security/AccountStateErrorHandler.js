import Swal from 'sweetalert2'

/**
 * Handles 403 responses that indicate account-state issues (unverified email, suspended account).
 * Parses the JSON body and shows an appropriate SweetAlert2 dialog.
 *
 * @param {Response} response  - The fetch Response with status 403
 * @param {object} translations - Translation keys: { unverified, suspended, error }
 * @param {string} fallbackMsg  - Default message when 403 is not an account-state error
 */
export function handleAccountState403(response, translations, fallbackMsg) {
  return response
    .json()
    .then((body) => {
      let msg = fallbackMsg || translations.error || 'Something went wrong.'
      if (body?.error === 'Email verification required.') {
        msg =
          translations.unverified ||
          'Please make sure you are logged in and your account\u2019s email is verified.'
      } else if (body?.error === 'Your account has been suspended.') {
        msg = translations.suspended || 'Your account has been suspended due to community reports.'
      }
      Swal.fire({
        text: msg,
        icon: 'warning',
        customClass: { confirmButton: 'btn btn-primary' },
        buttonsStyling: false,
      })
    })
    .catch(() => {
      Swal.fire({
        text: translations.error || 'Something went wrong.',
        icon: 'warning',
        customClass: { confirmButton: 'btn btn-primary' },
        buttonsStyling: false,
      })
    })
}
