export class OAuthHandler {
  constructor() {
    const oAuthGreeting = document.querySelector('.js-oauth-greeting')
    this.show = oAuthGreeting.dataset.isFirstOauthLogin
    this.infoText = oAuthGreeting.dataset.transInfo
    this.infoTitle = oAuthGreeting.dataset.transTitle
    this.infoConfirm = oAuthGreeting.dataset.transOk
  }

  async showOAuthFirstLoginInformationIfNecessary() {
    if (this.show === '1' && localStorage.getItem('oauthSignIn') !== '1') {
      const { default: Swal } = await import('sweetalert2')
      Swal.fire({
        title: this.infoTitle,
        html: this.infoText,
        showCancelButton: false,
        allowOutsideClick: false,
        confirmButtonText: this.infoConfirm,
        icon: 'info',
        customClass: {
          confirmButton: 'btn btn-primary',
        },
        buttonsStyling: false,
      }).then(() => {
        localStorage.setItem('oauthSignIn', '1')
      })
    }
  }
}
