/* global uploadAvatarUrl */
/* global apiUserPrograms */
/* global userID */
/* global profileUrl */
/* global saveUsername */
/* global saveEmailUrl */
/* global saveCountryUrl */
/* global savePasswordUrl */
/* global deleteUrl */
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
/* global statusCodeUserCountryInvalid */
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

import $ from 'jquery'
import { MyProfile } from './custom/MyProfile'
import { ProjectLoader } from './custom/ProjectLoader'
import './custom/PasswordVisibilityToggler'
import { setImageUploadListener } from './custom/ImageUpload'

require('../styles/custom/login.scss')
require('../styles/custom/profile.scss')

$(() => {
  setImageUploadListener(uploadAvatarUrl, '#avatar-upload', '#avatar-img')

  MyProfile(
    profileUrl,
    saveUsername,
    saveEmailUrl,
    saveCountryUrl,
    savePasswordUrl,
    deleteUrl,
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
    parseInt(statusCodeUserCountryInvalid),
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
    parseInt(statusCodeUsernameContainsEmail)
  )

  const programs = new ProjectLoader('#myprofile-programs', apiUserPrograms)
  programs.initProfile(userID)
})
