import $ from 'jquery'
import { Carousel } from 'bootstrap'
import Swal from 'sweetalert2'
import { ProjectList } from './components/project_list'

require('../styles/index.scss')

new Carousel('#feature-slider')

$(() => {
  initHomeProjects()

  const $oauthGreeting = $('.js-oauth-greeting')
  showOauthPopup(
    $oauthGreeting.data('path-oauth-first-login'),
    $oauthGreeting.data('trans-info'),
    $oauthGreeting.data('trans-title'),
    $oauthGreeting.data('trans-ok')
  )
})

function initHomeProjects () {
  const $homeProjects = $('#home-projects')
  $('.project-list', $homeProjects).each(function () {
    const category = $(this).data('category')
    const property = $(this).data('property')
    const theme = $(this).data('theme')
    const flavor = $(this).data('flavor')
    const baseUrl = $(this).data('base-url')

    let url = baseUrl + '/api/projects?category=' + category

    if (flavor !== 'pocketcode' || category === 'example') {
      // Only the pocketcode flavor shows projects from all flavors!
      // Other flavors must only show projects from their flavor.
      url += '&flavor=' + flavor
    }

    const list = new ProjectList(this, category, url, property, theme)
    $(this).data('list', list)
  })
}

function showOauthPopup (firstOauthLoginUrl, informationText, title, okTranslation) {
  $.get(firstOauthLoginUrl, function (data) {
      if (data.first_login === true) {
        const shown = localStorage.getItem('oauthSignIn')
        if (shown == null) {
          localStorage.setItem('oauthSignIn', '1')
          Swal.fire({
            title: title,
            html: informationText,
            showCancelButton: false,
            allowOutsideClick: false,
            confirmButtonText: okTranslation,
            icon: 'info',
            customClass: {
              confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false
          })
        }
      }
  })
}
