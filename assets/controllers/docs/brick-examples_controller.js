import { Controller } from '@hotwired/stimulus'
import { ProjectList } from '../../Project/ProjectList'

/**
 * Community projects that use a brick (templates/Docs/BrickExamples.html.twig). A click on a card plays the
 * project in the inline browser player; without a player or WebGL the card stays a link to the project page.
 * The page entry registers it: app.register('docs--brick-examples', BrickExamplesController)
 */
export default class extends Controller {
  static values = { url: String, theme: String, playerOrigin: String }
  static targets = ['list', 'player']

  connect() {
    new ProjectList(
      this.listTarget,
      'brick_examples',
      this.urlValue,
      'downloads',
      this.themeValue,
      10,
    )
    this.canPlay =
      this.playerOriginValue !== '' && document.createElement('canvas').getContext('webgl') !== null
  }

  play(event) {
    const card = event.target.closest('.project-list__project')
    if (!this.canPlay || !card?.dataset.id) return
    event.preventDefault()
    this.playerTarget.hidden = false
    this.playerTarget.querySelector('iframe').src =
      `${this.playerOriginValue}/#${encodeURIComponent(card.dataset.id)}`
    this.playerTarget.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
  }
}
