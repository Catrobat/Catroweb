import { ProjectComments } from './ProjectComments'
import { TranslateComments } from './TranslateComments'

require('../../styles/custom/program.scss')

const projectComments = document.querySelector('.js-project-comments')

ProjectComments(
  projectComments.dataset.projectId,
  5,
  5,
  5,
  projectComments.dataset.totalNumberOfComments,
  projectComments.dataset.transCancel,
  projectComments.dataset.transDeleteIt,
  projectComments.dataset.transReportIt,
  projectComments.dataset.transAreYouSure,
  projectComments.dataset.transNoWayOfReturn,
  projectComments.dataset.transDeleteConfirmation,
  projectComments.dataset.transReportConfirmation,
  projectComments.dataset.transPopUpCommentReportedTitle,
  projectComments.dataset.transPopUpCommentReportedText,
  projectComments.dataset.transPopUpDeletedTitle,
  projectComments.dataset.transPopUpDeletedText,
  projectComments.dataset.transNoAdminRightsMessage,
  projectComments.dataset.transDefaultErrorMessage,
)

new TranslateComments(
  projectComments.dataset.translatedByLine,
  projectComments.dataset.googleTranslateDisplayName,
)
