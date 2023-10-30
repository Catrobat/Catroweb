import { showTopBarDownload, showTopBarDefault } from '../layout/top_bar'

export function MediaLibrary(
  packageName,
  mediaSearchPath,
  translations,
  isWebView,
) {
  // Removing the project navigation items and showing just the category menu items
  const element = document.getElementById('project-navigation')
  element.parentNode.removeChild(element)

  initMediaLibrary()

  function initMediaLibrary() {
    let downloadList = []

    // files
    const fileContainers = document.querySelectorAll('a.media-library-file')
    for (let i = 0; i < fileContainers.length; i++) {
      const fileContainer = fileContainers[i]

      // const fileName = fileContainer.dataset.fileName
      const fileType = fileContainer.dataset.fileType
      const fileDownloadUrl = fileContainer.dataset.downloadUrl

      // download
      fileContainer.addEventListener('click', function () {
        if (isWebView) {
          // due to missing support for multiple selection download in android web view only one element can be selected
          fileContainer.href = fileDownloadUrl
          fileContainer.addEventListener('click', function () {
            medialibOnDownload(fileContainer)
          })
        } else {
          fileContainer.classList.toggle('selected')

          const indexInDownloadList = downloadList.indexOf(fileContainer.id)
          if (indexInDownloadList === -1) {
            downloadList.push(fileContainer.id)
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
          document.getElementById('top-app-bar__download-nr-selected',).innerText = elementsText

          if (downloadList.length > 0) {
            showTopBarDownload()
          } else {
            showTopBarDefault()
          }
        }
      })

      // download button
      if (isWebView) {
        const downloadBtn = fileContainer.querySelector('.button-container')
        downloadBtn.style.display = 'flex'
      }

      // sound
      if (fileType === 'sound') {
        const audioContainer = fileContainer.querySelector('.img-container')
        const audioBtn = audioContainer.querySelector('.audio-control')

        const audio = new Audio(fileDownloadUrl)

        audioContainer.addEventListener('click', function (event) {
          event.stopPropagation()
          if (audio.paused) {
            audioBtn.textContent = 'pause'
            audio.play()
          } else {
            audioBtn.textContent = 'play_arrow'
            audio.pause()
          }
          return false
        })

        audio.onended = function () {
          audioBtn.textContent = 'play_arrow'
        }
      }
    }

    // topbar
    document.getElementById('top-app-bar__btn-download-selection').onclick = function () {
      for (let i = 0; i < downloadList.length; i++) {
        const fileContainer = document.getElementById(downloadList[i])

        const file = {
          name: fileContainer.dataset.fileName,
          download_url: fileContainer.dataset.downloadUrl,
        }
        medialibDownloadSelectedFile(file)
      }
      document.getElementById('top-app-bar__btn-cancel-download-selection').click()
    }

    document.getElementById('top-app-bar__btn-cancel-download-selection').onclick = function () {
      for (let i = 0; i < downloadList.length; i++) {
        document.getElementById(downloadList[i]).classList.remove('selected')
      }
      downloadList = []
      showTopBarDefault()
    }

    // navbar
    document.querySelectorAll('#content .media-library-category').forEach((item) => {
      if (item.children.length === 0) {
        return
      }
      item.style.display = 'block'

      const catId = item.dataset.category
      document.querySelector('#sidebar #menu-media-library-category-' + catId).style.display = 'block'
    })
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
