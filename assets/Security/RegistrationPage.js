import '../Components/TextField'
import '../Components/Switch'
import { PasswordVisibilityToggle } from '../Components/PasswordVisibilityToggle'

require('./login.scss')

new PasswordVisibilityToggle()

document.getElementById('username').focus()
