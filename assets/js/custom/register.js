/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
const Register = function (googleLoginUrl, facebookLoginUrl, appleLoginUrl) {
  const self = this
  self.termsModalAgreeButton = ''
  self.termsModalAgreed = false
  //
  // RegisterListener
  $(document).ready(function () {
    $(document).on('click', '#agreeButton', function () {
      self.termsModalAgreed = true
      if (self.termsModalAgreeButton === 'registration_form') {
        $('#registration_form').submit()
        self.termsModalAgreeButton = ''
      }
    })
    // Default registration
    $('#registration_form').submit(function () {
      self.termsModalAgreeButton = 'registration_form'
      return self.termsModalAgreed // only submit after agreed terms of modal
    })
  })

  // -------------------------------------------------------------------------------------------------------------------
  // OAuth Sign In
  //
  $(document).ready(function () {
    $(document).on('click', '#agreeButton', function () {
      if (self.termsModalAgreeButton === 'google_login') {
        window.location.href = googleLoginUrl
        self.termsModalAgreeButton = ''
      } else if (self.termsModalAgreeButton === 'facebook_login') {
        window.location.href = facebookLoginUrl
        self.termsModalAgreeButton = ''
      } else if (self.termsModalAgreeButton === 'apple_login') {
        window.location.href = appleLoginUrl
        self.termsModalAgreeButton = ''
      }
    })
    // OAuth Sign in
    $(document).on('click', '#btn-login-google', function () {
      self.termsModalAgreeButton = 'google_login'
    })
    $(document).on('click', '#btn-login-facebook', function () {
      self.termsModalAgreeButton = 'facebook_login'
    })
    $(document).on('click', '#btn-login-apple', function () {
      self.termsModalAgreeButton = 'apple_login'
    })
  })
}
