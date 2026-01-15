import './Logs.scss'

let currentLevelFilter = 'all'
let currentSearchTerm = ''

const initializeColorScheme = () => {
  // Toggle log details on header click
  document.querySelectorAll('.log-header').forEach(function (logHeader) {
    logHeader.addEventListener('click', function () {
      const logDetails = logHeader.nextElementSibling
      if (logDetails && logDetails.classList.contains('log-details')) {
        logDetails.classList.toggle('hide')
      }
    })
  })

  // File switcher
  document.querySelectorAll('.files').forEach(function (fileElement) {
    fileElement.addEventListener('click', function () {
      loadFileContent(fileElement.value)
      document.getElementById('currentFile').value = fileElement.value
    })
  })

  // Level filter buttons
  document.querySelectorAll('.filter-btn[data-filter-type="level"]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      // Remove active class from all level filter buttons
      document
        .querySelectorAll('.filter-btn[data-filter-type="level"]')
        .forEach((b) => b.classList.remove('active'))
      btn.classList.add('active')

      currentLevelFilter = btn.dataset.filterValue
      applyFilters()
    })
  })

  // Search input
  const searchInput = document.getElementById('searchInput')
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      currentSearchTerm = this.value.toLowerCase()
      applyFilters()
    })
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeColorScheme)
} else {
  initializeColorScheme()
}

function applyFilters() {
  const logEntries = document.querySelectorAll('.log-entry')
  let visibleCount = 0

  logEntries.forEach(function (entry) {
    let visible = true

    // Apply level filter
    if (currentLevelFilter !== 'all') {
      const entryLevel = entry.dataset.level
      if (entryLevel !== currentLevelFilter) {
        visible = false
      }
    }

    // Apply search filter
    if (visible && currentSearchTerm) {
      const textContent = entry.textContent.toLowerCase()
      if (!textContent.includes(currentSearchTerm)) {
        visible = false
      }
    }

    if (visible) {
      entry.style.display = ''
      visibleCount++
    } else {
      entry.style.display = 'none'
    }
  })

  // Show count of visible logs
  console.log(`Showing ${visibleCount} of ${logEntries.length} log entries`)
}

function loadFileContent(file) {
  document.getElementById('loading-spinner').style.display = 'block'
  document.getElementById('innerLogContainer').innerHTML = ''
  document.getElementById('currentFileName').innerHTML = file

  fetch(`${window.location.href}?file=${file}&count=1000`)
    .then((response) => {
      if (!response.ok) {
        throw new Error('Network response was not ok')
      }
      return response.text()
    })
    .then((data) => {
      document.getElementById('loading-spinner').style.display = 'none'
      const tempDiv = document.createElement('div')
      tempDiv.innerHTML = data
      document.getElementById('innerLogContainer').innerHTML =
        tempDiv.querySelector('#innerLogContainer').innerHTML

      // Reinitialize event listeners for new content
      initializeColorScheme()

      // Reset filters
      currentLevelFilter = 'all'
      currentSearchTerm = ''
      document
        .querySelectorAll('.filter-btn[data-filter-type="level"]')
        .forEach((b) => b.classList.remove('active'))
      document.querySelector('.filter-btn[data-filter-value="all"]')?.classList.add('active')
      if (document.getElementById('searchInput')) {
        document.getElementById('searchInput').value = ''
      }
    })
    .catch(() => {
      document.getElementById('loading-spinner').style.display = 'none'
      alert('something went terribly wrong')
    })
}
