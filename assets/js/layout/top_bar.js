import $ from 'jquery'
import { MDCTopAppBar } from '@material/top-app-bar'

require('../../styles/layout/top_bar.scss')

/**
 * Define elements
 */
const topAppBarElement = document.querySelector('.mdc-top-app-bar')
new MDCTopAppBar(topAppBarElement)

const $title = $('#top-app-bar__title')
const $toggleSidebarButton = $('#top-app-bar__btn-sidebar-toggle')
let $backButton = $('#top-app-bar__back__btn-back')

const $searchButton = $('#top-app-bar__btn-search')
const $searchBackButton = $('#top-app-bar__btn-search-back')
const $searchClearButton = $('#top-app-bar__btn-search-clear')
const $searchInput = $('#top-app-bar__search-input')
const $searchForm = $('#top-app-bar__search-form')

const $optionsButton = $('#top-app-bar__btn-options')
const $optionsContainer = $('#top-app-bar__options-container')

const defaultAppBarHref = $title.attr('href')
const defaultTitle = $title.html()

/**
 * Register eventListener
 */
$searchButton.on('click', () => { showTopBarSearch() })
$searchBackButton.on('click', () => { handleSearchBackButton() })
$searchClearButton.on('click', () => { clearTopBarSearch() })
$searchInput.on('input change', () => { controlTopBarSearchClearButton() })
$searchForm.on('submit', (event) => { submitSearchForm(event) })
$optionsButton.on('click', () => { showTopBarOptions() })
$(document).on('click', (event) => {
  if (!$optionsButton.is(event.target)) {
    $optionsContainer.hide() // hide options container when user clicks
  }
})

/**
 * Session
 */
const PREVIOUS_SEARCH_URL_KEY = 'KEY_BEFORE_SEARCH_URL'

/**
 * Dynamic data
 */
const searchUrl = $('.js-header').data('path-search-url')

/**
 * public
 */
export function showTopBarDownload () {
  hideTopBars()
  $('#top-app-bar__media-library-download').show()
}

export function showTopBarDefault () {
  hideTopBars()
  $('#top-app-bar__default').show()
}

export function showCustomTopBarTitle (title, onBack) {
  $title.text(title)
  $title.removeAttr('href')
  $backButton.hide()

  $('.mdc-top-app-bar').css('top', 0)
  if (typeof onBack === 'function') {
    $backButton = $('<button/>', {
      id: 'top-app-bar__back__btn-back',
      class: 'material-icons mdc-top-app-bar__action-item mdc-icon-button',
      'aria-label': 'Back to previous page',
      text: 'arrow_back'
    }).insertAfter($toggleSidebarButton)
    $toggleSidebarButton.hide()
    $backButton.show()
    $backButton.off('click').on('click', onBack)
  }
}

export function showDefaultTopBarTitle () {
  $title.html(defaultTitle)
  $title.attr('href', defaultAppBarHref)
  $toggleSidebarButton.show()
  if ($backButton) $backButton.hide()
}

function submitSearchForm (event) {
  event.preventDefault()
  const query = $searchInput.val()
  if (!window.location.pathname.includes('/search/')) {
    // keeping track of last page that was no search page
    window.sessionStorage.setItem(PREVIOUS_SEARCH_URL_KEY, window.location.href)
  }
  window.location.href = searchUrl + encodeURIComponent(query.trim())
}

function hideTopBars () {
  $('.mdc-top-app-bar__row').hide()
}

function handleSearchBackButton () {
  const beforeSearchUrl = window.sessionStorage.getItem(PREVIOUS_SEARCH_URL_KEY)
  if (window.location.pathname.includes('/search/') && beforeSearchUrl !== null) {
    window.sessionStorage.removeItem(PREVIOUS_SEARCH_URL_KEY)
    window.location.href = beforeSearchUrl
  } else {
    showTopBarDefault()
  }
}

function showTopBarOptions () {
  const container = $optionsContainer
  container.show()
  container.trigger('focus')
}

export function showTopBarSearch () {
  hideTopBars()
  $('#top-app-bar__search').show()
  $searchInput.trigger('focus')
}

export function controlTopBarSearchClearButton () {
  if ($searchInput.val()) {
    $searchClearButton.show()
  } else {
    $searchClearButton.hide()
  }
}

function clearTopBarSearch () {
  const inputField = $searchInput
  inputField.val('')
  $searchClearButton.hide()
  inputField.trigger('focus')
}
