import { showTopBarDownload, showTopBarDefault } from '../Layout/TopBar'

export function MediaLib(
  packageName,
  mediaSearchPath,
  flavor,
  assetsDir,
  translations,
  isWebView,
  mediaLibPackageByNameUrlApi,
) {
  // Removing the project navigation items and showing just the category menu items
  const element = document.getElementById('project-navigation')
  element.parentNode.removeChild(element)

  getPackageFiles(packageName, mediaSearchPath, flavor)

  function getPackageFiles(packageName, mediaSearchPath, flavor) {
    let downloadList = []

    document.getElementById('top-app-bar__btn-download-selection').onclick =
      function () {
        for (let i = 0; i < downloadList.length; i++) {
          medialibDownloadSelectedFile(downloadList[i])
        }
        document
          .getElementById('top-app-bar__btn-cancel-download-selection')
          .click()
      }

    document.getElementById(
      'top-app-bar__btn-cancel-download-selection',
    ).onclick = function () {
      for (let i = 0; i < downloadList.length; i++) {
        document
          .getElementById('mediafile-' + downloadList[i].id)
          .classList.remove('selected')
      }
      downloadList = []
      showTopBarDefault()
    }

    // accepted flavors
    const acceptedFlavors = ['pocketcode']
    if (acceptedFlavors.indexOf(flavor) === -1) {
      acceptedFlavors.push(flavor)
    }

    // api url
    let url
    const attributes =
      'id,name,flavors,packages,category,extension,file_type,size,download_url'
    const limit = 1000
    if (mediaSearchPath !== '') {
      url = mediaSearchPath + '&attributes=' + attributes + '&limit=' + limit
    } else {
      url =
        mediaLibPackageByNameUrlApi +
        '?attributes=' +
        attributes +
        '&limit=' +
        limit
    }

    fetch(url)
      .then((response) => response.json())
      .then((data) => {
        data.forEach((file) => {
          let fileFlavorArray = []
          if ('flavors' in file && Array.isArray(file.flavors)) {
            fileFlavorArray = file.flavors
          } else if ('flavor' in file) {
            fileFlavorArray.push(file.flavor)
          }

          const isFlavored = !fileFlavorArray.includes('pocketcode')
          const flavorFound = fileFlavorArray.some((item) =>
            acceptedFlavors.includes(item),
          )
          if (!flavorFound) {
            return
          }

          // media container
          const mediafileContainer = document.createElement('a')
          mediafileContainer.classList.add('mediafile')
          mediafileContainer.id = 'mediafile-' + file.id
          mediafileContainer.addEventListener('click', function () {
            // !!! Due to missing android web view support the download multiple files feature was "disabled" !!!!!
            // For now it is only possible to select one element!
            if (isWebView) {
              mediafileContainer.href = file.download_url
              mediafileContainer.dataset.extension = file.extension
              mediafileContainer.addEventListener('click', function () {
                medialibOnDownload(mediafileContainer)
              })
            } else {
              // -- end of disable
              mediafileContainer.classList.toggle('selected')
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
                elementsText += translations.elementsSingular
              } else {
                elementsText += translations.elementsPlural
              }

              document.getElementById(
                'top-app-bar__download-nr-selected',
              ).innerText = elementsText

              if (downloadList.length > 0) {
                showTopBarDownload()
              } else {
                showTopBarDefault()
              }
            }
          })

          if (isFlavored) {
            mediafileContainer.classList.add('flavored')
          }

          // check circle
          const checkCircle = document.createElement('i')
          checkCircle.classList.add('checkbox')
          checkCircle.classList.add('material-icons')
          checkCircle.textContent = 'check_circle'
          mediafileContainer.appendChild(checkCircle)

          // build image
          mediafileContainer.appendChild(buildImageContainer(file))

          // list item
          const listItemElement = document.createElement('div')
          listItemElement.classList.add('text-container')

          let listItemNameElement
          if ('project_url' in file && file.project_url) {
            listItemNameElement = document.createElement('a')
            listItemNameElement.classList.add('name')
            listItemNameElement.classList.add('name--link')
            listItemNameElement.href = file.project_url
            listItemNameElement.textContent = getFileName(file)
          } else {
            listItemNameElement = document.createElement('div')
            listItemNameElement.classList.add('name')
            listItemNameElement.textContent = getFileName(file)
          }

          const listItemDescriptionElement = document.createElement('div')
          listItemDescriptionElement.classList.add('description')
          listItemDescriptionElement.innerHTML = getFileDescription(file)

          listItemElement.appendChild(listItemNameElement)
          listItemElement.appendChild(listItemDescriptionElement)

          mediafileContainer.appendChild(listItemElement)

          // download button
          if (isWebView) {
            const listItemDownloadButton = document.createElement('div')
            listItemDownloadButton.classList.add('button-container')

            const listItemDownloadIcon = document.createElement('i')
            listItemDownloadIcon.classList.add('material-icons')
            listItemDownloadIcon.textContent = 'get_app'

            listItemDownloadButton.appendChild(listItemDownloadIcon)

            mediafileContainer.appendChild(listItemDownloadButton)
          }

          // flavor
          if (file.category.startsWith('ThemeSpecial')) {
            if (flavorFound) {
              document
                .querySelector('#content #category-theme-special .files')
                .prepend(mediafileContainer)
            } // else ignore
          } else {
            const catEscaped = file.category.replace(/"/g, '\\"')
            document
              .querySelector(
                '#content .category[data-name="' + catEscaped + '"] .files',
              )
              .prepend(mediafileContainer)
          }
        })

        document.querySelectorAll('#content .category').forEach((item) => {
          if (item.children.length === 0) {
            return
          }

          const catId = /^category-(.+)$/.exec(item.id)[1]

          item.style.display = 'block'
          document.querySelector(
            '#sidebar #menu-mediacat-' + catId,
          ).style.display = 'block'
        })

        document.getElementById('loading-spinner').style.display = 'none'
      })
      .catch(() => {
        console.error('Error loading media lib package ' + packageName)
      })
  }

  function getFileName(file) {
    return file.name // make word breaks easier:
      .replace(/([a-z])([A-Z])/g, '$1​$2') // insert zero-width space between CamelCase
      .replace(/([A-Za-z])([0-9])/g, '$1​$2') // insert zero-width space between letters and numbers
      .replace(/_([A-Za-z0-9])/g, '_​$1') // insert zero-width space between underline and letters
  }

  function getFileDescription(file) {
    let type
    if (file.file_type in translations.type) {
      type = translations.type[file.file_type]
    } else {
      type = translations.type.unknown
    }
    const size = (file.size / (1024 * 1024)).toFixed(2) + 'MB'
    return type + '<br>' + translations.size.replace('%size%', size)
  }

  function buildImageContainer(file) {
    const imageContainer = document.createElement('div')
    imageContainer.classList.add('img-container')
    let audio, previewBtn

    if (file.file_type === 'image' || file.extension === 'catrobat') {
      imageContainer.setAttribute('data-filetype', 'image')
      imageContainer.appendChild(buildImageFromFile(file))
    } else if (file.file_type === 'sound') {
      imageContainer.setAttribute('data-filetype', 'audio')
      audio = new Audio(file.download_url)

      previewBtn = document.createElement('i')
      previewBtn.classList.add('audio-control', 'material-icons')
      previewBtn.textContent = 'play_arrow'

      previewBtn.addEventListener('click', function () {
        if (audio.paused) {
          previewBtn.textContent = 'pause'
          audio.play()
        } else {
          previewBtn.textContent = 'play_arrow'
          audio.pause()
        }
        return false
      })

      audio.onended = function () {
        previewBtn.textContent = 'play_arrow'
      }

      imageContainer.appendChild(previewBtn)
    } else if (file.file_type === 'video') {
      const videoIcon = document.createElement('i')
      videoIcon.classList.add('media-file-icon', 'material-icons')
      videoIcon.textContent = 'videocam'

      imageContainer.appendChild(videoIcon)
    } else {
      const fileIcon = document.createElement('i')
      fileIcon.classList.add('media-file-icon', 'material-icons')
      fileIcon.textContent = 'insert_drive_file'

      imageContainer.appendChild(fileIcon)
    }

    return imageContainer
  }

  function buildImageFromFile(file) {
    const imgExtension = file.extension === 'catrobat' ? 'png' : file.extension
    const image = document.createElement('img')
    image.setAttribute('alt', file.id)
    image.setAttribute(
      'src',
      assetsDir + 'thumbs/' + file.id + '.' + imgExtension,
    )
    image.setAttribute('title', file.name)
    image.addEventListener('error', function () {
      image.remove()
    })
    return image
  }
}

function medialibDownloadSelectedFile(file) {
  const link = document.createElement('a')
  link.href = file.download_url
  link.download = file.name
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  return false
}

function medialibOnDownload(link) {
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
