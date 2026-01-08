import { MDCMenu } from '@material/menu'
require('../Components/MdcMenu.scss')

window.addEventListener('DOMContentLoaded', () => {
  const colorSchemeButton = document.getElementById('top-app-bar__btn-color-scheme')
  const colorSchemeMenuEl = document.getElementById('color-scheme-menu')
  const colorSchemeMenu = new MDCMenu(colorSchemeMenuEl)

  colorSchemeButton.addEventListener('click', () => {
    colorSchemeMenu.open = true
  })
})
