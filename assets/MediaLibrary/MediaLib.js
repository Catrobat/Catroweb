import { showTopBarDefault, showTopBarDownload } from '../Layout/TopBar'

export function MediaLib(
  categoryId,
  categoryName,
  mediaSearchPath,
  flavor,
  translations,
  mediaAssetsApi,
  searchQuery,
) {
  getCategoryFiles(categoryId, mediaSearchPath, flavor)

  function getCategoryFiles(categoryId, mediaSearchPath, flavor) {
    let downloadList = []
    let displayedAssets = 0

    const downloadBtn = document.getElementById('top-app-bar__btn-download-selection')
    const cancelBtn = document.getElementById('top-app-bar__btn-cancel-download-selection')
    const skeletonMediaItems = document.getElementById('skeleton-media-items')
    const categoryEl = document.querySelector('#content .category')

    if (downloadBtn) {
      downloadBtn.onclick = function () {
        for (let i = 0; i < downloadList.length; i++) {
          medialibDownloadSelectedFile(downloadList[i])
        }
        if (cancelBtn) {
          cancelBtn.click()
        }
      }
    }

    if (cancelBtn) {
      cancelBtn.onclick = function () {
        for (let i = 0; i < downloadList.length; i++) {
          document.getElementById('mediafile-' + downloadList[i].id).classList.remove('selected')
        }
        downloadList = []
        if (typeof showTopBarDefault === 'function') {
          showTopBarDefault()
        }
      }
    }

    // accepted flavors
    const acceptedFlavors = ['pocketcode']
    if (acceptedFlavors.indexOf(flavor) === -1) {
      acceptedFlavors.push(flavor)
    }

    // api url - new media library API
    const limit = 20
    let offset = 0
    let isLoading = false
    let hasMore = true
    let totalAssets = null

    function removeSkeletonMediaItems() {
      if (skeletonMediaItems) {
        skeletonMediaItems.remove()
      }
    }

    function showCategoryContent() {
      removeSkeletonMediaItems()

      if (categoryEl) {
        categoryEl.style.display = 'block'
      }
    }

    function buildUrl(pageOffset) {
      let url
      if (mediaSearchPath !== '' && mediaSearchPath) {
        // Search path already has query string
        url = `${mediaSearchPath}&limit=${limit}&offset=${pageOffset}`
      } else {
        // Regular category assets endpoint
        url = `${mediaAssetsApi}&limit=${limit}&offset=${pageOffset}`
      }
      return url
    }

    function loadMoreAssets() {
      if (isLoading || !hasMore) {
        return
      }

      isLoading = true
      fetch(buildUrl(offset))
        .then((response) => response.json())
        .then((responseData) => {
          // New API returns {assets: [], pagination: {...}}
          const assets = responseData.assets || responseData
          const pagination = responseData.pagination
          if (pagination && Number.isInteger(pagination.total)) {
            totalAssets = pagination.total
          }

          assets.forEach((file) => {
            // Normalize file_type to lowercase (API returns uppercase)
            if (file.file_type) {
              file.file_type = file.file_type.toLowerCase()
            }

            let fileFlavorArray = []
            if ('flavors' in file && Array.isArray(file.flavors)) {
              fileFlavorArray = file.flavors
            } else if ('flavor' in file) {
              fileFlavorArray.push(file.flavor)
            }

            const isFlavored = !fileFlavorArray.includes('pocketcode')
            const flavorFound = fileFlavorArray.some((item) => acceptedFlavors.includes(item))
            if (!flavorFound) {
              return
            }

            // media container
            const mediafileContainer = document.createElement('a')
            mediafileContainer.classList.add('mediafile')
            mediafileContainer.id = 'mediafile-' + file.id
            mediafileContainer.addEventListener('click', function () {
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

              const downloadCountEl = document.getElementById('top-app-bar__download-nr-selected')
              if (downloadCountEl) {
                downloadCountEl.innerText = elementsText
              }

              if (downloadList.length > 0) {
                if (typeof showTopBarDownload === 'function') {
                  showTopBarDownload()
                }
              } else {
                if (typeof showTopBarDefault === 'function') {
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

            // Add to category container (single category per page now)
            const categoryContainer = document.querySelector('#content .category .files')
            if (categoryContainer) {
              categoryContainer.appendChild(mediafileContainer)
              displayedAssets += 1
            }
          })

          offset += assets.length
          if (totalAssets !== null) {
            hasMore = offset < totalAssets
          } else {
            hasMore = assets.length === limit
          }

          const searchResultsEl = document.getElementById('search-results')
          const searchResultsText = document.getElementById('search-results-text')
          if (searchQuery && searchResultsEl && searchResultsText) {
            if (displayedAssets === 0 && !hasMore) {
              searchResultsText.textContent = translations.noResults
              searchResultsEl.style.display = 'block'
            } else {
              searchResultsEl.style.display = 'none'
            }
          }

          showCategoryContent()
        })
        .catch((error) => {
          console.error('Error loading media library category ' + categoryName, error)
          removeSkeletonMediaItems()
        })
        .finally(() => {
          isLoading = false
        })
    }

    window.addEventListener('scroll', () => {
      if (isLoading || !hasMore) {
        return
      }
      const threshold = 240
      const scrollPosition = window.innerHeight + window.scrollY
      const pageHeight = document.documentElement.scrollHeight
      if (scrollPosition >= pageHeight - threshold) {
        loadMoreAssets()
      }
    })

    loadMoreAssets()
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

    let sizeText = ''
    if (file.size) {
      const size = (file.size / (1024 * 1024)).toFixed(2) + 'MB'
      sizeText = '<br>' + translations.size.replace('%size%', size)
    }

    return type + sizeText
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
    const image = document.createElement('img')
    image.setAttribute('alt', file.id)
    // Use thumbnail_url from API response
    image.setAttribute('src', file.thumbnail_url || file.download_url)
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
