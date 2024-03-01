import { ProjectComments } from './ProjectComments'
import { TranslateComments } from './TranslateComments'
import $ from 'jquery'

require('../../styles/custom/program.scss')

const $projectComments = $('.js-project-comments')

ProjectComments(
  $projectComments.data('project-id'),
  5,
  5,
  5,
  $projectComments.data('total-number-of-comments'),
  $projectComments.data('trans-cancel'),
  $projectComments.data('trans-delete-it'),
  $projectComments.data('trans-report-it'),
  $projectComments.data('trans-are-you-sure'),
  $projectComments.data('trans-no-way-of-return'),
  $projectComments.data('trans-delete-confirmation'),
  $projectComments.data('trans-report-confirmation'),
  $projectComments.data('trans-pop-up-comment-reported-title'),
  $projectComments.data('trans-pop-up-comment-reported-text'),
  $projectComments.data('trans-pop-up-deleted-title'),
  $projectComments.data('trans-pop-up-deleted-text'),
  $projectComments.data('trans-no-admin-rights-message'),
  $projectComments.data('trans-default-error-message'),
)

new TranslateComments(
  $projectComments.data('translated-by-line'),
  $projectComments.data('google-translate-display-name'),
)
