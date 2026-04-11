import { extractFieldErrors } from '../../Api/ResponseHelper'
import { showSnackbar, SnackbarDuration } from '../../Layout/Snackbar'
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

    const dobInput = document.getElementById('date-of-birth__input')
    const parentSection = document.getElementById('parent-email-section')
    if (dobInput && parentSection) {
      dobInput.addEventListener('change', () => {
        const dob = dobInput.value
        if (!dob) {
          parentSection.style.display = 'none'
          return
        }
        const msPerYear = 365.25 * 24 * 60 * 60 * 1000
        const age = Math.floor((new Date() - new Date(dob)) / msPerYear)
        parentSection.style.display = age < 14 ? '' : 'none'
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
      date_of_birth: document.getElementById('date-of-birth__input').value,
      parent_email: document.getElementById('parent-email__input').value || undefined,
    }

    const response = await this.fetchPost(this.apiPathValue, data)
    this.resetRegistrationButton()

    if (response.status === 429) {
      showSnackbar(
        '#share-snackbar',
        'Too many registration attempts. Please wait a while and try again.',
        SnackbarDuration.error,
      )
      return
    }

    if (response.status === 403) {
      showSnackbar(
        '#share-snackbar',
        'CAPTCHA check did not pass. Please try again!',
        SnackbarDuration.error,
      )
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
    showSnackbar(
      '#share-snackbar',
      'Oops, that did not work. Please try again!',
      SnackbarDuration.error,
    )
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
    const byField = extractFieldErrors(responseObj)
    showValidationMessage(byField.username, 'username')
    showValidationMessage(byField.email, 'email')
    showValidationMessage(byField.password, 'password')
    showValidationMessage(byField.date_of_birth, 'date-of-birth')
    showValidationMessage(byField.parent_email, 'parent-email')
  }
}
