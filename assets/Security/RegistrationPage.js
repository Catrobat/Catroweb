import '../Components/TextField'
import '../Components/Switch'
import { app } from '../bootstrap'
import { PasswordVisibilityToggle } from '../Components/PasswordVisibilityToggle'
import RegistrationController from '../controllers/security/registration_controller'

import './login.css'

app.register('security--registration', RegistrationController)

new PasswordVisibilityToggle()

document.getElementById('username').focus()
