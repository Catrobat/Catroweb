import { MDCFloatingLabel } from '@material/floating-label'
import { MDCTextField } from '@material/textfield'

require('../styles/login.scss')

new MDCTextField(document.querySelector('.username'))
new MDCFloatingLabel(document.querySelector('.username-label'))
