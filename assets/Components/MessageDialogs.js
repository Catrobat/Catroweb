/* global globalConfiguration */
import Swal from 'sweetalert2'

export default class MessageDialogs {
  static showErrorMessage(message, retryCallback = null) {
    const options = {
      title: globalConfiguration.messages.errorTitle,
      text: message,
      icon: 'warning',
      customClass: {
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-outline-primary ms-2',
      },
      buttonsStyling: false,
      allowOutsideClick: false,
      confirmButtonText: globalConfiguration.messages.okayButtonText,
    }

    if (typeof retryCallback === 'function') {
      options.showCancelButton = true
      options.confirmButtonText = globalConfiguration.messages.retryButtonText || 'Try again'
      options.cancelButtonText = globalConfiguration.messages.okayButtonText
    }

    return Swal.fire(options).then((result) => {
      if (result.isConfirmed && typeof retryCallback === 'function') {
        retryCallback()
      }
      return result
    })
  }

  static showErrorList(errors) {
    if (errors == null) return
    if (!Array.isArray(errors)) {
      errors = Object.values(errors)
    }

    return Swal.fire({
      title: globalConfiguration.messages.errorTitle,
      html: '<ul class="text-start"><li>' + errors.join('</li><li>') + '</li></ul>',
      icon: 'warning',
      customClass: {
        confirmButton: 'btn btn-primary',
      },
      buttonsStyling: false,
      allowOutsideClick: false,
      confirmButtonText: globalConfiguration.messages.okayButtonText,
    })
  }

  static showSuccessMessage(message) {
    return Swal.fire({
      title: globalConfiguration.messages.successTitle,
      text: message,
      icon: 'success',
      customClass: {
        confirmButton: 'btn btn-primary',
      },
      buttonsStyling: false,
      allowOutsideClick: false,
      confirmButtonText: globalConfiguration.messages.okayButtonText,
    })
  }
}
