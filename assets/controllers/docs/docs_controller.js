import { Controller } from '@hotwired/stimulus'

// Docs page: "In this section" and the YouTube embeds (`::video`), which load nothing from YouTube until clicked.
export default class extends Controller {
  static targets = ['section']

  connect() {
    // "In this section" is closed on a phone; beside the page it is open (DocsPage.css shows it from the first paint
    // where the browser knows ::details-content).
    if (this.hasSectionTarget && window.matchMedia('(min-width: 992px)').matches) {
      this.sectionTarget.open = true
    }
    const label = this.element.dataset.transVideo
    this.element.querySelectorAll('.docs-video').forEach((link) => {
      link.querySelector('.docs-video__label').textContent = label
    })
  }

  playVideo(event) {
    const link = event.target.closest('.docs-video')
    if (!link) {
      return
    }
    event.preventDefault()
    const iframe = document.createElement('iframe')
    iframe.src = `https://www.youtube-nocookie.com/embed/${encodeURIComponent(link.dataset.youtubeId)}?autoplay=1`
    iframe.title = 'YouTube'
    iframe.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen'
    iframe.allowFullscreen = true
    iframe.className = 'docs-video__frame'
    link.replaceWith(iframe)
  }
}
