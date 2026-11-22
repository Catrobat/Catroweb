import '../Components/TextField'
import '../Components/Switch'
import { PasswordVisibilityToggle } from '../Components/PasswordVisibilityToggle'
// Stimulus loads controllers lazily; import this page's own so it downloads with the page, not one round trip later.
import '../controllers/security/registration_controller'

import './login.css'

new PasswordVisibilityToggle()

document.getElementById('username').focus()
