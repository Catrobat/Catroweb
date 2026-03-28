import { Carousel } from 'bootstrap'

export class FeaturedProjects {
  constructor(containerId) {
    this.container = document.getElementById(containerId)
  }

  init() {
    if (!this.container) {
      return
    }

    const { baseUrl, theme, flavor, isGuest, isWebview, transFeatured } = this.container.dataset

    const apiUrl = `${baseUrl}/api/projects/featured?flavor=${flavor}&attributes=url,project_url,featured_image`

    fetch(apiUrl)
      .then((response) => {
        if (!response.ok) throw new Error(`HTTP ${response.status}`)
        return response.json()
      })
      .then((items) => {
        if (!Array.isArray(items) || items.length === 0) {
          this.container.style.display = 'none'
          return
        }

        const slides = items.map((item) => ({
          url: item.project_url ? item.project_url.replace('/app/', `/${theme}/`) : item.url,
          image: item.featured_image,
        }))

        if (isGuest === '1' && isWebview !== '1') {
          const heading = document.createElement('h2')
          heading.textContent = transFeatured
          this.container.before(heading)
        }

        this.renderCarousel(slides)
      })
      .catch((error) => {
        console.error('Failed to load featured projects', error)
        this.container.style.display = 'none'
      })
  }

  renderCarousel(slides) {
    const carouselId = 'feature-slider'

    const carouselDiv = document.createElement('div')
    carouselDiv.id = carouselId
    carouselDiv.className = 'carousel slide center mb-4'
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
      const link = document.createElement('a')
      link.className = i === 0 ? 'carousel-item active' : 'carousel-item'
      link.href = slide.url
      if (i > 0) {
        link.style.background = '#fff'
      }

      const img = document.createElement('img')
      img.src = slide.image
      img.className = 'carousel-item__image d-block w-100'
      img.alt = ''
      img.width = 1024
      img.height = 400
      img.loading = i === 0 ? 'eager' : 'lazy'

      link.appendChild(img)
      inner.appendChild(link)
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

    this.container.appendChild(carouselDiv)
    new Carousel(carouselDiv)
  }
}
