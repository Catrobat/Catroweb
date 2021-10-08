import { MDCTextField } from '@material/textfield'
import { MDCFloatingLabel } from '@material/floating-label'

require('../../styles/components/text_field.scss')

for (const el of document.querySelectorAll('.mdc-text-field')) {
  new MDCTextField(el)
}

for (const el of document.querySelectorAll('.mdc-floating-label')) {
  new MDCFloatingLabel(el)
}
