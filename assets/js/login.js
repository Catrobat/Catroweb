import { MDCFloatingLabel } from '@material/floating-label'
import { MDCTextField } from '@material/textfield'
import { Modal } from 'bootstrap'
import { PasswordVisibilityToggle } from './components/password_visibility_toggle'
import { Register } from './custom/register'

require('../styles/login.scss')

new MDCTextField(document.querySelector('.username'))
new MDCTextField(document.querySelector('.password'))
new MDCFloatingLabel(document.querySelector('#username-label'))
new MDCFloatingLabel(document.querySelector('#password-label'))
new PasswordVisibilityToggle()

new Modal('#termsModal')
Register()
