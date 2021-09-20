import $ from 'jquery'
import Swal from 'sweetalert2'
/* global Routing */

/**
 * @deprecated
 *
 * @param profileUrl
 * @param saveUsername
 * @param saveEmailUrl
 * @param saveCountryUrl
 * @param savePasswordUrl
 * @param deleteUrl
 * @param deleteAccountUrl
 * @param toggleVisibilityUrl
 * @param uploadUrl
 * @param statusCodeOk
 * @param statusCodeUsernameAlreadyExists
 * @param statusCodeUsernameMissing
 * @param statusCodeUsernameInvalid
 * @param statusCodeUserEmailAlreadyExists
 * @param statusCodeUserEmailMissing
 * @param statusCodeUserEmailInvalid
 * @param statusCodeUserCountryInvalid
 * @param statusCodeUsernamePasswordEqual
 * @param statusCodeUserPasswordTooShort
 * @param statusCodeUserPasswordTooLong
 * @param statusCodeUserPasswordNotEqualPassword2
 * @param statusCodePasswordInvalid
 * @param successText
 * @param checkMailText
 * @param passwordUpdatedText
 * @param programCanNotChangeVisibilityTitle
 * @param programCanNotChangeVisibilityText
 * @param statusCodeUsernameContainsEmail
 * @constructor
 */
export const MyProfile = function (
  profileUrl,
  saveUsername,
  saveEmailUrl,
  saveCountryUrl,
  savePasswordUrl,
  deleteUrl,
  deleteAccountUrl,
  toggleVisibilityUrl,
  uploadUrl,
  statusCodeOk,
  statusCodeUsernameAlreadyExists,
  statusCodeUsernameMissing,
  statusCodeUsernameInvalid,
  statusCodeUserEmailAlreadyExists,
  statusCodeUserEmailMissing,
  statusCodeUserEmailInvalid,
  statusCodeUserCountryInvalid,
  statusCodeUsernamePasswordEqual,
  statusCodeUserPasswordTooShort,
  statusCodeUserPasswordTooLong,
  statusCodeUserPasswordNotEqualPassword2,
  statusCodePasswordInvalid,
  successText,
  checkMailText,
  passwordUpdatedText,
  programCanNotChangeVisibilityTitle,
  programCanNotChangeVisibilityText,
  statusCodeUsernameContainsEmail) {
  const passwordEditContainer = $('#password-edit-container')
  const usernameEditContainer = $('#username-edit-container')
  const usernameData = $('#username-wrapper > .profile-data')
  const emailEditContainer = $('#email-edit-container')
  const emailData = $('#email-wrapper > .profile-data')
  const countryEditContainer = $('#country-edit-container')
  const countryData = $('#country-wrapper > .profile-data')
  const passwordData = $('#password-wrapper > .profile-data')
  const accountSettingsContainer = $('#account-settings-container')
  const profileSections = [
    [passwordEditContainer, passwordData],
    [emailEditContainer, emailData], [countryEditContainer, countryData],
    [accountSettingsContainer, null]
  ]

  $(function () {
    $('.edit-container').hide()
  })

  $('#edit-password-button').on('click', function () {
    toggleEditSection(passwordEditContainer, passwordData)
  })

  $('#edit-email-button').on('click', function () {
    toggleEditSection(emailEditContainer, emailData)
  })

  $('#edit-username-button').on('click', function () {
    toggleEditSection(usernameEditContainer, usernameData)
  })

  $('#edit-country-button').on('click', function () {
    toggleEditSection(countryEditContainer, countryData)
  })

  $('#account-settings-button').on('click', function () {
    toggleEditSection(accountSettingsContainer)
  })

  function toggleEditSection (container, data = null) {
    const fadeTime = 250
    if (container.is(':visible')) {
      container.slideUp()
      if (data) {
        data.fadeIn(fadeTime)
      }
    } else {
      container.slideDown()
      if (data) {
        data.fadeOut(fadeTime)
      }
      profileSections.forEach(function (entry) {
        if (entry[0] !== container) {
          entry[0].slideUp()
          if (entry[1]) {
            entry[1].fadeIn(fadeTime)
          }
        }
      })
    }
  }

  $(document).on('click', '#delete-account-button', function () {
    const url = Routing.generate('translate', {
      word: 'programs.deleteAccountConfirmation'
    }, false)
    $.get(url, function (data) {
      const split = data.split('\n')
      Swal.fire({
        title: split[0],
        html: split[1] + '<br><br>' + split[2],
        icon: 'warning',
        showCancelButton: true,
        allowOutsideClick: false,
        customClass: {
          confirmButton: 'btn btn-danger',
          cancelButton: 'btn btn-outline-primary'
        },
        buttonsStyling: false,
        confirmButtonText: split[3],
        cancelButtonText: split[4]
      }).then((result) => {
        if (result.value) {
          $.post(deleteAccountUrl, null, function (data) {
            switch (parseInt(data.statusCode)) {
              case statusCodeOk:
                window.location.href = '../../'
            }
          })
        }
      })
      $('.swal2-container.swal2-shown').css('background-color', 'rgba(255, 0, 0, 0.75)')// changes the color of the overlay
    })
  })

  $(document).on('click', '#save-email', function () {
    $(this).hide()
    $('#email-ajax').show()

    const email = $('#email')
    $('.error-message').addClass('d-none')

    const newEmail = email.val()
    $.post(saveEmailUrl, {
      firstEmail: newEmail,
      secondEmail: ''
    }, function (data) {
      switch (parseInt(data.statusCode)) {
        case statusCodeOk:
          Swal.fire({
            title: successText,
            text: checkMailText,
            icon: 'success',
            customClass: {
              confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false,
            allowOutsideClick: false
          }).then(() => {
            window.location.href = profileUrl
          })
          break

        case statusCodeUserEmailAlreadyExists:
          if (parseInt(data.email) === 1) {
            $('.text-email1-exists').removeClass('d-none')
          }
          if (parseInt(data.email) === 2) {
            $('.text-email2-exists').removeClass('d-none')
          }
          break

        case statusCodeUserEmailMissing:
          $('.text-email-missing').removeClass('d-none')
          break

        case statusCodeUserEmailInvalid:
          if (parseInt(data.email) === 1) {
            $('.text-email1-not-valid').removeClass('d-none')
          }
          if (parseInt(data.email) === 2) {
            $('.text-email2-not-valid').removeClass('d-none')
          }
          break

        default:
          window.location.href = profileUrl
      }
      $('#email-ajax').hide()
      $('#save-email').show()
    })
  })

  $(document).on('click', '#save-username', function () {
    $(this).hide()
    $('#username-ajax').show()

    const username = $('#username')
    $('.error-message').addClass('d-none')

    const newUsername = username.val()

    $.post(saveUsername, { username: newUsername }, function (data) {
      switch (parseInt(data.statusCode)) {
        case statusCodeUsernameAlreadyExists:
          $('.text-username-exists').removeClass('d-none')
          break

        case statusCodeUsernameMissing:
          $('.text-username-missing').removeClass('d-none')
          break

        case statusCodeUsernameInvalid:
          $('.text-username-not-valid').removeClass('d-none')
          break
        case statusCodeUsernameContainsEmail:
          $('.text-username-contains-email').removeClass('d-none')
          break
        default:
          window.location.href = profileUrl
      }
      $('#username-ajax').hide()
      $('#save-username').show()
    })
  })

  $(document).on('click', '#save-country', function () {
    $(this).hide()
    $('#country-ajax').show()
    const country = $('#select-country').find('select').val()
    $.post(saveCountryUrl, {
      country: country
    }, function (data) {
      switch (parseInt(data.statusCode)) {
        case statusCodeUserCountryInvalid:
          break

        default:
          window.location.href = profileUrl
          break
      }
      $('#country-ajax').hide()
      $('#save-country').show()
    })
  })

  $(document).on('click', '#save-password', function () {
    $(this).hide()
    $('#password-ajax').show()

    const password = $('#password')
    const repeatPassword = $('#repeat-password')
    $('.error-message').addClass('d-none')
    password.parent().removeClass('password-failed')
    repeatPassword.parent().removeClass('password-failed')
    const newPassword = password.val()
    const oldPassword = $('#old-password').val()
    const repeatPasswordVal = repeatPassword.val()

    $.post(savePasswordUrl, {
      oldPassword: oldPassword,
      newPassword: newPassword,
      repeatPassword: repeatPasswordVal
    }, function (data) {
      switch (parseInt(data.statusCode)) {
        case statusCodeUsernamePasswordEqual:
          $('.text-password-isusername').removeClass('d-none')
          break

        case statusCodeUserPasswordTooShort:
          $('.text-password-tooshort').removeClass('d-none')
          break

        case statusCodeUserPasswordTooLong:
          $('.text-password-toolong').removeClass('d-none')
          break

        case statusCodeUserPasswordNotEqualPassword2:
          $('.text-password-nomatch').removeClass('d-none')
          break

        case statusCodePasswordInvalid:
          $('.text-password-wrongpassword').removeClass('d-none')
          break

        default:
          Swal.fire({
            title: successText,
            text: passwordUpdatedText,
            icon: 'success',
            customClass: {
              confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false,
            allowOutsideClick: false
          }).then(() => {
            window.location.href = profileUrl
          })
          break
      }
      $('#password-ajax').hide()
      $('#save-password').show()
    })
  })
}
