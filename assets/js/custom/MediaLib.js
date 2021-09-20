/* global Routing */
import $ from 'jquery'

import { showTopBarDownload, showTopBarDefault } from '../layout/header'

export function MediaLib (packageName, mediaSearchPath, flavor, assetsDir,
  elementsTranslationSingular, elementsTranslationPlural, isWebView = false) {
  $(function () {
    // Removing the project navigation items and showing just the category menu items
    const element = document.getElementById('project-navigation')
    element.parentNode.removeChild(element)

    getPackageFiles(packageName, mediaSearchPath, flavor, assetsDir)
    const content = $('#content')
    content.find('#thumbsize-control input[type=radio]').change(function () {
      content.attr('size', this.value)
    })
    initTilePinchToZoom()
  })

  function getPackageFiles (packageName, mediaSearchPath, flavor, assetsDir) {
    let downloadList = []

    document.getElementById('top-app-bar__btn-download-selection').onclick = function () {
      for (let i = 0; i < downloadList.length; i++) {
        medialibDownloadSelectedFile(downloadList[i])
      }
      document.getElementById('top-app-bar__btn-cancel-download-selection').click()
    }

    document.getElementById('top-app-bar__btn-cancel-download-selection').onclick = function () {
      for (let i = 0; i < downloadList.length; i++) {
        document.getElementById('mediafile-' + downloadList[i].id).classList.remove('selected')
      }
      downloadList = []
      showTopBarDefault()
    }

    let url
    if (mediaSearchPath !== '') {
      url = mediaSearchPath
    } else {
      url = Routing.generate('api_media_lib_package_bynameurl', { flavor: flavor, package: packageName }, false)
    }

    $.get(url, {}, pkgFiles => {
      pkgFiles.forEach(file => {
        if (file.flavor !== 'pocketcode' && file.flavor !== flavor) {
          return // don't display files of other flavors
        }

        const mediafileContainer = $('<a class="mediafile" id="mediafile-' + file.id + '"/>')
        mediafileContainer.click(function () {
          // !!! Due to missing android web view support the download multiple files feature was "disabled" !!!!!
          // For now it is only possible to select one element! ToDo: send zip files if multiple files are downloaded.
          if (isWebView) {
            mediafileContainer.attr('href', file.download_url)
            mediafileContainer.attr('data-extension', file.extension)
            mediafileContainer.click(function () {
              medialibOnDownload(this)
            })
          } else { // -- end of disable
            mediafileContainer.toggleClass('selected')
            const indexInDownloadList = downloadList.indexOf(file)

            if (indexInDownloadList === -1) {
              downloadList.push(file)
            } else {
              downloadList.splice(indexInDownloadList, 1)
            }

            let elementsText = downloadList.length + ' '
            // Dispense support for languages where the count would be right.
            // This way there is no need to dynamically load the translation. (No delay - Less requests)
            if (downloadList.length === 1) {
              elementsText += elementsTranslationSingular
            } else {
              elementsText += elementsTranslationPlural
            }

            document.getElementById('top-app-bar__download-nr-selected').innerText = elementsText

            if (downloadList.length > 0) {
              showTopBarDownload()
            } else {
              showTopBarDefault()
            }
          }
        })

        if (flavor !== 'pocketcode' && file.flavor === flavor) {
          mediafileContainer.addClass('flavored')
        }

        mediafileContainer.append($('<i class="checkbox material-icons">check_circle</i>'))
        mediafileContainer.append(buildImageContainer(file))
        if (file.project_url) {
          mediafileContainer.append(
            '<div class="text-container">' +
            '  <a class="name name--link" href="' + file.project_url + '">' + getFileName(file) + '</a>' +
            '  <div class="description">' + file.description + '</div>' +
            '</div>'
          )
        } else {
          mediafileContainer.append(
            '<div class="text-container">' +
            '  <div class="name">' + getFileName(file) + '</div>' +
            '  <div class="description">' + file.description + '</div>' +
            '</div>'
          )
        }

        if (isWebView) {
          mediafileContainer.append('<div class="button-container">' + '<i class="material-icons">get_app</i>' + '</div>')
        }

        if (file.category.startsWith('ThemeSpecial')) {
          if (file.flavor === flavor) {
            $('#content #category-theme-special .files').prepend(mediafileContainer)
          } // else ignore
        } else {
          const catEscaped = file.category.replace(/"/g, '\\"')
          $('#content .category[data-name="' + catEscaped + '"] .files').prepend(mediafileContainer)
        }
      })

      $('#content .category').each(function () {
        if ($(this).find('.files').children().length === 0) {
          return
        }

        const catId = /^category-(.+)$/.exec(this.id)[1]

        $(this).show()
        $('#sidebar #menu-mediacat-' + catId).show()
      })
    }

    )
      .fail(function () {
        console.error('Error loading media lib package ' + packageName)
      })
  }

  function getFileName (file) {
    return file.name // make word breaks easier:
      .replace(/([a-z])([A-Z])/g, '$1​$2') // insert zero-width space between CamelCase
      .replace(/([A-Za-z])([0-9])/g, '$1​$2') // insert zero-width space between letters and numbers
      .replace(/_([A-Za-z0-9])/g, '_​$1') // insert zero-width space between underline and letters
  }

  function buildImageContainer (file) {
    const imageContainer = $('<div class="img-container"></div>')
    let audio, previewBtn

    switch (file.type) {
      case 'image':
      case 'catrobat':
        imageContainer.attr('data-filetype', 'image')
        imageContainer.append(buildImageFromFile(file))
        break
      case 'sound':
        imageContainer.attr('data-filetype', 'audio')
        audio = new Audio(file.download_url)
        previewBtn = $('<i class="audio-control material-icons">play_arrow</i>')
        previewBtn.click(function () {
          if (audio.paused) {
            previewBtn.text('pause')
            audio.play()
          } else {
            previewBtn.text('play_arrow')
            audio.pause()
          }
          return false
        })
        audio.onended = function () {
          previewBtn.text('play_arrow')
        }
        imageContainer.append(previewBtn)
        break
      case 'video':
        imageContainer.append($('<i class="media-file-icon material-icons">videocam</i>'))
        break
      default:
        imageContainer.append($('<i class="media-file-icon material-icons">insert_drive_file</i>'))
    }

    return imageContainer
  }

  function buildImageFromFile (file) {
    const imgExtension = file.extension === 'catrobat' ? 'png' : file.extension
    const image = $('<img alt="' + file.id + '" src="' + assetsDir + 'thumbs/' + file.id + '.' + imgExtension + '"/>')
    image.attr('title', file.name)
    image.attr('alt', file.name)
    image.on('error', function () {
      image.remove()
    })

    return image
  }

  function initTilePinchToZoom () {
    let mediaFiles = null

    let active = false
    let startDistance = null
    let currentSize = null

    const borderSpacing = 8

    function refreshStyle () {
      mediaFiles.css('width', currentSize).css('height', currentSize)
      const innerSize = currentSize - borderSpacing
      mediaFiles.find('> img').attr('style', 'max-width:' + innerSize + 'px !important; max-height:' + innerSize + 'px;')
      mediaFiles.find('.fas, .far').css('font-size', currentSize - 15)
    }

    document.addEventListener('touchstart', function (e) {
      reset()

      if (e.touches.length === 2) {
        const touch1 = e.touches[0]
        const touch2 = e.touches[1]

        const xDiff = touch2.clientX - touch1.clientX
        const yDiff = touch2.clientY - touch1.clientY

        startDistance = Math.sqrt((xDiff * xDiff) + (yDiff * yDiff))
        active = true
        $('#thumbsize-control').hide()

        if (mediaFiles == null || currentSize == null) {
          mediaFiles = $('.category > .files .mediafile')
          currentSize = mediaFiles.outerWidth()
        }
      }
    })

    document.addEventListener('touchmove', function (e) {
      if (active && !!startDistance && e.touches.length === 2) {
        const touch1 = e.touches[0]
        const touch2 = e.touches[1]

        const xDiff = touch2.clientX - touch1.clientX
        const yDiff = touch2.clientY - touch1.clientY

        const distance = Math.sqrt((xDiff * xDiff) + (yDiff * yDiff))
        const scale = distance / startDistance

        currentSize *= scale

        if (currentSize < 40) {
          currentSize = 40
        } else if (currentSize > 200) {
          currentSize = 200
        }
        refreshStyle()
      }
    })

    document.addEventListener('touchend', function () {
      reset()
    })

    function reset () {
      active = false
      startDistance = null
    }
  }
}

function medialibDownloadSelectedFile (file) {
  const link = document.createElement('a')
  link.href = file.download_url
  link.download = file.name
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  return false
}

function medialibOnDownload (link) {
  if (link.href !== 'javascript:void(0)') {
    const downloadHref = link.href
    link.href = 'javascript:void(0)'

    setTimeout(function () {
      link.href = downloadHref
    }, 5000)

    window.location = downloadHref
  }
  return false
}
