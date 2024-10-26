import { MDCMenu } from '@material/menu'
import { showSnackbar } from '../Layout/Snackbar'
require('./Studios.scss')

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

  document.querySelectorAll('.studios-list-item .mdc-icon-button').forEach((el) => {
    el.addEventListener('click', (ev) => {
      ev.preventDefault()
      const id = el.dataset.studioId
      menus[id].open = menus[id].open ? !menus[id].open : true
    })
  })

  document.querySelectorAll('.ajaxRequestJoinLeaveReport').forEach((el) => {
    el.addEventListener('click', (event) => {
      event.preventDefault()
      const url = el.getAttribute('data-url')
      makeAjaxRequest(url)
    })
  })
})
function makeAjaxRequest(url) {
  fetch(url, {
    method: 'POST',
  })
    .then((response) => {
      if (!response.ok) {
        console.error('There was a problem with the server.')
      } else {
        return response.json()
      }
    })
    .then((data) => {
      if (!data) {
        console.error('There was a problem with the server.')
      } else {
        showSnackbar('#share-snackbar', data.message.toString())
        window.location.reload()
      }
    })
    .catch((error) => {
      console.error('There was an error with the fetch operation:', error)
    })
}
