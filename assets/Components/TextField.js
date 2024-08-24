import { MDCTextField } from '@material/textfield'
import { MDCFloatingLabel } from '@material/floating-label'

require('./TextField.scss')

for (const el of document.querySelectorAll('.mdc-text-field')) {
  new MDCTextField(el)
}

for (const el of document.querySelectorAll('.mdc-floating-label')) {
  new MDCFloatingLabel(el)
}

export function showValidationMessage(msg, textFieldId) {
  const element = document.getElementById(textFieldId)
  const errorElement = document.getElementById(textFieldId + '__helper')
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
