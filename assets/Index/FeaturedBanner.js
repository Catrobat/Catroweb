import { Carousel } from 'bootstrap'

export class FeaturedBanner {
  constructor(containerId) {
    this.container = document.getElementById(containerId)
  }

  init() {
    if (!this.container) {
      return
    }

    const { baseUrl, flavor } = this.container.dataset

    const apiUrl = `${baseUrl}/api/featured-banners`

    fetch(apiUrl)
      .then((r) => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`)
        return r.json()
      })
      .then((items) => {
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
            image: item.image_url || '/images/default/screenshot.png',
            title: item.title || '',
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
      btn.setAttribute('aria-label', `Slide ${i}`)
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
      const wrapper = slide.url ? document.createElement('a') : document.createElement('div')
      wrapper.className = i === 0 ? 'carousel-item active' : 'carousel-item'
      if (slide.url) {
        wrapper.href = slide.url
      }
      if (i > 0) {
        wrapper.style.background = '#fff'
      }

      const img = document.createElement('img')
      img.src = slide.image
      img.className = 'carousel-item__image d-block w-100'
      img.alt = slide.title || ''
      img.width = 1024
      img.height = 400
      img.loading = i === 0 ? 'eager' : 'lazy'
      if (i === 0) {
        img.fetchPriority = 'high'
        img.onload = () => this.removeSkeleton()
        img.onerror = () => this.removeSkeleton()
      }

      wrapper.appendChild(img)

      if (slide.title) {
        const caption = document.createElement('div')
        caption.className = 'carousel-caption d-block'
        const captionTitle = document.createElement('h5')
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
    new Carousel(carouselDiv)
  }

  removeSkeleton() {
    const skeleton = this.container.querySelector('.featured-slider__skeleton')
    if (skeleton) {
      skeleton.remove()
    }
  }
}
