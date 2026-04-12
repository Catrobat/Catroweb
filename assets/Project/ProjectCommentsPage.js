import { ProjectComments } from './ProjectComments'
import { TranslateComments } from '../Translate/TranslateComments'

require('./ProjectPage.scss')

const projectComments = document.querySelector('.js-project-comments')

ProjectComments({
  showStep: 5,
  minAmountOfVisibleComments: 5,
  cancel: projectComments.dataset.transCancel,
  deleteIt: projectComments.dataset.transDeleteIt,
  areYouSure: projectComments.dataset.transAreYouSure,
  noWayOfReturn: projectComments.dataset.transNoWayOfReturn,
  deleteConfirmation: projectComments.dataset.transDeleteConfirmation,
  popUpDeletedTitle: projectComments.dataset.transPopUpDeletedTitle,
  popUpDeletedText: projectComments.dataset.transPopUpDeletedText,
  noAdminRightsMessage: projectComments.dataset.transNoAdminRightsMessage,
  defaultErrorMessage: projectComments.dataset.transDefaultErrorMessage,
})

new TranslateComments(
  projectComments.dataset.translatedByLine,
  projectComments.dataset.googleTranslateDisplayName,
)
