import '@material/web/iconbutton/icon-button.js'
import '@material/web/textfield/filled-text-field.js'

export function showValidationMessage(msg, textFieldId) {
  const element = document.getElementById(textFieldId)
  if (!element) return

  if (element.matches('md-filled-text-field')) {
    element.error = Boolean(msg)
    element.errorText = msg || ''
    return
  }

  const errorElement = document.getElementById(`${textFieldId}__helper`)
  if (!errorElement) return

  if (msg) {
    errorElement.innerText = msg
    errorElement.classList.add('mdc-text-field-helper-text--persistent')
    errorElement.classList.add('mdc-text-field-helper-text--validation-msg')
    element.classList.add('mdc-text-field--invalid')
  } else {
    errorElement.innerText = ''
    errorElement.classList.remove('mdc-text-field-helper-text--persistent')
    errorElement.classList.remove('mdc-text-field-helper-text--validation-msg')
    element.classList.remove('mdc-text-field--invalid')
  }
}
