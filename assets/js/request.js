import { MDCFloatingLabel } from '@material/floating-label'
import { MDCTextField } from '@material/textfield'
import { Modal } from 'bootstrap'
import { Register } from './custom/register'

require('../styles/login.scss')

new MDCTextField(document.querySelector('.username'))
new MDCFloatingLabel(document.querySelector('#username-label'))

new Modal('#termsModal')
Register()
