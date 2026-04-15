import { showSnackbar } from './Snackbar'
import { MDCTopAppBar } from '@material/top-app-bar'
import { MDCMenu } from '@material/menu'

import './TopBar.scss'
import '../Components/MdcMenu.scss'

const topAppBarElement = document.querySelector('.mdc-top-app-bar')
const mdcObject = topAppBarElement ? new MDCTopAppBar(topAppBarElement) : null

const title = document.querySelector('#top-app-bar__title')
const toggleSidebarButton = document.querySelector('#top-app-bar__btn-sidebar-toggle')
let backButton = document.querySelector('#top-app-bar__back__btn-back')

if (backButton && backButton.dataset.backPath) {
  backButton.addEventListener('click', () => {
    window.location.href = backButton.dataset.backPath
  })
}

const searchButton = document.querySelector('#top-app-bar__btn-search')
const searchBackButton = document.querySelector('#top-app-bar__btn-search-back')
const searchClearButton = document.querySelector('#top-app-bar__btn-search-clear')
const searchInput = document.querySelector('#top-app-bar__search-input')
const searchForm = document.querySelector('#top-app-bar__search-form')

const optionsButton = document.querySelector('#top-app-bar__btn-options')
const optionsMenuEl = document.querySelector('#top-app-bar__options-menu')
const optionsMenu = optionsMenuEl ? new MDCMenu(optionsMenuEl) : null

const defaultAppBarHref = title.getAttribute('href')
const defaultTitle = title.innerHTML

const searchUrl = document.querySelector('.js-header').dataset.pathSearchUrl
const sameSearchMessage = document.querySelector('.js-header').dataset.sameSearchMessage

const SEARCH_PARAM = 'search'

function getSearchQuery() {
  return new URLSearchParams(window.location.search).get(SEARCH_PARAM)
}

function hasSearchQuery() {
  const query = getSearchQuery()
  return query !== null && query.trim() !== ''
}

function isOnSearchPage() {
  return window.location.pathname.includes('/search/') || hasSearchQuery()
}

searchButton?.addEventListener('click', () => {
  showTopBarSearch()
})
searchBackButton?.addEventListener('click', () => {
  handleSearchBackButton()
})
searchClearButton?.addEventListener('click', () => {
  clearTopBarSearch()
})
searchInput?.addEventListener('input', () => {
  controlTopBarSearchClearButton()
})
searchInput?.addEventListener('change', () => {
  controlTopBarSearchClearButton()
})
searchForm?.addEventListener('submit', (event) => {
  submitSearchForm(event)
})
optionsButton?.addEventListener('click', () => {
  showTopBarOptions()
})

export function showTopBarDownload() {
  hideTopBars()
  document.querySelector('#top-app-bar__media-library-download').style.display = 'flex'
}

export function showTopBarDefault() {
  hideTopBars()
  document.querySelector('#top-app-bar__default').style.display = 'flex'
}

export function showCustomTopBarTitle(titleText, onBack) {
  title.textContent = titleText
  title.removeAttribute('href')
  if (backButton) {
    backButton.style.display = 'none'
  }

  document.querySelector('.mdc-top-app-bar').style.top = '0'
  if (mdcObject) mdcObject.setScrollTarget(document.createElement('div'))
  if (typeof onBack === 'function') {
    backButton = document.createElement('button')
    backButton.id = 'top-app-bar__back__btn-back'
    backButton.className = 'material-icons mdc-top-app-bar__action-item mdc-icon-button'
    backButton.setAttribute('aria-label', 'Back to previous page')
    backButton.textContent = 'arrow_back'
    toggleSidebarButton.parentNode.insertBefore(backButton, toggleSidebarButton.nextSibling)
    toggleSidebarButton.style.display = 'none'
    backButton.style.display = 'block'
    backButton.addEventListener('click', onBack)
  }
}

export function showDefaultTopBarTitle() {
  title.innerHTML = defaultTitle
  title.setAttribute('href', defaultAppBarHref)
  toggleSidebarButton.style.display = 'block'
  if (backButton) backButton.style.display = 'none'
  if (mdcObject) mdcObject.setScrollTarget(window)
}

function submitSearchForm(event) {
  event.preventDefault()
  const query = searchInput.value.trim()
  const targetUrl = searchUrl + encodeURIComponent(query)

  const target = new URL(targetUrl, window.location.origin)
  const current = new URL(window.location.href)
  if (target.pathname !== current.pathname || target.search !== current.search) {
    window.location.href = targetUrl
  } else {
    showSnackbar('#share-snackbar', sameSearchMessage)
  }
}

function hideTopBars() {
  const topBarRows = document.querySelectorAll('.mdc-top-app-bar__row')
  topBarRows.forEach((row) => {
    row.style.display = 'none'
  })
}

const PREVIOUS_NON_SEARCH_URL_KEY = 'previousNonSearchUrl'
let searchBarPushedState = false

function closeSearchBar() {
  searchBarPushedState = false
  sessionStorage.removeItem(PREVIOUS_NON_SEARCH_URL_KEY)
  showTopBarDefault()
}

function handleSearchBackButton() {
  const previousUrl = sessionStorage.getItem(PREVIOUS_NON_SEARCH_URL_KEY)

  if (!isOnSearchPage()) {
    // Only the search bar was shown, no navigation happened
    if (searchBarPushedState) {
      // Pop the state we pushed when opening the search bar
      window.history.back()
    } else {
      closeSearchBar()
    }
    return
  }

  if (previousUrl) {
    sessionStorage.removeItem(PREVIOUS_NON_SEARCH_URL_KEY)
    window.location.href = previousUrl
  } else if (window.history.length > 1) {
    window.history.back()
  } else {
    showTopBarDefault()
  }
}

window.addEventListener('popstate', function (event) {
  if (searchBarPushedState && (!event.state || event.state.type !== 'search-bar-open')) {
    closeSearchBar()
  }
})

function showTopBarOptions() {
  if (optionsMenu) {
    optionsMenu.open = true
  }
}

export function showTopBarSearch() {
  hideTopBars()
  document.querySelector('#top-app-bar__search').style.display = 'flex'
  searchInput.focus()
  if (!isOnSearchPage()) {
    sessionStorage.setItem(PREVIOUS_NON_SEARCH_URL_KEY, window.location.href)
    if (!searchBarPushedState) {
      searchBarPushedState = true
      window.history.pushState({ type: 'search-bar-open' }, '', window.location.href)
    }
  }
}

export function controlTopBarSearchClearButton() {
  if (searchInput.value) {
    searchClearButton.style.display = 'block'
  } else {
    searchClearButton.style.display = 'none'
  }
}

function clearTopBarSearch() {
  searchInput.value = ''
  searchClearButton.style.display = 'none'

  if (hasSearchQuery()) {
    const url = new URL(window.location.href)
    url.searchParams.delete(SEARCH_PARAM)
    window.location.href = url.pathname + (url.search ? `?${url.searchParams.toString()}` : '')
    return
  }

  searchInput.focus()
}

if (searchInput && hasSearchQuery()) {
  showTopBarSearch()
  searchInput.value = getSearchQuery()
  controlTopBarSearchClearButton()
}
