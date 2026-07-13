import { MDCTextField } from '@material/textfield'

import './TextField.css'

for (const el of document.querySelectorAll('.mdc-text-field--textarea')) {
  new MDCTextField(el)
}
