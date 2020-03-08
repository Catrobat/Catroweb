/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
function enableNavButtonIfCategoryContainsProjects (container, url) {
  if (setNavContainerWithSession(container)) {
    return
  }

  $.get(url, { limit: 1, offset: 0 }, function (data) {
    setNavContainerAndSession(container, data)
  })
}

// eslint-disable-next-line no-unused-vars
function enableNavButtonIfRecommendedCategoryContainsProjects (container, url, programId) {
  if (setNavContainerWithSession(container)) {
    return
  }

  $.get(url, { programId: programId }, function (data) {
    setNavContainerAndSession(container, data)
  })
}

function setNavContainerWithSession (container) {
  const navItem = sessionStorage.getItem(container)

  if (navItem !== null) {
    const navItemVisible = parseInt(sessionStorage.getItem(container))
    if (navItemVisible === 1) {
      $(container).show()
    } else {
      $(container).hide()
    }
    return true
  }
}

function setNavContainerAndSession (container, data) {
  if (data.CatrobatProjects === undefined || data.CatrobatProjects.length === 0) {
    sessionStorage.setItem(container, 0)
    $(container).hide()
  } else {
    sessionStorage.setItem(container, 1)
    $(container).show()
  }
}

// eslint-disable-next-line no-unused-vars
function manageNotificationsDropdown () {
  const notificationDropdownToggler = document.getElementById('notifications-dropdown-toggler')
  const notificationDropdownContent = document.getElementById('notifications-dropdown-content')

  if (notificationDropdownToggler === null || notificationDropdownContent === null) {
    return // nothing to do when user is not logged in -> not notification categories
  }

  notificationDropdownToggler.addEventListener('click', function () {
    notificationDropdownContent.style.maxHeight ? collapseNotificationDropdownMenu() : expandNotificationDropdownMenu()
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

  notificationDropdownContent.style.maxHeight = notificationDropdownContent.scrollHeight + 'px'
  notificationDropdownArrow.classList.remove('fa-caret-left')
  notificationDropdownArrow.classList.add('fa-caret-down')

  const notificationCategories = notificationDropdownContent.getElementsByTagName('a')
  Array.prototype.forEach.call(notificationCategories, function (category) {
    category.style.visibility = 'visible'
  })
}

function collapseNotificationDropdownMenu () {
  const notificationDropdownArrow = document.getElementById('notifications-dropdown-arrow')
  const notificationDropdownContent = document.getElementById('notifications-dropdown-content')

  notificationDropdownContent.style.maxHeight = null
  notificationDropdownArrow.classList.remove('fa-caret-down')
  notificationDropdownArrow.classList.add('fa-caret-left')

  const notificationCategories = notificationDropdownContent.getElementsByTagName('a')
  Array.prototype.forEach.call(notificationCategories, function (category) {
    category.style.visibility = 'hidden'
  })
}
