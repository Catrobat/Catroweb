import { AjaxController } from '../ajax_controller'

export default class extends AjaxController {
  static values = {
    url: String,
    studioId: String,
    listElementId: String,
  }

  /**
   * Initializing the activity list of a studio
   *
   * data-action="click->studio--member-list#loadActivities"
   *
   * @returns {Promise<void>}
   */
  async loadActivities() {
    await this.fetchData(
      this.urlValue,
      this.listElementIdValue,
      new URLSearchParams({ studio_id: this.studioIdValue }),
    )
  }
}
