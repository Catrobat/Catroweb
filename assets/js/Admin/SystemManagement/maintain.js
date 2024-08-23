/* global data_space_values */
/* global data_mem_values */
/* global wholeSpace */
/* global wholeRam */
import '../../../styles/Admin/SystemManagement/maintain.scss'
import { PieChart } from '../PieChart'

const pieChart = new PieChart()
pieChart.add(
  'piechart_3d',
  data_space_values,
  'Disk Space (' + wholeSpace + ')',
)
pieChart.add('piechart_3d_mem', data_mem_values, 'RAM (' + wholeRam + ')')
