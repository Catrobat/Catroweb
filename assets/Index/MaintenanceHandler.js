import { escapeHtml } from '../Components/HtmlEscape'

export class MaintenanceHandler {
  constructor() {
    const container = document.getElementById('maintenance-container')
    if (!container) return

    const url = container.dataset.maintenanceUrl
    const closeUrlTemplate = container.dataset.closeUrlTemplate
    if (!url) return

    this.container = container
    this.closeUrlTemplate = closeUrlTemplate
    this.fetchAndRender(url)
  }

  async fetchAndRender(url) {
    try {
      const response = await fetch(url, { credentials: 'same-origin' })
      if (!response.ok) return

      const items = await response.json()
      if (!Array.isArray(items) || items.length === 0) return

      items.forEach((item) => this.renderBanner(item))
    } catch (e) {
      console.error('Failed to load maintenance info:', e)
    }
  }

  renderBanner(item) {
    const el = document.createElement('div')
    el.className = 'maintenance'
    el.id = 'viewID_' + item.id

    const icon = escapeHtml(item.icon || 'info')
    const code = escapeHtml(item.code || '')
    const featureName = item.feature_name ? escapeHtml(item.feature_name) : ''
    const startDate = item.maintenance_start || ''
    const endDate = item.maintenance_end || ''
    const additionalInfo = item.additional_info || ''
    const closeUrl = this.closeUrlTemplate.replace('__ID__', item.id)

    let message = code
    if (featureName) {
      message = message.replace(
        '%featureName%',
        '<span class="maintenance-feature-name bold-text">' + escapeHtml(featureName) + '</span>',
      )
    }
    if (startDate) {
      message = message.replace(
        '%maintenanceStart%',
        '<span class="maintenanceStart-start-date bold-text">' + escapeHtml(startDate) + '</span>',
      )
    }
    if (endDate) {
      message = message.replace(
        '%maintenanceEnd%',
        '<span class="maintenanceStart-end-date bold-text">' + escapeHtml(endDate) + '</span>',
      )
    }

    let expandHtml = ''
    let additionalHtml = ''
    if (additionalInfo) {
      expandHtml =
        '<div class="expand-button" data-id="' +
        escapeHtml(code) +
        '">' +
        '<i class="material-icons">arrow_drop_down</i></div>'
      additionalHtml =
        '<div class="additional-info-section" id="additional-info-' +
        escapeHtml(code) +
        '" style="display:none;">' +
        '<span>' +
        escapeHtml(additionalInfo) +
        '</span></div>'
    }

    el.innerHTML =
      '<div class="maintenance-content">' +
      '<div class="maintenance-text">' +
      '<div class="maintenance-icon"><i class="material-icons md-48">' +
      icon +
      '</i></div>' +
      '<span>' +
      message +
      '</span>' +
      expandHtml +
      '</div>' +
      additionalHtml +
      '</div>' +
      '<button class="close-button" data-maintenance-id="' +
      item.id +
      '">' +
      '<i class="material-icons md-48">close</i></button>'

    const closeBtn = el.querySelector('.close-button')
    if (closeBtn) {
      closeBtn.addEventListener('click', () => {
        el.style.display = 'none'
        this.sendCloseEvent(item.id, closeUrl)
      })
    }

    const expandBtn = el.querySelector('.expand-button')
    if (expandBtn) {
      expandBtn.addEventListener('click', () => {
        const section = el.querySelector('.additional-info-section')
        if (section) {
          const visible = section.style.display === 'block'
          section.style.display = visible ? 'none' : 'block'
          expandBtn.classList.toggle('expanded')
        }
      })
    }

    this.container.appendChild(el)
  }

  sendCloseEvent(viewId, url) {
    const formData = new FormData()
    formData.append('viewId', viewId)
    fetch(url, { method: 'POST', credentials: 'same-origin', body: formData }).catch((e) =>
      console.error('Failed to close maintenance banner:', e),
    )
  }
}
