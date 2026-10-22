import { escapeAttr, escapeHtml } from '../Components/HtmlEscape'
import { createPictureElement } from '../Layout/ImageVariants'
import { replaceSnackbar, SnackbarDuration, showSnackbar } from '../Layout/Snackbar'
import { controlTopBarSearchClearButton, showTopBarSearch } from '../Layout/TopBar'
import './Search.css'

const RESULTS_PER_PAGE = 30

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
    noResults: container.dataset.transNoResults || 'No results for “%query%”',
    showMore: container.dataset.transShowMore || 'Show more',
    members: container.dataset.transMembers || 'members',
    scratchProjects: container.dataset.transScratchProjects || 'Scratch projects',
    scratchConverting: container.dataset.transScratchConverting || 'Converting…',
    scratchConvertingSnackbar:
      container.dataset.transScratchConvertingSnackbar ||
      'Converting “%name%” — this can take a few seconds',
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
    h1.textContent = `${trans.searchResults} `
    const querySpan = document.createElement('span')
    querySpan.id = 'search-results-text'
    querySpan.textContent = `"${query}"`
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

  // Only shown when there are matches: most searches have none, and an empty section is noise.
  const scratchSection = createSection(
    'search-scratch-projects',
    'scratch',
    trans.scratchProjects,
    trans.showMore,
  )
  scratchSection.classList.add('d-none')

  container.appendChild(projectsSection)
  container.appendChild(usersSection)
  container.appendChild(studiosSection)
  container.appendChild(scratchSection)

  // Fixed priority, not relevance: scores from different indexes (and Scratch, which has none) don't compare.
  const sections = [
    { section: projectsSection, empty: trans.noProjects },
    { section: scratchSection, empty: null },
    { section: usersSection, empty: trans.noUsers },
    { section: studiosSection, empty: trans.noStudios },
  ]

  if (!query) {
    arrangeSections(container, sections, [], query, trans)
    return
  }

  const apiUrl =
    baseUrl +
    '/api/search?query=' +
    encodeURIComponent(query) +
    '&type=all&limit=' +
    RESULTS_PER_PAGE

  fetch(apiUrl)
    .then((response) => {
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      return response.json()
    })
    .then((data) => {
      renderProjects(projectsSection, data.projects || [], theme)
      renderUsers(usersSection, data.users || [], baseUrl, trans)
      renderStudios(studiosSection, data.studios || [], baseUrl, trans)
      renderScratchProjects(
        scratchSection,
        data.scratch_projects || [],
        data.scratch_projects_total || 0,
        baseUrl,
        theme,
        trans,
      )

      arrangeSections(
        container,
        sections,
        [
          (data.projects || []).length > 0,
          !scratchSection.classList.contains('d-none'),
          (data.users || []).length > 0,
          (data.studios || []).length > 0,
        ],
        query,
        trans,
      )

      wireShowMore(projectsSection, 'projects', query, baseUrl, theme, trans)
      wireShowMore(usersSection, 'users', query, baseUrl, theme, trans)
      wireShowMore(studiosSection, 'studios', query, baseUrl, theme, trans)
      wireShowMore(scratchSection, 'scratch', query, baseUrl, theme, trans)
    })
    .catch((error) => {
      console.error('Search API error:', error)
      arrangeSections(container, sections, [], query, trans)
    })
}

// Sections with results first, in their fixed order; the empty ones collapse into one line at the bottom.
function arrangeSections(container, sections, hasResults, query, trans) {
  const empties = []
  sections.forEach(({ section, empty }, i) => {
    section.classList.toggle('d-none', !hasResults[i])
    container.appendChild(section)
    if (!hasResults[i] && empty) empties.push(empty)
  })

  const summary = document.createElement('p')
  summary.id = 'search-empty-summary'
  summary.className = 'search-section__empty'
  summary.textContent =
    query && !hasResults.some(Boolean)
      ? trans.noResults.replace('%query%', query)
      : empties.join(' · ')
  if (summary.textContent) container.appendChild(summary)
}

function wireShowMore(section, type, query, baseUrl, theme, trans) {
  const btn = section.querySelector('.search-section__title__show-more')
  if (!btn || btn.classList.contains('d-none')) return

  let nextOffset = RESULTS_PER_PAGE
  let loading = false

  btn.addEventListener('click', (e) => {
    e.preventDefault()
    if (loading) return
    loading = true
    btn.style.opacity = '0.5'

    const cursor = btoa(String(nextOffset))
    const apiUrl =
      baseUrl +
      '/api/search?query=' +
      encodeURIComponent(query) +
      '&type=' +
      type +
      '&limit=' +
      RESULTS_PER_PAGE +
      '&cursor=' +
      encodeURIComponent(cursor)

    fetch(apiUrl)
      .then((response) => {
        if (!response.ok) throw new Error(`HTTP ${response.status}`)
        return response.json()
      })
      .then((data) => {
        const items = data[type === 'scratch' ? 'scratch_projects' : type] || []
        const container = section.querySelector('.search-section__items')

        if (type === 'projects') {
          items.forEach((project) => {
            const url = (project.project_url || '').replace('/app/', `/${theme}/`)
            container.appendChild(
              createCard(
                url,
                project.screenshot,
                'card',
                '/images/default/screenshot-card@1x.webp',
                project.name || '',
                'calendar_today',
                project.uploaded_string || '',
                project.not_for_kids,
              ),
            )
          })
        } else if (type === 'users') {
          items.forEach((user) => {
            const url = `${baseUrl}/app/user/${escapeAttr(String(user.id))}`
            container.appendChild(
              createCard(
                url,
                user.avatar,
                'thumb',
                '/images/default/avatar_default-thumb@1x.webp',
                user.username || '',
                'code',
                `${user.projects || 0} ${trans.projects}`,
              ),
            )
          })
        } else if (type === 'studios') {
          items.forEach((studio) => {
            const url = `${baseUrl}/app/studio/${escapeAttr(String(studio.id))}`
            container.appendChild(
              createCard(
                url,
                studio.cover,
                'card',
                '/images/default/screenshot-card@1x.webp',
                studio.name || '',
                'group',
                `${studio.members_count || 0} ${trans.members}`,
              ),
            )
          })
        } else if (type === 'scratch') {
          for (const project of items)
            container.appendChild(createScratchCard(project, baseUrl, theme, trans))
        }

        nextOffset += RESULTS_PER_PAGE

        const more =
          type === 'scratch'
            ? (data.scratch_projects_total || 0) > nextOffset
            : items.length >= RESULTS_PER_PAGE
        if (!more) {
          btn.classList.add('d-none')
        }
      })
      .catch((error) => {
        console.error('Search load more error:', error)
      })
      .finally(() => {
        loading = false
        btn.style.opacity = ''
      })
  })
}

function createSection(id, variant, title, showMoreText) {
  const section = document.createElement('div')
  section.id = id
  section.className = `search-section search-section--${variant}`

  const titleDiv = document.createElement('div')
  titleDiv.className = 'search-section__title'

  const h2 = document.createElement('h2')
  h2.textContent = title
  titleDiv.appendChild(h2)

  const showMoreBtn = document.createElement('a')
  showMoreBtn.className = 'search-section__title__show-more d-none'
  showMoreBtn.href = '#'
  showMoreBtn.innerHTML = `${escapeHtml(showMoreText)} <i class="material-icons">arrow_forward</i>`
  titleDiv.appendChild(showMoreBtn)

  section.appendChild(titleDiv)

  const items = document.createElement('div')
  items.className = 'search-section__items'
  section.appendChild(items)

  return section
}

function renderProjects(section, projects, theme) {
  const items = section.querySelector('.search-section__items')
  items.innerHTML = ''

  if (projects.length === 0) {
    return
  }

  if (projects.length >= RESULTS_PER_PAGE) {
    section.querySelector('.search-section__title__show-more').classList.remove('d-none')
  }

  projects.forEach((project) => {
    const url = (project.project_url || '').replace('/app/', `/${theme}/`)
    const card = createCard(
      url,
      project.screenshot,
      'card',
      '/images/default/screenshot-card@1x.webp',
      project.name || '',
      'calendar_today',
      project.uploaded_string || '',
      project.not_for_kids,
    )
    items.appendChild(card)
  })
}

function renderUsers(section, users, baseUrl, trans) {
  const items = section.querySelector('.search-section__items')
  items.innerHTML = ''

  if (users.length === 0) {
    return
  }

  if (users.length >= RESULTS_PER_PAGE) {
    section.querySelector('.search-section__title__show-more').classList.remove('d-none')
  }

  users.forEach((user) => {
    const url = `${baseUrl}/app/user/${escapeAttr(String(user.id))}`
    const card = createCard(
      url,
      user.avatar,
      'thumb',
      '/images/default/avatar_default-thumb@1x.webp',
      user.username || '',
      'code',
      `${user.projects || 0} ${trans.projects}`,
    )
    items.appendChild(card)
  })
}

function renderStudios(section, studios, baseUrl, trans) {
  const items = section.querySelector('.search-section__items')
  items.innerHTML = ''

  if (studios.length === 0) {
    return
  }

  if (studios.length >= RESULTS_PER_PAGE) {
    section.querySelector('.search-section__title__show-more').classList.remove('d-none')
  }

  studios.forEach((studio) => {
    const url = `${baseUrl}/app/studio/${escapeAttr(String(studio.id))}`
    const card = createCard(
      url,
      studio.cover,
      'card',
      '/images/default/screenshot-card@1x.webp',
      studio.name || '',
      'group',
      `${studio.members_count || 0} ${trans.members}`,
    )
    items.appendChild(card)
  })
}

function renderScratchProjects(section, projects, total, baseUrl, theme, trans) {
  // The total counts Scratch's full page, before projects already imported here are left out.
  const more = total > RESULTS_PER_PAGE
  if (projects.length === 0 && !more) return

  section.classList.remove('d-none')
  if (more) {
    section.querySelector('.search-section__title__show-more').classList.remove('d-none')
  }

  const items = section.querySelector('.search-section__items')
  for (const project of projects)
    items.appendChild(createScratchCard(project, baseUrl, theme, trans))
}

const SCRATCH_PLACEHOLDER = '/images/default/screenshot-card@1x.webp'

function createScratchCard(project, baseUrl, theme, trans) {
  // A button, not a link: the card converts the project through the API instead of leaving for Scratch.
  const card = createCard(
    null,
    null,
    'card',
    SCRATCH_PLACEHOLDER,
    project.name || '',
    'person',
    project.username || '',
  )
  const scratchId = encodeURIComponent(String(project.id))

  // The real thumbnail, straight from Scratch; the placeholder if Scratch has none or is unreachable.
  const thumbnail = document.createElement('img')
  thumbnail.className = 'search-card__image'
  thumbnail.alt = project.name || ''
  thumbnail.loading = 'lazy'
  thumbnail.addEventListener('error', () => (thumbnail.src = SCRATCH_PLACEHOLDER), { once: true })
  // Kept in data-scratch-thumbnail too: the error fallback changes src, this attribute stays.
  thumbnail.dataset.scratchThumbnail = `https://uploads.scratch.mit.edu/get_image/project/${scratchId}_480x360.png`
  thumbnail.src = thumbnail.dataset.scratchThumbnail
  card.querySelector('picture').replaceWith(thumbnail)

  const badge = document.createElement('span')
  badge.className = 'search-card__badge'
  badge.textContent = 'Scratch'
  card.insertBefore(badge, card.querySelector('.search-card__name'))

  // Present from the start, so screen readers announce the label once it is filled in.
  const overlay = document.createElement('span')
  overlay.className = 'search-card__overlay d-none'
  const label = document.createElement('span')
  label.className = 'search-card__overlay-label'
  label.setAttribute('aria-live', 'polite')
  overlay.append(document.createElement('md-circular-progress'), label)
  overlay.firstChild.setAttribute('indeterminate', '')
  card.appendChild(overlay)

  card.addEventListener('click', () =>
    importScratchProject(card, project.name || '', scratchId, baseUrl, theme, trans),
  )

  return card
}

// Converting can take seconds: the card shows that it is busy, then the converted project opens.
function importScratchProject(card, name, scratchId, baseUrl, theme, trans) {
  const overlay = card.querySelector('.search-card__overlay')
  const label = card.querySelector('.search-card__overlay-label')
  card.disabled = true
  card.setAttribute('aria-busy', 'true')
  card.classList.add('search-card--loading')
  overlay.classList.remove('d-none')
  label.textContent = trans.scratchConverting
  showSnackbar('#share-snackbar', trans.scratchConvertingSnackbar.replace('%name%', name))

  fetch(`${baseUrl}/api/projects/scratch/${scratchId}`, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { Accept: 'application/json' },
  })
    .then((response) =>
      response
        .json()
        .catch(() => ({}))
        .then((body) => {
          if (!response.ok || !body?.id) {
            throw new Error(body?.error?.message || `HTTP ${response.status}`)
          }
          window.location.href = `${baseUrl}/${theme}/project/${encodeURIComponent(body.id)}`
        }),
    )
    .catch((error) => {
      card.disabled = false
      card.removeAttribute('aria-busy')
      card.classList.remove('search-card--loading')
      overlay.classList.add('d-none')
      label.textContent = ''
      // Replace the "Converting…" message at once, so the outcome doesn't wait behind it.
      replaceSnackbar('#share-snackbar', error.message, SnackbarDuration.error)
    })
}

// Without a url the card is a button; its caller handles the click.
function createCard(url, variants, size, fallback, name, metaIcon, metaText, blur = false) {
  const a = document.createElement(url === null ? 'button' : 'a')
  a.className = 'search-card'
  if (url === null) a.type = 'button'
  else a.href = url

  const picture = createPictureElement(
    variants,
    size,
    fallback,
    {
      class: 'search-card__image lazyload',
      alt: name,
      ...(blur ? { style: 'filter: blur(10px)' } : {}),
    },
    { lazy: true },
  )
  a.appendChild(picture)

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
