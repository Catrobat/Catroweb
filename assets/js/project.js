import { MDCTextField } from '@material/textfield'
import $ from 'jquery'
import './components/fullscreen_list_modal'
import { TranslateProgram } from './custom/TranslateProgram'
import { TranslateComments } from './custom/TranslateComments'
import { ProjectList } from './components/project_list'
import { setImageUploadListener } from './custom/ImageUpload'
import { Program } from './custom/Program'
import { shareLink } from './custom/ShareLink'
import { ProgramReport } from './custom/ProgramReport'
import { ProgramDescription } from './custom/ProgramDescription'
import { ProgramCredits } from './custom/ProgramCredits'
import { ProgramComments } from './custom/ProgramComments'
import { CustomTranslationApi } from './api/CustomTranslationApi'
import { ProjectEditorNavigation } from './components/ProjectEditorNavigation'
import { ProjectEditor } from './components/ProjectEditor'
import { ProjectEditorTextField } from './components/ProjectEditorTextField'
import { ProgramName } from './custom/ProgramName'
import { ProjectEditorTextFieldModel } from './components/ProjectEditorTextFieldModel'
import { ProjectEditorModel } from './components/ProjectEditorModel'

require('../styles/custom/profile.scss')
require('../styles/custom/program.scss')

const $project = $('.js-project')
const $projectShare = $('.js-project-share')
const $projectReport = $('.js-project-report')
const $projectDescriptionCredits = $('.js-project-description-credits')
const $projectComments = $('.js-project-comments')
const $appLanguage = $('#app-language')

let editorNavigation = null

if ($project.data('my-program')) {
  new MDCTextField(document.querySelector('.comment-message'))

  const nameEditorTextFieldModel = new ProjectEditorTextFieldModel(
    $projectDescriptionCredits,
    $projectDescriptionCredits.data('project-id'),
    'name',
    true,
    $('#name').text().trim()
  )
  new ProjectEditorTextField(nameEditorTextFieldModel)

  const descriptionEditorTextFieldModel = new ProjectEditorTextFieldModel(
    $projectDescriptionCredits,
    $projectDescriptionCredits.data('project-id'),
    'description',
    $projectDescriptionCredits.data('has-description'),
    $('#description').text().trim()
  )
  new ProjectEditorTextField(descriptionEditorTextFieldModel)

  const creditsEditorTextFieldModel = new ProjectEditorTextFieldModel(
    $projectDescriptionCredits,
    $projectDescriptionCredits.data('project-id'),
    'credits',
    $projectDescriptionCredits.data('has-credits'),
    $('#credits').text().trim()
  )
  new ProjectEditorTextField(creditsEditorTextFieldModel)

  const projectEditorModel = new ProjectEditorModel(
    $projectDescriptionCredits.data('project-id'),
    [nameEditorTextFieldModel, descriptionEditorTextFieldModel, creditsEditorTextFieldModel]
  )
  const projectEditor = new ProjectEditor(
    $projectDescriptionCredits,
    $projectDescriptionCredits.data('project-id'),
    projectEditorModel
  )

  editorNavigation = new ProjectEditorNavigation(
    $projectDescriptionCredits,
    $projectDescriptionCredits.data('project-id'),
    projectEditor
  )
}

shareLink(
  $projectShare.data('theme-display-name'),
  $projectShare.data('trans-check-out-project'),
  $projectShare.data('project-url'),
  $projectShare.data('trans-share-success'),
  $projectShare.data('trans-share-error'),
  $projectShare.data('trans-copy'),
  $projectShare.data('trans-clipboard-success'),
  $projectShare.data('trans-clipboard-fail')
)

ProgramReport(
  $projectReport.data('project-id'),
  $projectReport.data('path-report'),
  $projectReport.data('path-login'),
  $projectReport.data('trans-success'),
  $projectReport.data('trans-error'),
  $projectReport.data('trans-report'),
  $projectReport.data('trans-cancel'),
  $projectReport.data('trans-header'),
  $projectReport.data('trans-reason'),
  $projectReport.data('trans-inappropriate'),
  $projectReport.data('trans-copyright'),
  $projectReport.data('trans-spam'),
  $projectReport.data('trans-dislike'),
  $projectReport.data('const-ok'),
  $projectReport.data('logged-in')
)

Program(
  $project.data('project-id'),
  $project.data('project-name'),
  $project.data('csrf-token'),
  $project.data('user-role'),
  $project.data('my-program') === 'true',
  $project.data('path-ci-status'),
  $project.data('path-ci-build'),
  $project.data('path-project-like'),
  $project.data('path-like-details'),
  $project.data('trans-apk-prep'),
  $project.data('trans-apk-text'),
  $project.data('trans-update-app-header'),
  $project.data('trans-update-app-text'),
  $project.data('trans-btn-close'),
  $project.data('const-action-add'),
  $project.data('const-action-remove'),
  $project.data('path-profile'),
  $project.data('asset-wow-white'),
  $project.data('asset-wow-black'),
  $project.data('trans-reaction'),
  $project.data('trans-download-error'),
  $project.data('trans-download-start')
)

ProgramName(
  $projectDescriptionCredits.data('project-id'),
  $appLanguage.data('app-language'),
  $project.data('my-program'),
  new CustomTranslationApi('name'),
  editorNavigation
)

ProgramDescription(
  $projectDescriptionCredits.data('project-id'),
  $appLanguage.data('app-language'),
  $projectDescriptionCredits.data('trans-more-info'),
  $projectDescriptionCredits.data('trans-less-info'),
  $project.data('my-program'),
  new CustomTranslationApi('description')
)

ProgramCredits(
  $projectDescriptionCredits.data('project-id'),
  $appLanguage.data('app-language'),
  $project.data('my-program'),
  new CustomTranslationApi('credit')
)

setImageUploadListener($project.data('path-change-image'), '#change-project-thumbnail-button', '#project-thumbnail-big')

initProjects()

function initProjects () {
  const $recommendedProjects = $('#recommended-projects')
  $('.project-list', $recommendedProjects).each(function () {
    const id = $(this).data('project-id')
    const category = $(this).data('category')
    const property = $(this).data('property')
    const theme = $(this).data('theme')
    const flavor = $(this).data('flavor')
    const baseUrl = $(this).data('base-url')

    let url = baseUrl + '/api/project/' + id + '/recommendations?category=' + category

    if (flavor !== 'pocketcode' || category === 'example') {
      // Only the pocketcode flavor shows projects from all flavors!
      // Other flavors must only show projects from their flavor.
      url += '&flavor=' + flavor
    }

    const list = new ProjectList(this, category, url, property, theme)
    $(this).data('list', list)
  })
}

ProgramComments(
  $projectComments.data('project-id'), 5, 5, 5,
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
  $projectComments.data('trans-default-error-message')
)

new TranslateProgram(
  $project.data('translated-by-line'),
  $project.data('google-translate-display-name'),
  $project.data('project-id'),
  $project.data('has-description'),
  $project.data('has-credits')
)

new TranslateComments($project.data('translated-by-line'), $project.data('google-translate-display-name'))
