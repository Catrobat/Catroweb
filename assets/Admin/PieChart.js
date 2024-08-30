import { GoogleCharts } from 'google-charts'

export class PieChart {
  constructor() {
    this.isLoaded = false
    this.queue = [] // Queue to hold add requests until Google Charts is loaded
    this.init()
  }

  init() {
    GoogleCharts.load(() => {
      this.isLoaded = true
      this.queue.forEach(({ elementId, dataArray, title }) =>
        this.drawChart(elementId, dataArray, title),
      )
      this.queue = [] // Clear the queue after processing
    })
  }

  add(elementId, dataArray, title) {
    if (!this.isLoaded) {
      console.warn('Google Charts is not loaded yet, queuing the chart rendering.')
      this.queue.push({ elementId, dataArray, title }) // Queue the request
      return
    }

    this.drawChart(elementId, dataArray, title)
  }

  drawChart(elementId, dataArray, title) {
    // eslint-disable-next-line new-cap
    const dataSpace = new GoogleCharts.api.visualization.arrayToDataTable(dataArray)
    const optionsSpace = {
      title,
      is3D: true,
    }

    // eslint-disable-next-line new-cap
    const chart = new GoogleCharts.api.visualization.PieChart(document.getElementById(elementId))
    chart.draw(dataSpace, optionsSpace)
  }
}
