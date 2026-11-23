import { Controller } from '@hotwired/stimulus'

// Docs page: the contents list and the YouTube embeds (`::video`), which load nothing from YouTube until clicked.
export default class extends Controller {
  static targets = ['nav']

  connect() {
    // Closed on a phone so it does not push the page down; beside the page there is room to show it.
    if (window.matchMedia('(min-width: 992px)').matches) {
      this.navTarget.open = true
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
