require('./OverviewPage.scss')

import { showTopBarDefault, showTopBarDownload } from '../Layout/TopBar'

const overviewContainer = document.querySelector('.js-media-library-overview')

if (overviewContainer) {
  const apiUrl = overviewContainer.dataset.pathMediaLibraryApi
  const mediaAssetsApi = overviewContainer.dataset.pathMediaAssetsApi
  const categoryWebUrl = overviewContainer.dataset.pathMediaCategoryWeb
  const translations = JSON.parse(overviewContainer.dataset.translations)

  const limit = 10
  const assetsPerCategory = 8
  let categoriesNextCursor = null
  let isLoadingCategories = false
  let categoriesHasMore = true
  const searchQuery = new URLSearchParams(window.location.search).get('search')

  const downloadList = []

  const loadingSpinner = document.getElementById('loading-spinner')
  const categoriesContainer = document.getElementById('categories-container')
  const paginationContainer = document.getElementById('pagination-container')
  const noCategoriesAlert = document.getElementById('no-categories')

  const categoryStates = new Map()

  if (paginationContainer) {
    paginationContainer.style.display = 'none'
  }

  setupDownloadActions()
  setupInfiniteScroll()

  const skeletonCategories = document.getElementById('skeleton-categories')

  function removeSkeletons() {
    if (skeletonCategories) {
      skeletonCategories.remove()
    }
  }

  function showLoading(append = false) {
    if (append) {
      loadingSpinner.style.display = 'block'
    }
    paginationContainer.style.display = 'none'
    if (!append) {
      categoriesContainer.style.display = 'none'
    }
  }

  function hideLoading() {
    loadingSpinner.style.display = 'none'
  }

  function loadCategories(cursor = null, append = false) {
    if (isLoadingCategories) {
      return
    }
    isLoadingCategories = true
    showLoading(append)

    let url = `${apiUrl}?limit=${limit}&assets_per_category=${assetsPerCategory}`
    if (cursor) {
      url += `&cursor=${encodeURIComponent(cursor)}`
    }
    if (searchQuery) {
      url += `&search=${encodeURIComponent(searchQuery)}`
    }

    fetch(url)
      .then((response) => response.json())
      .then((data) => {
        hideLoading()
        removeSkeletons()

        const categories = data.data || []
        if (categories.length > 0) {
          renderCategories(categories, append)
          categoriesContainer.style.display = 'block'
          categoriesNextCursor = data.next_cursor || null
          categoriesHasMore = data.has_more ?? categories.length === limit
        } else {
          if (searchQuery) {
            noCategoriesAlert.textContent = translations.no_results
          }
          noCategoriesAlert.style.display = 'block'
          categoriesHasMore = false
        }
      })
      .catch((error) => {
        console.error('Error loading media library overview', error)
        hideLoading()
        removeSkeletons()
        if (searchQuery) {
          noCategoriesAlert.textContent = translations.no_results
        }
        noCategoriesAlert.style.display = 'block'
      })
      .finally(() => {
        isLoadingCategories = false
      })
  }

  function renderCategories(categories, append = false) {
    if (!append) {
      categoriesContainer.innerHTML = ''
      categoryStates.clear()
      noCategoriesAlert.style.display = 'none'
    }

    categories.forEach((category) => {
      const categorySection = document.createElement('div')
      categorySection.classList.add('category-section', 'mb-5')

      // Category header
      const header = document.createElement('div')
      header.classList.add('d-flex', 'justify-content-between', 'align-items-center', 'mb-3')

      const headerLeft = document.createElement('div')
      const title = document.createElement('h2')
      title.classList.add('mb-0')
      title.textContent = category.name
      headerLeft.appendChild(title)

      if (category.description) {
        const description = document.createElement('p')
        description.classList.add('text-muted', 'mb-0', 'small')
        description.textContent = category.description
        headerLeft.appendChild(description)
      }

      const headerRight = document.createElement('div')
      const assetsCount = document.createElement('span')
      assetsCount.classList.add('badge', 'bg-secondary')
      assetsCount.textContent = `${category.assets_count} ${translations.assets_count}`
      headerRight.appendChild(assetsCount)

      header.appendChild(headerLeft)
      header.appendChild(headerRight)

      categorySection.appendChild(header)

      // Assets grid
      if (category.preview_assets && category.preview_assets.length > 0) {
        const assetsWrapper = document.createElement('div')
        assetsWrapper.classList.add('media-assets-wrapper')

        const assetsGrid = document.createElement('div')
        assetsGrid.classList.add('media-assets-row', 'mb-3')
        assetsGrid.dataset.categoryId = category.id

        const chevrons = document.createElement('div')
        chevrons.classList.add('media-assets-chevrons')

        const leftChevron = document.createElement('button')
        leftChevron.type = 'button'
        leftChevron.classList.add(
          'media-assets-chevron',
          'media-assets-chevron--left',
          'material-icons',
        )
        leftChevron.textContent = 'chevron_left'

        const rightChevron = document.createElement('button')
        rightChevron.type = 'button'
        rightChevron.classList.add(
          'media-assets-chevron',
          'media-assets-chevron--right',
          'material-icons',
        )
        rightChevron.textContent = 'chevron_right'

        chevrons.appendChild(leftChevron)
        chevrons.appendChild(rightChevron)

        const state = {
          offset: category.preview_assets.length,
          total: category.assets_count,
          isLoading: false,
          hasMore: category.assets_count > category.preview_assets.length,
        }
        categoryStates.set(category.id, state)

        category.preview_assets.forEach((asset) => {
          assetsGrid.appendChild(buildAssetCard(asset))
        })

        assetsGrid.addEventListener('scroll', () => {
          const activeState = categoryStates.get(category.id)
          if (!activeState || activeState.isLoading || !activeState.hasMore) {
            return
          }

          const threshold = 120
          const rightEdge = assetsGrid.scrollLeft + assetsGrid.clientWidth
          if (rightEdge >= assetsGrid.scrollWidth - threshold) {
            loadMoreAssets(category.id, assetsGrid)
          }

          updateChevronVisibility(assetsGrid, leftChevron, rightChevron)
        })

        assetsGrid.addEventListener(
          'wheel',
          (event) => {
            if (Math.abs(event.deltaY) <= Math.abs(event.deltaX)) {
              return
            }
            assetsGrid.scrollLeft += event.deltaY
            event.preventDefault()
          },
          { passive: false },
        )

        leftChevron.addEventListener('click', () => {
          const card = assetsGrid.querySelector('.media-asset-col')
          const cardWidth = card ? card.getBoundingClientRect().width : 160
          assetsGrid.scrollLeft -= cardWidth * 2
        })

        rightChevron.addEventListener('click', () => {
          const card = assetsGrid.querySelector('.media-asset-col')
          const cardWidth = card ? card.getBoundingClientRect().width : 160
          assetsGrid.scrollLeft += cardWidth * 2
        })

        assetsWrapper.appendChild(assetsGrid)
        assetsWrapper.appendChild(chevrons)
        categorySection.appendChild(assetsWrapper)

        updateChevronVisibility(assetsGrid, leftChevron, rightChevron)
        ensureScrollable(category.id, assetsGrid)
      }

      // View all link
      const viewAllLink = document.createElement('a')
      viewAllLink.href = categoryWebUrl.replace('CATEGORY-ID', category.id)
      if (searchQuery) {
        viewAllLink.href += `?search=${encodeURIComponent(searchQuery)}`
      }
      viewAllLink.classList.add('btn', 'btn-primary', 'btn-sm')
      if (translations.view_all_category) {
        viewAllLink.textContent = translations.view_all_category.replace(
          '%category%',
          category.name,
        )
      } else {
        viewAllLink.textContent = `${translations.view_all} ${category.name}`
      }
      categorySection.appendChild(viewAllLink)

      categoriesContainer.appendChild(categorySection)
    })
  }

  function setupInfiniteScroll() {
    window.addEventListener('scroll', () => {
      if (isLoadingCategories || !categoriesHasMore) {
        return
      }

      const threshold = 240
      const scrollPosition = window.innerHeight + window.scrollY
      const pageHeight = document.documentElement.scrollHeight
      if (scrollPosition >= pageHeight - threshold) {
        loadCategories(categoriesNextCursor, true)
      }
    })
  }

  function setupDownloadActions() {
    const downloadBtn = document.getElementById('top-app-bar__btn-download-selection')
    const cancelBtn = document.getElementById('top-app-bar__btn-cancel-download-selection')

    if (downloadBtn) {
      downloadBtn.onclick = function () {
        downloadList.forEach((file) => downloadSelectedFile(file))
        if (cancelBtn) {
          cancelBtn.click()
        }
      }
    }

    if (cancelBtn) {
      cancelBtn.onclick = function () {
        downloadList.forEach((file) => {
          const card = document.getElementById(`media-asset-card-${file.id}`)
          if (card) {
            card.classList.remove('selected')
          }
        })
        downloadList.length = 0
        updateDownloadCount()
        if (typeof showTopBarDefault === 'function') {
          showTopBarDefault()
        }
      }
    }
  }

  function updateDownloadCount() {
    const downloadCountEl = document.getElementById('top-app-bar__download-nr-selected')
    if (!downloadCountEl) {
      return
    }

    let elementsText = downloadList.length + ' '
    if (downloadList.length === 1) {
      elementsText += translations.elementsSingular
    } else {
      elementsText += translations.elementsPlural
    }
    downloadCountEl.innerText = elementsText
  }

  function toggleSelection(assetCard, asset) {
    assetCard.classList.toggle('selected')
    const index = downloadList.findIndex((item) => item.id === asset.id)
    if (index === -1) {
      downloadList.push(asset)
    } else {
      downloadList.splice(index, 1)
    }

    updateDownloadCount()
    if (downloadList.length > 0) {
      if (typeof showTopBarDownload === 'function') {
        showTopBarDownload()
      }
    } else if (typeof showTopBarDefault === 'function') {
      showTopBarDefault()
    }
  }

  function buildAssetCard(asset) {
    const assetCol = document.createElement('div')
    assetCol.classList.add('media-asset-col')

    const assetCard = document.createElement('div')
    assetCard.classList.add('card', 'h-100', 'asset-card')
    assetCard.id = `media-asset-card-${asset.id}`
    assetCard.addEventListener('click', () => toggleSelection(assetCard, asset))

    const checkCircle = document.createElement('i')
    checkCircle.classList.add('material-icons', 'asset-check')
    checkCircle.textContent = 'check_circle'
    assetCard.appendChild(checkCircle)

    // Asset image/preview
    const imageContainer = document.createElement('div')
    imageContainer.classList.add('asset-preview')

    const fileType = (asset.file_type || '').toLowerCase()
    if (fileType === 'image') {
      const img = document.createElement('img')
      img.src = asset.thumbnail_url || asset.download_url
      img.alt = asset.name
      img.classList.add('card-img-top')
      img.loading = 'lazy'
      imageContainer.appendChild(img)
    } else if (fileType === 'sound') {
      const soundIcon = document.createElement('i')
      soundIcon.classList.add('material-icons', 'asset-icon')
      soundIcon.textContent = 'volume_up'
      imageContainer.appendChild(soundIcon)
    } else {
      const fileIcon = document.createElement('i')
      fileIcon.classList.add('material-icons', 'asset-icon')
      fileIcon.textContent = 'insert_drive_file'
      imageContainer.appendChild(fileIcon)
    }

    assetCard.appendChild(imageContainer)

    // Asset name
    const cardBody = document.createElement('div')
    cardBody.classList.add('card-body', 'p-2')

    const assetName = document.createElement('p')
    assetName.classList.add('card-text', 'small', 'mb-0', 'text-truncate')
    assetName.title = asset.name
    assetName.textContent = asset.name

    cardBody.appendChild(assetName)
    assetCard.appendChild(cardBody)

    assetCol.appendChild(assetCard)
    return assetCol
  }

  function loadMoreAssets(categoryId, assetsGrid) {
    const state = categoryStates.get(categoryId)
    if (!state || state.isLoading || !state.hasMore) {
      return
    }

    state.isLoading = true

    const url = new URL(mediaAssetsApi, window.location.origin)
    url.searchParams.set('category_id', categoryId)
    url.searchParams.set('limit', assetsPerCategory)
    // The media assets API uses base64-encoded offset as cursor
    url.searchParams.set('cursor', btoa(String(state.offset)))
    if (searchQuery) {
      url.searchParams.set('search', searchQuery)
    }

    fetch(url.toString())
      .then((response) => response.json())
      .then((data) => {
        const assets = data.data || data.assets || []
        assets.forEach((asset) => {
          assetsGrid.appendChild(buildAssetCard(asset))
        })
        state.offset += assets.length
        if (data.has_more !== undefined) {
          state.hasMore = data.has_more
        } else if (assets.length < assetsPerCategory || state.offset >= state.total) {
          state.hasMore = false
        }

        const wrapper = assetsGrid.closest('.media-assets-wrapper')
        const leftChevron = wrapper?.querySelector('.media-assets-chevron--left')
        const rightChevron = wrapper?.querySelector('.media-assets-chevron--right')
        if (leftChevron && rightChevron) {
          updateChevronVisibility(assetsGrid, leftChevron, rightChevron)
        }
        ensureScrollable(categoryId, assetsGrid)
      })
      .catch((error) => {
        console.error('Error loading more assets', error)
      })
      .finally(() => {
        state.isLoading = false
      })
  }

  function updateChevronVisibility(assetsGrid, leftChevron, rightChevron) {
    const scrollWidth = assetsGrid.scrollWidth
    const clientWidth = assetsGrid.clientWidth

    if (scrollWidth <= clientWidth + 1) {
      leftChevron.style.display = 'none'
      rightChevron.style.display = 'none'
      return
    }

    if (assetsGrid.scrollLeft <= 0) {
      leftChevron.style.display = 'none'
    } else {
      leftChevron.style.display = 'inline-flex'
    }

    if (assetsGrid.scrollLeft + clientWidth >= scrollWidth - 1) {
      rightChevron.style.display = 'none'
    } else {
      rightChevron.style.display = 'inline-flex'
    }
  }

  function ensureScrollable(categoryId, assetsGrid) {
    const state = categoryStates.get(categoryId)
    if (!state || state.isLoading || !state.hasMore) {
      return
    }

    if (assetsGrid.scrollWidth <= assetsGrid.clientWidth + 1) {
      loadMoreAssets(categoryId, assetsGrid)
    }
  }

  function downloadSelectedFile(file) {
    if (!file.download_url) {
      console.error('Download URL missing for asset', file)
      return
    }

    const link = document.createElement('a')
    link.href = file.download_url
    link.download = file.name || 'download'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  }

  // Initial load
  loadCategories(null)
}
