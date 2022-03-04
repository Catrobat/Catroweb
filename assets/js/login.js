import './components/text_field'
import { PasswordVisibilityToggle } from './components/password_visibility_toggle'
import { LoginTokenHandler } from './security/LoginTokenHandler'

require('../styles/login.scss')

new PasswordVisibilityToggle()

const loginTokenHandler = new LoginTokenHandler()
loginTokenHandler.initListeners()

document.getElementById('username').focus()
