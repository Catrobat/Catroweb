/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
function AdminMaintain (google, dataSpaceValues, dataMemValues, wholeSpace, wholeRam) {
  const self = this
  self.google = google
  self.dataSpaceValues = dataSpaceValues
  self.dataMemValues = dataMemValues
  self.wholeSpace = wholeSpace
  self.wholeRam = wholeRam
  self.init = function () {
    self.google.load('visualization', '1', { packages: ['corechart'] })
    self.google.setOnLoadCallback(self.drawDiskChart)
    self.google.setOnLoadCallback(self.drawRamChart)
  }
  self.drawDiskChart = function () {
    const dataSpace = google.visualization.arrayToDataTable(self.dataSpaceValues)
    const optionsSpace = {
      title: 'Disk Space (' + self.wholeSpace + ')',
      is3D: true
    }
    const chart = new google.visualization.PieChart(document.getElementById('piechart_3d'))
    chart.draw(dataSpace, optionsSpace)
  }

  self.drawRamChart = function () {
    const optionsMem = {
      title: 'RAM (' + self.wholeRam + ')',
      is3D: true
    }
    const dataMem = google.visualization.arrayToDataTable(dataMemValues)
    const chartMem = new google.visualization.PieChart(document.getElementById('piechart_3d_mem'))
    chartMem.draw(dataMem, optionsMem)
  }
}
