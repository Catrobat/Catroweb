/* eslint-env jquery */
/* global Swal */

// eslint-disable-next-line no-unused-vars
const Program = function (projectId, csrfToken, userRole, myProgram, statusUrl, createUrl, likeUrl,
  likeDetailUrl, apkPreparing, apkText, updateAppHeader, updateAppText,
  btnClosePopup, likeActionAdd, likeActionRemove, profileUrl) {
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

  self.getApkStatus = function () {
    $.get(self.statusUrl, null, self.onResult)
  }

  self.createApk = function () {
    $('#apk-generate').addClass('d-none')
    $('#apk-pending').removeClass('d-none')
    $.get(self.createUrl, null, self.onResult)
    self.showPreparingApkPopup()
  }

  self.onResult = function (data) {
    const apkPending = $('#apk-pending')
    const apkDownload = $('#apk-download')
    const apkGenerate = $('#apk-generate')
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

    popupDiv.append('<h2>' + self.apkPreparing + '</h2><br>')
    popupDiv.append('<i class="fa fa-spinner fa-pulse fa-2x fa-fw" aria-hidden="true">')
    popupDiv.append('<p>' + self.apkText + '</p>')

    const closePopupButton = '<button id="btn-close-popup" class="btn btn-primary btn-close-popup">' + self.btnClosePopup + '</button>'
    popupDiv.append(closePopupButton)

    body.append(popupBackground)
    body.append(popupDiv)

    $('#popup-background, #btn-close-popup').click(function () {
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

    $('body').on('mousedown', function () {
      if (detailOpened) {
        $detail.fadeOut()
        detailOpened = false
      }
    })

    $counter.on('click', function () {
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
                thumbs_up: 'fa-thumbs-up',
                smile: 'fa-grin-squint',
                love: 'fa-heart',
                wow: 'fa-surprise'
              }

              like.types.forEach((type) => {
                $likeTypes.append($('<i/>').addClass('fas').addClass(iconMapping[type]))
              })

              $content.append($like)
            })
          }

          fnUpdateContent('all', data)
          fnUpdateContent('thumbs-up', thumbsUpData)
          fnUpdateContent('smile', smileData)
          fnUpdateContent('love', loveData)
          fnUpdateContent('wow', wowData)

          $modal.modal('show')
        }).fail(function (jqXHR, textStatus, errorThrown) {
        self.showErrorAlert()
        console.error('Failed fetching like list', jqXHR, textStatus, errorThrown)
      })
    })

    $detail.find('.btn').on('click', function (event) {
      event.preventDefault()
      const action = this.classList.contains('active') ? likeActionRemove : likeActionAdd
      self.sendProjectLike($(this).data('like-type'), action)
    })
  }

  self.sendProjectLike = function (likeType, likeAction) {
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
        const typeBtn = self.$projectLikeDetail.find('.btn[data-like-type=' + likeType + ']')
        if (likeAction === likeActionAdd) {
          typeBtn.addClass('active')
        } else {
          typeBtn.removeClass('active')
        }

        // update like count
        self.$projectLikeCounter.text(data.totalLikeCount.stringValue)
        if (data.totalLikeCount.value === 0) {
          self.$projectLikeCounter.addClass('d-none')
        } else {
          self.$projectLikeCounter.removeClass('d-none')
        }

        // update like buttons (behavior like in program.html.twig)
        if (!Array.isArray(data.activeLikeTypes) || data.activeLikeTypes.length === 0) {
          self.$projectLikeButtons.html('<div class="btn btn-primary btn-round"><i class="fas fa-thumbs-up"></i></div>')
        } else {
          let html = ''

          if (data.activeLikeTypes.indexOf('thumbs_up') !== -1) {
            html += '<div class="btn btn-primary btn-round"><i class="fas fa-thumbs-up"></i></div>'
          }

          if (data.activeLikeTypes.indexOf('smile') !== -1) {
            html += '<div class="btn icon-only"><i class="fas fa-grin-squint"></i></div>'
          }

          if (data.activeLikeTypes.indexOf('love') !== -1) {
            html += '<div class="btn btn-primary btn-round"><i class="fas fa-heart"></i></div>'
          }

          if (data.activeLikeTypes.indexOf('wow') !== -1) {
            html += '<div class="btn icon-only"><i class="fas fa-surprise"></i></div>'
          }

          self.$projectLikeButtons.html(html)
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
}
