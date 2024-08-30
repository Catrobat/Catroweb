/* global globalConfiguration */
import Swal from 'sweetalert2'

export default class MessageDialogs {
  static showErrorMessage(message) {
    return Swal.fire({
      title: globalConfiguration.messages.errorTitle,
      text: message,
      icon: 'error',
      customClass: {
        confirmButton: 'btn btn-primary',
      },
      buttonsStyling: false,
      allowOutsideClick: false,
      confirmButtonText: globalConfiguration.messages.okayButtonText,
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
      icon: 'error',
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
