import '../Components/TabBar'
import '../Components/FullscreenListModal'
import '../Components/Switch'
import '../Components/TextArea'
import '../Components/TextField'
import AcceptLanguage from '../Api/AcceptLanguage'
import { escapeHtml } from '../Components/HtmlEscape'
import { updatePictureSources } from '../Layout/ImageVariants'
import { SnackbarDuration, showSnackbar } from '../Layout/Snackbar'
import { compressImageIfNeeded, exceedsMaxSize, isAllowedImageType } from './ImageCompressor'

import '../Project/ProjectList.css'
import './AdminSettings.css'
import './MembersList.css'
import './ActivityList.css'
import './Studio.css'

document.getElementById('studio-detail-tabs')?.addEventListener('change', (event) => {
  const tabs = Array.from(event.currentTarget.querySelectorAll('md-primary-tab'))
  const activeTab = tabs[event.currentTarget.activeTabIndex]
  const activePane = activeTab && document.getElementById(activeTab.getAttribute('aria-controls'))

  if (!activePane) return

  document.querySelector('.show.active')?.classList.remove('show', 'active')
  activePane.classList.add('show', 'active')
})

document.getElementById('std-header-form')?.addEventListener('change', (event) => {
  event.preventDefault()
  const fileInput = document.getElementById('std-header')
  const studioId = document.getElementById('studio-id').value
  const url = `${document.getElementById('js-api-routing').dataset.baseUrl}/api/studios/${studioId}`
  if (fileInput.files.length > 0) {
    uploadCoverImage(url, fileInput.files[0])
  }
})

async function uploadCoverImage(url, file) {
  const header = document.getElementById('studio-header')

  if (!isAllowedImageType(file)) {
    showSnackbar(
      '#share-snackbar',
      header?.dataset.transInvalidType || 'Invalid image type. Please use JPEG, PNG, or GIF.',
      SnackbarDuration.error,
    )
    return
  }

  let uploadFile = file

  if (exceedsMaxSize(file)) {
    try {
      const result = await compressImageIfNeeded(file)
      uploadFile = result.file

      if (result.wasCompressed) {
        showSnackbar(
          '#share-snackbar',
          header?.dataset.transImageCompressed ||
            'Image was automatically compressed to fit the 1 MB limit.',
          SnackbarDuration.short,
        )
      }

      if (exceedsMaxSize(uploadFile)) {
        showSnackbar(
          '#share-snackbar',
          header?.dataset.transFileTooLarge ||
            'Image is too large even after compression. Please choose a smaller file.',
          SnackbarDuration.error,
        )
        return
      }
    } catch {
      showSnackbar(
        '#share-snackbar',
        header?.dataset.transCompressionFailed || 'Failed to process image.',
        SnackbarDuration.error,
      )
      return
    }
  }

  const formData = new FormData()
  formData.append('image_file', uploadFile)

  const response = await fetch(url, {
    method: 'PATCH',
    credentials: 'same-origin',
    body: formData,
    headers: {
      Accept: 'application/json',
      'Accept-Language': new AcceptLanguage().get(),
    },
  })

  if (response.status === 200) {
    response.json().then((data) => {
      const uploadedImg = document.querySelector('#studio-img-container img')
      if (uploadedImg) {
        updatePictureSources(
          uploadedImg,
          data.cover,
          'detail',
          '/images/default/thumbnail-card@1x.webp',
        )
      }
    })
  }

  if (response.status === 422) {
    response.text().then((text) => {
      showSnackbar('#share-snackbar', text, SnackbarDuration.error)
    })
  }
}

// Report studio button
const reportStudioBtn = document.getElementById('top-app-bar__btn-report-studio')
if (reportStudioBtn) {
  import('../Moderation/ReportDialog').then(({ showReportDialog }) => {
    reportStudioBtn.addEventListener('click', () => {
      showReportDialog({
        contentType: reportStudioBtn.dataset.contentType,
        contentId: reportStudioBtn.dataset.contentId,
        apiUrl: reportStudioBtn.dataset.reportUrl,
        loginUrl: reportStudioBtn.dataset.loginUrl,
        isLoggedIn: reportStudioBtn.dataset.loggedIn === 'true',
        translations: {
          title: reportStudioBtn.dataset.transReportTitle,
          submit: reportStudioBtn.dataset.transReportSubmit,
          cancel: reportStudioBtn.dataset.transReportCancel,
          success: reportStudioBtn.dataset.transReportSuccess,
          error: reportStudioBtn.dataset.transReportError,
          duplicate: reportStudioBtn.dataset.transReportDuplicate,
          trustTooLow: reportStudioBtn.dataset.transReportTrustTooLow,
          rateLimited: reportStudioBtn.dataset.transReportRateLimited,
          notePlaceholder: reportStudioBtn.dataset.transReportPlaceholder,
          unverified: reportStudioBtn.dataset.transReportUnverified,
          suspended: reportStudioBtn.dataset.transReportSuspended,
        },
      })
    })
  })
}

// Admin settings form submission via API
document.getElementById('studio-settings__submit-button')?.addEventListener('click', () => {
  submitStudioSettings()
})

async function submitStudioSettings() {
  const modal = document.getElementById('studio-admin-settings-modal')
  if (!modal) return

  const studioId = modal.dataset.studioId
  const baseUrl = document.getElementById('js-api-routing').dataset.baseUrl
  const url = `${baseUrl}/api/studios/${studioId}`

  const nameInput = document.querySelector('#studio-settings__studio-name')
  const descTextarea = document.querySelector('#studio-settings__studio-description')
  // Switch.js keeps the value in a hidden input that is a SIBLING of the
  // md-switch host, id {switch-id}-native.
  const commentsSwitch = document.getElementById('studio-setting__switch-enable-comments-native')
  const publicSwitch = document.getElementById('studio-setting__switch-studio-privacy-native')

  const formData = new FormData()
  if (nameInput) formData.append('name', nameInput.value)
  if (descTextarea) formData.append('description', descTextarea.value)
  if (commentsSwitch)
    formData.append(
      'enable_comments',
      commentsSwitch.value === 'true' || commentsSwitch.value === '1' ? 'true' : 'false',
    )
  if (publicSwitch)
    formData.append(
      'is_public',
      publicSwitch.value === 'true' || publicSwitch.value === '1' ? 'true' : 'false',
    )

  try {
    const response = await fetch(url, {
      method: 'PATCH',
      credentials: 'same-origin',
      body: formData,
      headers: {
        Accept: 'application/json',
        'Accept-Language': new AcceptLanguage().get(),
      },
    })

    if (response.ok) {
      showSnackbar(
        '#share-snackbar',
        modal.dataset.transSaveSuccess || 'Settings saved.',
        SnackbarDuration.short,
      )
      window.location.reload()
    } else if (response.status === 422) {
      let errorMessage = modal.dataset.transSaveError || 'Failed to save settings.'
      try {
        const json = await response.json()
        const msg = json?.error?.message || json.error
        if (msg) errorMessage = msg
      } catch {
        // Use default error message
      }
      showSnackbar('#share-snackbar', errorMessage, SnackbarDuration.error)
    } else {
      showSnackbar(
        '#share-snackbar',
        modal.dataset.transSaveError || 'Failed to save settings.',
        SnackbarDuration.error,
      )
    }
  } catch (error) {
    console.error('Studio settings save failed:', error)
    showSnackbar(
      '#share-snackbar',
      modal.dataset.transSaveError || 'Failed to save settings.',
      SnackbarDuration.error,
    )
  }
}

document.addEventListener('DOMContentLoaded', async () => {
  const header = document.getElementById('studio-header')
  if (!header) return

  const studioId = header.dataset.studioId
  const baseUrl = header.dataset.baseUrl

  try {
    const response = await fetch(`${baseUrl}/api/studios/${studioId}`, {
      credentials: 'same-origin',
      headers: { Accept: 'application/json' },
    })
    if (!response.ok) {
      clearStudioHeaderLoadingState(header)
      return
    }
    const studio = await response.json()
    renderHeader(header, studio)
    initDescriptionToggle()

    // Pass user role to the project-list Stimulus controller
    const projectPane = document.getElementById('projects-pane')
    if (projectPane && studio.user_role) {
      projectPane.setAttribute('data-studio--project-list-user-role-value', studio.user_role)
    }
  } catch (e) {
    console.error('Failed to load studio header:', e)
    clearStudioHeaderLoadingState(header)
  }
})

function renderHeader(header, studio) {
  const baseUrl = header.dataset.baseUrl
  const studioId = header.dataset.studioId
  const isLoggedIn = header.dataset.isLoggedIn === 'true'
  const defaultCover = header.dataset.defaultCover

  // Cover image
  const coverImg = header.querySelector('#studio-img-container img')
  if (coverImg) {
    updatePictureSources(coverImg, studio.cover, 'detail', defaultCover)
  }

  // Studio name
  const nameEl = header.querySelector('.studio-detail__header__name')
  if (nameEl) {
    nameEl.textContent = studio.name
  }

  // Public/private label
  const visibilityEl = document.getElementById('header-visibility')
  if (visibilityEl) {
    visibilityEl.textContent = studio.is_public
      ? header.dataset.transPublic
      : header.dataset.transPrivate
  }

  // Admin-only elements
  const isAdmin = studio.user_role === 'admin'
  const isMember = studio.is_member === true

  // Show/hide admin-only top bar elements (settings button, edit option)
  if (isAdmin) {
    document.querySelectorAll('.js-studio-admin-only').forEach((el) => {
      el.style.display = ''
    })
    document.querySelectorAll('.js-studio-non-admin-only').forEach((el) => {
      el.style.display = 'none'
    })
  }

  // Show member-only elements (add project FAB) for admins and members
  if (isAdmin || isMember) {
    document.querySelectorAll('.js-studio-member-only').forEach((el) => {
      el.style.display = ''
    })
  }

  // Cover upload form + hint (admin only)
  const uploadForm = document.getElementById('std-header-form')
  if (uploadForm && isAdmin) {
    uploadForm.style.display = ''
  }
  const coverHint = header.querySelector('.studio-cover-hint-wrap')
  if (coverHint && isAdmin) {
    coverHint.style.display = ''
    const tooltip = coverHint.querySelector('.studio-cover-hint-tooltip')
    if (tooltip) {
      tooltip.textContent = header.dataset.transCoverHint
    }
  }

  // Members section
  renderMembersSection(header, studio, baseUrl, studioId, isLoggedIn)

  // Activities section
  renderActivitiesSection(header, studio, baseUrl, studioId, isAdmin)

  // Join requests section (admin + private only)
  if (isAdmin && !studio.is_public) {
    renderJoinRequestsSection(header, studio, baseUrl, studioId)
  }

  // Action button (join/leave/pending/declined)
  renderActionButton(header, studio, baseUrl, studioId, isLoggedIn, isAdmin, isMember)

  // Tab counts
  const projectsCount = document.getElementById('projects-count')
  if (projectsCount) {
    projectsCount.textContent = `(${studio.projects_count ?? 0})`
  }
  const commentsCount = document.getElementById('comments-count')
  if (commentsCount) {
    commentsCount.textContent = studio.comments_count ?? 0
  }

  clearStudioHeaderLoadingState(header)
}

function initDescriptionToggle() {
  const desc = document.getElementById('studio-desc')
  const toggleBtn = document.getElementById('studio-desc-toggle')
  if (!desc || !toggleBtn) return

  // Only show toggle if text is actually clamped (overflowing)
  requestAnimationFrame(() => {
    if (desc.scrollHeight > desc.clientHeight + 1) {
      toggleBtn.classList.remove('d-none')
    }
  })

  let expanded = false
  toggleBtn.addEventListener('click', () => {
    expanded = !expanded
    desc.classList.toggle('studio-desc--clamped', !expanded)
    toggleBtn.textContent = expanded
      ? toggleBtn.dataset.transShowLess || 'Show less'
      : toggleBtn.dataset.transShowMore || 'Show more'
  })
}

function clearStudioHeaderLoadingState(header) {
  header.classList.remove('studio-detail__header--loading')
  header.querySelectorAll('.js-studio-header-skeleton').forEach((element) => {
    element.remove()
  })
}

function renderMembersSection(header, studio, baseUrl, studioId, isLoggedIn) {
  const container = document.getElementById('header-members')
  if (!container) return

  if (isLoggedIn && studio.user_role) {
    const membersUrl = `${baseUrl}/api/studios/${studioId}/members`
    const isAdmin = studio.user_role === 'admin'
    const modalId = 'studioDetailMembersListModal'
    const listId = 'studioDetailMembersList'

    container.innerHTML = `
      <div data-controller="studio--member-list"
           data-studio--member-list-members-url-value="${escapeHtml(membersUrl)}"
           data-studio--member-list-studio-id-value="${escapeHtml(studioId)}"
           data-studio--member-list-list-element-id-value="${listId}"
           data-is-studio-admin="${isAdmin ? 'true' : 'false'}"
           data-trans-promote-member="${escapeHtml(header.dataset.transPromoteMember)}"
           data-trans-ban-member="${escapeHtml(header.dataset.transBanMember)}"
           data-trans-promotion-failed="${escapeHtml(header.dataset.transPromotionFailed)}"
           data-trans-ban-failed="${escapeHtml(header.dataset.transBanFailed)}"
           data-trans-demote-member="${escapeHtml(header.dataset.transDemoteMember)}"
           data-trans-demotion-failed="${escapeHtml(header.dataset.transDemotionFailed)}"
           data-trans-no-studio-projects="${escapeHtml(header.dataset.transNoStudioProjects)}"
           data-trans-one-studio-project="${escapeHtml(header.dataset.transOneStudioProject)}"
           data-trans-n-studio-projects="${escapeHtml(header.dataset.transNStudioProjects)}"
      >
        <a type="button"
           class="studio-detail__header__details__button studio-detail__header__details__button--member"
           data-bs-toggle="modal"
           data-bs-target="#${modalId}"
           data-action="click->studio--member-list#loadMembers"
        >
          <span class="material-icons">person</span><span class="ms-2 member_count">${studio.members_count ?? 0}</span>
        </a>
        ${createModalHtml(modalId, header.dataset.transMembersTitle, listId)}
      </div>
    `
  } else {
    container.innerHTML = `
      <div class="studio-detail__header__details__info">
        <span class="material-icons">person</span>
        <span class="ms-2 member_count">${studio.members_count ?? 0}</span>
      </div>
    `
  }
}

function renderActivitiesSection(header, studio, baseUrl, studioId, isAdmin) {
  const container = document.getElementById('header-activities')
  if (!container) return

  if (isAdmin) {
    const activitiesUrl = `${baseUrl}/api/studios/${studioId}/activities`
    const modalId = 'studioDetailActivityListModal'
    const listId = 'studioDetailActivityList'

    container.style.display = ''
    container.innerHTML = `
      <div data-controller="studio--activity-list"
           data-studio--activity-list-activities-url-value="${escapeHtml(activitiesUrl)}"
           data-studio--activity-list-studio-id-value="${escapeHtml(studioId)}"
           data-studio--activity-list-list-element-id-value="${listId}"
           data-trans-join-studio="${escapeHtml(header.dataset.transJoinStudio)}"
           data-trans-add-project="${escapeHtml(header.dataset.transAddProject)}"
           data-trans-add-comment="${escapeHtml(header.dataset.transAddComment)}"
      >
        <a type="button"
           class="studio-detail__header__details__button studio-detail__header__details__button--activity"
           data-bs-toggle="modal"
           data-bs-target="#${modalId}"
           data-action="click->studio--activity-list#loadActivities"
        >
          <span class="material-icons">schedule</span>
          <span id="activity_count" class="ms-2 activity_count">${studio.activities_count ?? 0}</span>
        </a>
        ${createModalHtml(modalId, header.dataset.transActivityTitle, listId)}
      </div>
    `
  } else {
    container.style.display = ''
    container.innerHTML = `
      <div class="studio-detail__header__details__info">
        <span class="material-icons">schedule</span>
        <span id="activity_count" class="ms-2 activity_count">${studio.activities_count ?? 0}</span>
      </div>
    `
  }
}

function renderJoinRequestsSection(header, studio, baseUrl, studioId) {
  const container = document.getElementById('header-join-requests')
  if (!container) return

  const joinRequestsUrl = `${baseUrl}/api/studios/${studioId}/join-requests`
  const modalId = 'studioDetailJoinRequestsModal'
  const listId = 'studioDetailJoinRequestsList'
  const count = studio.pending_join_requests_count ?? 0

  container.style.display = ''
  container.innerHTML = `
    <div data-controller="studio--join-requests"
         data-studio--join-requests-join-requests-url-value="${escapeHtml(joinRequestsUrl)}"
         data-studio--join-requests-studio-id-value="${escapeHtml(studioId)}"
         data-studio--join-requests-list-element-id-value="${listId}"
         data-trans-accept="${escapeHtml(header.dataset.transAccept)}"
         data-trans-decline="${escapeHtml(header.dataset.transDecline)}"
         data-trans-accept-success="${escapeHtml(header.dataset.transAcceptSuccess)}"
         data-trans-decline-success="${escapeHtml(header.dataset.transDeclineSuccess)}"
         data-trans-error="${escapeHtml(header.dataset.transJoinRequestsError)}"
         data-trans-no-requests="${escapeHtml(header.dataset.transNoRequests)}"
    >
      <a type="button"
         class="studio-detail__header__details__button studio-detail__header__details__button--join-requests"
         data-bs-toggle="modal"
         data-bs-target="#${modalId}"
         data-action="click->studio--join-requests#loadJoinRequests"
      >
        <span class="material-icons">person_add</span>
        <span id="join-requests-badge"
              class="badge bg-danger ms-1 rounded-pill"
              ${count === 0 ? 'style="display: none"' : ''}>
          ${count}
        </span>
      </a>
      ${createModalHtml(modalId, header.dataset.transJoinRequestsTitle, listId)}
    </div>
  `
}

function renderActionButton(header, studio, baseUrl, studioId, isLoggedIn, isAdmin, isMember) {
  const container = document.getElementById('header-action-button')
  if (!container) return

  if (!isLoggedIn) {
    container.innerHTML = `
      <a href="${escapeHtml(header.dataset.loginUrl)}"
         class="studio-detail__header__details__join-button btn btn-outline-primary btn-block">
        ${escapeHtml(header.dataset.transJoin)}
      </a>
    `
    return
  }

  if (isAdmin) {
    return
  }

  if (isMember) {
    container.innerHTML = `
      <button class="studio-detail__header__details__leave-button btn btn-outline-primary btn-block ajaxRequestJoinLeaveReport ajaxRequestLeave"
              data-url="${baseUrl}/api/studios/${escapeHtml(studioId)}/leave"
              data-method="DELETE">
        ${escapeHtml(header.dataset.transLeave)}
      </button>
    `
  } else if (studio.join_request_status === 'pending') {
    container.innerHTML = `
      <button class="studio-detail__header__details__pending-button btn btn-outline-primary btn-block requestPending" disabled>
        ${escapeHtml(header.dataset.transPending)}
      </button>
    `
  } else if (studio.join_request_status === 'declined') {
    container.innerHTML = `
      <button class="studio-detail__header__details__declined-button btn btn-outline-danger btn-block requestDecline" disabled>
        ${escapeHtml(header.dataset.transDeclined)}
      </button>
    `
  } else {
    container.innerHTML = `
      <button class="studio-detail__header__details__join-button btn btn-primary btn-block ajaxRequestJoinLeaveReport ajaxRequestJoin"
              data-url="${baseUrl}/api/studios/${escapeHtml(studioId)}/join"
              data-method="POST">
        ${escapeHtml(header.dataset.transJoin)}
      </button>
    `
  }

  // Bind click handlers for join/leave buttons
  container.querySelectorAll('.ajaxRequestJoinLeaveReport').forEach((el) => {
    el.addEventListener('click', (event) => {
      event.preventDefault()
      const url = el.getAttribute('data-url')
      const method = el.getAttribute('data-method') || 'POST'
      makeAjaxRequest(url, method)
    })
  })
}

function createModalHtml(modalId, title, listId) {
  return `
    <div class="modal fade modal-full" id="${modalId}" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog full-modal-dialog" role="document">
        <div class="modal-content full-modal-content">
          <div class="modal-header modal-header-full mdc-top-app-bar__row">
            <section class="mdc-top-app-bar__section mdc-top-app-bar__section--align-start">
              <button data-bs-dismiss="modal"
                      class="material-icons mdc-top-app-bar__action-item mdc-icon-button"
                      aria-label="Back to top bar">
                arrow_back
              </button>
              <span class="mdc-top-app-bar__title">${escapeHtml(title)}</span>
            </section>
          </div>
          <div class="container">
            <ul id="${listId}"></ul>
          </div>
        </div>
      </div>
    </div>
  `
}

function makeAjaxRequest(url, method) {
  fetch(url, {
    method: method,
    credentials: 'same-origin',
  })
    .then((response) => {
      if (!response.ok) {
        showSnackbar(
          '#share-snackbar',
          'Oops, that did not work. Please try again!',
          SnackbarDuration.error,
        )
        console.error('Studio request failed:', response.status)
      } else {
        window.location.reload()
      }
    })
    .catch((error) => {
      console.error('There was an error with the fetch operation:', error)
    })
}

// Invite codes (admin settings modal). The API enforces admin rights; the modal only loads them.
const invitesSection = document.getElementById('studio-invites')
if (invitesSection) {
  document
    .getElementById('studio-admin-settings-modal')
    ?.addEventListener('show.bs.modal', () => loadInvites())
  document.getElementById('studio-invite-create')?.addEventListener('click', () => createInvite())
}

function invitesUrl() {
  const studioId = document.getElementById('studio-admin-settings-modal').dataset.studioId
  return `${document.getElementById('js-api-routing').dataset.baseUrl}/api/studios/${studioId}/invites`
}

function inviteError() {
  showSnackbar('#share-snackbar', invitesSection.dataset.transError, SnackbarDuration.error)
}

async function loadInvites() {
  const response = await fetch(invitesUrl(), {
    credentials: 'same-origin',
    headers: { Accept: 'application/json' },
  })
  if (!response.ok) {
    inviteError()
    return
  }
  const { data } = await response.json()
  const list = document.getElementById('studio-invite-list')
  list.replaceChildren(...data.map(renderInvite))
  if (data.length === 0) {
    const empty = document.createElement('li')
    empty.className = 'text-muted small studio-invite__empty'
    empty.textContent = invitesSection.dataset.transNone
    list.appendChild(empty)
  }
}

async function createInvite() {
  const expires = document.getElementById('studio-invite-expires').value
  const maxUses = document.getElementById('studio-invite-max-uses').value
  const body = {}
  // The chosen day still counts: the code works until its end, in the admin's time zone.
  if (expires) body.expires_at = new Date(`${expires}T23:59:59`).toISOString()
  if (maxUses) body.max_uses = Number.parseInt(maxUses, 10)

  const response = await fetch(invitesUrl(), {
    method: 'POST',
    credentials: 'same-origin',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  })
  if (response.status !== 201) {
    inviteError()
    return
  }
  await loadInvites()
}

function renderInvite(invite) {
  const ds = invitesSection.dataset
  const li = document.createElement('li')
  li.className = 'studio-invite border rounded p-2 mb-2'
  li.id = `studio-invite-${invite.id}`

  const code = document.createElement('div')
  code.className = 'studio-invite__code fs-4 font-monospace'
  code.textContent = invite.code

  const link = document.createElement('div')
  link.className = 'studio-invite__url small text-break'
  link.textContent = invite.url

  const meta = document.createElement('div')
  meta.className = 'small text-muted'
  const uses =
    invite.max_uses === null || invite.max_uses === undefined
      ? ds.transUses.replace('%uses%', invite.uses)
      : ds.transUsesMax.replace('%uses%', invite.uses).replace('%max%', invite.max_uses)
  meta.textContent = invite.expires_at
    ? `${uses} · ${ds.transExpires.replace('%date%', new Date(invite.expires_at).toLocaleDateString())}`
    : uses

  const copy = document.createElement('button')
  copy.type = 'button'
  copy.className = 'btn btn-sm btn-outline-primary me-2 studio-invite__copy'
  copy.textContent = ds.transCopy
  copy.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(invite.url)
      showSnackbar('#share-snackbar', ds.transCopied, SnackbarDuration.short)
    } catch {
      inviteError()
    }
  })

  const revoke = document.createElement('button')
  revoke.type = 'button'
  revoke.className = 'btn btn-sm btn-outline-danger studio-invite__revoke'
  revoke.textContent = ds.transRevoke
  revoke.addEventListener('click', async () => {
    const response = await fetch(`${invitesUrl()}/${invite.id}`, {
      method: 'DELETE',
      credentials: 'same-origin',
    })
    if (response.status !== 204) {
      inviteError()
      return
    }
    await loadInvites()
  })

  const actions = document.createElement('div')
  actions.className = 'mt-2'
  actions.append(copy, revoke)
  li.append(code, link, meta, actions)
  return li
}
