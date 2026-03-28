/* global globalConfiguration */
/* global myProfileConfiguration */

import '../Components/FullscreenListModal'
import '../Components/TextField'
import '../Components/TabBar'
import { Modal } from 'bootstrap'
import { PasswordVisibilityToggle } from '../Components/PasswordVisibilityToggle'
import { OwnProjectList } from '../Project/OwnProjectList'
import Swal from 'sweetalert2'
import MessageDialogs from '../Components/MessageDialogs'
import { ApiFetch, ApiDeleteFetch, ApiPutFetch } from '../Api/ApiHelper'
import VerifyAccountHandler from './VerifyAccountHandler'

require('./Profile.scss')

document.addEventListener('DOMContentLoaded', () => {
  if (
    window.location.search.includes('profileChangeSuccess') ||
    window.location.search.includes('profilePictureChangeSuccess')
  ) {
    window.history.replaceState(
      undefined,
      document.title,
      window.location.origin + window.location.pathname,
    )
  }

  new PasswordVisibilityToggle()

  const projectsContainer = document.getElementById('own-projects')
  const theme = projectsContainer.dataset.theme
  const baseUrl = projectsContainer.dataset.baseUrl
  const emptyMessage = projectsContainer.dataset.emptyMessage

  const url = baseUrl + '/api/projects/user'

  new OwnProjectList(projectsContainer, url, theme, emptyMessage, baseUrl).initialize()
  new OwnProfile(baseUrl).initializeAll()
  new VerifyAccountHandler().init()

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

  updateProfile(data, successCallback, finalCallback) {
    new ApiPutFetch(
      this.baseUrl + '/api/user',
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
    input.addEventListener('change', () => {
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
      const reader = new window.FileReader()
      reader.onerror = () => {
        if (
          loadingSpinner &&
          self.avatarElement &&
          loadingSpinner.parentElement === this.avatarElement
        ) {
          this.avatarElement.removeChild(loadingSpinner)
          loadingSpinner = null
        }
        MessageDialogs.showErrorMessage(myProfileConfiguration.messages.profilePictureInvalid)
      }
      reader.onload = (event) => {
        const image = event.currentTarget.result // base64 data url
        self.updateProfile(
          { picture: image },
          function () {
            window.location.search = 'profilePictureChangeSuccess'
          },
          function () {
            if (
              loadingSpinner &&
              self.avatarElement &&
              loadingSpinner.parentElement === self.avatarElement
            ) {
              self.avatarElement.removeChild(loadingSpinner)
              loadingSpinner = null
            }
          },
        )
      }
      reader.readAsDataURL(input.files[0])
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
          this.baseUrl + '/api/user/data-export',
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
            this.baseUrl + '/api/user',
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
