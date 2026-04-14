import { showSnackbar, SnackbarDuration } from '../../Layout/Snackbar'
import { showValidationMessage } from '../../Components/TextField'
import { AjaxController } from '../ajax_controller'
import { initCaptchaWidget } from '../../Security/CaptchaWidget'

/* stimulusFetch: 'lazy' */
export default class extends AjaxController {
  static values = {
    sendLinkPath: String,
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

  async requestManagementLink() {
    const emailInput = document.getElementById('parent-email__input')
    const email = emailInput?.value?.trim() || ''

    if (!email) {
      showValidationMessage('Please enter your email address', 'parent-email')
      return
    }

    const submitBtn = document.getElementById('parent-portal-submit')
    if (submitBtn) {
      submitBtn.disabled = true
      submitBtn.textContent = 'Sending...'
    }

    const data = {
      email,
      captcha_token: this.captchaWidget?.getToken() ?? '',
    }

    try {
      const response = await this.fetchPost(this.sendLinkPathValue, data)

      if (submitBtn) {
        submitBtn.disabled = false
        submitBtn.textContent = 'Send Management Link'
      }

      if (response.status === 429) {
        showSnackbar(
          '#share-snackbar',
          'You can only request one management link per day. Please try again tomorrow.',
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

      if (response.status === 204 || response.status === 200) {
        document.getElementById('parent-portal-form')?.classList.add('d-none')
        document.getElementById('parent-portal-success')?.classList.remove('d-none')
        return
      }

      if (response.status === 422) {
        const text = await response.text()
        const obj = JSON.parse(text)
        showValidationMessage(obj.email, 'parent-email')
        return
      }

      showSnackbar(
        '#share-snackbar',
        'Something went wrong. Please try again.',
        SnackbarDuration.error,
      )
    } catch {
      if (submitBtn) {
        submitBtn.disabled = false
        submitBtn.textContent = 'Send Management Link'
      }
      showSnackbar(
        '#share-snackbar',
        'Something went wrong. Please try again.',
        SnackbarDuration.error,
      )
    }
  }
}
