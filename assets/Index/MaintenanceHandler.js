export class MaintenanceHandler {
  constructor() {
    this.initCloseButtons()
    this.initExpandButtons()
  }

  // Initialize close buttons and attach event listeners
  initCloseButtons() {
    const closeButtons = document.querySelectorAll('.close-button')
    closeButtons.forEach((closeButton) => {
      closeButton.addEventListener('click', () => this.handleCloseButtonClick(closeButton))
    })
  }

  // Handle close button click event
  handleCloseButtonClick(closeButton) {
    const parentElement = closeButton.parentElement
    if (parentElement) {
      const url = closeButton.getAttribute('data-url')
      const viewId = closeButton.getAttribute('data-maintenance-id')
      parentElement.style.display = 'none'
      this.sendCloseEventToServer(viewId, url)
    }
  }

  // Send close event data to the server
  sendCloseEventToServer(viewId, url) {
    const formData = new FormData()
    formData.append('viewId', viewId)
    fetch(url, {
      method: 'POST',
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          console.error('Failed to send close event')
        }
      })
      .catch((error) => {
        console.error('Error:', error.message)
      })
  }

  // Initialize expand buttons and attach event listeners
  initExpandButtons() {
    const expandButtons = document.querySelectorAll('.expand-button')
    expandButtons.forEach((expandButton) => {
      expandButton.addEventListener('click', () => this.handleExpandButtonClick(expandButton))
    })
  }

  // Handle expand button click event
  handleExpandButtonClick(expandButton) {
    const dataId = expandButton.getAttribute('data-id')
    const additionalInfoSection = document.getElementById(`additional-info-${dataId}`)
    if (additionalInfoSection) {
      const isCurrentlyVisible = additionalInfoSection.style.display === 'block'
      additionalInfoSection.style.display = isCurrentlyVisible ? 'none' : 'block'
      expandButton.classList.toggle('expanded')
    }
  }
}
