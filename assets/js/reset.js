import { MDCFloatingLabel } from '@material/floating-label'
import { MDCTextField } from '@material/textfield'

require('../styles/login.scss')

new MDCTextField(document.querySelector('.password-first'))
new MDCTextField(document.querySelector('.password-second'))
new MDCFloatingLabel(document.querySelector('.password-first-label'))
new MDCFloatingLabel(document.querySelector('.password-second-label'))
