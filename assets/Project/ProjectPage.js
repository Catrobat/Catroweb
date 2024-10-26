/* global globalConfiguration */
/* global projectConfiguration */

import { MDCTextField } from '@material/textfield'
import '../Components/FullscreenListModal'
import { TranslateProgram } from '../Translate/TranslateProgram'
import { TranslateComments } from '../Translate/TranslateComments'
import { ProjectList } from './ProjectList'
import { Project } from './Project'
import { shareLink } from '../Components/ShareLink'
import { ProjectDescription } from './ProjectDescription'
import { ProjectCredits } from './ProjectCredits'
import { ProjectComments } from './ProjectComments'
import { CustomTranslationApi } from '../Api/CustomTranslationApi'
import { ProjectEditorNavigation } from './ProjectEditorNavigation'
import { ProjectEditor } from './ProjectEditor'
import { ProjectEditorTextField } from './ProjectEditorTextField'
import { ProjectName } from './ProjectName'
import ProjectApi from '../Api/ProjectApi'
import { ProjectEditorTextFieldModel } from './ProjectEditorTextFieldModel'
import { ProjectEditorModel } from './ProjectEditorModel'
import MessageDialogs from '../Components/MessageDialogs'

require('./ProjectPage.scss')

const projectElement = document.querySelector('.js-project')
const projectShareElement = document.querySelector('.js-project-share')
const projectDescriptionCreditsElement = document.querySelector('.js-project-description-credits')
const projectCommentsElement = document.querySelector('.js-project-comments')
const appLanguageElement = document.querySelector('#app-language')

let editorNavigation = null

if (projectElement.dataset.myProject === 'true') {
  new MDCTextField(document.querySelector('.comment-message'))

  const nameEditorTextFieldModel = new ProjectEditorTextFieldModel(
    projectDescriptionCreditsElement.dataset.projectId,
    'name',
    true,
    document.querySelector('#name').textContent.trim(),
  )
  new ProjectEditorTextField(nameEditorTextFieldModel)

  const descriptionEditorTextFieldModel = new ProjectEditorTextFieldModel(
    projectDescriptionCreditsElement.dataset.projectId,
    'description',
    projectDescriptionCreditsElement.dataset.hasDescription === 'true',
    document.querySelector('#description').textContent.trim(),
  )
  new ProjectEditorTextField(descriptionEditorTextFieldModel)

  const creditsEditorTextFieldModel = new ProjectEditorTextFieldModel(
    projectDescriptionCreditsElement.dataset.projectId,
    'credits',
    projectDescriptionCreditsElement.dataset.hasCredits === 'true',
    document.querySelector('#credits').textContent.trim(),
  )
  new ProjectEditorTextField(creditsEditorTextFieldModel)

  const projectEditorModel = new ProjectEditorModel(
    projectDescriptionCreditsElement.dataset.projectId,
    [nameEditorTextFieldModel, descriptionEditorTextFieldModel, creditsEditorTextFieldModel],
  )
  const projectEditor = new ProjectEditor(
    projectDescriptionCreditsElement,
    projectDescriptionCreditsElement.dataset.projectId,
    projectEditorModel,
  )

  editorNavigation = new ProjectEditorNavigation(
    projectDescriptionCreditsElement,
    projectDescriptionCreditsElement.dataset.projectId,
    projectEditor,
  )
}

shareLink(
  projectShareElement.dataset.themeDisplayName,
  projectShareElement.dataset.transCheckOutProject,
  projectShareElement.dataset.projectUrl,
  projectShareElement.dataset.transShareSuccess,
  projectShareElement.dataset.transShareError,
  projectShareElement.dataset.transCopy,
  projectShareElement.dataset.transClipboardSuccess,
  projectShareElement.dataset.transClipboardFail,
)

/* TODO: Disable Report Project for now. Needs a separate flag in database - a new concept!
ProjectReport(
  projectReport.dataset.projectId,
  projectReport.dataset.pathReport,
  projectReport.dataset.pathLogin,
  projectReport.dataset.transSuccess,
  projectReport.dataset.transError,
  projectReport.dataset.transReport,
  projectReport.dataset.transCancel,
  projectReport.dataset.transHeader,
  projectReport.dataset.transReason,
  projectReport.dataset.transInappropriate,
  projectReport.dataset.transCopyright,
  projectReport.dataset.transSpam,
  projectReport.dataset.transDislike,
  projectReport.dataset.constOk,
  projectReport.dataset.loggedIn
)
*/

Project(
  projectElement.dataset.projectId,
  projectElement.dataset.projectName,
  projectElement.dataset.userRole,
  projectElement.dataset.myProject === 'true',
  projectElement.dataset.pathCiStatus,
  projectElement.dataset.pathCiBuild,
  projectElement.dataset.pathProjectLike,
  projectElement.dataset.pathLikeDetails,
  projectElement.dataset.transApkPrep,
  projectElement.dataset.transApkText,
  projectElement.dataset.transUpdateAppHeader,
  projectElement.dataset.transUpdateAppText,
  projectElement.dataset.transBtnClose,
  projectElement.dataset.constActionAdd,
  projectElement.dataset.constActionRemove,
  projectElement.dataset.pathProfile,
  projectElement.dataset.assetWowWhite,
  projectElement.dataset.assetWowBlack,
  projectElement.dataset.transReaction,
  projectElement.dataset.transDownloadError,
  projectElement.dataset.transDownloadStart,
)

ProjectName(
  projectDescriptionCreditsElement.dataset.projectId,
  appLanguageElement.dataset.appLanguage,
  projectElement.dataset.myProject === 'true',
  new CustomTranslationApi('name'),
  editorNavigation,
)

ProjectDescription(
  projectDescriptionCreditsElement.dataset.projectId,
  appLanguageElement.dataset.appLanguage,
  projectDescriptionCreditsElement.dataset.transMoreInfo,
  projectDescriptionCreditsElement.dataset.transLessInfo,
  projectElement.dataset.myProject === 'true',
  new CustomTranslationApi('description'),
)

ProjectCredits(
  projectDescriptionCreditsElement.dataset.projectId,
  appLanguageElement.dataset.appLanguage,
  projectElement.dataset.myProject === 'true',
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
        MessageDialogs.showErrorMessage(projectConfiguration.messages.screenshotInvalid)
      }
      reader.onload = (event) => {
        const image = event.currentTarget.result // base64 data url
        const projectApi = new ProjectApi()
        projectApi.updateProject(
          projectElement.dataset.projectId,
          { screenshot: image },
          function () {
            const imageElement = document.getElementById('project-thumbnail-big')
            if (imageElement.src.includes('?')) {
              imageElement.src += '&x=' + new Date().getTime()
            } else {
              imageElement.src += '?x=' + new Date().getTime()
            }
            document.querySelector('.text-img-upload-success').classList.remove('d-none')
            setTimeout(function () {
              document.querySelector('.text-img-upload-success').classList.add('d-none')
            }, 3000)
          },
          function () {
            document.getElementById('upload-image-spinner').classList.add('d-none')
          },
        )
      }
      reader.readAsDataURL(input.files[0])
    }
  }
  const changeButton = document.getElementById('change-project-thumbnail-button')
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
  const recommendedProjectsElement = document.querySelector('#recommended-projects')
  document.querySelectorAll('.project-list', recommendedProjectsElement).forEach((element) => {
    const id = element.dataset.projectId
    const category = element.dataset.category
    const property = element.dataset.property
    const theme = element.dataset.theme
    const flavor = element.dataset.flavor
    const baseUrl = element.dataset.baseUrl

    let url = `${baseUrl}/api/project/${id}/recommendations?category=${category}`

    if (flavor !== 'pocketcode' || category === 'example') {
      // Only the pocketcode flavor shows projects from all flavors!
      // Other flavors must only show projects from their flavor.
      url += `&flavor=${flavor}`
    }

    const list = new ProjectList(element, category, url, property, theme)
    element.dataset.list = list
  })
}

new TranslateComments(
  projectElement.dataset.translatedByLine,
  projectElement.dataset.googleTranslateDisplayName,
)

ProjectComments(
  projectCommentsElement.dataset.projectId,
  5,
  5,
  5,
  projectCommentsElement.dataset.totalNumberOfComments,
  projectCommentsElement.dataset.transCancel,
  projectCommentsElement.dataset.transDeleteIt,
  projectCommentsElement.dataset.transReportIt,
  projectCommentsElement.dataset.transAreYouSure,
  projectCommentsElement.dataset.transNoWayOfReturn,
  projectCommentsElement.dataset.transDeleteConfirmation,
  projectCommentsElement.dataset.transReportConfirmation,
  projectCommentsElement.dataset.transPopUpCommentReportedTitle,
  projectCommentsElement.dataset.transPopUpCommentReportedText,
  projectCommentsElement.dataset.transPopUpDeletedTitle,
  projectCommentsElement.dataset.transPopUpDeletedText,
  projectCommentsElement.dataset.transNoAdminRightsMessage,
  projectCommentsElement.dataset.transDefaultErrorMessage,
)

new TranslateProgram(
  projectElement.dataset.translatedByLine,
  projectElement.dataset.googleTranslateDisplayName,
  projectElement.dataset.projectId,
  projectElement.dataset.hasDescription === 'true',
  projectElement.dataset.hasCredits === 'true',
)
