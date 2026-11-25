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
    error: container.dataset.transError || 'Oops, something did not work. Please try again!',
    docs: container.dataset.transDocs || 'Documentation',
    noDocs: container.dataset.transNoDocs || 'No documentation pages found',
    docsGroups: {
      guides: container.dataset.transDocsGuides || 'Guides',
      bricks: container.dataset.transDocsBricks || 'Bricks',
      formula: container.dataset.transDocsFormula || 'Formula elements',
    },
  }
  const docs = {
    locale: container.dataset.locale || '',
    first: container.dataset.docsFirst === 'true',
    only: container.dataset.docsOnly === 'true',
    explicitLocale: container.dataset.docsExplicitLocale,
    sections: JSON.parse(container.dataset.docsSections || '{}'),
  }

  const searchInput = document.querySelector('#top-app-bar__search-input')
  if (searchInput) {
    searchInput.value = query
  }

  if (!docs.only) {
    showTopBarSearch()
    controlTopBarSearchClearButton()
  }

  if (docs.only) {
    renderDocsOnly(container, query, theme, baseUrl, trans, docs)
    return
  }
  renderPage(container, query, theme, baseUrl, trans, docs)
})

function fetchDocs(baseUrl, query, locale) {
  return fetch(
    `${baseUrl}/api/docs/search?${new URLSearchParams({ q: query.slice(0, 200), locale })}`,
  ).then((response) => {
    if (!response.ok) throw new Error(`HTTP ${response.status}`)
    return response.json()
  })
}

async function renderDocsOnly(container, query, theme, baseUrl, trans, docs) {
  const section = createSection('search-docs', 'docs', trans.docs, trans.showMore)
  const status = document.createElement('p')
  status.className = 'search-section__empty'
  status.setAttribute('role', 'status')
  container.append(section, status)
  try {
    const data = query ? await fetchDocs(baseUrl, query, docs.locale) : null
    if (!renderDocs(section, data, baseUrl, theme, trans, docs)) {
      status.textContent = trans.noDocs
    }
  } catch {
    status.textContent = trans.error
  }
}

function renderPage(container, query, theme, baseUrl, trans, docs) {
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

  // Scratch results arrive late, at the bottom: screen readers hear about them (and about a failed search) here.
  // Present from the start, so the text set later is announced.
  const status = document.createElement('p')
  status.id = 'search-status'
  status.className = 'visually-hidden'
  status.setAttribute('role', 'status')
  status.setAttribute('aria-live', 'polite')
  container.appendChild(status)

  const projectsSection = createSection(
    'search-projects',
    'projects',
    trans.projects,
    trans.showMore,
  )
  const usersSection = createSection('search-users', 'users', trans.users, trans.showMore)
  const studiosSection = createSection('search-studios', 'studios', trans.studios, trans.showMore)
  const docsSection = createSection('search-docs', 'docs', trans.docs, trans.showMore)

  // Only shown when there are matches: most searches have none, and an empty section is noise.
  const scratchSection = createSection(
    'search-scratch-projects',
    'scratch',
    trans.scratchProjects,
    trans.showMore,
  )
  scratchSection.classList.add('d-none')

  // Fixed priority, not relevance: scores from different indexes (and Scratch, which has none) don't compare.
  // Docs come first when the search was started on a docs page, else after studios.
  // Scratch is last because it can arrive after the rest: showing it then must not push results the user is about to tap.
  const ours = [
    { section: projectsSection, empty: trans.noProjects },
    { section: usersSection, empty: trans.noUsers },
    { section: studiosSection, empty: trans.noStudios },
  ]
  const docsEntry = { section: docsSection, empty: trans.noDocs }
  const sections = [
    ...(docs.first ? [docsEntry, ...ours] : [...ours, docsEntry]),
    { section: scratchSection, empty: null },
  ]
  for (const { section } of sections) container.appendChild(section)

  if (!query) {
    arrangeSections(container, sections, [], query, trans)
    return
  }

  const search = (type) =>
    fetch(
      `${baseUrl}/api/search?query=${encodeURIComponent(query)}&type=${type}&limit=${RESULTS_PER_PAGE}`,
    ).then((response) => {
      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      return response.json()
    })

  // Scratch is searched live and takes ~200 ms when not cached, so it gets its own request and doesn't hold up ours.
  let found = null // per section except Scratch (in the order of sections), once our results are in
  let scratchFound = false
  let failed = false // our search failed: say so instead of "no results"
  let scratchCount = 0
  const arrange = () => {
    if (found) arrangeSections(container, sections, [...found, scratchFound], query, trans, failed)
  }
  // Built from all state, so neither message replaces the other; Scratch only once its section can be shown.
  const announce = () => {
    const parts = []
    if (failed) parts.push(trans.error)
    if (found && scratchCount > 0) parts.push(`${trans.scratchProjects}: ${scratchCount}`)
    status.textContent = parts.join(' ')
  }

  // The docs search is optional: when it fails, the rest is still shown.
  const docsSearch = fetchDocs(baseUrl, query, docs.locale).catch(() => null)

  Promise.all([search('all'), docsSearch])
    .then(([data, docsData]) => {
      renderProjects(projectsSection, data.projects || [], theme)
      renderUsers(usersSection, data.users || [], baseUrl, trans)
      renderStudios(studiosSection, data.studios || [], baseUrl, trans)
      const docsFound = renderDocs(docsSection, docsData, baseUrl, theme, trans)
      const hits = new Map([
        [projectsSection, (data.projects || []).length > 0],
        [usersSection, (data.users || []).length > 0],
        [studiosSection, (data.studios || []).length > 0],
        [docsSection, docsFound],
      ])
      found = sections.slice(0, -1).map(({ section }) => hits.get(section))
      arrange()
      announce()

      wireShowMore(projectsSection, 'projects', query, baseUrl, theme, trans)
      wireShowMore(usersSection, 'users', query, baseUrl, theme, trans)
      wireShowMore(studiosSection, 'studios', query, baseUrl, theme, trans)
    })
    .catch((error) => {
      console.error('Search API error:', error)
      found = sections.slice(0, -1).map(() => false)
      failed = true
      arrange()
      announce()
    })

  search('scratch')
    .then((data) => {
      renderScratchProjects(
        scratchSection,
        data.scratch_projects || [],
        data.scratch_projects_total || 0,
        baseUrl,
        theme,
        trans,
      )
      scratchFound = !scratchSection.classList.contains('d-none')
      scratchCount = (data.scratch_projects || []).length
      // Before our results: stay hidden, or they would push the Scratch cards down when they arrive.
      // After them: renderScratchProjects already showed the section in its slot; arranging again would re-insert
      // (and unfocus) every section, so only do it to replace the "no results" line.
      if (!found) scratchSection.classList.add('d-none')
      else if (!found.some(Boolean)) arrange()
      announce()
      wireShowMore(scratchSection, 'scratch', query, baseUrl, theme, trans)
    })
    .catch((error) => console.error('Scratch search error:', error))
}

// Sections with results first, in their fixed order; the empty ones collapse into one line at the bottom.
function arrangeSections(container, sections, hasResults, query, trans, failed = false) {
  container.querySelector('#search-empty-summary')?.remove()
  const empties = []
  sections.forEach(({ section, empty }, i) => {
    section.classList.toggle('d-none', !hasResults[i])
    container.appendChild(section)
    if (!hasResults[i] && empty) empties.push(empty)
  })

  const summary = document.createElement('p')
  summary.id = 'search-empty-summary'
  summary.className = 'search-section__empty'
  summary.textContent = failed
    ? trans.error
    : query && !hasResults.some(Boolean)
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

// Docs results in groups (guides, bricks, formula elements), each a link with its snippet. True when there are any.
function renderDocs(section, data, baseUrl, theme, trans, docs) {
  const items = section.querySelector('.search-section__items')
  items.replaceChildren()
  let found = false
  for (const [group, title] of Object.entries(trans.docsGroups)) {
    const results = data?.[group] || []
    if (results.length === 0) continue
    found = true
    const heading = document.createElement('h3')
    heading.className = 'search-docs__group'
    heading.textContent = title
    const list = document.createElement('ul')
    list.className = 'search-docs__list'
    list.id = `search-docs-${group}`
    for (const result of results) {
      const link = document.createElement('a')
      link.className = 'search-docs__link'
      link.href = `${baseUrl}/${theme}/docs${result.path === 'index' ? '' : `/${result.path}`}`
      const label = document.createElement('span')
      label.dir = 'auto'
      label.textContent = result.title
      link.append(label)
      if (docs?.only) {
        if (docs.explicitLocale) link.href += `?locale=${encodeURIComponent(docs.explicitLocale)}`
        const sectionLabel = document.createElement('span')
        sectionLabel.className = 'search-docs__section'
        sectionLabel.dir = 'auto'
        sectionLabel.textContent =
          (group === 'guides' && docs.sections[result.path.split('/')[0]]) || title
        link.append(sectionLabel)
      }
      const snippet = document.createElement('p')
      snippet.className = 'search-docs__snippet'
      snippet.textContent = result.snippet
      const item = document.createElement('li')
      item.lang = result.locale.replace('_', '-')
      item.append(link, snippet)
      list.append(item)
    }
    items.append(heading, list)
  }
  return found
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

  // Converting runs on a background worker: the import answers 202 with a job, which is polled until it is done.
  fetch(`${baseUrl}/api/projects/scratch/${scratchId}`, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { Accept: 'application/json', Prefer: 'respond-async' },
  })
    .then(readJson)
    .then(({ response, body }) =>
      response.status === 202 ? waitForJob(baseUrl, body) : { response, body },
    )
    .then(({ response, body }) => {
      const projectId = body?.project_id ?? body?.id
      if (!response.ok || !projectId) {
        throw new Error(body?.error_message || body?.error?.message || `HTTP ${response.status}`)
      }
      window.location.href = `${baseUrl}/${theme}/project/${encodeURIComponent(projectId)}`
    })
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

function readJson(response) {
  return response
    .json()
    .catch(() => ({}))
    .then((body) => ({ response, body }))
}

const JOB_POLL_MS = 2000
const JOB_TIMEOUT_MS = 10 * 60 * 1000

// Resolves with the finished job; a failed job comes back as a non-ok response carrying its message.
function waitForJob(baseUrl, job, startedAt = Date.now()) {
  if (job?.state === 'done') return { response: { ok: true, status: 200 }, body: job }
  if (job?.state === 'failed')
    return { response: { ok: false, status: job.error_status || 500 }, body: job }
  if (!job?.id || Date.now() - startedAt > JOB_TIMEOUT_MS) {
    return { response: { ok: false, status: 504 }, body: {} }
  }

  return new Promise((resolve) => setTimeout(resolve, JOB_POLL_MS))
    .then(() =>
      fetch(`${baseUrl}/api/jobs/${encodeURIComponent(job.id)}`, {
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
      }),
    )
    .then(readJson)
    .then(({ response, body }) =>
      response.ok ? waitForJob(baseUrl, body, startedAt) : { response, body },
    )
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
