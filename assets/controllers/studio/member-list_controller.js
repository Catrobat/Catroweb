import { Controller } from '@hotwired/stimulus'
import { showSnackbar, SnackbarDuration } from '../../Layout/Snackbar'
import { escapeHtml, escapeAttr } from '../../Components/HtmlEscape'
import { getImageUrl } from '../../Layout/ImageVariants'
import { MDCMenu } from '@material/menu'
import Swal from 'sweetalert2'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static values = {
    membersUrl: String,
    studioId: String,
    listElementId: String,
  }

  /**
   * Initializing the members list of a studio
   *
   * data-action="click->studio--member-list#loadMembers"
   *
   * @returns {Promise<void>}
   */
  async loadMembers() {
    const listElement = document.getElementById(this.listElementIdValue)
    listElement.innerHTML = ''

    try {
      const response = await fetch(this.membersUrlValue + '?limit=50', {
        credentials: 'same-origin',
      })
      if (!response.ok) {
        return
      }

      const data = await response.json()
      if (data.data && data.data.length > 0) {
        const isStudioAdmin = this.element.dataset.isStudioAdmin === 'true'
        data.data.forEach((member) => {
          listElement.appendChild(this._renderMember(member, isStudioAdmin))
        })
      }

      for (const el of listElement.querySelectorAll('.mdc-menu')) {
        const menu = new MDCMenu(el)
        menu.open = false
      }
    } catch (e) {
      console.error('Failed to load members:', e)
    }
  }

  _renderMember(member, isStudioAdmin) {
    const li = document.createElement('li')
    li.className = 'member__list-entry'

    const avatarUrl = getImageUrl(
      member.avatar,
      'thumb',
      '/images/default/avatar_default-thumb@1x.webp',
    )
    const profileUrl = '/app/user/' + escapeAttr(String(member.user_id))

    const adminIndicator =
      member.role === 'admin'
        ? `<div>
        <i class="material-icons member__list-entry__admin-indicator">admin_panel_settings</i>
        <a href="${profileUrl}">${escapeHtml(member.username)}</a>
      </div>`
        : `<a href="${profileUrl}">${escapeHtml(member.username)}</a>`

    const projectCount = member.studio_project_count || 0
    const transNoProjects = this.element.dataset.transNoStudioProjects || 'No studio projects'
    const transOneProject = this.element.dataset.transOneStudioProject || '1 studio project'
    const transNProjects = this.element.dataset.transNStudioProjects || '%count% studio projects'
    let projectCountText = transNoProjects
    if (projectCount === 1) {
      projectCountText = transOneProject
    } else if (projectCount > 1) {
      projectCountText = transNProjects.replace('%count%', String(projectCount))
    }

    let adminButtons = ''
    if (isStudioAdmin && member.role !== 'admin') {
      const transPromote = this.element.dataset.transPromoteMember || 'Promote'
      const transBan = this.element.dataset.transBanMember || 'Remove'
      const transPromoteFailed = this.element.dataset.transPromotionFailed || 'Promotion failed'
      const transBanFailed = this.element.dataset.transBanFailed || 'Ban failed'
      const promoteUrl =
        this.membersUrlValue + '/' + encodeURIComponent(member.user_id) + '/promote'
      const banUrl = this.membersUrlValue + '/' + encodeURIComponent(member.user_id) + '/ban'

      adminButtons = `
        <div class="member__list-entry__admin-buttons mdc-menu-surface--anchor">
          <button class="member__list-entry__admin-button btn material-icons"
                  data-action="click->studio--member-list#openAdminMenu"
                  role="button">more_vert</button>
          <div class="mdc-menu mdc-menu-surface mdc-menu-surface--fixed">
            <ul class="mdc-list" role="menu" aria-hidden="true">
              <li class="member__list-entry__admin-button__promote btn mdc-list-item mdc-ripple-upgraded" role="menuitem"
                  data-action="click->studio--member-list#promoteMemberToAdmin"
                  data-url="${escapeAttr(promoteUrl)}"
                  data-user-id="${escapeAttr(String(member.user_id))}"
                  data-error-message="${escapeAttr(transPromoteFailed)}">
                <span class="material-icons me-2">upgrade</span>
                <span class="member__list-entry__admin-buttons__text">${escapeHtml(transPromote)}</span>
              </li>
              <li class="member__list-entry__admin-button__ban btn mdc-list-item mdc-ripple-upgraded" role="menuitem"
                  data-action="click->studio--member-list#banUserFromStudio"
                  data-url="${escapeAttr(banUrl)}"
                  data-user-id="${escapeAttr(String(member.user_id))}"
                  data-error-message="${escapeAttr(transBanFailed)}">
                <span class="material-icons me-2 text-danger">delete</span>
                <span class="member__list-entry__admin-buttons__text">${escapeHtml(transBan)}</span>
              </li>
            </ul>
          </div>
        </div>`
    } else if (isStudioAdmin && member.role === 'admin') {
      const transDemote = this.element.dataset.transDemoteMember || 'Demote'
      const transDemoteFailed = this.element.dataset.transDemotionFailed || 'Demotion failed'
      const demoteUrl = this.membersUrlValue + '/' + encodeURIComponent(member.user_id) + '/demote'

      adminButtons = `
        <div class="member__list-entry__admin-buttons mdc-menu-surface--anchor">
          <button class="member__list-entry__admin-button btn material-icons"
                  data-action="click->studio--member-list#openAdminMenu"
                  role="button">more_vert</button>
          <div class="mdc-menu mdc-menu-surface mdc-menu-surface--fixed">
            <ul class="mdc-list" role="menu" aria-hidden="true">
              <li class="member__list-entry__admin-button__demote btn mdc-list-item mdc-ripple-upgraded" role="menuitem"
                  data-action="click->studio--member-list#demoteAdminToMember"
                  data-url="${escapeAttr(demoteUrl)}"
                  data-user-id="${escapeAttr(String(member.user_id))}"
                  data-error-message="${escapeAttr(transDemoteFailed)}">
                <span class="material-icons me-2 text-warning">arrow_downward</span>
                <span class="member__list-entry__admin-buttons__text">${escapeHtml(transDemote)}</span>
              </li>
            </ul>
          </div>
        </div>`
    }

    li.innerHTML = `
      <a href="${profileUrl}">
        <img class="member__list-entry__image"
             src="${escapeAttr(avatarUrl)}" alt="">
      </a>
      <div class="ps-3">
        ${adminIndicator}
        <div class="member__list-entry__project-count text-muted small">${escapeHtml(projectCountText)}</div>
      </div>
      ${adminButtons}
    `

    return li
  }

  /**
   * Opening the admin menu on a member card providing actions to modify the member
   *
   * data-action="click->studio--member-list#openAdminMenu"
   *
   * @param event
   */
  openAdminMenu(event) {
    const menu = new MDCMenu(
      event.currentTarget.parentElement.getElementsByClassName('mdc-menu')[0],
    )
    menu.open = true
  }

  /**
   * Promoting a member to an admin
   *
   * @param event
   * @returns {Promise<void>}
   */
  async promoteMemberToAdmin(event) {
    const { url, errorMessage, confirmButton, cancelButton, confirmText } =
      event.currentTarget.dataset

    const result = await Swal.fire({
      title: confirmText || 'Promote this member to admin?',
      icon: 'warning',
      showCancelButton: true,
      allowOutsideClick: false,
      customClass: {
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-outline-primary',
      },
      buttonsStyling: false,
      confirmButtonText: confirmButton || 'Promote',
      cancelButtonText: cancelButton || 'Cancel',
    })

    if (!result.isConfirmed) {
      return
    }

    const response = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
    })

    if (response.status !== 204) {
      showSnackbar('#share-snackbar', errorMessage, SnackbarDuration.error)
      return
    }

    window.location.reload()
  }

  /**
   * Banning a member from the studio
   *
   * @param event
   * @returns {Promise<void>}
   */
  async banUserFromStudio(event) {
    const { url, errorMessage, confirmButton, cancelButton, confirmText } =
      event.currentTarget.dataset

    const result = await Swal.fire({
      title: confirmText || 'Remove this member from the studio?',
      icon: 'warning',
      showCancelButton: true,
      allowOutsideClick: false,
      customClass: {
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-outline-primary',
      },
      buttonsStyling: false,
      confirmButtonText: confirmButton || 'Remove',
      cancelButtonText: cancelButton || 'Cancel',
    })

    if (!result.isConfirmed) {
      return
    }

    const response = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
    })

    if (response.status !== 204) {
      showSnackbar('#share-snackbar', errorMessage, SnackbarDuration.error)
      return
    }

    window.location.reload()
  }

  /**
   * Demoting an admin to a regular member
   *
   * @param event
   * @returns {Promise<void>}
   */
  async demoteAdminToMember(event) {
    const { url, errorMessage } = event.currentTarget.dataset

    const result = await Swal.fire({
      title: 'Demote this admin to member?',
      icon: 'warning',
      showCancelButton: true,
      allowOutsideClick: false,
      customClass: {
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-outline-primary',
      },
      buttonsStyling: false,
      confirmButtonText: 'Demote',
      cancelButtonText: 'Cancel',
    })

    if (!result.isConfirmed) {
      return
    }

    const response = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
    })

    if (response.status !== 204) {
      showSnackbar('#share-snackbar', errorMessage, SnackbarDuration.error)
      return
    }

    window.location.reload()
  }
}
