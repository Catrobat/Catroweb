import { controlTopBarSearchClearButton, showTopBarSearch } from '../Layout/TopBar'
import { escapeHtml, escapeAttr } from '../Components/HtmlEscape'
import { getImageUrl } from '../Layout/ImageVariants'
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
    studios: container.dataset.transStudios || 'Studios',
    noProjects: container.dataset.transNoProjects || 'No projects found',
    noUsers: container.dataset.transNoUsers || 'No users found',
    noStudios: container.dataset.transNoStudios || 'No studios found',
    showMore: container.dataset.transShowMore || 'Show more',
    members: container.dataset.transMembers || 'members',
  }

  const searchInput = document.querySelector('#top-app-bar__search-input')
  if (searchInput) {
    searchInput.value = query
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
  if (query) {
    h1.textContent = trans.searchResults + ' '
    const querySpan = document.createElement('span')
    querySpan.id = 'search-results-text'
    querySpan.textContent = '"' + query + '"'
    h1.appendChild(querySpan)
  } else {
    h1.textContent = trans.searchResults
  }
  titleDiv.appendChild(h1)
  container.appendChild(titleDiv)

  const projectsSection = createSection(
    'search-projects',
    'projects',
    trans.projects,
    trans.showMore,
  )
  const usersSection = createSection('search-users', 'users', trans.users, trans.showMore)
  const studiosSection = createSection('search-studios', 'studios', trans.studios, trans.showMore)

  container.appendChild(projectsSection)
  container.appendChild(usersSection)
  container.appendChild(studiosSection)

  if (!query) {
    showEmpty(projectsSection, trans.noProjects)
    showEmpty(usersSection, trans.noUsers)
    showEmpty(studiosSection, trans.noStudios)
    return
  }

  const apiUrl =
    baseUrl + '/api/search?query=' + encodeURIComponent(query) + '&type=all&limit=30&offset=0'

  fetch(apiUrl)
    .then((response) => {
      if (!response.ok) throw new Error('HTTP ' + response.status)
      return response.json()
    })
    .then((data) => {
      renderProjects(projectsSection, data.projects || [], theme, trans)
      renderUsers(usersSection, data.users || [], baseUrl, trans)
      renderStudios(studiosSection, data.studios || [], baseUrl, trans)
    })
    .catch((error) => {
      console.error('Search API error:', error)
      showEmpty(projectsSection, trans.noProjects)
      showEmpty(usersSection, trans.noUsers)
      showEmpty(studiosSection, trans.noStudios)
    })
}

function createSection(id, variant, title, showMoreText) {
  const section = document.createElement('div')
  section.id = id
  section.className = 'search-section search-section--' + variant

  const titleDiv = document.createElement('div')
  titleDiv.className = 'search-section__title'

  const h2 = document.createElement('h2')
  h2.textContent = title
  titleDiv.appendChild(h2)

  const showMoreBtn = document.createElement('a')
  showMoreBtn.className = 'search-section__title__show-more d-none'
  showMoreBtn.href = '#'
  showMoreBtn.innerHTML = escapeHtml(showMoreText) + ' <i class="material-icons">arrow_forward</i>'
  titleDiv.appendChild(showMoreBtn)

  section.appendChild(titleDiv)

  const items = document.createElement('div')
  items.className = 'search-section__items'
  section.appendChild(items)

  return section
}

function renderProjects(section, projects, theme, trans) {
  const items = section.querySelector('.search-section__items')
  items.innerHTML = ''

  if (projects.length === 0) {
    showEmpty(section, trans.noProjects)
    return
  }

  if (projects.length >= 30) {
    section.querySelector('.search-section__title__show-more').classList.remove('d-none')
  }

  projects.forEach((project) => {
    const url = (project.project_url || '').replace('/app/', '/' + theme + '/')
    const card = createCard(
      url,
      getImageUrl(project.screenshot, 'card', '/images/default/screenshot-card@1x.webp'),
      project.name || '',
      'calendar_today',
      project.uploaded_string || '',
    )
    items.appendChild(card)
  })
}

function renderUsers(section, users, baseUrl, trans) {
  const items = section.querySelector('.search-section__items')
  items.innerHTML = ''

  if (users.length === 0) {
    showEmpty(section, trans.noUsers)
    return
  }

  if (users.length >= 30) {
    section.querySelector('.search-section__title__show-more').classList.remove('d-none')
  }

  users.forEach((user) => {
    const url = baseUrl + '/app/user/' + escapeAttr(String(user.id))
    const avatar = getImageUrl(user.avatar, 'thumb', '/images/default/avatar_default-thumb@1x.webp')
    const card = createCard(
      url,
      avatar,
      user.username || '',
      'code',
      (user.projects || 0) + ' ' + trans.projects,
    )
    items.appendChild(card)
  })
}

function renderStudios(section, studios, baseUrl, trans) {
  const items = section.querySelector('.search-section__items')
  items.innerHTML = ''

  if (studios.length === 0) {
    showEmpty(section, trans.noStudios)
    return
  }

  if (studios.length >= 30) {
    section.querySelector('.search-section__title__show-more').classList.remove('d-none')
  }

  studios.forEach((studio) => {
    const url = baseUrl + '/app/studio/' + escapeAttr(String(studio.id))
    const image = getImageUrl(studio.cover, 'card', '/images/default/screenshot-card@1x.webp')
    const card = createCard(
      url,
      image,
      studio.name || '',
      'group',
      (studio.members_count || 0) + ' ' + trans.members,
    )
    items.appendChild(card)
  })
}

function createCard(url, imageSrc, name, metaIcon, metaText) {
  const a = document.createElement('a')
  a.className = 'search-card'
  a.href = url

  const img = document.createElement('img')
  img.className = 'search-card__image lazyload'
  img.setAttribute('data-src', imageSrc)
  img.alt = name
  a.appendChild(img)

  const nameSpan = document.createElement('span')
  nameSpan.className = 'search-card__name'
  nameSpan.textContent = name
  a.appendChild(nameSpan)

  const meta = document.createElement('div')
  meta.className = 'search-card__meta'

  const icon = document.createElement('i')
  icon.className = 'material-icons'
  icon.textContent = metaIcon
  meta.appendChild(icon)

  const value = document.createElement('span')
  value.textContent = metaText
  meta.appendChild(value)

  a.appendChild(meta)

  return a
}

function showEmpty(section, message) {
  const items = section.querySelector('.search-section__items')
  if (items) {
    items.innerHTML = ''
    const p = document.createElement('p')
    p.className = 'search-section__empty'
    p.textContent = message
    items.appendChild(p)
  }
}
