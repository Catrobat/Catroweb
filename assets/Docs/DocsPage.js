// Docs page entrypoint -- UI logic handled by the Stimulus controllers (docs, docs--comments)
import './DocsPage.css'
import { app } from '../bootstrap'
import CommentsController from '../controllers/docs/comments_controller'
import DocsController from '../controllers/docs/docs_controller'

app.register('docs', DocsController)
app.register('docs--comments', CommentsController)
