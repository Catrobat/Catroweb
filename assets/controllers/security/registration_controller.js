import { showSnackbar } from '../../Layout/Snackbar'
import { showValidationMessage } from '../../Components/TextField'
import { AjaxController } from '../ajax_controller'
import { LoginTokenHandler } from '../../Security/LoginTokenHandler'

export default class extends AjaxController {
  static values = {
    baseUrl: String,
    apiPath: String,
  }

  /**
   * Handle user registration
   *
   * data-action="click->security--registration#register"
   *
   * @returns {Promise<void>}
   */
  async register() {
    this.initRegistrationButton()
    this.spinRegistrationButton()

    const data = {
      username: document.getElementById('username__input').value,
      email: document.getElementById('email__input').value,
      password: document.getElementById('password__input').value,
    }

    const response = await this.fetchPost(this.apiPathValue, data)
    this.resetRegistrationButton()

    if (response.status === 201) {
      const loginTokenHandler = new LoginTokenHandler()
      loginTokenHandler.login(data)
      return
    }

    if (response.status === 422) {
      const self = this
      response.text().then(function (text) {
        self.handleValidationError(text)
      })
      return
    }

    response.text().then(function (text) {
      console.error('Registration error: ' + response.status + text)
    })
    showSnackbar('#share-snackbar', 'Unexpected Error. Try again later.')
  }

  registrationButton = null
  registrationButtonSpinner = null

  initRegistrationButton() {
    if (null === this.registrationButton) {
      this.registrationButton = document.getElementById('register-btn')
    }
    if (null === this.registrationButtonSpinner) {
      this.registrationButtonSpinner = document.getElementById(
        'register-btn__spinner',
      )
    }
  }

  spinRegistrationButton() {
    this.registrationButton.disabled = true
    this.registrationButtonSpinner.classList.remove('d-none')
  }

  resetRegistrationButton() {
    this.registrationButton.disabled = false
    this.registrationButtonSpinner.classList.add('d-none')
  }

  handleValidationError(responseText) {
    const responseObj = JSON.parse(responseText)
    showValidationMessage(responseObj.username, 'username')
    showValidationMessage(responseObj.email, 'email')
    showValidationMessage(responseObj.password, 'password')
  }
}
