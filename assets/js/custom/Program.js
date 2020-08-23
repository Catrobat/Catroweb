/* eslint-env jquery */
/* global Swal */

// eslint-disable-next-line no-unused-vars
const Program = function (projectId, csrfToken, userRole, myProgram, statusUrl, createUrl, likeUrl,
  likeDetailUrl, apkPreparing, apkText, updateAppHeader, updateAppText,
  btnClosePopup, likeActionAdd, likeActionRemove, profileUrl, wowWhite, wowBlack, reactionsText, downloadErrorText) {
  const self = this

  self.projectId = projectId
  self.csrfToken = csrfToken
  self.userRole = userRole
  self.myProgram = myProgram
  self.statusUrl = statusUrl
  self.createUrl = createUrl
  self.apkPreparing = apkPreparing
  self.apkText = apkText
  self.updateAppHeader = updateAppHeader
  self.updateAppText = updateAppText
  self.btnClosePopup = btnClosePopup
  self.likeActionAdd = likeActionAdd
  self.likeActionRemove = likeActionRemove
  self.apk_url = null
  self.apk_download_timeout = false
  self.wowWhite = wowWhite
  self.wowBlack = wowBlack
  self.reactionsText = reactionsText
  self.downloadErrorText = downloadErrorText
  self.download = function (downloadUrl, projectId, buttonId, supported = true, isWebView = false,
    downloadPbID, downloadIconID) {
    const downloadProgressBar = $(downloadPbID)
    const downloadIcon = $(downloadIconID)
    const button = document.querySelector(buttonId)
    if (isWebView) {
      window.location = downloadUrl
      return
    }
    button.disabled = true
    if (!supported) {
      self.showPreparingApkPopup()
      button.disabled = false
      return
    }
    downloadIcon.addClass('d-none')
    downloadProgressBar.removeClass('d-none')
    downloadProgressBar.addClass('d-inline-block')
    fetch(downloadUrl)
      .then(function (response) {
        if (!response.ok) {
          // eslint-disable-next-line no-undef
          showSnackbar('#share-snackbar', self.downloadErrorText)
          return null
        }
        return response.blob()
      })
      .then(blob => {
        const url = window.URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.style.display = 'none'
        a.href = url
        a.download = projectId + '.catrobat'
        document.body.appendChild(a)
        a.click()
        window.URL.revokeObjectURL(url)
        button.disabled = false
        downloadIcon.removeClass('d-none')
        downloadIcon.addClass('d-inline-block')
        downloadProgressBar.removeClass('d-inline-block')
        downloadProgressBar.addClass('d-none')
      })
      .catch(() => {
        button.disabled = false
        downloadIcon.removeClass('d-none')
        downloadIcon.addClass('d-inline-block')
        downloadProgressBar.removeClass('d-inline-block')
        downloadProgressBar.addClass('d-none')
        console.error('downloading project ' + projectId + 'failed')
      })
  }

  self.getApkStatus = function () {
    $.get(self.statusUrl, null, self.onResult)
  }

  self.createApk = function () {
    $('#apk-generate, #apk-generate-small').addClass('d-none')
    $('#apk-pending, #apk-pending-small').removeClass('d-none')
    $.get(self.createUrl, null, self.onResult)
    self.showPreparingApkPopup()
  }

  self.onResult = function (data) {
    const apkPending = $('#apk-pending, #apk-pending-small')
    const apkDownload = $('#apk-download, #apk-download-small')
    const apkGenerate = $('#apk-generate, #apk-generate-small')
    apkGenerate.addClass('d-none')
    apkDownload.addClass('d-none')
    apkPending.addClass('d-none')
    if (data.status === 'ready') {
      self.apk_url = data.url
      apkDownload.removeClass('d-none')
      apkDownload.click(function () {
        if (!self.apk_download_timeout) {
          self.apk_download_timeout = true

          setTimeout(function () {
            self.apk_download_timeout = false
          }, 5000)

          top.location.href = self.apk_url
        }
      })
    } else if (data.status === 'pending') {
      apkPending.removeClass('d-none')
      setTimeout(self.getApkStatus, 5000)
    } else if (data.status === 'none') {
      apkGenerate.removeClass('d-none')
      apkGenerate.click(self.createApk)
    } else {
      apkGenerate.removeClass('d-none')
    }

    const bgDarkPopupInfo = $('#bg-dark, #popup-info')
    if (bgDarkPopupInfo.length > 0 && data.status === 'ready') {
      bgDarkPopupInfo.remove()
    }
  }

  self.createLinks = function () {
    $('#description').each(function () {
      $(this).html($(this).html().replace(/((http|https|ftp):\/\/[\w?=&./+-;#~%-]+(?![\w\s?&./;#~%"=-]*>))/g, '<a href="$1" target="_blank">$1</a> '))
    })
  }

  self.showUpdateAppPopup = function () {
    const popupBackground = self.createPopupBackgroundDiv()
    const popupDiv = self.createPopupDiv()
    const body = $('body')
    popupDiv.append('<h2>' + self.updateAppHeader + '</h2><br>')
    popupDiv.append('<p>' + self.updateAppText + '</p>')

    const closePopupButton = '<button id="btn-close-popup" class="btn btn-primary btn-close-popup">' + self.btnClosePopup + '</button>'
    popupDiv.append(closePopupButton)

    body.append(popupBackground)
    body.append(popupDiv)

    $('#popup-background, #btn-close-popup').click(function () {
      popupDiv.remove()
      popupBackground.remove()
    })
  }

  self.showPreparingApkPopup = function () {
    const popupBackground = self.createPopupBackgroundDiv()
    const popupDiv = self.createPopupDiv()
    const body = $('body')
    const apkSpinner = $('#apk-pb')
    apkSpinner.removeClass('d-none')

    popupDiv.append('<h2>' + self.apkPreparing + '</h2><br>')
    popupDiv.append(apkSpinner)
    popupDiv.append('<p>' + self.apkText + '</p>')

    const closePopupButton = '<button id="btn-close-popup" class="btn btn-primary btn-close-popup">' + self.btnClosePopup + '</button>'
    popupDiv.append(closePopupButton)

    body.append(popupBackground)
    body.append(popupDiv)

    $('#popup-background, #btn-close-popup').click(function () {
      apkSpinner.addClass('d-none')
      popupDiv.remove()
      popupBackground.remove()
    })
  }

  self.createPopupDiv = function () {
    return $('<div id="popup-info" class="popup-div"></div>')
  }

  self.createPopupBackgroundDiv = function () {
    return $('<div id="popup-background" class="popup-bg"></div>')
  }

  self.createCookie = function createCookie (name, value, days2expire, path) {
    const date = new Date()
    date.setTime(date.getTime() + (days2expire * 24 * 60 * 60 * 1000))
    const expires = date.toUTCString()
    document.cookie = name + '=' + value + ';' +
      'expires=' + expires + ';' +
      'path=' + path + ';'
  }

  self.createCookie('referrer', document.referrer, 1, '/')

  self.showErrorAlert = function (message) {
    if (typeof message !== 'string' || message === '') {
      message = 'Something went wrong! Please try again later.'
    }

    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: message
    })
  }

  self.$projectLikeCounter = undefined
  self.$projectLikeButtons = undefined
  self.$projectLikeDetail = undefined

  self.initProjectLike = function () {
    let detailOpened = false

    const $container = $('#project-like')

    const $buttons = $('#project-like-buttons', $container)
    const $detail = $('#project-like-detail', $container)
    const $counter = $('#project-like-counter', $container)
    self.$projectLikeCounter = $counter
    self.$projectLikeButtons = $buttons
    self.$projectLikeDetail = $detail

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
    self.$projectLikeCounterSmall = $counterSmall
    self.$projectLikeButtonsSmall = $buttonsSmall
    self.$projectLikeDetailSmall = $detailSmall

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
    $counter.on('click', { small: false }, self.counterClickAction)
    $counterSmall.on('click', { small: true }, self.counterClickAction)
    $detail.find('.btn').on('click', self.detailsAction)
    $detailSmall.find('.btn').on('click', self.detailsAction)
  }

  self.counterClickAction = function (event) {
    if (event.data.small) {
      $('#project-reactions-spinner-small').removeClass('d-none')
    } else {
      $('#project-reactions-spinner').removeClass('d-none')
    }
    $.getJSON(likeDetailUrl,
      /** @param {{user: {id: string, name: string}, types: string[]}[]} data */
      function (data) {
        if (!Array.isArray(data)) {
          self.showErrorAlert()
          console.error('Invalid data returned by likeDetailUrl', data)
          return
        }

        const $modal = $('#project-like-modal')

        const thumbsUpData = data.filter(x => x.types.indexOf('thumbs_up') !== -1)
        const smileData = data.filter(x => x.types.indexOf('smile') !== -1)
        const loveData = data.filter(x => x.types.indexOf('love') !== -1)
        const wowData = data.filter(x => x.types.indexOf('wow') !== -1)

        /**
         * @param type string
         * @param data {{user: {id: string, name: string}, types: string[]}[]}
         */
        const fnUpdateContent = (type, data) => {
          const $tab = /** @type jQuery */ $modal.find('a#' + type + '-tab')
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
            $like.append($('<a/>').attr('href', profileUrl.replace('USERID', like.user.id)).text(like.user.name))
            const $likeTypes = $('<div/>').addClass('types')
            $like.append($likeTypes)

            const iconMapping = {
              thumbs_up: 'thumb_up',
              smile: 'sentiment_very_satisfied',
              love: 'favorite',
              wow: 'wow'
            }
            const iconMappingClasses = {
              thumbs_up: 'thumbs-up',
              smile: 'smile',
              love: 'love',
              wow: 'wow'
            }

            like.types.forEach((type) => {
              if (type !== 'wow') {
                $likeTypes.append($('<i/>').addClass('material-icons md-18 ' + iconMappingClasses[type]).append(iconMapping[type]))
              } else {
                const img = document.createElement('IMG')
                img.src = self.wowBlack
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

        $modal.modal('show')
      }).fail(function (jqXHR, textStatus, errorThrown) {
      $('#project-reactions-spinner').hide()
      $('#project-reactions-spinner-small').hide()
      self.showErrorAlert()
      console.error('Failed fetching like list', jqXHR, textStatus, errorThrown)
    })
  }
  self.detailsAction = function (event) {
    event.preventDefault()
    const action = this.classList.contains('active') ? likeActionRemove : likeActionAdd
    self.sendProjectLike($(this).data('like-type'), action, self.$projectLikeButtonsSmall,
      self.$projectLikeCounterSmall, self.$projectLikeDetailSmall, true)
    self.sendProjectLike($(this).data('like-type'), action, self.$projectLikeButtons,
      self.$projectLikeCounter, self.$projectLikeDetail, false)
  }
  self.sendProjectLike = function (likeType, likeAction, likeButtons, likeCounter, likeDetail, smallScreen) {
    const url = likeUrl +
      '?type=' + encodeURIComponent(likeType) +
      '&action=' + encodeURIComponent(likeAction) +
      '&token=' + encodeURIComponent(csrfToken)

    if (self.userRole === 'guest') {
      window.location.href = url
      return false
    }

    $.ajax({
      url: url,
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
        likeCounter.text(data.totalLikeCount.stringValue + ' ' + self.reactionsText)
        if (data.totalLikeCount.value === 0) {
          likeCounter.addClass('d-none')
        } else {
          likeCounter.removeClass('d-none')
        }

        // update like buttons (behavior like in program.html.twig)
        if (!Array.isArray(data.activeLikeTypes) || data.activeLikeTypes.length === 0) {
          likeButtons.html('<div class="btn btn-primary btn-round d-inline-flex justify-content-center">' +
            '<i class="material-icons thumbs-up ' + iconSize + '">thumb_up</i></div>')
        } else {
          let html = ''

          if (data.activeLikeTypes.indexOf('thumbs_up') !== -1) {
            html += '<div class="btn btn-primary btn-round d-inline-flex justify-content-center">' +
              '<i class="material-icons thumbs-up ' + iconSize + '">thumb_up</i></div>'
          }

          if (data.activeLikeTypes.indexOf('smile') !== -1) {
            html += '<div class="btn btn-primary btn-round d-inline-flex justify-content-center">' +
              '<i class="material-icons smile ' + iconSize + '">sentiment_very_satisfied</i></div>'
          }

          if (data.activeLikeTypes.indexOf('love') !== -1) {
            html += '<div class="btn btn-primary btn-round d-inline-flex justify-content-center">' +
              '<i class="material-icons love ' + iconSize + '">favorite</i></div>'
          }

          if (data.activeLikeTypes.indexOf('wow') !== -1) {
            const img = document.createElement('IMG')
            const div = document.createElement('DIV')
            div.className = 'btn btn-primary btn-round d-inline-flex justify-content-center align-items-center'
            div.id = 'wow-reaction'
            img.src = self.wowWhite
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
      }
    }).fail(function (jqXHR, textStatus, errorThrown) {
      // on 401 redirect to url to log in
      if (jqXHR.status === 401) {
        window.location.href = url
      } else {
        console.error('Like failure', jqXHR, textStatus, errorThrown)
        self.showErrorAlert()
      }
    })
  }

  $(function () {
    self.initProjectLike()
  })
  self.projectViewButtonsAction = function (url, spinner, icon = null) {
    const buttonSpinner = $(spinner)
    if (icon) {
      const buttonIcon = $(icon)
      buttonIcon.hide()
    }
    buttonSpinner.removeClass('d-none')
    window.location.href = url
  }
}
