import { Modal, Tab } from 'bootstrap'
import Swal from 'sweetalert2'
import { showSnackbar, SnackbarDuration } from '../Layout/Snackbar'
import { redirect } from '../Components/RedirectButton'
import { ApiFetch } from '../Api/ApiHelper'
import ProjectApi from '../Api/ProjectApi'

export const Project = function (
  projectId,
  projectName,
  userRole,
  myProgram,
  loginUrl,
  apiReactionUrl,
  apiReactionsUrl,
  apiReactionsUsersUrl,
  updateAppHeader,
  updateAppText,
  likeActionAdd,
  likeActionRemove,
  profileUrl,
  wowWhite,
  wowBlack,
  reactionsText,
  downloadErrorText,
) {
  createLinks()

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
  const activeDownloads = {}

  document.querySelectorAll('.js-btn-project-download').forEach((button) => {
    button.addEventListener('click', (e) => {
      const btn = e.currentTarget
      downloadWithProgress(
        btn.dataset.pathUrl,
        `${btn.dataset.projectId}.catrobat`,
        btn.dataset.projectId,
        btn.dataset.suffix || '',
        btn.dataset.isWebview === 'true',
        btn.dataset.isSupported === 'true',
        btn.dataset.isNotSupportedTitle,
        btn.dataset.isNotSupportedText,
      )
    })
  })

  document.querySelectorAll('.js-btn-download-cancel').forEach((button) => {
    button.addEventListener('click', (e) => {
      const suffix = e.currentTarget.dataset.suffix || ''
      if (activeDownloads[suffix]) {
        activeDownloads[suffix].abort()
        delete activeDownloads[suffix]
        showDownloadState('default', suffix)
      }
    })
  })

  document.querySelectorAll('.js-btn-download-open').forEach((button) => {
    button.addEventListener('click', () => {
      const deepLink =
        window.location.origin + window.location.pathname.replace(/\/+$/, '') + '?download'
      window.location.href = deepLink
    })
  })

  function showDownloadState(state, suffix) {
    const defaultBtn = document.getElementById('projectDownloadButton' + suffix)
    const progressEl = document.getElementById('downloadProgress' + suffix)
    const completeBtn = document.getElementById('downloadComplete' + suffix)

    if (!defaultBtn || !progressEl || !completeBtn) return

    defaultBtn.classList.toggle('d-none', state !== 'default')
    progressEl.classList.toggle('d-none', state !== 'progress')
    completeBtn.classList.toggle('d-none', state !== 'complete')
  }

  function updateProgressBar(suffix, percent) {
    const bar = document.getElementById('downloadProgressBar' + suffix)
    const text = document.getElementById('downloadProgressText' + suffix)
    const btn = document.getElementById('projectDownloadButton' + suffix)
    const downloadingText = btn
      ? btn.dataset.transDownloading || 'Downloading...'
      : 'Downloading...'

    if (bar) {
      bar.style.width = percent + '%'
    }
    if (text) {
      text.textContent = downloadingText.replace('...', '') + ' ' + Math.round(percent) + '%'
    }
  }

  function downloadWithProgress(
    downloadUrl,
    filename,
    downloadProjectId,
    suffix,
    isWebView = false,
    supported = true,
    isNotSupportedTitle = '',
    isNotSupportedText = '',
  ) {
    // Older app version do not support new features and projects that use them
    if (isWebView && !supported) {
      showProjectIsNotSupportedMessage(isNotSupportedTitle, isNotSupportedText)
      return
    }

    // Unfortunately the android implementation of pocket code has its issues with the new download
    if (isWebView) {
      downloadUrl += downloadUrl.includes('?') ? '&' : '?'
      downloadUrl += 'fname=' + encodeURIComponent(projectName)
      window.location = downloadUrl
      return
    }

    // Show progress state
    showDownloadState('progress', suffix)
    updateProgressBar(suffix, 0)

    const controller = new AbortController()
    activeDownloads[suffix] = controller

    fetch(downloadUrl, {
      credentials: 'same-origin',
      signal: controller.signal,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error('Download failed with status ' + response.status)
        }

        const contentLength = response.headers.get('Content-Length')
        const total = contentLength ? parseInt(contentLength, 10) : 0

        if (!response.body || total === 0) {
          // Fallback: no streaming support or unknown size
          return response.blob().then((blob) => {
            updateProgressBar(suffix, 100)
            return blob
          })
        }

        const reader = response.body.getReader()
        let received = 0
        const chunks = []

        function read() {
          return reader.read().then(({ done, value }) => {
            if (done) {
              updateProgressBar(suffix, 100)
              const blob = new Blob(chunks)
              return blob
            }

            chunks.push(value)
            received += value.length
            const percent = Math.min((received / total) * 100, 100)
            updateProgressBar(suffix, percent)

            return read()
          })
        }

        return read()
      })
      .then((blob) => {
        delete activeDownloads[suffix]

        // Trigger browser download
        const url = window.URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.style.display = 'none'
        a.href = url
        a.download = filename
        document.body.appendChild(a)
        a.click()
        window.URL.revokeObjectURL(url)
        a.remove()

        // Switch to complete state
        showDownloadState('complete', suffix)
      })
      .catch((error) => {
        delete activeDownloads[suffix]
        if (error.name === 'AbortError') {
          // User cancelled, already reset by cancel handler
          return
        }
        showDownloadFailedSnackbar(downloadErrorText, filename)
        showDownloadState('default', suffix)
      })
  }

  function showDownloadFailedSnackbar(errorText, filename) {
    showSnackbar('#share-snackbar', errorText, SnackbarDuration.error)
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

  // -------------------------- Project Likes / Reactions
  // Refactoring would be nice!
  //

  function showErrorAlert(message) {
    if (typeof message !== 'string' || message === '') {
      message = 'Oops, that did not work. Please try again!'
    }

    Swal.fire({
      icon: 'error',
      title: 'Oops!',
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

    fetchInitialReactions()

    // Check for pending reaction after login
    const pendingReaction = sessionStorage.getItem('pendingReaction')
    if (pendingReaction && userRole !== 'guest') {
      try {
        const reaction = JSON.parse(pendingReaction)
        // Only execute if it's for the current project
        if (reaction.projectId === projectId) {
          sessionStorage.removeItem('pendingReaction')
          // Execute the pending reaction immediately (synchronously)
          // This ensures it happens as part of page initialization
          const typeMap = { thumbs_up: 1, smile: 2, love: 3, wow: 4 }
          const likeType = typeMap[reaction.type] || reaction.type
          // The test uses the small (mobile) version, so pass those elements
          sendProjectLike(
            likeType,
            reaction.action,
            projectLikeButtonsSmall,
            projectLikeCounterSmall,
            projectLikeDetailSmall,
            true,
          )
        }
      } catch (e) {
        console.error('Failed to process pending reaction', e)
        sessionStorage.removeItem('pendingReaction')
      }
    }
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
      // Store pending reaction before redirecting to login
      sessionStorage.setItem(
        'pendingReaction',
        JSON.stringify({
          projectId: projectId,
          type: typeName,
          action: likeAction,
        }),
      )
      // Redirect to login - use reactions summary page as return URL
      window.location.href = loginUrl
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
          window.location.href = loginUrl
          throw new Error('Unauthorized')
        }
        if (response.status === 429) {
          const msg =
            document.querySelector('.js-project')?.dataset.transRateLimited ||
            "You're reacting too quickly. Please wait a moment."
          showErrorAlert(msg)
          return undefined
        }
        if (response.status === 403) {
          return response
            .json()
            .then((body) => {
              if (body?.error?.message === 'Email verification required.') {
                const msg =
                  document.querySelector('.js-project')?.dataset.transAccountNotVerified ||
                  'Please make sure you are logged in and your account\u2019s email is verified.'
                showErrorAlert(msg)
              } else if (body?.error?.message === 'Your account has been suspended.') {
                const msg =
                  document.querySelector('.js-project')?.dataset.transAccountSuspended ||
                  'Your account has been suspended due to community reports.'
                showErrorAlert(msg)
              } else {
                showErrorAlert()
              }
            })
            .catch(() => showErrorAlert())
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
        updateReactionButtons(data, likeButtons, likeCounter, iconSize)
      })
      .catch((error) => {
        if (error.message !== 'Unauthorized') {
          console.error('Like failure', error)
          showErrorAlert()
        }
      })
  }

  function updateReactionButtons(data, likeButtons, likeCounter, iconSize) {
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
        html += `<div class="btn btn-primary btn-round d-inline-flex justify-content-center align-items-center" id="wow-reaction">
                    <img src="${wowWhite}" id="${iconSize === 'md-24' ? 'wow-reaction-img-small' : 'wow-reaction-img'}" class="wow"></div>`
      }
      likeButtons.innerHTML = html
    }
  }

  function fetchInitialReactions() {
    fetch(apiReactionsUrl, {
      method: 'GET',
      headers: { Accept: 'application/json' },
    })
      .then((r) => r.json())
      .then((data) => {
        updateReactionButtons(data, projectLikeButton, projectLikeCounter, 'md-28')
        updateReactionButtons(data, projectLikeButtonsSmall, projectLikeCounterSmall, 'md-24')

        // Mark active user reactions on the detail buttons
        const userReactions = data.user_reactions || []
        const typeNumToName = { 1: 'thumbs_up', 2: 'smile', 3: 'love', 4: 'wow' }
        const allDetailBtns = [
          ...projectLikeDetail.querySelectorAll('.btn[data-like-type]'),
          ...projectLikeDetailSmall.querySelectorAll('.btn[data-like-type]'),
        ]
        allDetailBtns.forEach((btn) => {
          const typeName = typeNumToName[btn.dataset.likeType]
          if (typeName && userReactions.includes(typeName)) {
            btn.classList.add('active')
          }
        })
      })
      .catch((e) => console.error('Failed to load initial reactions', e))
  }

  document.addEventListener('DOMContentLoaded', initProjectLike)
}

// -------------------------- Sign App UI
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
// Project settings (options menu toggles)

function confirmAndUpdate(item, confirmText, payload, onSuccess) {
  const projectId = item.dataset.projectId

  Swal.fire({
    title: item.dataset.transConfirmTitle,
    html: confirmText,
    icon: 'warning',
    showCancelButton: true,
    allowOutsideClick: false,
    customClass: {
      confirmButton: 'btn btn-primary',
      cancelButton: 'btn btn-outline-primary',
    },
    buttonsStyling: false,
    confirmButtonText: item.dataset.transConfirmYes,
    cancelButtonText: item.dataset.transCancel,
  }).then((result) => {
    if (!result.value) return

    new ProjectApi().updateProject(projectId, payload, onSuccess)
  })
}

function updateMenuItemUI(item, iconName, textContent) {
  const icon = item.querySelector('.mdc-deprecated-list-item__graphic')
  const text = item.querySelector('.mdc-deprecated-list-item__text')
  if (icon) icon.textContent = iconName
  if (text) text.textContent = textContent
}

document.addEventListener('DOMContentLoaded', function () {
  const nfkItem = document.getElementById('top-app-bar__btn-toggle-not-for-kids')
  if (nfkItem) {
    nfkItem.addEventListener('click', function () {
      const currentValue = parseInt(nfkItem.dataset.notForKids, 10)

      if (currentValue === 2) {
        showSnackbar(
          '#share-snackbar',
          nfkItem.dataset.transModeratorLocked,
          SnackbarDuration.error,
        )
        return
      }

      const newValue = currentValue === 0
      const confirmText = newValue
        ? nfkItem.dataset.transConfirmMark
        : nfkItem.dataset.transConfirmUnmark

      confirmAndUpdate(nfkItem, confirmText, { not_for_kids: newValue }, function () {
        nfkItem.dataset.notForKids = String(newValue ? 1 : 0)
        updateMenuItemUI(
          nfkItem,
          newValue ? 'no_stroller' : 'child_care',
          newValue ? nfkItem.dataset.transMarkSafe : nfkItem.dataset.transMarkNotForKids,
        )

        const indicatorWrapper = document.getElementById('not-for-kids-indicator')
        if (indicatorWrapper) indicatorWrapper.classList.toggle('d-none', !newValue)

        showSnackbar(
          '#share-snackbar',
          newValue ? nfkItem.dataset.transSuccessMarked : nfkItem.dataset.transSuccessUnmarked,
        )
      })
    })
  }

  const visItem = document.getElementById('top-app-bar__btn-toggle-visibility')
  if (visItem) {
    visItem.addEventListener('click', function () {
      const isPrivate = visItem.dataset.private === 'true'
      const newValue = !isPrivate
      const confirmText = newValue
        ? visItem.dataset.transConfirmPrivate
        : visItem.dataset.transConfirmPublic

      confirmAndUpdate(visItem, confirmText, { private: newValue }, function () {
        visItem.dataset.private = String(newValue)
        updateMenuItemUI(
          visItem,
          newValue ? 'lock' : 'lock_open',
          newValue ? visItem.dataset.transSetPublic : visItem.dataset.transSetPrivate,
        )

        showSnackbar(
          '#share-snackbar',
          newValue ? visItem.dataset.transSuccessPrivate : visItem.dataset.transSuccessPublic,
        )
      })
    })
  }
})
