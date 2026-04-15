import { Carousel } from 'bootstrap'
import { createPictureElement } from '../Layout/ImageVariants'

export class FeaturedBanner {
  constructor(containerId) {
    this.container = document.getElementById(containerId)
  }

  init() {
    if (!this.container) {
      return
    }

    const { baseUrl, flavor } = this.container.dataset

    const params = flavor ? `?flavor=${encodeURIComponent(flavor)}` : ''
    const apiUrl = `${baseUrl}/api/featured-banners${params}`

    fetch(apiUrl)
      .then((r) => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`)
        return r.json()
      })
      .then((response) => {
        const items = Array.isArray(response) ? response : response?.data || []
        if (!Array.isArray(items) || items.length === 0) {
          this.removeSkeleton()
          this.container.style.display = 'none'
          return
        }

        const slides = items.map((item) => {
          let url = item.link_url
          if (url && flavor) {
            url = url.replace('/app/', `/${flavor}/`)
          }
          return {
            url: url || null,
            imageVariants: item.image_variants || null,
            title: item.title || '',
            videoUrl: item.video_url || null,
            youtubeThumbnail: this.extractYouTubeThumbnail(item.video_url),
          }
        })

        this.renderCarousel(slides)
      })
      .catch((error) => {
        console.error('Failed to load featured banners', error)
        this.removeSkeleton()
        this.container.style.display = 'none'
      })
  }

  renderCarousel(slides) {
    const carouselId = 'feature-slider'

    const carouselDiv = document.createElement('div')
    carouselDiv.id = carouselId
    carouselDiv.className = 'carousel slide center mb-2 featured-banner'
    carouselDiv.setAttribute('data-bs-ride', 'carousel')

    // Indicators
    const indicators = document.createElement('div')
    indicators.className = 'carousel-indicators'
    slides.forEach((_, i) => {
      const btn = document.createElement('button')
      btn.type = 'button'
      btn.setAttribute('data-bs-target', `#${carouselId}`)
      btn.setAttribute('data-bs-slide-to', String(i))
      btn.setAttribute('aria-label', `Slide ${i + 1} of ${slides.length}`)
      if (i === 0) {
        btn.className = 'active'
        btn.setAttribute('aria-current', 'true')
      }
      indicators.appendChild(btn)
    })
    carouselDiv.appendChild(indicators)

    // Slides
    const inner = document.createElement('div')
    inner.className = 'carousel-inner'
    slides.forEach((slide, i) => {
      const wrapper = document.createElement('div')
      wrapper.className = i === 0 ? 'carousel-item active' : 'carousel-item'
      if (i > 0) {
        wrapper.style.background = '#fff'
      }

      if (slide.videoUrl) {
        wrapper.appendChild(this.createVideoSlide(slide, i))
      } else {
        const link = slide.url ? document.createElement('a') : document.createElement('div')
        if (slide.url) link.href = slide.url
        link.appendChild(this.createSlideImage(slide, i))
        wrapper.appendChild(link)
      }

      if (slide.title) {
        const caption = document.createElement('div')
        caption.className = 'carousel-caption d-block'
        const captionTitle = document.createElement('p')
        captionTitle.className = 'featured-banner__caption-title'
        captionTitle.textContent = slide.title
        caption.appendChild(captionTitle)
        wrapper.appendChild(caption)
      }

      inner.appendChild(wrapper)
    })
    carouselDiv.appendChild(inner)

    // Controls
    const prevBtn = document.createElement('button')
    prevBtn.className = 'carousel-control-prev'
    prevBtn.type = 'button'
    prevBtn.setAttribute('data-bs-target', `#${carouselId}`)
    prevBtn.setAttribute('data-bs-slide', 'prev')
    prevBtn.innerHTML =
      '<span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span>'
    carouselDiv.appendChild(prevBtn)

    const nextBtn = document.createElement('button')
    nextBtn.className = 'carousel-control-next'
    nextBtn.type = 'button'
    nextBtn.setAttribute('data-bs-target', `#${carouselId}`)
    nextBtn.setAttribute('data-bs-slide', 'next')
    nextBtn.innerHTML =
      '<span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span>'
    carouselDiv.appendChild(nextBtn)

    // Stack carousel in same grid cell as skeleton (both grid-area: 1/1)
    carouselDiv.style.gridArea = '1/1'
    this.container.appendChild(carouselDiv)

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    const carouselInstance = new Carousel(carouselDiv, {
      interval: prefersReducedMotion ? false : 5000,
      ride: prefersReducedMotion ? false : 'carousel',
      // Keep manual pause/play deterministic: Bootstrap's default `pause: 'hover'`
      // can restart cycling on mouseleave/touchend via `_maybeEnableCycle()`.
      pause: false,
    })

    // Pause/play toggle button for auto-playing carousel (a11y)
    if (slides.length > 1) {
      const pauseBtn = document.createElement('button')
      pauseBtn.type = 'button'
      pauseBtn.className = 'featured-banner__pause-btn'
      pauseBtn.setAttribute('aria-label', 'Pause slideshow')
      pauseBtn.innerHTML =
        '<span class="visually-hidden">Pause slideshow</span>' +
        '<svg aria-hidden="true" focusable="false" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">' +
        '<rect x="3" y="2" width="3" height="12" rx="1"/><rect x="10" y="2" width="3" height="12" rx="1"/>' +
        '</svg>'

      let isPaused = prefersReducedMotion
      if (prefersReducedMotion) {
        pauseBtn.setAttribute('aria-label', 'Play slideshow')
        pauseBtn.innerHTML =
          '<span class="visually-hidden">Play slideshow</span>' +
          '<svg aria-hidden="true" focusable="false" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">' +
          '<path d="M4 2l10 6-10 6V2z"/>' +
          '</svg>'
      }

      pauseBtn.addEventListener('click', () => {
        if (isPaused) {
          carouselInstance.cycle()
          isPaused = false
          pauseBtn.setAttribute('aria-label', 'Pause slideshow')
          pauseBtn.innerHTML =
            '<span class="visually-hidden">Pause slideshow</span>' +
            '<svg aria-hidden="true" focusable="false" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">' +
            '<rect x="3" y="2" width="3" height="12" rx="1"/><rect x="10" y="2" width="3" height="12" rx="1"/>' +
            '</svg>'
        } else {
          carouselInstance.pause()
          isPaused = true
          pauseBtn.setAttribute('aria-label', 'Play slideshow')
          pauseBtn.innerHTML =
            '<span class="visually-hidden">Play slideshow</span>' +
            '<svg aria-hidden="true" focusable="false" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">' +
            '<path d="M4 2l10 6-10 6V2z"/>' +
            '</svg>'
        }
      })

      carouselDiv.appendChild(pauseBtn)
    }
  }

  createSlideImage(slide, index) {
    const attrs = {
      class: 'carousel-item__image d-block w-100',
      alt: slide.title || '',
      width: 1024,
      height: 600,
      loading: index === 0 ? 'eager' : 'lazy',
    }
    if (index === 0) {
      attrs.fetchpriority = 'high'
    }
    const fallback = slide.youtubeThumbnail || '/images/default/screenshot-detail@2x.avif'
    const el = createPictureElement(slide.imageVariants, 'detail', fallback, attrs)
    if (index === 0) {
      const img = el.tagName === 'IMG' ? el : el.querySelector('img')
      if (img) {
        img.onload = () => this.removeSkeleton()
        img.onerror = () => this.removeSkeleton()
      }
    }
    return el
  }

  createVideoSlide(slide, index) {
    const container = document.createElement('div')
    container.className = 'featured-video-slide'
    container.style.position = 'relative'
    container.style.cursor = 'pointer'

    const img = this.createSlideImage(slide, index)
    container.appendChild(img)

    // Play button overlay
    const playBtn = document.createElement('div')
    playBtn.className = 'featured-video-slide__play'
    playBtn.innerHTML =
      '<svg viewBox="0 0 68 48" width="68" height="48"><path d="M66.52 7.74c-.78-2.93-2.49-5.41-5.42-6.19C55.79.13 34 0 34 0S12.21.13 6.9 1.55C3.97 2.33 2.27 4.81 1.48 7.74.06 13.05 0 24 0 24s.06 10.95 1.48 16.26c.78 2.93 2.49 5.41 5.42 6.19C12.21 47.87 34 48 34 48s21.79-.13 27.1-1.55c2.93-.78 4.64-3.26 5.42-6.19C67.94 34.95 68 24 68 24s-.06-10.95-1.48-16.26z" fill="red"/><path d="M45 24L27 14v20" fill="#fff"/></svg>'
    container.appendChild(playBtn)

    container.addEventListener('click', () => {
      const iframe = document.createElement('iframe')
      iframe.src = slide.videoUrl + '?autoplay=1&controls=1'
      iframe.className = 'carousel-item__image d-block w-100'
      iframe.style.aspectRatio = '1024 / 600'
      iframe.style.border = 'none'
      iframe.allow = 'autoplay; encrypted-media'
      iframe.allowFullscreen = true

      container.replaceWith(iframe)

      // Pause carousel auto-rotation while video plays
      const carouselEl = this.container.querySelector('.carousel')
      if (carouselEl) {
        const bsCarousel = Carousel.getInstance(carouselEl)
        if (bsCarousel) bsCarousel.pause()
      }
    })

    return container
  }

  extractYouTubeThumbnail(videoUrl) {
    if (!videoUrl) return null
    const m = videoUrl.match(/\/embed\/([a-zA-Z0-9_-]+)/)
    return m ? `https://img.youtube.com/vi/${m[1]}/hqdefault.jpg` : null
  }

  removeSkeleton() {
    this.container
      .querySelectorAll('.featured-slider__skeleton, .featured-slider__ssr')
      .forEach((el) => el.remove())
  }
}
