import '../Components/TextField'
import '../Components/Switch'
import { app } from '../bootstrap'
import { PasswordVisibilityToggle } from '../Components/PasswordVisibilityToggle'
import RegistrationController from '../controllers/security/registration_controller'

import './login.css'

new PasswordVisibilityToggle()

document.getElementById('username').focus()

app.register('security--registration', RegistrationController)
