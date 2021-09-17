import $ from 'jquery'

require('../../styles/layout/sidebar.scss')

$(() => {
  if ($('.js-user-state').data('is-user-logged-in')) {
    const sidebar = $('.js-sidebar')

    updateBadge(
      sidebar.data('path-notifications-count'),
      'sidebar_badge--unseen-notifications'
    )

    updateBadge(
      sidebar.data('path-achievements-count'),
      'sidebar_badge--unseen-achievements',
      sidebar.data('trans-achievements-bade-text')
    )
  }
})

function updateBadge (url, badgeID, badgeText = null, maxAmountToFetch = 99, refreshRate = 10000) {
  const badge = document.getElementById(badgeID)
  if (!badge) {
    return
  }
  fetch(url)
    .then(response => response.json())
    .then(data => {
      const count = data.count
      if (count > 0) {
        if (badgeText === null) {
          badge.innerHTML = (count <= maxAmountToFetch) ? count.toString() : (maxAmountToFetch + '+')
        } else {
          badge.innerHTML = badgeText
        }
        badge.style.display = 'block'
      } else {
        badge.innerHTML = ''
        badge.style.display = 'none'
      }
      setTimeout(updateBadge, refreshRate, url, badgeID, badgeText, maxAmountToFetch, refreshRate)
    })
    .catch((error) => {
      console.error('Unable to update sidebar badge! Error: ', error)
    })
}
