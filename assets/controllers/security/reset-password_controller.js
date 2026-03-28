import { showSnackbar, SnackbarDuration } from '../../Layout/Snackbar'
import { showValidationMessage } from '../../Components/TextField'
import { AjaxController } from '../ajax_controller'
import { initCaptchaWidget } from '../../Security/CaptchaWidget'

export default class extends AjaxController {
  static values = {
    apiPath: String,
    checkYourMailsUrl: String,
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
   * Request email to reset password
   *
   * data-action="click->security--reset_password#requestPasswordResetEmail"
   *
   * @returns {Promise<void>}
   */
  async requestPasswordResetEmail() {
    const data = {
      email: document.getElementById('email__input').value,
      captcha_token: this.captchaWidget?.getToken() ?? '',
    }
    const response = await this.fetchPost(this.apiPathValue, data)

    if (response.status === 403) {
      showSnackbar(
        '#share-snackbar',
        'CAPTCHA check did not pass. Please try again!',
        SnackbarDuration.error,
      )
      return
    }

    if (response.status === 204) {
      window.location.href = this.checkYourMailsUrlValue
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
      console.error('Password reset error: ' + response.status + text)
    })
    showSnackbar(
      '#share-snackbar',
      'Oops, that did not work. Please try again!',
      SnackbarDuration.error,
    )
  }

  handleValidationError(responseText) {
    const responseObj = JSON.parse(responseText)
    showValidationMessage(responseObj.email, 'email')
  }
}
