import { Collapse } from 'bootstrap'
import './CodeStatistics.scss'

document.addEventListener('DOMContentLoaded', () => {
  const accordionElement = document.getElementById('accordionStatistics')
  new Collapse(accordionElement)
})
