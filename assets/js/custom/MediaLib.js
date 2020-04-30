/* eslint-env jquery */
/* global Routing */

// eslint-disable-next-line no-unused-vars
function MediaLib (packageName, mediaSearchPath, flavor, assetsDir) {
  $(function () {
    // Removing the project navigation items and showing just the category menu items
    const element = document.getElementById('project-navigation')
    element.parentNode.removeChild(element)

    // Adding back button on media library search results
    $('#medialib-header-back-btn').click(function () {
      window.history.back()
    })

    getPackageFiles(packageName, mediaSearchPath, flavor, assetsDir)
    const content = $('#content')
    content.find('#thumbsize-control input[type=radio]').change(function () {
      content.attr('size', this.value)
    })
    initTilePinchToZoom()
  })

  function getPackageFiles (packageName, mediaSearchPath, flavor, assetsDir) {
    var downloadList = []

    document.getElementById('downloadbar-start-downloads').onclick = function () {
      for (var i = 0; i < downloadList.length; i++) {
        medialibDownloadSelectedFile(downloadList[i])
      }
      document.getElementById('downloadbar-delete-selection-btn').click()
    }

    document.getElementById('downloadbar-delete-selection-btn').onclick = function () {
      for (var i = 0; i < downloadList.length; i++) {
        document.getElementById('mediafile-' + downloadList[i].id).classList.remove('selected')
      }
      downloadList = []
      hideDownloadbar()
    }

    var url = null

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
          mediafileContainer.toggleClass('selected')
          var indexInDownloadList = downloadList.indexOf(file)

          if (indexInDownloadList === -1) {
            downloadList.push(file)
          } else {
            downloadList.splice(indexInDownloadList, 1)
          }

          document.getElementById('downloadbar-nr-selected').innerText = downloadList.length

          if (downloadList.length > 0) {
            showDownloadbar()
          } else {
            hideDownloadbar()
          }
        })

        if (flavor !== 'pocketcode' && file.flavor === flavor) {
          mediafileContainer.addClass('flavored')
        }

        const name = file.name // make word breaks easier:
          .replace(/([a-z])([A-Z])/g, '$1​$2') // insert zero-width space between CamelCase
          .replace(/([A-Za-z])([0-9])/g, '$1​$2') // insert zero-width space between letters and numbers
          .replace(/_([A-Za-z0-9])/g, '_​$1') // insert zero-width space between underline and letters
        mediafileContainer.append($('<div class="name" />').text(name))
        mediafileContainer.append($('<div class="checkbox fas fa-check-circle" />'))
        mediafileContainer.addClass('showName')

        const imgExtension = file.extension === 'catrobat' ? 'png' : file.extension
        let audio, previewBtn, image
        switch (file.extension) {
          case 'adp':
          case 'au':
          case 'mid':
          case 'mp4a':
          case 'mpga':
          case 'oga':
          case 's3m':
          case 'sil':
          case 'uva':
          case 'eol':
          case 'dra':
          case 'dts':
          case 'dtshd':
          case 'lvp':
          case 'pya':
          case 'ecelp4800':
          case 'ecelp7470':
          case 'ecelp9600':
          case 'rip':
          case 'weba':
          case 'aac':
          case 'aif':
          case 'caf':
          case 'flac':
          case 'mka':
          case 'm3u':
          case 'wax':
          case 'wma':
          case 'ram':
          case 'rmp':
          case 'wav':
          case 'xm':
            mediafileContainer.attr('data-filetype', 'audio')
            mediafileContainer.append($('<i class="fas fa-file-audio"/>'))
            audio = new Audio(file.download_url)
            previewBtn = $('<div class="audio-control fas fa-play" />')
            previewBtn.click(function () {
              if (audio.paused) {
                previewBtn.removeClass('fa-play').addClass('fa-pause')
                audio.play()
              } else {
                previewBtn.removeClass('fa-pause').addClass('fa-play')
                audio.pause()
              }
              return false
            })
            audio.onended = function () {
              previewBtn.removeClass('fa-pause').addClass('fa-play')
            }

            mediafileContainer.append(previewBtn)
            break
          case '3gp':
          case '3g2':
          case 'h261':
          case 'h263':
          case 'h264':
          case 'jpgv':
          case 'jpm':
          case 'mj2':
          case 'mp4':
          case 'mpeg':
          case 'ogv':
          case 'qt':
          case 'uvh':
          case 'uvm':
          case 'uvp':
          case 'uvs':
          case 'uvv':
          case 'dvb':
          case 'fvt':
          case 'mxu':
          case 'pyv':
          case 'uvu':
          case 'viv':
          case 'webm':
          case 'f4v':
          case 'fli':
          case 'flv':
          case 'm4v':
          case 'mkv':
          case 'mng':
          case 'asf':
          case 'vob':
          case 'wm':
          case 'wmv':
          case 'wmx':
          case 'wvx':
          case 'avi':
          case 'movie':
          case 'smv':
            mediafileContainer.attr('data-filetype', 'video')
            mediafileContainer.append($('<i class="fas fa-file-video"/>'))
            break
          case 'pdf':
            mediafileContainer.attr('data-filetype', 'pdf')
            mediafileContainer.append($('<i class="fas fa-file-pdf"/>'))
            break
          case 'txt':
          case 'rtx':
            mediafileContainer.attr('data-filetype', 'text')
            mediafileContainer.append($('<i class="fas fa-file-alt"/>'))
            break
          case 'zip':
          case '7z':
            mediafileContainer.attr('data-filetype', 'archive')
            mediafileContainer.append($('<i class="fas fa-file-archive"/>'))
            break
          default:
            image = $('<img alt="' + file.id + '" src="' + assetsDir + 'thumbs/' + file.id + '.' + imgExtension + '"/>')
            image.attr('title', file.name)
            image.attr('alt', file.name)
            image.on('error', function () {
              mediafileContainer.addClass('showName')

              const pictureExtensions = ['bmp', 'cgm', 'g3', 'gif', 'ief', 'jpeg', 'ktx', 'png', 'btif', 'sgi', 'svg', 'tiff', 'psd', 'uvi', 'sub', 'djvu', 'dwg', 'dxf', 'fbs', 'fpx', 'fst', 'mmr', 'rlc', 'mdi', 'wdp', 'npx', 'wbmp', 'xif', 'webp', '3ds', 'ras', 'cmx', 'fh', 'ico', 'sid', 'pcx', 'pic', 'pnm', 'pbm', 'pgm', 'ppm', 'rgb', 'tga', 'xbm', 'xpm', 'xwd']
              image.remove()

              if (pictureExtensions.indexOf(file.extension) !== -1) {
                mediafileContainer.prepend($('<i class="fas fa-file-image"/>'))
              } else {
                mediafileContainer.prepend($('<i class="fas fa-file"/>'))
              }
            })
            mediafileContainer.removeClass('showName')
            mediafileContainer.append(image)
            break
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
    }).fail(function () {
      console.error('Error loading media lib package ' + packageName)
    })
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

    document.addEventListener('touchend', function (e) {
      reset()
    })

    function reset () {
      active = false
      startDistance = null
    }
  }
}

function medialibDownloadSelectedFile (file) {
  var link = document.createElement('a')
  link.href = file.download_url
  link.download = file.name
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  return false
}

function showDownloadbar () {
  document.getElementById('downloadbar').style.display = 'flex'
  document.getElementById('navbar').style.display = 'none'
  document.getElementById('searchbar').style.display = 'none'
}

function hideDownloadbar () {
  document.getElementById('downloadbar').style.display = 'none'
  document.getElementById('navbar').style.display = 'inline'
  document.getElementById('searchbar').style.display = 'inline'
}
