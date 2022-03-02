import './components/text_field'
import './components/switch'
import { PasswordVisibilityToggle } from './components/password_visibility_toggle'

require('../styles/login.scss')

new PasswordVisibilityToggle()

document.getElementById('username').focus()
