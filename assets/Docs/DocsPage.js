// Docs page entrypoint -- UI logic handled by the Stimulus controllers (docs, docs--comments, docs--brick-examples)
import '../Project/CodeViewInline.css'
import './DocsPage.css'
import { app } from '../bootstrap'
import BrickExamplesController from '../controllers/docs/brick-examples_controller'
import CommentsController from '../controllers/docs/comments_controller'
import DocsController from '../controllers/docs/docs_controller'

app.register('docs', DocsController)
app.register('docs--comments', CommentsController)
app.register('docs--brick-examples', BrickExamplesController)
