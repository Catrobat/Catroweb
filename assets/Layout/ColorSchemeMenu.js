import { MDCMenu } from '@material/menu'
require('../Components/MdcMenu.scss')

const initializeColorSchemeMenu = () => {
  const colorSchemeButton = document.getElementById('top-app-bar__btn-color-scheme')
  const colorSchemeMenuEl = document.getElementById('color-scheme-menu')
  if (!colorSchemeButton || !colorSchemeMenuEl) return

  const colorSchemeMenu = new MDCMenu(colorSchemeMenuEl)

  colorSchemeButton.addEventListener('click', () => {
    colorSchemeMenu.open = true
  })
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeColorSchemeMenu)
} else {
  initializeColorSchemeMenu()
}
