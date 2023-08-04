// eslint-disable-next-line no-unused-vars
function PieChart(google) {
  this.init = () => {
    google.load('visualization', '1', { packages: ['corechart'] })
  }

  this.add = (elementId, dataArray, title) => {
    google.setOnLoadCallback(() => {
      const dataSpace = google.visualization.arrayToDataTable(dataArray)
      const optionsSpace = {
        title,
        is3D: true,
      }
      const chart = new google.visualization.PieChart(
        document.getElementById(elementId),
      )
      chart.draw(dataSpace, optionsSpace)
    })
  }
}
