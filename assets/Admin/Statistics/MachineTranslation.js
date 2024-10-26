/* global providerData */
import { PieChart } from '../PieChart'

const pieChart = new PieChart()
pieChart.add('piechart_3d', providerData, 'Provider breakdown')

document.addEventListener('click', function (event) {
  if (event.target && event.target.id === 'delete-button') {
    event.preventDefault()
    document.getElementById('delete-button').style.display = 'none'
    document.getElementById('confirmation').style.display = 'block'
  }
})
