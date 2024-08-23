import { AjaxController } from '../ajax_controller'
import { showSnackbar } from '../../Layout/Snackbar'
import { MDCMenu } from '@material/menu'

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
    const userId = event.currentTarget.dataset.userId
    const errorMessage = event.currentTarget.dataset.errorMessage

    const response = await this.fetchPut(event.currentTarget.dataset.url, {
      studio_id: studioId,
      user_id: userId,
    })

    if (response.status !== 204) {
      showSnackbar('#share-snackbar', errorMessage)
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
    const userId = event.currentTarget.dataset.userId
    const errorMessage = event.currentTarget.dataset.errorMessage

    const response = await this.fetchPut(event.currentTarget.dataset.url, {
      studio_id: studioId,
      user_id: userId,
    })

    if (response.status !== 204) {
      showSnackbar('#share-snackbar', errorMessage)
      return
    }

    await this.loadMembers()
  }
}
