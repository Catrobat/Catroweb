document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.panel-heading').forEach(function (panelHeading) {
    panelHeading.addEventListener('click', function (event) {
      const panelCollapse = panelHeading.nextElementSibling
      if (panelCollapse && panelCollapse.classList.contains('panel-collapse')) {
        panelCollapse.classList.toggle('hide')
      }
    })
  })

  document.querySelectorAll('.files').forEach(function (fileElement) {
    fileElement.addEventListener('click', function (event) {
      loadFileContent(fileElement.value)
      document.getElementById('currentFile').value = fileElement.value
    })
  })
})

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

      console.error(tempDiv.querySelector('#innerLogContainer').innerHTML)

      document.getElementById('innerLogContainer').innerHTML =
        tempDiv.querySelector('#innerLogContainer').innerHTML
    })
    .catch(() => {
      document.getElementById('loading-spinner').style.display = 'none'
      alert('something went terribly wrong')
    })
}
