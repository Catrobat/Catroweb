import { Controller } from '@hotwired/stimulus'
import { escapeHtml } from '../../Components/HtmlEscape'

export default class extends Controller {
  static values = {
    activitiesUrl: String,
    studioId: String,
    listElementId: String,
  }

  cursor = null
  hasMore = false

  /**
   * Initializing the activity list of a studio
   *
   * data-action="click->studio--activity-list#loadActivities"
   *
   * @returns {Promise<void>}
   */
  async loadActivities() {
    const listElement = document.getElementById(this.listElementIdValue)
    listElement.innerHTML = ''
    this.cursor = null

    await this._fetchActivities(listElement)
  }

  async _fetchActivities(listElement) {
    const url = new URL(this.activitiesUrlValue, window.location.origin)
    url.searchParams.set('limit', '50')
    if (this.cursor) {
      url.searchParams.set('cursor', this.cursor)
    }

    try {
      const response = await fetch(url, { credentials: 'same-origin' })
      if (!response.ok) {
        return
      }

      const data = await response.json()
      this.hasMore = data.has_more
      this.cursor = data.next_cursor

      const transJoin = this.element.dataset.transJoinStudio || '%user% joined the studio'
      const transAddProject = this.element.dataset.transAddProject || '%user% added a project'
      const transAddComment = this.element.dataset.transAddComment || '%user% added a comment'

      if (data.data && data.data.length > 0) {
        data.data.forEach((activity) => {
          const li = document.createElement('li')
          li.className = 'activity__list-entry'

          const date = activity.created_at
            ? new Date(activity.created_at).toLocaleDateString('en-GB', {
                day: '2-digit',
                month: '2-digit',
                year: '2-digit',
              })
            : ''

          const userLink =
            '<a href="/app/user/' +
            encodeURIComponent(activity.user_id) +
            '">' +
            escapeHtml(activity.username) +
            '</a>'

          let text = ''
          if (activity.type === 'user') {
            text = transJoin.replace('%user%', userLink)
          } else if (activity.type === 'project') {
            text = transAddProject.replace('%user%', userLink)
          } else if (activity.type === 'comment') {
            text = transAddComment.replace('%user%', userLink)
          }

          li.innerHTML =
            '<span class="activity__list-entry__date">' +
            escapeHtml(date) +
            ':&nbsp;</span><span>' +
            text +
            '</span>'

          listElement.appendChild(li)
        })
      }
    } catch (e) {
      console.error('Failed to load activities:', e)
    }
  }
}
