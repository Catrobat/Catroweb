// Projects browse page entrypoint -- UI logic handled by Stimulus controller (project--browse)
import './ProjectList.css'
import './ProjectsBrowse.css'
import { app } from '../bootstrap'
import BrowseController from '../controllers/project/browse_controller'

// assets/bootstrap.js registers no controllers; each page registers its own.
app.register('project--browse', BrowseController)
