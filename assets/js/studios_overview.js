// import $ from 'jquery'
import { MDCMenu } from '@material/menu'

require('../styles/custom/studios.scss')

const menus = []

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.studios-list-item .mdc-menu').forEach((el) => {
    const id = el.dataset.studioId
    if (id) {
      menus[id] = new MDCMenu(el)
      for (const child of el.children[0].children) {
        child.addEventListener('click', (ev) => {
          ev.preventDefault()
        })
      }
    }
  })

  document
    .querySelectorAll('.studios-list-item .mdc-icon-button')
    .forEach((el) => {
      el.addEventListener('click', (ev) => {
        ev.preventDefault()
        const id = el.dataset.studioId
        menus[id].open = menus[id].open ? !menus[id].open : true
      })
    })
})
