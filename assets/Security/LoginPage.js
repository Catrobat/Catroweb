import '../Components/TextField'
import { PasswordVisibilityToggle } from '../Components/PasswordVisibilityToggle'
import { LoginTokenHandler } from './LoginTokenHandler'

require('./login.scss')

new PasswordVisibilityToggle()

const loginTokenHandler = new LoginTokenHandler()
loginTokenHandler.initListeners()

document.getElementById('username').focus()
