/* global uploadAvatarUrl */
/* global apiUserPrograms */
/* global userID */
/* global profileUrl */
/* global saveUsername */
/* global saveEmailUrl */
/* global savePasswordUrl */
/* global deleteAccountUrl */
/* global toggleVisibilityUrl */
/* global uploadUrl */
/* global statusCodeOk */
/* global statusCodeUsernameAlreadyExists */
/* global statusCodeUsernameMissing */
/* global statusCodeUsernameInvalid */
/* global statusCodeUserEmailAlreadyExists */
/* global statusCodeUserEmailMissing */
/* global statusCodeUserEmailInvalid */
/* global statusCodeUsernamePasswordEqual */
/* global statusCodeUserPasswordTooShort */
/* global statusCodeUserPasswordTooLong */
/* global statusCodeUserPasswordNotEqualPassword2 */
/* global statusCodePasswordInvalid */
/* global successText */
/* global checkMailText */
/* global passwordUpdatedText */
/* global statusCodeUsernameContainsEmail */
/* global programCanNotChangeVisibilityTitle */
/* global programCanNotChangeVisibilityText */
/* global deleteConfirmationMessage */

import $ from 'jquery'
import { MyProfile } from './custom/MyProfile'
import { ProjectLoader } from './custom/ProjectLoader'
import { setImageUploadListener } from './custom/ImageUpload'
import { PasswordVisibilityToggle } from './components/password_visibility_toggle'

require('../styles/custom/profile.scss')

// eslint-disable-next-line no-new
new PasswordVisibilityToggle()

$(() => {
  setImageUploadListener(uploadAvatarUrl, '#avatar-upload', '#avatar-img')

  MyProfile(
    profileUrl,
    saveUsername,
    saveEmailUrl,
    savePasswordUrl,
    deleteAccountUrl,
    toggleVisibilityUrl,
    uploadUrl,
    parseInt(statusCodeOk),
    parseInt(statusCodeUsernameAlreadyExists),
    parseInt(statusCodeUsernameMissing),
    parseInt(statusCodeUsernameInvalid),
    parseInt(statusCodeUserEmailAlreadyExists),
    parseInt(statusCodeUserEmailMissing),
    parseInt(statusCodeUserEmailInvalid),
    parseInt(statusCodeUsernamePasswordEqual),
    parseInt(statusCodeUserPasswordTooShort),
    parseInt(statusCodeUserPasswordTooLong),
    parseInt(statusCodeUserPasswordNotEqualPassword2),
    parseInt(statusCodePasswordInvalid),
    successText,
    checkMailText,
    passwordUpdatedText,
    programCanNotChangeVisibilityTitle,
    programCanNotChangeVisibilityText,
    parseInt(statusCodeUsernameContainsEmail),
    deleteConfirmationMessage
  )

  const programs = new ProjectLoader('#myprofile-programs', apiUserPrograms)
  programs.initProfile(userID)
})
