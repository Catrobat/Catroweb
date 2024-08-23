import { showSnackbar } from '../../Layout/Snackbar'
import { showValidationMessage } from '../../Components/TextField'
import { AjaxController } from '../ajax_controller'

export default class extends AjaxController {
  static values = {
    apiPath: String,
    checkYourMailsUrl: String,
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
    }
    const response = await this.fetchPost(this.apiPathValue, data)

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
      console.error('Registration error: ' + response.status + text)
    })
    showSnackbar('#share-snackbar', 'Unexpected Error. Try again later.')
  }

  handleValidationError(responseText) {
    const responseObj = JSON.parse(responseText)
    showValidationMessage(responseObj.email, 'email')
  }
}
