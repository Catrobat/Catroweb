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

}
