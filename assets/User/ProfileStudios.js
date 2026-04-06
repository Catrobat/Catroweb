import { escapeHtml, escapeAttr } from '../Components/HtmlEscape'
import './ProfileStudios.scss'

document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('.js-profile-studios')
  if (!container) return

  const userId = container.dataset.userId
  const baseUrl = container.dataset.baseUrl || ''
  const studioDetailsPath = container.dataset.studioDetailsPath || ''
  const transNoStudios = container.dataset.transNoStudios || 'No studios yet'

  function renderStudioCard(studio) {
    const id = escapeAttr(studio.id || '')
    const name = escapeHtml(studio.name || '')
    const imagePath = studio.image_path || '/images/default/screenshot.png'
    const membersCount = parseInt(studio.members_count, 10) || 0
    const projectsCount = parseInt(studio.projects_count, 10) || 0
    const isPublic = studio.is_public !== false
    const detailUrl = studioDetailsPath.replace('__ID__', id)

    return (
      '<div class="studios-list-item-wrapper" data-studio-id="' +
      id +
      '">' +
      '<a href="' +
      escapeAttr(detailUrl) +
      '" class="studios-list-item-link">' +
      '<div class="studios-list-item">' +
      '<img src="' +
      escapeAttr(imagePath) +
      '" class="img-fluid studios-list-item--image" alt="">' +
      '<div class="studios-list-item--content">' +
      '<div class="studios-list-item--heading">' +
      '<h3>' +
      name +
      '</h3>' +
      (!isPublic
        ? '<div class="studios-list-item--badge"><span class="material-icons">lock</span></div>'
        : '') +
      '</div>' +
      '<div class="studios-list-item--icons">' +
      '<div class="studios-list-item--icon-wrapper">' +
      '<span class="material-icons">person</span>' +
      '<span class="studios-list-item--icons-text ms-2">' +
      membersCount +
      '</span>' +
      '</div>' +
      '<div class="studios-list-item--icon-wrapper">' +
      '<span class="material-icons">app_shortcut</span>' +
      '<span class="studios-list-item--icon-text ms-2">' +
      projectsCount +
      '</span>' +
      '</div>' +
      '</div>' +
      '</div>' +
      '</div>' +
      '</a>' +
      '</div>'
    )
  }

  function renderList(cardsContainer, emptyContainer, studios) {
    cardsContainer.innerHTML = ''
    if (studios.length === 0) {
      emptyContainer.classList.remove('d-none')
      emptyContainer.classList.add('d-block')
    } else {
      emptyContainer.classList.remove('d-block')
      emptyContainer.classList.add('d-none')
      studios.forEach((studio) => {
        cardsContainer.insertAdjacentHTML('beforeend', renderStudioCard(studio))
      })
    }
  }

  function loadStudios() {
    const studioCards = document.getElementById('studio-cards')
    const noStudios = document.getElementById('no-studios')

    if (!studioCards) return

    const studiosUrl = baseUrl + '/api/user/' + userId + '/studios'

    fetch(studiosUrl, { credentials: 'same-origin' })
      .then((r) => {
        if (!r.ok) throw new Error('HTTP ' + r.status)
        return r.json()
      })
      .then((data) => {
        const studios = data.data || []
        const total = data.total ?? studios.length

        const studiosCount = document.getElementById('studios-count')
        if (studiosCount) studiosCount.textContent = total

        renderList(studioCards, noStudios, studios)
      })
      .catch((error) => {
        console.error('Failed to load user studios:', error)
        const studiosCount = document.getElementById('studios-count')
        if (studiosCount) studiosCount.textContent = '0'

        if (noStudios) {
          noStudios.textContent = transNoStudios
          noStudios.classList.remove('d-none')
          noStudios.classList.add('d-block')
        }
        if (studioCards) studioCards.innerHTML = ''
      })
  }

  loadStudios()
})
