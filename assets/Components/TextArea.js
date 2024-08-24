import { MDCTextField } from '@material/textfield'

require('./TextField.scss')

for (const el of document.querySelectorAll('.mdc-text-field--textarea')) {
  new MDCTextField(el)
}
