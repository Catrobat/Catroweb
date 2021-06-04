/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
function manageNotificationsDropdown () {
  const notificationDropdownToggler = document.getElementById('notifications-dropdown-toggler')
  const notificationDropdownContent = document.getElementById('notifications-dropdown-content')

  if (notificationDropdownToggler === null || notificationDropdownContent === null) {
    return // nothing to do when user is not logged in -> not notification categories
  }

  notificationDropdownToggler.addEventListener('click', function () {
    notificationDropdownContent.classList.contains('shown') ? collapseNotificationDropdownMenu() : expandNotificationDropdownMenu()
  })

  // automatically expand the notification dropdown when the user is on a notification page
  const notificationSubcategories = notificationDropdownContent.getElementsByTagName('a')

  let shouldBeExpanded = false
  for (const entry of notificationSubcategories) {
    if (entry.href === window.location.href) {
      expandNotificationDropdownMenu()
      shouldBeExpanded = true
      break
    }
  }
  if (!shouldBeExpanded) {
    collapseNotificationDropdownMenu()
  }
}

function expandNotificationDropdownMenu () {
  const notificationDropdownArrow = document.getElementById('notifications-dropdown-arrow')
  const notificationDropdownContent = document.getElementById('notifications-dropdown-content')

  notificationDropdownContent.classList.add('shown')
  notificationDropdownArrow.textContent = 'expand_more'

  const notificationCategories = notificationDropdownContent.getElementsByTagName('a')
  Array.prototype.forEach.call(notificationCategories, function (category) {
    category.style.visibility = 'visible'
  })
}

function collapseNotificationDropdownMenu () {
  const notificationDropdownArrow = document.getElementById('notifications-dropdown-arrow')
  const notificationDropdownContent = document.getElementById('notifications-dropdown-content')

  notificationDropdownContent.classList.remove('shown')
  notificationDropdownArrow.textContent = 'chevron_left'

  const notificationCategories = notificationDropdownContent.getElementsByTagName('a')
  Array.prototype.forEach.call(notificationCategories, function (category) {
    category.style.visibility = 'hidden'
  })
}
