import { MDCSwitch } from '@material/switch'

require('../../styles/components/switch.scss')

for (const el of document.querySelectorAll('.mdc-switch')) {
  const switchControl = new MDCSwitch(el)
  el.addEventListener('click', () => {
    el.getElementsByTagName('input')[0].value = '' + switchControl.selected
  })
}
