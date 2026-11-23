// Docs page entrypoint -- UI logic handled by the Stimulus controller (docs)
import './DocsPage.css'
import { app } from '../bootstrap'
import DocsController from '../controllers/docs/docs_controller'

app.register('docs', DocsController)
