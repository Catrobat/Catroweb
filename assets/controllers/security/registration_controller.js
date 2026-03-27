import { showSnackbar } from '../../Layout/Snackbar'
import { showValidationMessage } from '../../Components/TextField'
import { AjaxController } from '../ajax_controller'
import { LoginTokenHandler } from '../../Security/LoginTokenHandler'
import { initCaptchaWidget } from '../../Security/CaptchaWidget'

export default class extends AjaxController {
  static values = {
    baseUrl: String,
    apiPath: String,
    captchaEndpoint: String,
  }

  captchaWidget = null

  connect() {
    if (this.captchaEndpointValue) {
      initCaptchaWidget(this.captchaEndpointValue).then((widget) => {
        this.captchaWidget = widget
      })
    }
  }

  disconnect() {
    this.captchaWidget?.destroy()
    this.captchaWidget = null
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
      captcha_token: this.captchaWidget?.getToken() ?? '',
    }

    const response = await this.fetchPost(this.apiPathValue, data)
    this.resetRegistrationButton()

    if (response.status === 403) {
      showSnackbar('#share-snackbar', 'CAPTCHA verification failed. Please try again.')
      return
    }

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
      this.registrationButtonSpinner = document.getElementById('register-btn__spinner')
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
