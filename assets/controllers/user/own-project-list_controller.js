import { Controller } from '@hotwired/stimulus'
import { OwnProjectList } from '../../Project/OwnProjectList'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  connect() {
    const baseUrl = this.element.dataset.baseUrl
    const theme = this.element.dataset.theme
    const emptyMessage = this.element.dataset.emptyMessage
    const apiUrl = baseUrl + '/api/projects/user'

    this.ownProjectList = new OwnProjectList(this.element, apiUrl, theme, emptyMessage, baseUrl)
    this.ownProjectList.initialize()
  }
}
