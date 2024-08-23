import { MDCSelect } from '@material/select'
import { MDCFloatingLabel } from '@material/floating-label'

require('./Select.scss')

for (const el of document.querySelectorAll('.mdc-select')) {
  const select = new MDCSelect(el)
  select.listen('MDCSelect:change', () => {
    document.getElementById(el.id + '-native').value = select.value
  })
}

for (const el of document.querySelectorAll('.mdc-floating-label')) {
  new MDCFloatingLabel(el)
}
