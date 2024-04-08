/* global globalConfiguration */
/* global projectConfiguration */

import { MDCTextField } from '@material/textfield'
import $ from 'jquery'
import './components/fullscreen_list_modal'
import { TranslateProgram } from './custom/TranslateProgram'
import { TranslateComments } from './custom/TranslateComments'
import { ProjectList } from './components/project_list'
import { Project } from './custom/Project'
import { shareLink } from './custom/ShareLink'
import { ProjectDescription } from './custom/ProjectDescription'
import { ProjectCredits } from './custom/ProjectCredits'
import { ProjectComments } from './custom/ProjectComments'
import { CustomTranslationApi } from './api/CustomTranslationApi'
import { ProjectEditorNavigation } from './components/ProjectEditorNavigation'
import { ProjectEditor } from './components/ProjectEditor'
import { ProjectEditorTextField } from './components/ProjectEditorTextField'
import { ProjectName } from './custom/ProjectName'
import ProjectApi from './api/ProjectApi'
import { ProjectEditorTextFieldModel } from './components/ProjectEditorTextFieldModel'
import { ProjectEditorModel } from './components/ProjectEditorModel'
import MessageDialogs from './components/MessageDialogs'

require('../styles/custom/program.scss')

const $project = $('.js-project')
const $projectShare = $('.js-project-share')
const $projectDescriptionCredits = $('.js-project-description-credits')
const $projectComments = $('.js-project-comments')
const $appLanguage = $('#app-language')

let editorNavigation = null

if ($project.data('my-project')) {
  new MDCTextField(document.querySelector('.comment-message'))

  const nameEditorTextFieldModel = new ProjectEditorTextFieldModel(
    $projectDescriptionCredits,
    $projectDescriptionCredits.data('project-id'),
    'name',
    true,
    $('#name').text().trim(),
  )
  new ProjectEditorTextField(nameEditorTextFieldModel)

  const descriptionEditorTextFieldModel = new ProjectEditorTextFieldModel(
    $projectDescriptionCredits,
    $projectDescriptionCredits.data('project-id'),
    'description',
    $projectDescriptionCredits.data('has-description'),
    $('#description').text().trim(),
  )
  new ProjectEditorTextField(descriptionEditorTextFieldModel)

  const creditsEditorTextFieldModel = new ProjectEditorTextFieldModel(
    $projectDescriptionCredits,
    $projectDescriptionCredits.data('project-id'),
    'credits',
    $projectDescriptionCredits.data('has-credits'),
    $('#credits').text().trim(),
  )
  new ProjectEditorTextField(creditsEditorTextFieldModel)

  const projectEditorModel = new ProjectEditorModel(
    $projectDescriptionCredits.data('project-id'),
    [
      nameEditorTextFieldModel,
      descriptionEditorTextFieldModel,
      creditsEditorTextFieldModel,
    ],
  )
  const projectEditor = new ProjectEditor(
    $projectDescriptionCredits,
    $projectDescriptionCredits.data('project-id'),
    projectEditorModel,
  )

  editorNavigation = new ProjectEditorNavigation(
    $projectDescriptionCredits,
    $projectDescriptionCredits.data('project-id'),
    projectEditor,
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
  $projectShare.data('trans-clipboard-fail'),
)

/* TODO: Disable Report Project for now. Needs a separate flag in database - a new concept!
ProjectReport(
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
*/

Project(
  $project.data('project-id'),
  $project.data('project-name'),
  $project.data('user-role'),
  $project.data('my-project') === 'true',
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
  $project.data('trans-download-start'),
)

ProjectName(
  $projectDescriptionCredits.data('project-id'),
  $appLanguage.data('app-language'),
  $project.data('my-project'),
  new CustomTranslationApi('name'),
  editorNavigation,
)

ProjectDescription(
  $projectDescriptionCredits.data('project-id'),
  $appLanguage.data('app-language'),
  $projectDescriptionCredits.data('trans-more-info'),
  $projectDescriptionCredits.data('trans-less-info'),
  $project.data('my-project'),
  new CustomTranslationApi('description'),
)

ProjectCredits(
  $projectDescriptionCredits.data('project-id'),
  $appLanguage.data('app-language'),
  $project.data('my-project'),
  new CustomTranslationApi('credit'),
)

initProjectScreenshotUpload()

function initProjectScreenshotUpload() {
  const addChangeListenerToFileInput = function (input) {
    input.onchange = () => {
      document.getElementById('upload-image-spinner').classList.remove('d-none')

      const reader = new window.FileReader()
      reader.onerror = () => {
        document.getElementById('upload-image-spinner').classList.add('d-none')
        MessageDialogs.showErrorMessage(
          projectConfiguration.messages.screenshotInvalid,
        )
      }
      reader.onload = (event) => {
        const image = event.currentTarget.result // base64 data url
        const projectApi = new ProjectApi()
        projectApi.updateProject(
          $project.data('project-id'),
          { screenshot: image },
          function () {
            const imageElement = document.getElementById(
              'project-thumbnail-big',
            )
            if (imageElement.src.includes('?')) {
              imageElement.src += '&x=' + new Date().getTime()
            } else {
              imageElement.src += '?x=' + new Date().getTime()
            }
            document
              .querySelector('.text-img-upload-success')
              .classList.remove('d-none')
            setTimeout(function () {
              document
                .querySelector('.text-img-upload-success')
                .classList.add('d-none')
            }, 3000)
          },
          function () {
            document
              .getElementById('upload-image-spinner')
              .classList.add('d-none')
          },
        )
      }
      reader.readAsDataURL(input.files[0])
    }
  }
  const changeButton = document.getElementById(
    'change-project-thumbnail-button',
  )
  if (changeButton) {
    // otherwise user is not allowed to change screenshot (e.g., not owner of project)
    changeButton.addEventListener('click', function () {
      const input = document.createElement('input')
      input.type = 'file'
      input.accept = 'image/*'
      addChangeListenerToFileInput(input)
      input.click()
    })

    if (globalConfiguration.environment === 'test') {
      const input = document.createElement('input')
      input.type = 'file'
      input.accept = 'image/*'
      addChangeListenerToFileInput(input)
      input.name = 'project-screenshot-upload-field'
      input.className = 'd-none'
      changeButton.parentElement.appendChild(input)
    }
  }
}

initProjects()

function initProjects() {
  const $recommendedProjects = $('#recommended-projects')
  $('.project-list', $recommendedProjects).each(function () {
    const id = $(this).data('project-id')
    const category = $(this).data('category')
    const property = $(this).data('property')
    const theme = $(this).data('theme')
    const flavor = $(this).data('flavor')
    const baseUrl = $(this).data('base-url')

    let url =
      baseUrl + '/api/project/' + id + '/recommendations?category=' + category

    if (flavor !== 'pocketcode' || category === 'example') {
      // Only the pocketcode flavor shows projects from all flavors!
      // Other flavors must only show projects from their flavor.
      url += '&flavor=' + flavor
    }

    const list = new ProjectList(this, category, url, property, theme)
    $(this).data('list', list)
  })
}

new TranslateComments(
  $project.data('translated-by-line'),
  $project.data('google-translate-display-name'),
)

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

new TranslateProgram(
  $project.data('translated-by-line'),
  $project.data('google-translate-display-name'),
  $project.data('project-id'),
  $project.data('has-description'),
  $project.data('has-credits'),
)
