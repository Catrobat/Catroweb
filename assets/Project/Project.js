import { Modal, Tab } from 'bootstrap'
import Swal from 'sweetalert2'
import { showSnackbar } from '../Layout/Snackbar'
import { redirect } from '../Components/RedirectButton'
import { ApiFetch } from '../Api/ApiHelper'

export const Project = function (
  projectId,
  projectName,
  userRole,
  myProgram,
  statusUrl,
  createUrl,
  apiReactionUrl,
  apiReactionsUrl,
  apiReactionsUsersUrl,
  apkPreparing,
  apkText,
  updateAppHeader,
  updateAppText,
  btnClosePopup,
  likeActionAdd,
  likeActionRemove,
  profileUrl,
  wowWhite,
  wowBlack,
  reactionsText,
  downloadErrorText,
  downloadStartedText,
) {
  createLinks()
  // getApkStatus() - APKs are disabled

  // -------------------------- FileHelper

  function createLinks() {
    document.querySelectorAll('#description').forEach((element) => {
      element.innerHTML = element.innerHTML.replace(
        /((http|https|ftp):\/\/[\w?=&./+\-;#~%]+(?![\w\s?&./;#~%"=-]*>))/g,
        '<a href="$1" target="_blank">$1</a> ',
      )
    })
  }

  // -------------------------- Redirect Buttons
  document.querySelectorAll('.js-redirect-button').forEach((button) => {
    button.addEventListener('click', (e) => {
      redirect(
        e.currentTarget.dataset.url,
        e.currentTarget.dataset.buttonId,
        e.currentTarget.dataset.spinnerId,
        e.currentTarget.dataset.iconId,
      )
    })
  })

  // -------------------------- Download
  document.querySelectorAll('.js-btn-project-download').forEach((button) => {
    button.addEventListener('click', (e) => {
      download(
        e.currentTarget.dataset.pathUrl,
        `${e.currentTarget.dataset.projectId}.catrobat`,
        e.currentTarget.dataset.buttonId,
        e.currentTarget.dataset.spinnerId,
        e.currentTarget.dataset.iconId,
        e.currentTarget.dataset.isWebview,
        e.currentTarget.dataset.isSupported,
        e.currentTarget.dataset.isNotSupportedTitle,
        e.currentTarget.dataset.isNotSupportedText,
      )
    })
  })

  document.querySelectorAll('.js-btn-project-apk-download').forEach((button) => {
    button.addEventListener('click', (e) => {
      download(
        e.currentTarget.dataset.pathUrl,
        `${e.currentTarget.dataset.projectId}.apk`,
        e.currentTarget.dataset.buttonId,
        e.currentTarget.dataset.spinnerId,
        e.currentTarget.dataset.iconId,
        e.currentTarget.dataset.isWebview,
        e.currentTarget.dataset.isSupported,
        e.currentTarget.dataset.isNotSupportedTitle,
        e.currentTarget.dataset.isNotSupportedText,
      )
    })
  })

  function download(
    downloadUrl,
    filename,
    buttonId,
    spinnerId,
    iconId,
    isWebView = false,
    supported = true,
    isNotSupportedTitle = '',
    isNotSupportedText = '',
  ) {
    const button = document.getElementById(buttonId)
    const loadingSpinner = document.getElementById(spinnerId)
    const icon = document.getElementById(iconId)

    // UX - feedback loop: downloads of large projects can take a few seconds / minutes
    showSnackbar('#share-snackbar', downloadStartedText)

    // UX + Performance: prevent multiple same downloads
    button.disabled = true
    icon.classList.add('d-none')
    loadingSpinner.classList.remove('d-none')
    loadingSpinner.classList.add('d-inline-block')

    // Older app version do not support new features and projects that use them
    if (isWebView && !supported) {
      showProjectIsNotSupportedMessage(isNotSupportedTitle, isNotSupportedText)
      resetDownloadButtonIcon(icon, loadingSpinner)
      setTimeout(() => {
        button.disabled = false
      }, 2000)
      return
    }

    // Unfortunately the android implementation of pocket code has its issues with the new download implementation
    if (isWebView) {
      downloadUrl += downloadUrl.includes('?') ? '&' : '?'
      downloadUrl += 'fname=' + encodeURIComponent(projectName)
      window.location = downloadUrl
      resetDownloadButtonIcon(icon, loadingSpinner)
      setTimeout(() => {
        button.disabled = false
      }, 2000)
      return
    }

    new ApiFetch(downloadUrl)
      .generateAuthenticatedFetch()
      .then((response) => {
        // fetching the data in the background; this allows us to detect when the download is finished!
        if (response.ok) {
          return response.blob()
        }
      })
      .then((blob) => {
        // once the data was fetched the downloaded data can be saved
        const url = window.URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.style.display = 'none'
        a.href = url
        a.download = filename
        document.body.appendChild(a)
        a.click()
        window.URL.revokeObjectURL(url)
      })
      .catch(() => {
        // UX: Tell the user that something went wrong
        showDownloadFailedSnackbar(downloadErrorText, filename)
      })
      .finally(() => {
        // UX: Reset the button to further indicate the successful download
        resetDownloadButtonIcon(icon, loadingSpinner)
        // UX: Keep the button disabled briefly to prevent accidental double-clicks
        setTimeout(() => {
          button.disabled = false
        }, 2000)
      })
  }

  function resetDownloadButtonIcon(icon, spinner) {
    icon.classList.remove('d-none')
    icon.classList.add('d-inline-block')
    spinner.classList.remove('d-inline-block')
    spinner.classList.add('d-none')
  }

  function showDownloadFailedSnackbar(downloadErrorText, filename) {
    showSnackbar('#share-snackbar', downloadErrorText)
    console.error('Downloading ' + filename + ' failed')
  }

  function showProjectIsNotSupportedMessage(isNotSupportedTitle, isNotSupportedText) {
    Swal.fire({
      icon: 'error',
      title: isNotSupportedTitle,
      text: isNotSupportedText,
      customClass: {
        confirmButton: 'btn btn-primary',
      },
      buttonsStyling: false,
      allowOutsideClick: false,
    }).then()
  }

  // -------------------------- APK Logic
  // Refactoring would be nice!
  //

  function getApkStatus() {
    fetch(statusUrl)
      .then((response) => response.json())
      .then(onResult)
  }

  function createApk() {
    document.getElementById('apk-generate').classList.add('d-none')
    document.getElementById('apk-generate-small').classList.add('d-none')
    document.getElementById('apk-pending').classList.remove('d-none')
    document.getElementById('apk-pending-small').classList.remove('d-none')
    fetch(createUrl)
      .then((response) => response.json())
      .then(onResult)
    showPreparingApkPopup()
  }

  function onResult(data) {
    const apkPending = document.querySelectorAll('#apk-pending, #apk-pending-small')
    const apkDownload = document.querySelectorAll(
      '#projectApkDownloadButton, #projectApkDownloadButton-small',
    )
    const apkGenerate = document.querySelectorAll('#apk-generate, #apk-generate-small')
    apkGenerate.forEach((el) => el.classList.add('d-none'))
    apkDownload.forEach((el) => el.classList.add('d-none'))
    apkPending.forEach((el) => el.classList.add('d-none'))

    if (data && data.status === 'ready') {
      apkDownload.forEach((el) => el.classList.remove('d-none'))
    } else if (data && data.status === 'pending') {
      apkPending.forEach((el) => el.classList.remove('d-none'))
      setTimeout(getApkStatus, 5000)
    } else if (data && data.status === 'none') {
      apkGenerate.forEach((el) => el.classList.remove('d-none'))
      apkGenerate.forEach((el) => el.addEventListener('click', createApk))
    } else {
      apkGenerate.forEach((el) => el.classList.remove('d-none'))
    }

    const bgDarkPopupInfo = document.querySelectorAll('#bg-dark, #popup-info')
    if (bgDarkPopupInfo.length > 0) {
      bgDarkPopupInfo.forEach((el) => el.classList.add('d-none'))
    }
  }

  function showPreparingApkPopup() {
    const popupBackground = createPopupBackgroundDiv()
    const popupDiv = createPopupDiv()
    const body = document.body
    const apkSpinner = document.getElementById('apk-pb')
    apkSpinner.classList.remove('d-none')

    const h2 = document.createElement('h2')
    h2.textContent = apkPreparing
    popupDiv.appendChild(h2)
    popupDiv.appendChild(document.createElement('br'))
    popupDiv.appendChild(apkSpinner)

    const p = document.createElement('p')
    p.textContent = apkText
    popupDiv.appendChild(p)

    const closePopupButton = document.createElement('button')
    closePopupButton.id = 'btn-close-popup'
    closePopupButton.className = 'btn btn-primary btn-close-popup'
    closePopupButton.textContent = btnClosePopup
    popupDiv.appendChild(closePopupButton)

    body.appendChild(popupBackground)
    body.appendChild(popupDiv)

    popupBackground.addEventListener('click', closePopup)
    closePopupButton.addEventListener('click', closePopup)

    function closePopup() {
      apkSpinner.classList.add('d-none')
      popupDiv.remove()
      popupBackground.remove()
    }
  }

  function createPopupDiv() {
    const div = document.createElement('div')
    div.id = 'popup-info'
    div.className = 'popup-div'
    return div
  }

  function createPopupBackgroundDiv() {
    const div = document.createElement('div')
    div.id = 'popup-background'
    div.className = 'popup-bg'
    return div
  }

  // -------------------------- Project Likes / Reactions
  // Refactoring would be nice!
  //

  function showErrorAlert(message) {
    if (typeof message !== 'string' || message === '') {
      message = 'Something went wrong! Please try again later.'
    }

    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: message,
      customClass: {
        confirmButton: 'btn btn-primary',
      },
      buttonsStyling: false,
      allowOutsideClick: false,
    })
  }

  let projectLikeCounter, projectLikeButton, projectLikeDetail
  let projectLikeCounterSmall, projectLikeButtonsSmall, projectLikeDetailSmall

  function initProjectLike() {
    let detailOpened = false

    const container = document.getElementById('project-like')

    projectLikeButton = container.querySelector('#project-like-buttons')
    projectLikeDetail = container.querySelector('#project-like-detail')
    projectLikeCounter = container.querySelector('#project-like-counter')

    projectLikeButton.addEventListener('click', function () {
      if (projectLikeDetail.style.display === 'flex') {
        return
      }
      projectLikeDetail.style.display = 'flex'
      detailOpened = true
    })

    const containerSmall = document.getElementById('project-like-small')
    projectLikeButtonsSmall = containerSmall.querySelector('#project-like-buttons-small')
    projectLikeDetailSmall = containerSmall.querySelector('#project-like-detail-small')
    projectLikeCounterSmall = containerSmall.querySelector('#project-like-counter-small')

    projectLikeButtonsSmall.addEventListener('click', function () {
      if (projectLikeDetailSmall.style.display === 'flex') {
        return
      }
      projectLikeDetailSmall.style.display = 'flex'
      detailOpened = true
    })

    document.body.addEventListener('mousedown', function () {
      if (!detailOpened) {
        return
      }
      const isClickInsideDetail =
        projectLikeDetail.contains(event.target) || projectLikeDetailSmall.contains(event.target)
      if (!isClickInsideDetail) {
        projectLikeDetail.style.display = 'none'
        projectLikeDetailSmall.style.display = 'none'
        detailOpened = false
      }
    })

    projectLikeCounter.addEventListener('click', (event) => counterClickAction(event, false))
    projectLikeCounterSmall.addEventListener('click', (event) => counterClickAction(event, true))

    projectLikeDetail
      .querySelectorAll('.btn')
      .forEach((button) => button.addEventListener('click', detailsAction))
    projectLikeDetailSmall
      .querySelectorAll('.btn')
      .forEach((button) => button.addEventListener('click', detailsActionSmall))
  }

  function counterClickAction(event, small) {
    const spinner = small
      ? document.getElementById('project-reactions-spinner-small')
      : document.getElementById('project-reactions-spinner')
    spinner.classList.remove('d-none')

    fetch(apiReactionsUsersUrl + '?limit=100', {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
      },
    })
      .then((response) => response.json())
      .then((responseData) => {
        // Transform new API response to old format for compatibility
        const data = (responseData.data || []).map((entry) => ({
          user: {
            id: entry.user?.id || '',
            name: entry.user?.username || '',
          },
          types: entry.types || [],
        }))

        if (!Array.isArray(data)) {
          showErrorAlert()
          console.error('Invalid data returned by apiReactionsUsersUrl', responseData)
          return
        }

        const modal = document.getElementById('project-like-modal')
        const bootstrapModal = new Modal(modal)
        const firstTabEl = document.querySelector('#reaction-modal-tab li:first-child button')
        const firstTab = new Tab(firstTabEl)
        firstTab.show()

        const thumbsUpData = data.filter((x) => x.types.includes('thumbs_up'))
        const smileData = data.filter((x) => x.types.includes('smile'))
        const loveData = data.filter((x) => x.types.includes('love'))
        const wowData = data.filter((x) => x.types.includes('wow'))

        const fnUpdateContent = (type, data) => {
          const tab = modal.querySelector('button#' + type + '-tab')
          const content = modal.querySelector('#' + type + '-tab-content')
          content.innerHTML = ''

          tab.querySelector('span').textContent = data.length

          if (data.length === 0 && type !== 'all') {
            tab.parentElement.style.display = 'none'
            return
          } else {
            tab.parentElement.style.display = 'block'
          }

          data.forEach(function (like) {
            const likeDiv = document.createElement('div')
            likeDiv.className = 'reaction'

            const likeLink = document.createElement('a')
            likeLink.href = profileUrl.replace('USERID', like.user.id)
            likeLink.textContent = like.user.name
            likeDiv.appendChild(likeLink)

            const likeTypes = document.createElement('div')
            likeTypes.className = 'types'
            likeDiv.appendChild(likeTypes)

            const iconMapping = {
              thumbs_up: 'thumb_up',
              smile: 'sentiment_very_satisfied',
              love: 'favorite',
              wow: 'wow',
            }
            const iconMappingClasses = {
              thumbs_up: 'thumbs-up',
              smile: 'smile',
              love: 'love',
              wow: 'wow',
            }

            like.types.forEach((type) => {
              if (type !== 'wow') {
                const icon = document.createElement('i')
                icon.className = 'material-icons md-18 ' + iconMappingClasses[type]
                icon.textContent = iconMapping[type]
                likeTypes.appendChild(icon)
              } else {
                const img = document.createElement('IMG')
                img.src = wowBlack
                img.className = 'wow wow-reaction-modal'
                img.alt = 'Wow Reaction'
                likeTypes.appendChild(img)
              }
            })

            content.appendChild(likeDiv)
          })
        }

        fnUpdateContent('all', data)
        fnUpdateContent('thumbs-up', thumbsUpData)
        fnUpdateContent('smile', smileData)
        fnUpdateContent('love', loveData)
        fnUpdateContent('wow', wowData)

        document.getElementById('project-reactions-spinner').classList.add('d-none')
        document.getElementById('project-reactions-spinner-small').classList.add('d-none')

        bootstrapModal.show()
      })
      .catch((error) => {
        document.getElementById('project-reactions-spinner').style.display = 'none'
        document.getElementById('project-reactions-spinner-small').style.display = 'none'
        showErrorAlert()
        console.error('Failed fetching like list', error)
      })
  }

  function detailsAction(event) {
    event.preventDefault()
    const action = this.classList.contains('active') ? likeActionRemove : likeActionAdd
    sendProjectLike(
      this.dataset.likeType,
      action,
      projectLikeButton,
      projectLikeCounter,
      projectLikeDetail,
      false,
    )
  }

  function detailsActionSmall(event) {
    event.preventDefault()
    const action = this.classList.contains('active') ? likeActionRemove : likeActionAdd
    sendProjectLike(
      this.dataset.likeType,
      action,
      projectLikeButtonsSmall,
      projectLikeCounterSmall,
      projectLikeDetailSmall,
      true,
    )
  }

  function sendProjectLike(
    likeType,
    likeAction,
    likeButtons,
    likeCounter,
    likeDetail,
    smallScreen,
  ) {
    // Map numeric type to string type name
    const typeMap = { 1: 'thumbs_up', 2: 'smile', 3: 'love', 4: 'wow' }
    const typeName = typeMap[likeType] || likeType

    if (userRole === 'guest') {
      // Redirect to login - use reactions summary page as return URL
      window.location.href = '/login'
      return false
    }

    const isAdd = likeAction === likeActionAdd
    let url = apiReactionUrl
    if (!isAdd) {
      url = `${apiReactionUrl}?type=${encodeURIComponent(typeName)}`
    }

    // Use ApiFetch to include JWT Bearer token for authenticated requests
    const apiFetch = new ApiFetch(
      url,
      isAdd ? 'POST' : 'DELETE',
      isAdd ? { type: typeName } : undefined,
    )
    apiFetch
      .generateAuthenticatedFetch()
      .then((response) => {
        if (response.status === 401) {
          window.location.href = '/login'
          throw new Error('Unauthorized')
        }
        // Both POST (201/200) and DELETE (204) need to fetch summary for updated counts
        // POST returns ReactionResponse with just {type}, DELETE returns 204 No Content
        if (response.ok) {
          return fetch(apiReactionsUrl, {
            method: 'GET',
            headers: { Accept: 'application/json' },
          }).then((r) => r.json())
        }
        // Handle other errors
        throw new Error(`Request failed with status ${response.status}`)
      })
      .then((data) => {
        if (!data) return

        const typeBtn = likeDetail.querySelector(`.btn[data-like-type="${likeType}"]`)
        if (likeAction === likeActionAdd) {
          typeBtn.classList.add('active')
        } else {
          typeBtn.classList.remove('active')
        }

        const iconSize = smallScreen ? 'md-24' : 'md-28'
        const totalCount = data.total || 0
        likeCounter.textContent = `${totalCount} ${reactionsText}`

        if (totalCount === 0) {
          likeCounter.classList.add('d-none')
        } else {
          likeCounter.classList.remove('d-none')
        }

        const activeLikeTypes = data.active_types || []
        if (!Array.isArray(activeLikeTypes) || activeLikeTypes.length === 0) {
          likeButtons.innerHTML = `<div class="btn btn-primary btn-round d-inline-flex justify-content-center">
                    <i class="material-icons thumbs-up ${iconSize}">thumb_up</i></div>`
        } else {
          let html = ''

          if (activeLikeTypes.includes('thumbs_up')) {
            html += `<div class="btn btn-primary btn-round d-inline-flex justify-content-center">
                        <i class="material-icons thumbs-up ${iconSize}">thumb_up</i></div>`
          }

          if (activeLikeTypes.includes('smile')) {
            html += `<div class="btn btn-primary btn-round d-inline-flex justify-content-center">
                        <i class="material-icons smile ${iconSize}">sentiment_very_satisfied</i></div>`
          }

          if (activeLikeTypes.includes('love')) {
            html += `<div class="btn btn-primary btn-round d-inline-flex justify-content-center">
                        <i class="material-icons love ${iconSize}">favorite</i></div>`
          }

          if (activeLikeTypes.includes('wow')) {
            const img = document.createElement('IMG')
            const div = document.createElement('DIV')
            div.className =
              'btn btn-primary btn-round d-inline-flex justify-content-center align-items-center'
            div.id = 'wow-reaction'
            img.src = wowWhite
            img.id = smallScreen ? 'wow-reaction-img-small' : 'wow-reaction-img'
            img.className = 'wow'
            div.appendChild(img)
            html += div.outerHTML
          }
          likeButtons.innerHTML = html
        }
      })
      .catch((error) => {
        if (error.message !== 'Unauthorized') {
          console.error('Like failure', error)
          showErrorAlert()
        }
      })
  }

  document.addEventListener('DOMContentLoaded', initProjectLike)
}

// -------------------------- APK Logic
// Implementation not finished
//

document.addEventListener('click', function (e) {
  const ellipsisContainer = document.getElementById('sign-app-ellipsis-container')
  const ellipsis = document.getElementById('sign-app-ellipsis')

  if (
    ellipsisContainer &&
    !(ellipsisContainer.contains(e.target) || ellipsis?.contains(e.target))
  ) {
    ellipsisContainer.style.display = 'none'
  }
})

document.addEventListener('DOMContentLoaded', function () {
  document.getElementById('sign-app-ellipsis')?.addEventListener('click', function () {
    document.getElementById('sign-app-ellipsis-container').style.display = 'block'
  })

  document.getElementById('toggle_ads')?.addEventListener('click', function () {
    const adsInfo = document.getElementById('ads_info')
    const showAdsChk = document.getElementById('show_ads_chk')

    if (showAdsChk.checked) {
      adsInfo.style.display = 'block'
    } else {
      adsInfo.style.display = 'none'
    }
  })

  const keyStoreFile = document.getElementById('key_store_file')
  const keyStoreFileText = document.getElementById('key_store_file_text')
  const keyStoreIcon = document.getElementById('key_store_icon')
  const keyStorePath = document.getElementById('key_store_path')
  const keyStorePathText = document.getElementById('key_store_path_text')
  const keyFilePathIcon = document.getElementById('key_file_path_icon')

  keyStoreFile?.addEventListener('change', function () {
    keyStoreFileText.value = keyStoreFile.value
  })

  keyStoreFileText?.addEventListener('click', function () {
    keyStoreFile.click()
    keyStoreFileText.blur()
  })

  keyStoreIcon?.addEventListener('click', function () {
    keyStoreFile.click()
  })

  keyStorePath?.addEventListener('change', function () {
    keyStorePathText.value = keyStorePath.value
  })

  keyFilePathIcon?.addEventListener('click', function () {
    keyStorePath.click()
  })

  keyStorePathText?.addEventListener('click', function () {
    keyStorePath.click()
    keyStorePathText.blur()
  })

  document.getElementById('inc_years')?.addEventListener('click', function () {
    const yearsField = document.getElementById('key_validity')
    if (yearsField.value < 99) {
      yearsField.value = parseInt(yearsField.value) + 1
    }
  })

  document.getElementById('dec_years')?.addEventListener('click', function () {
    const yearsField = document.getElementById('key_validity')
    if (yearsField.value > 0) {
      yearsField.value = parseInt(yearsField.value) - 1
    }
  })
})

// --------------------------------
// Not 4 kids!

document.addEventListener('DOMContentLoaded', function () {
  const button = document.getElementById('projectNotForKidsButton')
  if (button != null) {
    button.addEventListener('click', function (event) {
      event.preventDefault()
      const markSafeForKidsText = document.getElementById('markSafeForKidsText')
      const markNotForKidsText = document.getElementById('markNotForKidsText')
      const url = document.getElementById('projectNotForKidsButton').getAttribute('data-url')
      let text = ''
      if (markSafeForKidsText != null) {
        text = 'Are you sure you want to remove the not for kids flag from this project?'
      } else if (markNotForKidsText != null) {
        text = 'Are you sure you want to mark this project as not for kids?'
      }
      askForConfirmation(submitNotForKidsForm, url, text)
    })
  }
})

function askForConfirmation(continueWithAction, url, text) {
  const areYouSure = 'Confirmation'
  const cancel = 'Cancel'
  const okayText = 'Yes, proceed!'

  Swal.fire({
    title: areYouSure,
    html: text + '<br><br>',
    icon: 'warning',
    showCancelButton: true,
    allowOutsideClick: false,
    customClass: {
      confirmButton: 'btn btn-primary',
      cancelButton: 'btn btn-outline-primary',
    },
    buttonsStyling: false,
    confirmButtonText: okayText,
    cancelButtonText: cancel,
  }).then((result) => {
    if (result.value) {
      continueWithAction(url)
    }
  })
}

function submitNotForKidsForm(url) {
  const form = document.createElement('form')
  form.setAttribute('method', 'post')
  form.setAttribute('action', url)
  form.style.display = 'hidden'
  document.body.appendChild(form)
  form.submit()
}
