// Studios overview page entrypoint — UI logic handled by Stimulus controller (studio--overview)
import '@material/web/iconbutton/icon-button.js'
import '@material/web/menu/menu-item.js'
import '@material/web/menu/menu.js'
import { app } from '../bootstrap'
import OverviewController from '../controllers/studio/overview_controller'

import './Studios.css'
import '../Project/ProjectList.css'

for (const button of document.querySelectorAll('[id^="studios-list-item--button-"]')) {
  button.addEventListener('click', (event) => {
    event.preventDefault()
    event.stopPropagation()
    const menu = document.getElementById(button.id.replace('--button-', '--menu-'))
    if (menu) menu.open = true
  })
}

// assets/bootstrap.js registers no controllers; each page registers its own.
app.register('studio--overview', OverviewController)
