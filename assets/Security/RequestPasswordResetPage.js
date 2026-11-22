import '../Components/TextField'
import { app } from '../bootstrap'
import ParentPortalController from '../controllers/security/parent-portal_controller'
import ResetPasswordController from '../controllers/security/reset-password_controller'

import './login.css'

// assets/bootstrap.js registers no controllers; each page registers its own.
app.register('security--parent-portal', ParentPortalController)
app.register('security--reset-password', ResetPasswordController)
