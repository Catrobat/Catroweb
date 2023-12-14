document.addEventListener('DOMContentLoaded', function () {
  const closeButtons = document.querySelectorAll('.close-button')
  closeButtons.forEach((closeButton) => {
    if (closeButton) {
      closeButton.addEventListener('click', () => {
        const parentElement = closeButton.parentElement
        if (parentElement) {
          const url = closeButton.getAttribute('data-url')
          const viewId = closeButton.getAttribute('data-maintenance-id')
          parentElement.style.display = 'none'
          sendCloseEventToServer(viewId, url)
        }
      })
    }
  })
  const expandButtons = document.querySelectorAll('.expand-button')

  expandButtons.forEach((expandButton) => {
    expandButton.addEventListener('click', () => {
      const dataId = expandButton.getAttribute('data-id')
      const additionalInfoSection = document.getElementById(
        `additional-info-${dataId}`,
      )
      if (
        additionalInfoSection.style.display === 'none' ||
        additionalInfoSection.style.display === ''
      ) {
        additionalInfoSection.style.display = 'block'
        expandButton.classList.toggle('expanded')
      } else {
        additionalInfoSection.style.display = 'none'
        expandButton.classList.toggle('expanded')
      }
    })
  })
})

function sendCloseEventToServer(viewId, url) {
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
      // Handle network errors or other exceptions
      console.error('Error:', error.message)
    })
}