// Projects browse page entrypoint -- UI logic handled by Stimulus controller (project--browse)
import './ProjectList.css'
import './ProjectsBrowse.css'
// Stimulus loads controllers lazily; import this page's own so it downloads with the page, not one round trip later.
import '../controllers/project/browse_controller'
