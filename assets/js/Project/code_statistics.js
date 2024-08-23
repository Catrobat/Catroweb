import { Collapse } from 'bootstrap'
import '../../styles/Project/code_statistics.scss'

document.addEventListener('DOMContentLoaded', () => {
  const accordionElement = document.getElementById('accordionStatistics')
  new Collapse(accordionElement)
})
