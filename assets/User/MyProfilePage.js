/* global globalConfiguration */
/* global myProfileConfiguration */

import 'external-svg-loader'
import '../Components/FullscreenListModal'
import '../Components/TextField'
import '../Components/TabBar'
import { Modal } from 'bootstrap'
import { PasswordVisibilityToggle } from '../Components/PasswordVisibilityToggle'
import Swal from 'sweetalert2'
import MessageDialogs from '../Components/MessageDialogs'
import { ApiDeleteFetch, ApiFetch, ApiPatchFetch } from '../Api/ApiHelper'
import { showSnackbar, SnackbarDuration } from '../Layout/Snackbar'
import VerifyAccountHandler from './VerifyAccountHandler'
import { escapeHtml } from '../Components/HtmlEscape'
import { achievementBadgeHtml } from './AchievementBadge'
import { ProjectList } from '../Project/ProjectList'
import { prepareImageFileForUpload, readFileAsDataUrl } from '../Components/ImageUploadHelper'

require('./Profile.scss')
require('./Achievements.scss')
require('../Project/ProjectsBrowse.scss')

document.addEventListener('DOMContentLoaded', () => {
  if (window.location.search.includes('profileChangeSuccess')) {
    window.history.replaceState(
      undefined,
      document.title,
      window.location.origin + window.location.pathname,
    )
  }

  new PasswordVisibilityToggle()

  const ownProjects = document.getElementById('own-projects')
  const baseUrl = ownProjects.dataset.baseUrl
  new OwnProfile(baseUrl).initializeAll()
  new VerifyAccountHandler().init()
  initProfileAchievements()
  initOwnProjectList(ownProjects)

  // Appeal button for suspended accounts
  const appealBtn = document.getElementById('btn-appeal-user')
  if (appealBtn) {
    import('../Moderation/AppealDialog').then(({ showAppealDialog }) => {
      appealBtn.addEventListener('click', () => {
        showAppealDialog({
          contentType: appealBtn.dataset.contentType,
          contentId: appealBtn.dataset.contentId,
          apiUrl: appealBtn.dataset.appealUrl,
          translations: {
            title: appealBtn.dataset.transAppealTitle,
            placeholder: appealBtn.dataset.transAppealPlaceholder,
            submit: appealBtn.dataset.transAppealSubmit,
            cancel: appealBtn.dataset.transAppealCancel,
            success: appealBtn.dataset.transAppealSuccess,
            alreadyPending: appealBtn.dataset.transAppealAlreadyPending,
            error: appealBtn.dataset.transAppealError,
            rateLimited: appealBtn.dataset.transAppealRateLimited,
          },
        })
      })
    })
  }
})

class OwnProfile {
  constructor(baseUrl) {
    this.baseUrl = baseUrl
  }

  initializeAll() {
    this.initProfilePictureChange()
    this.initSaveProfileSettings()
    this.initSaveSecuritySettings()
    this.initExportData()
    this.initDeleteAccount()
  }

  async updateProfilePicture(pictureDataUrl) {
    const response = await window.fetch(this.baseUrl + '/api/users/me', {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: { 'Content-type': 'application/json' },
      body: JSON.stringify({ picture: pictureDataUrl }),
    })

    return response.status === 204
  }

  updateProfile(data, successCallback, finalCallback) {
    new ApiPatchFetch(
      this.baseUrl + '/api/users/me',
      data,
      'Save Profile',
      myProfileConfiguration.messages.unspecifiedErrorText,
      successCallback,
      undefined,
      finalCallback,
    ).run()
  }

  initProfilePictureChange() {
    const self = this
    const avatarElements = document.getElementsByClassName('profile__basic-info__avatar')
    if (avatarElements.length) {
      this.avatarElement = avatarElements[0]
      this.avatarElement.addEventListener('click', function () {
        const input = document.createElement('input')
        input.type = 'file'
        input.accept = 'image/*'
        input.style.display = 'none'
        document.body.appendChild(input)
        self.addProfilePictureChangeListenerToInput(input)
        input.click()
      })

      if (globalConfiguration.environment === 'test') {
        const input = document.createElement('input')
        input.type = 'file'
        input.accept = 'image/*'
        self.addProfilePictureChangeListenerToInput(input)
        input.name = 'own-profile-picture-upload-field'
        input.className = 'd-none'
        this.avatarElement.appendChild(input)
      }
    }
  }

  addProfilePictureChangeListenerToInput(input) {
    const self = this
    input.addEventListener('change', async () => {
      let loadingSpinner
      if (this.avatarElement) {
        const loadingSpinnerTemplate = document.getElementById(
          'profile-loading-spinner-template',
        ).content
        this.avatarElement.appendChild(loadingSpinnerTemplate.cloneNode(true))
        loadingSpinner = this.avatarElement.getElementsByClassName(
          loadingSpinnerTemplate.children[0].className,
        )[0]
      }

      const removeLoadingSpinner = () => {
        if (
          loadingSpinner &&
          self.avatarElement &&
          loadingSpinner.parentElement === this.avatarElement
        ) {
          this.avatarElement.removeChild(loadingSpinner)
          loadingSpinner = null
        }
      }

      const processed = await prepareImageFileForUpload(input.files?.[0])
      if (!processed.ok) {
        removeLoadingSpinner()
        showSnackbar(
          '#share-snackbar',
          myProfileConfiguration.messages.profilePictureInvalid,
          SnackbarDuration.error,
        )
        return
      }

      try {
        const image = await readFileAsDataUrl(processed.file)
        const wasSuccessful = await self.updateProfilePicture(image)
        if (!wasSuccessful) {
          showSnackbar(
            '#share-snackbar',
            myProfileConfiguration.messages.profilePictureInvalid,
            SnackbarDuration.error,
          )
          return
        }

        const avatarImage = self.avatarElement?.querySelector('.profile__basic-info__avatar__img')
        if (avatarImage) {
          avatarImage.src = image
        }
        showSnackbar('#share-snackbar', myProfileConfiguration.messages.imageUploadSuccess)
      } catch {
        showSnackbar(
          '#share-snackbar',
          myProfileConfiguration.messages.profilePictureInvalid,
          SnackbarDuration.error,
        )
      } finally {
        removeLoadingSpinner()
      }
    })
  }

  initSaveProfileSettings() {
    const self = this
    document.getElementById('profile_settings-save_action').addEventListener('click', () => {
      const form = document.getElementById('profile-settings-form')
      if (form.reportValidity() === true) {
        const formData = new window.FormData(form)
        const data = {}
        formData.forEach((value, key) => (data[key] = value))
        self.updateProfile(data, function () {
          window.location.search = 'profileChangeSuccess'
        })
      }
    })
  }

  initSaveSecuritySettings() {
    const self = this
    document.getElementById('security_settings-save_action').addEventListener('click', () => {
      const form = document.getElementById('security-settings-form')
      if (form.reportValidity() === true) {
        const formData = new window.FormData(form)
        if (formData.get('password') !== formData.get('repeat-password')) {
          MessageDialogs.showErrorMessage(
            myProfileConfiguration.messages.security.passwordsDontMatch,
          )
        } else {
          self.updateProfile(
            {
              current_password: formData.get('current-password'),
              password: formData.get('password'),
            },
            function () {
              MessageDialogs.showSuccessMessage(
                myProfileConfiguration.messages.passwordChangedSuccessText,
              ).then(() => {
                form.reset()
                Modal.getInstance(document.getElementById('security-settings-modal')).hide()
              })
            },
          )
        }
      }
    })
  }

  initExportData() {
    const btn = document.getElementById('btn-export-data')
    if (!btn) return

    const originalHTML = btn.innerHTML
    const loadingText = btn.dataset.transExportLoading
    const errorText = btn.dataset.transExportError
    const rateLimitedText = btn.dataset.transExportRateLimited

    btn.addEventListener('click', async () => {
      btn.disabled = true
      btn.innerHTML =
        '<span class="spinner-border spinner-border-sm me-1" role="status"></span> ' + loadingText

      try {
        const response = await new ApiFetch(
          this.baseUrl + '/api/users/me/data-export',
          'GET',
          undefined,
          'none',
        ).generateAuthenticatedFetch()

        if (response.ok) {
          const data = await response.json()
          const blob = new Blob([JSON.stringify(data, null, 2)], {
            type: 'application/json',
          })
          const url = URL.createObjectURL(blob)
          const a = document.createElement('a')
          a.href = url
          a.download = 'my-data-export.json'
          document.body.appendChild(a)
          a.click()
          document.body.removeChild(a)
          URL.revokeObjectURL(url)
        } else if (response.status === 401) {
          window.location.href = this.baseUrl + '/app/login'
          return
        } else if (response.status === 429) {
          MessageDialogs.showErrorMessage(rateLimitedText)
        } else {
          MessageDialogs.showErrorMessage(errorText)
        }
      } catch {
        MessageDialogs.showErrorMessage(errorText)
      }

      btn.innerHTML = originalHTML
      btn.disabled = false
    })
  }

  initDeleteAccount() {
    const routingDataset = document.getElementById('js-api-routing').dataset
    document.getElementById('btn-delete-account').addEventListener('click', () => {
      const msgParts =
        myProfileConfiguration.userSettings.deleteAccount.confirmationText.split('\n')
      Swal.fire({
        title: msgParts[0],
        html: msgParts[1] + '<br><br>' + msgParts[2],
        icon: 'warning',
        showCancelButton: true,
        allowOutsideClick: false,
        customClass: {
          confirmButton: 'btn btn-danger',
          cancelButton: 'btn btn-outline-primary',
        },
        buttonsStyling: false,
        confirmButtonText: msgParts[3],
        cancelButtonText: msgParts[4],
      }).then((result) => {
        if (result.value) {
          new ApiDeleteFetch(
            this.baseUrl + '/api/users/me',
            'Delete User',
            myProfileConfiguration.messages.unspecifiedErrorText,
            function () {
              window.location.href = routingDataset.index
            },
          ).run()
        }
      })
      document.querySelector('.swal2-container.swal2-backdrop-show').style.backgroundColor =
        'rgba(220, 53, 69, 0.75)' // changes the color of the overlay
    })
  }
}

function initProfileAchievements() {
  const container = document.querySelector('.js-profile-achievements')
  if (!container) {
    return
  }

  const baseUrl = container.dataset.baseUrl
  const userId = container.dataset.userId
  const title = container.dataset.transTitle

  fetch(baseUrl + '/api/users/' + userId + '/achievements', {
    headers: { Accept: 'application/json' },
  })
    .then((r) => {
      if (!r.ok) throw new Error('HTTP ' + r.status)
      return r.json()
    })
    .then((response) => {
      const achievements = Array.isArray(response) ? response : response?.data || []
      if (!achievements || achievements.length === 0) {
        container.remove()
        return
      }

      const badgesHtml = achievements
        .map(
          (achievement) =>
            '<div class="achievement__badge achievement__badge--profile">' +
            achievementBadgeHtml(achievement, 'profile') +
            '</div>',
        )
        .join('')

      container.innerHTML =
        '<hr>' +
        '<h3>' +
        escapeHtml(title) +
        '</h3>' +
        '<div class="horizontal-scrolling-wrapper">' +
        badgesHtml +
        '</div>' +
        '<hr>'
    })
    .catch((error) => {
      console.error('Failed to load profile achievements:', error)
      container.remove()
    })
}

function initOwnProjectList(container) {
  const baseUrl = container.dataset.baseUrl
  const theme = container.dataset.theme
  const emptyMessage = container.dataset.emptyMessage

  new ProjectList(
    container,
    'user-projects',
    `${baseUrl}/api/projects/user`,
    container.dataset.property,
    theme,
    999,
    emptyMessage,
    {},
  )
}
