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
  // Initialize MDCTextField only if a proper mdc-text-field element exists.
  const commentMessageWrapper = document.querySelector('.comment-message')
  if (commentMessageWrapper) {
    // Prefer an explicit .mdc-text-field inside the wrapper, otherwise try the wrapper itself
    const commentMdcRoot =
      commentMessageWrapper.querySelector('.mdc-text-field') ||
      (commentMessageWrapper.classList && commentMessageWrapper.classList.contains('mdc-text-field')
        ? commentMessageWrapper
        : null)
    if (commentMdcRoot) {
      new MDCTextField(commentMdcRoot)
    }
  }

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

// Report project button
const reportBtn = document.getElementById('top-app-bar__btn-report-project')
if (reportBtn) {
  import('../Moderation/ReportDialog').then(({ showReportDialog }) => {
    const buildReportDialogConfig = () => ({
      contentType: reportBtn.dataset.contentType,
      contentId: reportBtn.dataset.contentId,
      apiUrl: reportBtn.dataset.reportUrl,
      loginUrl: reportBtn.dataset.loginUrl,
      isLoggedIn: reportBtn.dataset.loggedIn === 'true',
      translations: {
        title: reportBtn.dataset.transReportTitle,
        submit: reportBtn.dataset.transReportSubmit,
        cancel: reportBtn.dataset.transReportCancel,
        success: reportBtn.dataset.transReportSuccess,
        error: reportBtn.dataset.transReportError,
        duplicate: reportBtn.dataset.transReportDuplicate,
        trustTooLow: reportBtn.dataset.transReportTrustTooLow,
        unverified: reportBtn.dataset.transReportUnverified,
        suspended: reportBtn.dataset.transReportSuspended,
        rateLimited: reportBtn.dataset.transReportRateLimited,
        notePlaceholder: reportBtn.dataset.transReportPlaceholder,
      },
    })

    reportBtn.addEventListener('click', () => {
      showReportDialog(buildReportDialogConfig())
    })

    // Re-open one pending report handoff once after login on the matching content page.
    if (reportBtn.dataset.loggedIn === 'true') {
      const pending = sessionStorage.getItem('pendingAction')
      if (pending) {
        try {
          const pendingAction = JSON.parse(pending)
          const isMatchingReportHandoff =
            pendingAction?.actionType === 'report' &&
            String(pendingAction?.contentType || '') ===
              String(reportBtn.dataset.contentType || '') &&
            String(pendingAction?.contentId || '') === String(reportBtn.dataset.contentId || '')

          if (isMatchingReportHandoff) {
            sessionStorage.removeItem('pendingAction')
            showReportDialog(buildReportDialogConfig())
          }
        } catch (e) {
          console.error('Failed to parse pending report handoff', e)
          sessionStorage.removeItem('pendingAction')
        }
      }
    }
  })
}

// Appeal button for auto-hidden projects
const appealBtn = document.getElementById('btn-appeal-project')
if (appealBtn) {
  import('../Moderation/AppealDialog').then(({ showAppealDialog }) => {
    appealBtn.addEventListener('click', () => {
      showAppealDialog({
        contentType: appealBtn.dataset.contentType,
        contentId: appealBtn.dataset.contentId,
        apiUrl: appealBtn.dataset.appealUrl,
        translations: {
          title: appealBtn.dataset.transAppealTitle,
          placeholder: appealBtn.dataset.transAppealPlaceholder,
          submit: appealBtn.dataset.transAppealSubmit,
          cancel: appealBtn.dataset.transAppealCancel,
          success: appealBtn.dataset.transAppealSuccess,
          alreadyPending: appealBtn.dataset.transAppealAlreadyPending,
          error: appealBtn.dataset.transAppealError,
          rateLimited: appealBtn.dataset.transAppealRateLimited,
        },
      })
    })
  })
}

Project(
  projectElement.dataset.projectId,
  projectElement.dataset.projectName,
  projectElement.dataset.userRole,
  projectElement.dataset.myProject === 'true',
  projectElement.dataset.pathCiStatus,
  projectElement.dataset.pathCiBuild,
  projectElement.dataset.loginUrl,
  projectElement.dataset.apiReactionUrl,
  projectElement.dataset.apiReactionsUrl,
  projectElement.dataset.apiReactionsUsersUrl,
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

    new ProjectList(element, category, url, property, theme)
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
