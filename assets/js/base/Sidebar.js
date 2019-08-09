
function enableNavButtonIfCategoryContainsProjects(container, url) {
  
  if (setNavContainerWithSession(container))
  {
    return
  }
  
  $.get(url, {limit: 1, offset: 0}, function (data) {
    setNavContainerAndSession(container, data)
  })
}

function enableNavButtonIfRecommendedCategoryContainsProjects(container, url, program_id) {
  
  if (setNavContainerWithSession(container))
  {
    return
  }
  
  $.get(url, {program_id: program_id}, function (data) {
    setNavContainerAndSession(container, data)
  })
}

function setNavContainerWithSession(container) {
  let nav_item = sessionStorage.getItem(container)
  
  if (nav_item !== null) {
    let nav_item_visible = parseInt(sessionStorage.getItem(container))
    if (nav_item_visible === 1) {
      $(container).show()
    }
    else
    {
      $(container).hide()
    }
    return true;
  }
}

function setNavContainerAndSession(container, data) {
  if (data.CatrobatProjects === undefined || data.CatrobatProjects.length === 0)
  {
    sessionStorage.setItem(container, 0)
    $(container).hide()
  }
  else
  {
    sessionStorage.setItem(container, 1)
    $(container).show()
  }
}
