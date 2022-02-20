import { MDCTextField } from '@material/textfield'
import $ from 'jquery'
import './components/fullscreen_list_modal'
import { TranslateProgram } from './custom/TranslateProgram'
import { TranslateComments } from './custom/TranslateComments'
import { ProjectList } from './components/project_list'
import { setImageUploadListener } from './custom/ImageUpload'
import { Program } from './custom/Program'
import { shareProject } from './custom/ProgramShare'
import { ProgramReport } from './custom/ProgramReport'
import { ProgramDescription } from './custom/ProgramDescription'
import { ProgramCredits } from './custom/ProgramCredits'
import { ProgramComments } from './custom/ProgramComments'
import { CustomTranslationApi } from './api/CustomTranslationApi'
import { ProjectEditor } from './components/ProjectEditor'
import { ProjectEditorTextField } from './components/ProjectEditorTextField'
import { ProgramName } from './custom/ProgramName'

require('../styles/custom/profile.scss')
require('../styles/custom/program.scss')

const $project = $('.js-project')
const $projectShare = $('.js-project-share')
const $projectReport = $('.js-project-report')
const $projectDescriptionCredits = $('.js-project-description-credits')
const $projectComments = $('.js-project-comments')
const $appLanguage = $('#app-language')

let editor = null

if ($project.data('my-program')) {
  new MDCTextField(document.querySelector('.comment-message'))

  const nameEditorTextField = new ProjectEditorTextField(
    $projectDescriptionCredits,
    $projectDescriptionCredits.data('project-id'),
    'name',
    true
  )

  const descriptionEditorTextField = new ProjectEditorTextField(
    $projectDescriptionCredits,
    $projectDescriptionCredits.data('project-id'),
    'description',
    $projectDescriptionCredits.data('has-description')
  )

  const creditsEditorTextField = new ProjectEditorTextField(
    $projectDescriptionCredits,
    $projectDescriptionCredits.data('project-id'),
    'credits',
    $projectDescriptionCredits.data('has-credits')
  )

  const showLanguageSelect = $projectDescriptionCredits.data('has-description') || $projectDescriptionCredits.data('has-credits')

  editor = new ProjectEditor(
    $projectDescriptionCredits,
    $projectDescriptionCredits.data('project-id'),
    [nameEditorTextField, descriptionEditorTextField, creditsEditorTextField],
    showLanguageSelect,
    $projectDescriptionCredits.data('trans-default')
  )
}

shareProject(
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
  editor
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
