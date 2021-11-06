import './components/text_field'
import './components/modal'
import { PasswordVisibilityToggle } from './components/password_visibility_toggle'
import { Register } from './custom/register'
import { LoginTokenHandler } from './custom/LoginTokenHandler'

require('../styles/login.scss')

new PasswordVisibilityToggle()
Register()
new LoginTokenHandler()
