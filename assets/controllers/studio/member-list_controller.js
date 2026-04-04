import { AjaxController } from '../ajax_controller'
import { showSnackbar, SnackbarDuration } from '../../Layout/Snackbar'
import { MDCMenu } from '@material/menu'
import Swal from 'sweetalert2'

export default class extends AjaxController {
  static values = {
    url: String,
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
    await this.fetchData(
      this.urlValue,
      this.listElementIdValue,
      new URLSearchParams({ studio_id: this.studioIdValue }),
    )

    for (const el of document.querySelectorAll('.mdc-menu')) {
      const menu = new MDCMenu(el)
      menu.open = false
    }
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
   * data-action="click->studio--member-list#promoteMemberToAdmin"
   * data-url="{{ path('...') }}"
   * data-user-id="{{ ... }}"
   * data-error-message="{{ "..."|trans({}, "catroweb") }}"
   *
   * @param event
   * @returns {Promise<void>}
   */
  async promoteMemberToAdmin(event) {
    const studioId = this.studioIdValue
    const { url, userId, errorMessage, confirmButton, cancelButton } = event.currentTarget.dataset
    const confirmText = event.currentTarget.dataset.confirmText || 'Promote this member to admin?'

    const result = await Swal.fire({
      title: confirmText,
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

    const response = await this.fetchPut(url, {
      studio_id: studioId,
      user_id: userId,
    })

    if (response.status !== 204) {
      showSnackbar('#share-snackbar', errorMessage, SnackbarDuration.error)
      return
    }

    await this.loadMembers()
  }

  /**
   * Banning a member from the studio
   *
   * data-action="click->studio--member-list#banUserFromStudio"
   * data-url="{{ path('...') }}"
   * data-user-id="{{ ... }}"
   * data-error-message="{{ "..."|trans({}, "catroweb") }}"
   *
   * @param event
   * @returns {Promise<void>}
   */
  async banUserFromStudio(event) {
    const studioId = this.studioIdValue
    const { url, userId, errorMessage, confirmButton, cancelButton } = event.currentTarget.dataset
    const confirmText =
      event.currentTarget.dataset.confirmText || 'Remove this member from the studio?'

    const result = await Swal.fire({
      title: confirmText,
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

    const response = await this.fetchPut(url, {
      studio_id: studioId,
      user_id: userId,
    })

    if (response.status !== 204) {
      showSnackbar('#share-snackbar', errorMessage, SnackbarDuration.error)
      return
    }

    await this.loadMembers()
  }
}
