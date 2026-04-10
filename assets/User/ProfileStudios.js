import { escapeAttr, escapeHtml } from '../Components/HtmlEscape'
import { shareOrCopy } from '../Components/ClipboardHelper'
import './ProfileStudios.scss'

document.addEventListener('DOMContentLoaded', () => {
  const container = document.querySelector('.js-profile-studios')
  if (!container) return

  const userId = container.dataset.userId
  const baseUrl = container.dataset.baseUrl || ''
  const studioDetailsPath = container.dataset.studioDetailsPath || ''
  const transNoStudios = container.dataset.transNoStudios || 'No studios yet'
  const isLoggedIn = container.dataset.isLoggedIn === 'true'

  const trans = {
    open: container.dataset.transOpen || 'Open',
    share: container.dataset.transShare || 'Share',
    leave: container.dataset.transLeave || 'Leave',
    shareSuccess: container.dataset.transShareSuccess || 'Link copied!',
    leaveConfirmTitle: container.dataset.transLeaveConfirmTitle || 'Leave studio?',
    leaveConfirm: container.dataset.transLeaveConfirm || 'Leave',
    cancel: container.dataset.transCancel || 'Cancel',
    roleAdmin: container.dataset.transRoleAdmin || 'Admin',
    roleMember: container.dataset.transRoleMember || 'Member',
    pending: container.dataset.transPending || 'Pending',
    cancelRequest: container.dataset.transCancelRequest || 'Cancel Request',
    cancelRequestConfirmTitle:
      container.dataset.transCancelRequestConfirmTitle || 'Cancel join request?',
  }

  function truncate(text, maxLength) {
    if (text.length <= maxLength) return text
    return text.substring(0, maxLength).trimEnd() + '…'
  }

  function renderStudioCard(studio) {
    const id = escapeAttr(studio.id || '')
    const name = escapeHtml(studio.name || '')
    const description = escapeHtml(truncate(studio.description || '', 100))
    const imagePath = studio.image_path || '/images/default/screenshot.png'
    const membersCount = parseInt(studio.members_count, 10) || 0
    const projectsCount = parseInt(studio.projects_count, 10) || 0
    const isPublic = studio.is_public !== false
    const isMember = studio.is_member === true
    const detailUrl = studioDetailsPath.replace('__ID__', id)
    const userRole = studio.user_role || null
    const joinRequestStatus = studio.join_request_status || null

    // Pills
    let pills = ''
    if (userRole === 'admin') {
      pills +=
        '<span class="studios-list-item--pill studios-list-item--pill-admin">' +
        escapeHtml(trans.roleAdmin) +
        '</span>'
    }
    if (joinRequestStatus === 'pending') {
      pills +=
        '<span class="studios-list-item--pill studios-list-item--pill-pending">' +
        escapeHtml(trans.pending) +
        '</span>'
    }

    // Action area (dropdown menu)
    let menuItems =
      '<a href="' +
      escapeAttr(detailUrl) +
      '" class="projects-list-item--dropdown-item">' +
      '<i class="material-icons">open_in_new</i>' +
      escapeHtml(trans.open) +
      '</a>' +
      '<button class="projects-list-item--dropdown-item" data-action="share" data-studio-id="' +
      id +
      '" data-studio-url="' +
      escapeAttr(detailUrl) +
      '">' +
      '<i class="material-icons">share</i>' +
      escapeHtml(trans.share) +
      '</button>'

    if (isLoggedIn && userRole && userRole !== 'admin') {
      menuItems +=
        '<div class="projects-list-item--dropdown-divider"></div>' +
        '<button class="projects-list-item--dropdown-item text-danger" data-action="leave" data-studio-id="' +
        id +
        '">' +
        '<i class="material-icons">logout</i>' +
        escapeHtml(trans.leave) +
        '</button>'
    }

    if (isLoggedIn && joinRequestStatus === 'pending' && !isMember) {
      menuItems +=
        '<div class="projects-list-item--dropdown-divider"></div>' +
        '<button class="projects-list-item--dropdown-item text-danger" data-action="cancel-request" data-studio-id="' +
        id +
        '">' +
        '<i class="material-icons">close</i>' +
        escapeHtml(trans.cancelRequest) +
        '</button>'
    }

    const actionArea =
      '<div class="projects-list-item--actions">' +
      '<button class="btn projects-list-item--menu-btn" data-studio-id="' +
      id +
      '">' +
      '<i class="material-icons">more_vert</i>' +
      '</button>' +
      '<div class="projects-list-item--dropdown" style="display:none;">' +
      menuItems +
      '</div>' +
      '</div>'

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
      pills +
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
      (description ? '<p class="text-muted small mb-0 mt-1">' + description + '</p>' : '') +
      '</div>' +
      '</div>' +
      '</a>' +
      actionArea +
      '</div>'
    )
  }

  function bindActions(cardsContainer) {
    document.addEventListener('click', () => {
      cardsContainer.querySelectorAll('.projects-list-item--dropdown').forEach((d) => {
        d.style.display = 'none'
      })
    })

    cardsContainer.addEventListener('click', async (e) => {
      const menuBtn = e.target.closest('.projects-list-item--menu-btn')
      if (menuBtn) {
        e.preventDefault()
        e.stopPropagation()
        const dropdown = menuBtn.nextElementSibling
        const isOpen = dropdown.style.display !== 'none'
        cardsContainer.querySelectorAll('.projects-list-item--dropdown').forEach((d) => {
          d.style.display = 'none'
        })
        dropdown.style.display = isOpen ? 'none' : 'block'
        return
      }

      const actionBtn = e.target.closest('[data-action]')
      if (!actionBtn) return

      e.preventDefault()
      e.stopPropagation()
      const dropdown = actionBtn.closest('.projects-list-item--dropdown')
      if (dropdown) dropdown.style.display = 'none'

      const action = actionBtn.dataset.action

      if (action === 'share') {
        const studioUrl = window.location.origin + actionBtn.dataset.studioUrl
        shareOrCopy(studioUrl, () => {
          import('../Layout/Snackbar').then(({ showSnackbar }) => {
            showSnackbar('#share-snackbar', trans.shareSuccess)
          })
        })
      }

      if (action === 'leave') {
        const studioId = actionBtn.dataset.studioId
        const { default: Swal } = await import('sweetalert2')
        const result = await Swal.fire({
          title: trans.leaveConfirmTitle,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: trans.leaveConfirm,
          cancelButtonText: trans.cancel,
          customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-outline-primary',
          },
          buttonsStyling: false,
        })
        if (result.isConfirmed) {
          try {
            const resp = await fetch(baseUrl + '/api/studio/' + studioId + '/leave', {
              method: 'DELETE',
              credentials: 'same-origin',
            })
            if (resp.ok) {
              const wrapper = cardsContainer.querySelector('[data-studio-id="' + studioId + '"]')
              if (wrapper) wrapper.remove()
              const studiosCount = document.getElementById('studios-count')
              if (studiosCount) {
                const current = parseInt(studiosCount.textContent, 10) || 0
                studiosCount.textContent = Math.max(0, current - 1)
              }
            }
          } catch (err) {
            console.error('Failed to leave studio:', err)
            const { showSnackbar } = await import('../Layout/Snackbar')
            showSnackbar('#share-snackbar', 'Failed to leave studio.', 3000)
          }
        }
      }

      if (action === 'cancel-request') {
        const studioId = actionBtn.dataset.studioId
        const { default: Swal } = await import('sweetalert2')
        const result = await Swal.fire({
          title: trans.cancelRequestConfirmTitle,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: trans.cancel,
          cancelButtonText: trans.cancel,
          customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-outline-primary',
          },
          buttonsStyling: false,
        })
        if (result.isConfirmed) {
          try {
            const resp = await fetch(baseUrl + '/api/studio/' + studioId + '/leave', {
              method: 'DELETE',
              credentials: 'same-origin',
            })
            if (resp.ok) {
              const wrapper = cardsContainer.querySelector('[data-studio-id="' + studioId + '"]')
              if (wrapper) wrapper.remove()
              const studiosCount = document.getElementById('studios-count')
              if (studiosCount) {
                const current = parseInt(studiosCount.textContent, 10) || 0
                studiosCount.textContent = Math.max(0, current - 1)
              }
            }
          } catch (err) {
            console.error('Failed to cancel request:', err)
            const { showSnackbar } = await import('../Layout/Snackbar')
            showSnackbar('#share-snackbar', 'Failed to cancel request.', 3000)
          }
        }
      }
    })
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
      bindActions(cardsContainer)
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
