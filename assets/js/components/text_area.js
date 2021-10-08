import { MDCTextField } from '@material/textfield'

require('../../styles/components/text_area.scss')

for (const el of document.querySelectorAll('.mdc-text-field--textarea')) {
  new MDCTextField(el)
}
