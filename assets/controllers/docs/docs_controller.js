import { Controller } from '@hotwired/stimulus'

// Docs page: "In this section" and the YouTube embeds (`::video`), which load nothing from YouTube until clicked.
export default class extends Controller {
  static targets = ['section']
  static values = { playerOrigin: String }

  connect() {
    // "In this section" is closed on a phone. Beside the page it is always open and not a toggle (DocsPage.css shows it
    // from the first paint where the browser knows ::details-content), so a screen reader never hears "collapsed".
    this.wide = window.matchMedia('(min-width: 992px)')
    if (this.hasSectionTarget) {
      this.fixSection()
      this.wide.addEventListener('change', this.fixSection)
    }
    const label = this.element.dataset.transVideo
    this.element.querySelectorAll('.docs-video').forEach((link) => {
      link.querySelector('.docs-video__label').textContent = label
    })
  }

  disconnect() {
    this.wide?.removeEventListener('change', this.fixSection)
  }

  fixSection = (breakpointChange) => {
    const summary = this.sectionTarget.querySelector('summary')
    if (this.wide.matches) {
      this.sectionTarget.open = true
      summary.tabIndex = -1
      summary.setAttribute('aria-hidden', 'true')
    } else {
      if (breakpointChange && this.element.classList.contains('docs--compact-lesson')) {
        this.sectionTarget.open = false
      }
      summary.removeAttribute('tabindex')
      summary.removeAttribute('aria-hidden')
    }
  }

  // Wired to the details' toggle event: on a wide screen it stays open.
  keepSectionOpen() {
    if (this.wide.matches && !this.sectionTarget.open) {
      this.sectionTarget.open = true
    }
  }

  playProject(event) {
    const link = event.target.closest('[data-docs-project-id]')
    if (!link || !this.playerOriginValue || !document.createElement('canvas').getContext('webgl')) {
      return
    }
    const preview = link.closest('.docs-project-preview')
    event.preventDefault()
    const player = document.createElement('div')
    player.className = 'ratio ratio-4x3'
    const iframe = document.createElement('iframe')
    iframe.src = `${this.playerOriginValue}/#${encodeURIComponent(link.dataset.docsProjectId)}`
    iframe.title = preview.querySelector('figcaption > span').textContent
    iframe.allow = 'fullscreen; autoplay'
    iframe.allowFullscreen = true
    player.append(iframe)
    preview.replaceWith(player)
    iframe.focus()
  }

  playVideo(event) {
    const link = event.target.closest('.docs-video')
    if (!link) {
      return
    }
    event.preventDefault()
    const iframe = document.createElement('iframe')
    iframe.src = `https://www.youtube-nocookie.com/embed/${encodeURIComponent(link.dataset.youtubeId)}?autoplay=1`
    iframe.referrerPolicy = 'strict-origin-when-cross-origin'
    iframe.title = 'YouTube'
    iframe.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen'
    iframe.allowFullscreen = true
    iframe.className = 'docs-video__frame'
    link.replaceWith(iframe)
  }
}
