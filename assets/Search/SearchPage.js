import { controlTopBarSearchClearButton, showTopBarSearch } from '../Layout/TopBar'
import { escapeHtml, escapeAttr } from '../Components/HtmlEscape'
import './Search.scss'

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('search-results')
  if (!container) return

  const query = container.dataset.query || ''
  const theme = container.dataset.theme || 'pocketcode'
  const baseUrl = container.dataset.baseUrl || ''

  const trans = {
    searchResults: container.dataset.transSearchResults || 'Search results',
    projects: container.dataset.transProjects || 'Projects',
    users: container.dataset.transUsers || 'Users',
    noProjects: container.dataset.transNoProjects || 'No projects found',
    noUsers: container.dataset.transNoUsers || 'No users found',
    showMore: container.dataset.transShowMore || 'Show more',
  }

  const searchInput = document.querySelector('#top-app-bar__search-input')
  if (searchInput) {
    searchInput.value = query
    searchInput.innerHTML = query
  }

  showTopBarSearch()
  controlTopBarSearchClearButton()

  renderPage(container, query, theme, baseUrl, trans)
})

function renderPage(container, query, theme, baseUrl, trans) {
  container.innerHTML = ''

  const titleDiv = document.createElement('div')
  titleDiv.className = 'search-results__title'
  const h1 = document.createElement('h1')
  const querySpan = document.createElement('span')
  querySpan.id = 'search-results-text'
  querySpan.textContent = query ? query + ' ' : ''
  h1.appendChild(querySpan)
  h1.appendChild(document.createTextNode(trans.searchResults))
  titleDiv.appendChild(h1)
  container.appendChild(titleDiv)

  const projectsSection = createSection(
    'search-projects',
    'project-list',
    trans.projects,
    trans.showMore,
  )
  container.appendChild(projectsSection)

  const usersSection = createSection('search-users', 'user-list', trans.users, trans.showMore)
  container.appendChild(usersSection)

  if (!query) {
    showEmpty(projectsSection, trans.noProjects, 'project-list')
    showEmpty(usersSection, trans.noUsers, 'user-list')
    return
  }

  const apiUrl =
    baseUrl + '/api/search?query=' + encodeURIComponent(query) + '&type=all&limit=30&offset=0'

  projectsSection.classList.add('loading')
  usersSection.classList.add('loading')

  fetch(apiUrl)
    .then((response) => response.json())
    .then((data) => {
      renderProjects(projectsSection, data.projects || [], theme, baseUrl, trans)
      renderUsers(usersSection, data.users || [], theme, baseUrl, trans)
    })
    .catch((error) => {
      console.error('Search API error:', error)
      projectsSection.classList.remove('loading')
      usersSection.classList.remove('loading')
    })
}

function createSection(id, listClass, title, showMoreText) {
  const section = document.createElement('div')
  section.id = id
  section.className = listClass + ' loading horizontal'

  const containerDiv = document.createElement('div')
  containerDiv.className = 'container'

  const titleDiv = document.createElement('div')
  titleDiv.className = listClass + '__title'

  const h2 = document.createElement('h2')
  h2.textContent = title
  titleDiv.appendChild(h2)

  const toggleBtn = document.createElement('div')
  toggleBtn.className = listClass + '__title__btn-toggle btn-view-open'

  const toggleText = document.createElement('div')
  toggleText.className = listClass + '__title__btn-toggle__text'
  toggleText.textContent = showMoreText

  const toggleIcon = document.createElement('div')
  toggleIcon.className = listClass + '__title__btn-toggle__icon material-icons'
  toggleIcon.textContent = 'arrow_forward'

  toggleBtn.appendChild(toggleText)
  toggleBtn.appendChild(toggleIcon)
  titleDiv.appendChild(toggleBtn)
  containerDiv.appendChild(titleDiv)

  const wrapper = document.createElement('div')
  wrapper.className = listClass + '__wrapper'

  const itemsContainer = document.createElement('div')
  itemsContainer.className = listClass === 'project-list' ? 'projects-container' : 'users-container'
  wrapper.appendChild(itemsContainer)

  containerDiv.appendChild(wrapper)
  section.appendChild(containerDiv)

  return section
}

function renderProjects(section, projects, theme, baseUrl, trans) {
  section.classList.remove('loading')

  const itemsContainer = section.querySelector('.projects-container')
  itemsContainer.innerHTML = ''

  if (projects.length === 0) {
    showEmpty(section, trans.noProjects, 'project-list')
    return
  }

  projects.forEach((project) => {
    const el = createProjectCard(project, theme)
    itemsContainer.appendChild(el)
  })
}

function createProjectCard(project, theme) {
  const projectUrl = (project.project_url || '').replace('/app/', '/' + theme + '/')

  const a = document.createElement('a')
  a.className = 'project-list__project'
  a.href = projectUrl
  a.dataset.id = project.id

  const img = document.createElement('img')
  if (project.screenshot_small) {
    img.setAttribute('data-src', project.screenshot_small)
    img.setAttribute(
      'data-srcset',
      project.screenshot_small +
        ' 80w, ' +
        (project.screenshot_large || project.screenshot_small) +
        ' 480w',
    )
    img.setAttribute('data-sizes', '(min-width: 768px) 10vw, 25vw')
  }
  img.className = 'lazyload project-list__project__image'
  if (project.not_for_kids) {
    img.style.filter = 'blur(10px)'
  }
  a.appendChild(img)

  const nameSpan = document.createElement('span')
  nameSpan.className = 'project-list__project__name'
  nameSpan.textContent = project.name || ''
  a.appendChild(nameSpan)

  const propDiv = document.createElement('div')
  propDiv.className =
    'lazyload project-list__project__property project-list__project__property-uploaded'

  const icon = document.createElement('i')
  icon.className = 'material-icons'
  icon.textContent = 'schedule'
  propDiv.appendChild(icon)

  const valueSpan = document.createElement('span')
  valueSpan.className = 'project-list__project__property__value'
  valueSpan.textContent = project.uploaded_string || ''
  propDiv.appendChild(valueSpan)

  a.appendChild(propDiv)

  if (project.not_for_kids) {
    const nfkDiv = document.createElement('div')
    nfkDiv.className = 'lazyload project-list__project__property__not-for-kids'

    const nfkImg = document.createElement('img')
    nfkImg.className = 'lazyload project-list__not-for-kids-logo'
    nfkImg.src = '/images/default/not_for_kids.svg'
    nfkDiv.appendChild(nfkImg)

    const nfkSpan = document.createElement('span')
    nfkSpan.className = 'project-list__project__property__value'
    nfkSpan.textContent = 'Not for kids'
    nfkDiv.appendChild(nfkSpan)

    a.appendChild(nfkDiv)
  }

  return a
}

function renderUsers(section, users, theme, baseUrl, trans) {
  section.classList.remove('loading')

  const itemsContainer = section.querySelector('.users-container')
  itemsContainer.innerHTML = ''

  if (users.length === 0) {
    showEmpty(section, trans.noUsers, 'user-list')
    return
  }

  users.forEach((user) => {
    const el = createUserCard(user, baseUrl, trans)
    itemsContainer.appendChild(el)
  })
}

function createUserCard(user, baseUrl, trans) {
  const userUrl = baseUrl + '/app/user/' + escapeAttr(String(user.id))

  const a = document.createElement('a')
  a.className = 'user-list__user'
  a.href = userUrl
  a.dataset.id = user.id

  const img = document.createElement('img')
  img.className = 'user-list__user__image'
  if (typeof user.picture === 'string' && user.picture.length > 0) {
    img.src = user.picture
  } else {
    img.setAttribute('data-src', '/images/default/avatar_default.png?v=3.7.1')
    img.classList.add('lazyload')
  }
  a.appendChild(img)

  const nameSpan = document.createElement('span')
  nameSpan.className = 'user-list__user__name'
  nameSpan.textContent = user.username || ''
  a.appendChild(nameSpan)

  const propDiv = document.createElement('div')
  propDiv.className = 'lazyload user-list__user__property'

  const valueSpan = document.createElement('span')
  valueSpan.className = 'user-list__user__property__value'
  valueSpan.textContent = (user.projects || 0) + ' ' + trans.projects
  propDiv.appendChild(valueSpan)

  a.appendChild(propDiv)

  return a
}

function showEmpty(section, message, listClass) {
  section.classList.remove('loading')

  const itemsContainer = section.querySelector(
    listClass === 'project-list' ? '.projects-container' : '.users-container',
  )
  if (itemsContainer) {
    itemsContainer.innerHTML = escapeHtml(message)
  }
  section.classList.add('empty-with-text')
}
