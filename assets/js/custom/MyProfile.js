/* eslint-env jquery */
/* global Swal */
/* global Routing */

// eslint-disable-next-line no-unused-vars
const MyProfile = function (profileUrl, saveUsername,
  saveEmailUrl, saveCountryUrl, savePasswordUrl,
  deleteUrl, deleteAccountUrl,
  toggleVisibilityUrl, uploadUrl,
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
  successText, checkMailText, passwordUpdatedText,
  programCanNotChangeVisibilityTitle,
  programCanNotChangeVisibilityText,
  statusCodeUsernameContainsEmail) {
  const self = this
  self.profileUrl = profileUrl
  self.profile_edit_url = profileUrl
  self.saveUsername = saveUsername
  self.saveEmailUrl = saveEmailUrl
  self.saveCountryUrl = saveCountryUrl
  self.savePasswordUrl = savePasswordUrl
  self.deleteUrl = deleteUrl
  self.uploadUrl = uploadUrl
  self.toggleVisibilityUrl = toggleVisibilityUrl
  self.country = null
  self.regex_email = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
  self.data_changed = false
  self.deleteAccountUrl = deleteAccountUrl
  const blueColor = '#3085d6'
  const redColor = '#d33'
  const passwordEditContainer = $('#password-edit-container')
  const usernameEditContainer = $('#username-edit-container')
  const usernameData = $('#username-wrapper > .profile-data')
  const emailEditContainer = $('#email-edit-container')
  const emailData = $('#email-wrapper > .profile-data')
  const countryEditContainer = $('#country-edit-container')
  const countryData = $('#country-wrapper > .profile-data')
  const accountSettingsContainer = $('#account-settings-container')
  const profileSections = [
    [passwordEditContainer, null],
    [emailEditContainer, emailData], [countryEditContainer, countryData],
    [accountSettingsContainer, null]
  ]

  $(function () {
    $('.edit-container').hide()
  })

  $('#edit-password-button').on('click', function () {
    toggleEditSection(passwordEditContainer)
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

  function stringTranslate (programName, catalogEntry) {
    const translations = []
    translations.push({ key: '%programName%', value: programName })
    return Routing.generate('translate', {
      word: catalogEntry,
      array: JSON.stringify(translations),
      domain: 'catroweb'
    }, false)
  }

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

  self.deleteProgram = function (id) {
    const programName = $('#program-' + id).find('.program-name').text()
    const catalogEntry = 'programs.deleteConfirmation'
    const url = stringTranslate(programName, catalogEntry)
    $.get(url, function (data) {
      const split = data.split('\n')
      Swal.fire({
        title: split[0],
        html: split[1] + '<br><br>' + split[2],
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: blueColor,
        cancelButtonColor: redColor,
        confirmButtonText: split[3],
        cancelButtonText: split[4]
      }).then((result) => {
        if (result.value) {
          window.location.href = self.deleteUrl + '/' + id
        }
      })
    })
  }

  self.toggleVisibility = function (id) {
    $.get(self.toggleVisibilityUrl + '/' + id, {}, function (data) {
      const visibilityLockId = $('#visibility-lock-' + id)
      const visibilityLockOpenId = $('#visibility-lock-open-' + id)
      const programName = $('#program-' + id).find('.program-name').text()
      const catalogEntry = 'programs.changeVisibility'
      const url = stringTranslate(programName, catalogEntry)

      if (data === 'true') {
        $.get(url, function (data) {
          const split = data.split('\n')
          if (visibilityLockId.is(':visible')) {
            Swal.fire({
              title: split[0],
              html: split[3],
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: blueColor,
              cancelButtonColor: redColor,
              confirmButtonText: split[4],
              cancelButtonText: split[6]
            }).then((result) => {
              if (result.value) {
                visibilityLockId.hide()
                visibilityLockOpenId.show()
              }
            })
          } else {
            Swal.fire({
              title: split[0],
              html: split[1] + '<br><br>' + split[2],
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: blueColor,
              cancelButtonColor: redColor,
              confirmButtonText: split[5],
              cancelButtonText: split[6]
            }).then((result) => {
              if (result.value) {
                visibilityLockId.show()
                visibilityLockOpenId.hide()
              }
            })
          }
        })
      } else if (data === 'false') {
        Swal.fire({
          title: programCanNotChangeVisibilityTitle,
          text: programCanNotChangeVisibilityText,
          icon: 'error',
          confirmButtonClass: 'btn btn-danger'
        })
      }
    })
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
        confirmButtonColor: blueColor,
        cancelButtonColor: redColor,
        confirmButtonText: split[3],
        cancelButtonText: split[4]
      }).then((result) => {
        if (result.value) {
          $.post(self.deleteAccountUrl, null, function (data) {
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
    const additionalEmail = $('#additional-email').val()
    $.post(self.saveEmailUrl, {
      firstEmail: newEmail,
      secondEmail: additionalEmail
    }, function (data) {
      switch (parseInt(data.statusCode)) {
        case statusCodeOk:
          Swal.fire({
            title: successText,
            text: checkMailText,
            icon: 'success',
            confirmButtonClass: 'btn btn-success'
          }).then(() => {
            window.location.href = self.profile_edit_url
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
          window.location.href = self.profile_edit_url
      }
      $('#email-ajax').hide()
      $('#save-email').show()
    })
    self.data_changed = false
  })

  $(document).on('click', '#save-username', function () {
    $(this).hide()
    $('#username-ajax').show()

    const username = $('#username')
    $('.error-message').addClass('d-none')

    const newUsername = username.val()

    $.post(self.saveUsername, {
      username: newUsername
    }, function (data) {
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
          window.location.href = self.profile_edit_url
      }
      $('#username-ajax').hide()
      $('#save-username').show()
    })
    self.data_changed = false
  })

  $(document).on('click', '#save-country', function () {
    $(this).hide()
    $('#country-ajax').show()
    const country = $('#select-country').find('select').val()
    $.post(self.saveCountryUrl, {
      country: country
    }, function (data) {
      switch (parseInt(data.statusCode)) {
        case statusCodeUserCountryInvalid:
          alert('invalid country')
          break

        default:
          window.location.href = self.profile_edit_url
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

    $.post(self.savePasswordUrl, {
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
            confirmButtonClass: 'btn btn-success'
          }).then(() => {
            window.location.href = self.profile_edit_url
          })
          break
      }
      $('#password-ajax').hide()
      $('#save-password').show()
    })
  })
}
