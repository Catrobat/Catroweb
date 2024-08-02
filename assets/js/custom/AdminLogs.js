// eslint-disable-next-line no-unused-vars
function AdminLogs() {
  document.addEventListener('click', function (event) {
    if (event.target.classList.contains('line-head')) {
      const panelHeading = event.target.closest('.panel-heading')
      if (panelHeading) {
        const panelCollapse = panelHeading.nextElementSibling
        if (
          panelCollapse &&
          panelCollapse.classList.contains('panel-collapse')
        ) {
          panelCollapse.classList.toggle('hide')
        }
      }
    }

    if (event.target.id === 'search') {
      loadFileContent(
        document.getElementById('currentFile').value,
        document.getElementById('logLevelSelect').value,
        document.getElementById('lineNumber').value,
        document.querySelector('.greaterThanRB:checked').value,
      )
    }

    if (event.target.classList.contains('files')) {
      loadFileContent(
        event.target.value,
        document.getElementById('logLevelSelect').value,
        document.getElementById('lineNumber').value,
        document.querySelector('.greaterThanRB:checked').value,
      )
      document.getElementById('currentFile').value = event.target.value
    }
  })
}

function loadFileContent(file, filter, count, greaterThan) {
  const loadingSpinner = document.getElementById('loading-spinner')
  const innerLogContainer = document.getElementById('innerLogContainer')
  const outerLogContainer = document.getElementById('outerLogContainer')

  loadingSpinner.style.display = 'block'
  innerLogContainer.innerHTML = ''

  const xhr = new XMLHttpRequest()
  xhr.open(
    'GET',
    `/your-endpoint?file=${file}&filter=${filter}&count=${count}&greaterThan=${greaterThan}`,
    true,
  )

  xhr.onload = function () {
    loadingSpinner.style.display = 'none'
    if (xhr.status >= 200 && xhr.status < 300) {
      try {
        const data = JSON.parse(xhr.responseText)
        outerLogContainer.innerHTML = formatLogData(data)
      } catch (error) {
        alert('Error parsing data: ' + error.message)
      }
    } else {
      alert('something went terribly wrong')
    }
  }

  xhr.onerror = function () {
    loadingSpinner.style.display = 'none'
    alert('something went terribly wrong')
  }

  xhr.send()
}

function formatLogData(data) {
  let formattedData = '<ul>'
  data.forEach((log) => {
    formattedData += `<li>${log}</li>`
  })
  formattedData += '</ul>'
  return formattedData
}
