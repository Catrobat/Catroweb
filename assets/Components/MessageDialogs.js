/* global globalConfiguration */

export default class MessageDialogs {
  static async showErrorMessage(message, retryCallback = null) {
    const { default: Swal } = await import('sweetalert2')
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

  static async showErrorList(errors) {
    if (errors == null) return
    if (!Array.isArray(errors)) {
      errors = Object.values(errors)
    }

    const { default: Swal } = await import('sweetalert2')
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

  static async showSuccessMessage(message) {
    const { default: Swal } = await import('sweetalert2')
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
