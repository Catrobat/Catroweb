import { Chart, PieController, ArcElement, Tooltip, Legend, Title } from 'chart.js'

Chart.register(PieController, ArcElement, Tooltip, Legend, Title)

export class PieChart {
  add(elementId, dataArray, title) {
    const container = document.getElementById(elementId)
    if (!container) {
      return
    }

    container.innerHTML = ''
    const canvas = document.createElement('canvas')
    container.appendChild(canvas)

    const labels = dataArray.slice(1).map((row) => row[0])
    const values = dataArray.slice(1).map((row) => row[1])

    new Chart(canvas, {
      type: 'pie',
      data: {
        labels,
        datasets: [
          {
            data: values,
            backgroundColor: [
              '#3366cc',
              '#dc3912',
              '#ff9900',
              '#109618',
              '#990099',
              '#0099c6',
              '#dd4477',
              '#66aa00',
              '#b82e2e',
              '#316395',
            ],
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          title: {
            display: true,
            text: title,
          },
        },
      },
    })
  }
}
