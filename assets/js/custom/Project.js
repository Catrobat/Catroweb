import $ from 'jquery'
import { Modal, Tab } from 'bootstrap'
import Swal from 'sweetalert2'
import { showSnackbar } from '../components/snackbar'
import { redirect } from '../components/redirect_button'
import { ApiFetch } from '../api/ApiHelper'

export const Project = function (
  projectId,
  projectName,
  userRole,
  myProgram,
  statusUrl,
  createUrl,
  likeUrl,
  likeDetailUrl,
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
  getApkStatus()

  // -------------------------- FileHelper

  function createLinks() {
    $('#description').each(function () {
      $(this).html(
        $(this)
          .html()
          .replace(
            /((http|https|ftp):\/\/[\w?=&./+-;#~%-]+(?![\w\s?&./;#~%"=-]*>))/g,
            '<a href="$1" target="_blank">$1</a> ',
          ),
      )
    })
  }

  // -------------------------- Redirect Buttons
  $('.js-redirect-button').on('click', (e) => {
    redirect(
      $(e.currentTarget).data('url'),
      $(e.currentTarget).data('button-id'),
      $(e.currentTarget).data('spinner-id'),
      $(e.currentTarget).data('icon-id'),
    )
  })

  // -------------------------- Download

  $('.js-btn-project-download').on('click', (e) => {
    download(
      $(e.currentTarget).data('path-url'),
      $(e.currentTarget).data('project-id') + '.catrobat',
      $(e.currentTarget).data('button-id'),
      $(e.currentTarget).data('spinner-id'),
      $(e.currentTarget).data('icon-id'),
      $(e.currentTarget).data('is-webview'),
      $(e.currentTarget).data('is-supported'),
      $(e.currentTarget).data('is-not-supported-title'),
      $(e.currentTarget).data('is-not-supported-text'),
    )
  })

  $(
    $('.js-btn-project-download-disabled').on('click', (e) => {
      downloadDisabled(
        $(e.currentTarget).data('redirect-url'),
        // $(e.currentTarget).data('alert-text')
      )
    }),
  )

  $('.js-btn-project-apk-download').on('click', (e) => {
    download(
      $(e.currentTarget).data('path-url'),
      $(e.currentTarget).data('project-id') + '.apk',
      $(e.currentTarget).data('button-id'),
      $(e.currentTarget).data('spinner-id'),
      $(e.currentTarget).data('icon-id'),
      $(e.currentTarget).data('is-webview'),
      $(e.currentTarget).data('is-supported'),
      $(e.currentTarget).data('is-not-supported-title'),
      $(e.currentTarget).data('is-not-supported-text'),
    )
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
      return
    }

    // Unfortunately the android implementation of pocket code has its issues with the new download implementation
    if (isWebView) {
      downloadUrl += downloadUrl.includes('?') ? '&' : '?'
      downloadUrl += 'fname=' + encodeURIComponent(projectName)
      window.location = downloadUrl
      return
    }

    new ApiFetch(downloadUrl)
      .generateAuthenticatedFetch()
      .then(function (response) {
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
        // UX: Tell the use that something went wrong
        showDownloadFailedSnackbar(downloadErrorText, filename)
      })
      .finally(() => {
        // UX: Reset the button to further indicate the successful download
        resetDownloadButtonIcon(icon, loadingSpinner)
        // Performance: Keep the button disabled to prevent spamming the download button
        setTimeout(() => {
          button.disabled = false
        }, 15000)
      })
  }

  function downloadDisabled(redirectUrl) {
    /* Swal.fire({
      icon: 'error',
      title: 'Login',
      text: text,
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false,
      allowOutsideClick: false,
      timer: 5000,
    }).then((result) => {
      if (result.value) {
        window.location.replace(redirectUrl)
      }
    }); */
    window.location.replace(redirectUrl)
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

  function showProjectIsNotSupportedMessage(
    isNotSupportedTitle,
    isNotSupportedText,
  ) {
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
    $.get(statusUrl, null, onResult)
  }

  function createApk() {
    $('#apk-generate, #apk-generate-small').addClass('d-none')
    $('#apk-pending, #apk-pending-small').removeClass('d-none')
    $.get(createUrl, null, onResult)
    showPreparingApkPopup()
  }

  function onResult(data) {
    const apkPending = $('#apk-pending, #apk-pending-small')
    const apkDownload = $(
      '#projectApkDownloadButton, #projectApkDownloadButton-small',
    )
    const apkGenerate = $('#apk-generate, #apk-generate-small')
    apkGenerate.addClass('d-none')
    apkDownload.addClass('d-none')
    apkPending.addClass('d-none')
    if (data && data.status === 'ready') {
      apkDownload.removeClass('d-none')
    } else if (data && data.status === 'pending') {
      apkPending.removeClass('d-none')
      setTimeout(getApkStatus, 5000)
    } else if (data && data.status === 'none') {
      apkGenerate.removeClass('d-none')
      apkGenerate.click(createApk)
    } else {
      apkGenerate.removeClass('d-none')
    }

    const bgDarkPopupInfo = $('#bg-dark, #popup-info')
    if (bgDarkPopupInfo.length > 0 && data.status === 'ready') {
      bgDarkPopupInfo.remove()
    }
  }

  function showPreparingApkPopup() {
    const popupBackground = createPopupBackgroundDiv()
    const popupDiv = createPopupDiv()
    const body = $('body')
    const apkSpinner = $('#apk-pb')
    apkSpinner.removeClass('d-none')

    popupDiv.append('<h2>' + apkPreparing + '</h2><br>')
    popupDiv.append(apkSpinner)
    popupDiv.append('<p>' + apkText + '</p>')

    const closePopupButton =
      '<button id="btn-close-popup" class="btn btn-primary btn-close-popup">' +
      btnClosePopup +
      '</button>'
    popupDiv.append(closePopupButton)

    body.append(popupBackground)
    body.append(popupDiv)

    $('#popup-background, #btn-close-popup').click(function () {
      apkSpinner.addClass('d-none')
      popupDiv.remove()
      popupBackground.remove()
    })
  }

  function createPopupDiv() {
    return $('<div id="popup-info" class="popup-div"></div>')
  }

  function createPopupBackgroundDiv() {
    return $('<div id="popup-background" class="popup-bg"></div>')
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

  let $projectLikeCounter, $projectLikeButtons, $projectLikeDetail
  let $projectLikeCounterSmall,
    $projectLikeButtonsSmall,
    $projectLikeDetailSmall

  function initProjectLike() {
    let detailOpened = false

    const $container = $('#project-like')

    const $buttons = $('#project-like-buttons', $container)
    const $detail = $('#project-like-detail', $container)
    const $counter = $('#project-like-counter', $container)
    $projectLikeCounter = $counter
    $projectLikeButtons = $buttons
    $projectLikeDetail = $detail

    $buttons.on('click', function () {
      if ($detail.css('display') === 'flex') {
        return
      }
      $detail.css('display', 'flex').hide().fadeIn()
      detailOpened = true
    })
    const $containerSmall = $('#project-like-small')
    const $buttonsSmall = $('#project-like-buttons-small', $containerSmall)
    const $detailSmall = $('#project-like-detail-small', $containerSmall)
    const $counterSmall = $('#project-like-counter-small', $containerSmall)
    $projectLikeCounterSmall = $counterSmall
    $projectLikeButtonsSmall = $buttonsSmall
    $projectLikeDetailSmall = $detailSmall

    $buttonsSmall.on('click', function () {
      if ($detailSmall.css('display') === 'flex') {
        return
      }
      $detailSmall.css('display', 'flex').hide().fadeIn()
      detailOpened = true
    })

    $('body').on('mousedown', function () {
      if (detailOpened) {
        $detail.fadeOut()
        $detailSmall.fadeOut()
        detailOpened = false
      }
    })
    $counter.on('click', { small: false }, counterClickAction)
    $counterSmall.on('click', { small: true }, counterClickAction)
    $detail.find('.btn').on('click', detailsAction)
    $detailSmall.find('.btn').on('click', detailsActionSmall)
  }

  function counterClickAction(event) {
    if (event.data.small) {
      $('#project-reactions-spinner-small').removeClass('d-none')
    } else {
      $('#project-reactions-spinner').removeClass('d-none')
    }
    $.getJSON(
      likeDetailUrl,
      /** @param {{user: {id: string, name: string}, types: string[]}[]} data */
      function (data) {
        if (!Array.isArray(data)) {
          showErrorAlert()
          console.error('Invalid data returned by likeDetailUrl', data)
          return
        }

        const $modal = $('#project-like-modal')
        const bootstrapModal = new Modal('#project-like-modal')
        const firstTabEl = document.querySelector(
          '#reaction-modal-tab li:first-child button',
        )
        const firstTab = new Tab(firstTabEl)
        firstTab.show()

        const thumbsUpData = data.filter(
          (x) => x.types.indexOf('thumbs_up') !== -1,
        )
        const smileData = data.filter((x) => x.types.indexOf('smile') !== -1)
        const loveData = data.filter((x) => x.types.indexOf('love') !== -1)
        const wowData = data.filter((x) => x.types.indexOf('wow') !== -1)

        /**
         * @param type string
         * @param data {{user: {id: string, name: string}, types: string[]}[]}
         */
        const fnUpdateContent = (type, data) => {
          const $tab = /** @type jQuery */ $modal.find(
            'button#' + type + '-tab',
          )
          const $content = $modal.find('#' + type + '-tab-content')
          $content.empty()

          // count
          $tab.find(' > span').text(data.length)

          if (data.length === 0 && type !== 'all') {
            $tab.parent().hide()
            return
          } else {
            $tab.parent().show()
          }

          // tab content
          data.forEach(function (like) {
            const $like = $('<div/>').addClass('reaction')
            $like.append(
              $('<a/>')
                .attr('href', profileUrl.replace('USERID', like.user.id))
                .text(like.user.name),
            )
            const $likeTypes = $('<div/>').addClass('types')
            $like.append($likeTypes)

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
                $likeTypes.append(
                  $('<i/>')
                    .addClass(
                      'material-icons md-18 ' + iconMappingClasses[type],
                    )
                    .append(iconMapping[type]),
                )
              } else {
                const img = document.createElement('IMG')
                img.src = wowBlack
                img.className = 'wow'
                img.id = 'wow-reaction-modal'
                img.alt = 'Wow Reaction'
                $likeTypes.append(img)
              }
            })

            $content.append($like)
          })
        }

        fnUpdateContent('all', data)
        fnUpdateContent('thumbs-up', thumbsUpData)
        fnUpdateContent('smile', smileData)
        fnUpdateContent('love', loveData)
        fnUpdateContent('wow', wowData)
        $('#project-reactions-spinner').addClass('d-none')
        $('#project-reactions-spinner-small').addClass('d-none')

        bootstrapModal.show()
      },
    ).fail(function (jqXHR, textStatus, errorThrown) {
      $('#project-reactions-spinner').hide()
      $('#project-reactions-spinner-small').hide()
      showErrorAlert()
      console.error('Failed fetching like list', jqXHR, textStatus, errorThrown)
    })
  }

  function detailsAction(event) {
    event.preventDefault()
    const action = this.classList.contains('active')
      ? likeActionRemove
      : likeActionAdd
    sendProjectLike(
      $(this).data('like-type'),
      action,
      $projectLikeButtons,
      $projectLikeCounter,
      $projectLikeDetail,
      false,
    )
  }

  function detailsActionSmall(event) {
    event.preventDefault()
    const action = this.classList.contains('active')
      ? likeActionRemove
      : likeActionAdd
    sendProjectLike(
      $(this).data('like-type'),
      action,
      $projectLikeButtonsSmall,
      $projectLikeCounterSmall,
      $projectLikeDetailSmall,
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
    const url =
      likeUrl +
      '?type=' +
      encodeURIComponent(likeType) +
      '&action=' +
      encodeURIComponent(likeAction)

    if (userRole === 'guest') {
      window.location.href = url
      return false
    }

    $.ajax({
      url,
      type: 'get',
      success: function (data) {
        // update .active of button
        const typeBtn = likeDetail.find('.btn[data-like-type=' + likeType + ']')
        if (likeAction === likeActionAdd) {
          typeBtn.addClass('active')
        } else {
          typeBtn.removeClass('active')
        }
        let iconSize = 'md-28'
        if (smallScreen) {
          iconSize = 'md-24'
        }

        // update like count
        likeCounter.text(data.totalLikeCount.stringValue + ' ' + reactionsText)
        if (data.totalLikeCount.value === 0) {
          likeCounter.addClass('d-none')
        } else {
          likeCounter.removeClass('d-none')
        }

        // update like buttons (behavior like in project.html.twig)
        if (
          !Array.isArray(data.activeLikeTypes) ||
          data.activeLikeTypes.length === 0
        ) {
          likeButtons.html(
            '<div class="btn btn-primary btn-round d-inline-flex justify-content-center">' +
              '<i class="material-icons thumbs-up ' +
              iconSize +
              '">thumb_up</i></div>',
          )
        } else {
          let html = ''

          if (data.activeLikeTypes.indexOf('thumbs_up') !== -1) {
            html +=
              '<div class="btn btn-primary btn-round d-inline-flex justify-content-center">' +
              '<i class="material-icons thumbs-up ' +
              iconSize +
              '">thumb_up</i></div>'
          }

          if (data.activeLikeTypes.indexOf('smile') !== -1) {
            html +=
              '<div class="btn btn-primary btn-round d-inline-flex justify-content-center">' +
              '<i class="material-icons smile ' +
              iconSize +
              '">sentiment_very_satisfied</i></div>'
          }

          if (data.activeLikeTypes.indexOf('love') !== -1) {
            html +=
              '<div class="btn btn-primary btn-round d-inline-flex justify-content-center">' +
              '<i class="material-icons love ' +
              iconSize +
              '">favorite</i></div>'
          }

          if (data.activeLikeTypes.indexOf('wow') !== -1) {
            const img = document.createElement('IMG')
            const div = document.createElement('DIV')
            div.className =
              'btn btn-primary btn-round d-inline-flex justify-content-center align-items-center'
            div.id = 'wow-reaction'
            img.src = wowWhite
            img.id = 'wow-reaction-img'
            if (smallScreen) {
              img.id = 'wow-reaction-img-small'
            }
            img.className = 'wow'
            div.append(img)
            html += div.outerHTML
          }
          likeButtons.html(html)
        }
      },
    }).fail(function (jqXHR, textStatus, errorThrown) {
      // on 401 redirect to url to log in
      if (jqXHR.status === 401) {
        window.location.href = url
      } else {
        console.error('Like failure', jqXHR, textStatus, errorThrown)
        showErrorAlert()
      }
    })
  }

  $(function () {
    initProjectLike()
  })
}

// -------------------------- APK Logic
// Implementation not even finished
//

$(document).on('click', function (e) {
  const ellipsisContainer = $('#sign-app-ellipsis-container')
  if (
    !(ellipsisContainer.is(e.target) || $('#sign-app-ellipsis').is(e.target))
  ) {
    ellipsisContainer.hide()
  }
})

$(document).ready(function () {
  $('#sign-app-ellipsis').on('click', function () {
    $('#sign-app-ellipsis-container').show()
  })

  $('#toggle_ads').on('click', function () {
    if ($('#show_ads_chk').is(':checked')) {
      $('#ads_info').show()
    } else {
      $('#ads_info').hide()
    }
  })

  $('#key_store_file').on('change', function () {
    $('#key_store_file_text').val($('#key_store_file').val())
  })
  $('#key_store_file_text').on('click', function () {
    $('#key_store_file').trigger('click')
    $(this).blur()
  })
  $('#key_store_icon').on('click', function () {
    $('#key_store_file').trigger('click')
  })

  $('#key_store_path').on('change', function () {
    $('#key_store_path_text').val($('#key_store_path').val())
  })
  $('#key_file_path_icon').on('click', function () {
    $('#key_store_path').trigger('click')
  })
  $('#key_store_path_text').on('click', function () {
    $('#key_store_path').trigger('click')
    $(this).blur()
  })
  $('#inc_years').on('click', function () {
    const yearsField = $('#key_validity')
    if (yearsField.val() < 99) {
      yearsField.val(parseInt(yearsField.val()) + 1)
    }
  })
  $('#dec_years').on('click', function () {
    const yearsField = $('#key_validity')
    if (yearsField.val() > 0) {
      yearsField.val(parseInt(yearsField.val()) - 1)
    }
  })
})

document.addEventListener('DOMContentLoaded', function () {
  const button = document.getElementById('projectNotForKidsButton')
  if (button != null) {
    button.addEventListener('click', function (event) {
      event.preventDefault()
      const markSafeForKidsText = document.getElementById('markSafeForKidsText')
      const markNotForKidsText = document.getElementById('markNotForKidsText')
      const url = document
        .getElementById('projectNotForKidsButton')
        .getAttribute('data-url')
      let text = ''
      if (markSafeForKidsText != null) {
        text =
          'Are you sure you want to remove the not for kids flag from this project?'
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
