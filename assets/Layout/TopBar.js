import { MDCTopAppBar } from '@material/top-app-bar'

import './TopBar.scss'

const topAppBarElement = document.querySelector('.mdc-top-app-bar')
const mdcObject = new MDCTopAppBar(topAppBarElement)

const title = document.querySelector('#top-app-bar__title')
const toggleSidebarButton = document.querySelector('#top-app-bar__btn-sidebar-toggle')
let backButton = document.querySelector('#top-app-bar__back__btn-back')

const searchButton = document.querySelector('#top-app-bar__btn-search')
const searchBackButton = document.querySelector('#top-app-bar__btn-search-back')
const searchClearButton = document.querySelector('#top-app-bar__btn-search-clear')
const searchInput = document.querySelector('#top-app-bar__search-input')
const searchForm = document.querySelector('#top-app-bar__search-form')

const optionsButton = document.querySelector('#top-app-bar__btn-options')
const optionsContainer = document.querySelector('#top-app-bar__options-container')

const defaultAppBarHref = title.getAttribute('href')
const defaultTitle = title.innerHTML

const PREVIOUS_SEARCH_URL_KEY = 'KEY_BEFORE_SEARCH_URL'
const searchUrl = document.querySelector('.js-header').dataset.pathSearchUrl

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

if (optionsContainer && optionsButton) {
  document.addEventListener('click', (event) => {
    if (optionsButton !== event.target) {
      optionsContainer.style.display = 'none'
    }
  })
}

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
  mdcObject.setScrollTarget(document.createElement('div'))
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
  mdcObject.setScrollTarget(window)
}

function submitSearchForm(event) {
  event.preventDefault()
  const query = searchInput.value
  if (!window.location.pathname.includes('/search/')) {
    // keeping track of last page that was no search page
    window.sessionStorage.setItem(PREVIOUS_SEARCH_URL_KEY, window.location.href)
  }
  window.location.href = searchUrl + encodeURIComponent(query.trim())
}

function hideTopBars() {
  const topBarRows = document.querySelectorAll('.mdc-top-app-bar__row')
  topBarRows.forEach((row) => {
    row.style.display = 'none'
  })
}

function handleSearchBackButton() {
  const beforeSearchUrl = window.sessionStorage.getItem(PREVIOUS_SEARCH_URL_KEY)
  if (window.location.pathname.includes('/search/') && beforeSearchUrl !== null) {
    window.sessionStorage.removeItem(PREVIOUS_SEARCH_URL_KEY)
    window.location.href = beforeSearchUrl
  } else {
    showTopBarDefault()
  }
}

function showTopBarOptions() {
  optionsContainer.style.display = 'flex'
  optionsContainer.focus()
}

export function showTopBarSearch() {
  hideTopBars()
  document.querySelector('#top-app-bar__search').style.display = 'flex'
  searchInput.focus()
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
  searchInput.focus()
}
