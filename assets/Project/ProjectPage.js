/* global globalConfiguration */
/* global projectConfiguration */

import { MDCTextField } from '@material/textfield'
import '../Components/FullscreenListModal'
import { TranslateProject } from '../Translate/TranslateProject'
import { TranslateComments } from '../Translate/TranslateComments'
import { ProjectList } from './ProjectList'
import { Project } from './Project'

import { ProjectDescription } from './ProjectDescription'
import { ProjectCredits } from './ProjectCredits'
import { ProjectComments } from './ProjectComments'
import { CustomTranslationApi } from '../Api/CustomTranslationApi'
import { ProjectEditorNavigation } from './ProjectEditorNavigation'
import { ProjectEditor } from './ProjectEditor'
import { ProjectEditorTextField } from './ProjectEditorTextField'
import { ProjectName } from './ProjectName'
import { ProjectEditorTextFieldModel } from './ProjectEditorTextFieldModel'
import { ProjectEditorModel } from './ProjectEditorModel'
import { escapeAttr, escapeHtml } from '../Components/HtmlEscape'
import { showSnackbar, SnackbarDuration } from '../Layout/Snackbar'
import { updatePictureSources } from '../Layout/ImageVariants'
import { prepareImageFileForUpload, readFileAsDataUrl } from '../Components/ImageUploadHelper'
import './RemixGraphInline'

require('./ProjectPage.scss')

const projectElement = document.querySelector('.js-project')
const projectDescriptionCreditsElement = document.querySelector('.js-project-description-credits')
const projectCommentsElement = document.querySelector('.js-project-comments')
const appLanguageElement = document.querySelector('#app-language')
const projectId = projectElement.dataset.projectId
const baseUrl = projectElement.dataset.baseUrl
const isMyProject = projectElement.dataset.myProject === 'true'
const isWebview = projectElement.dataset.isWebview === 'true'

// Fetch project data from API, then render and init all components
// Start independent fetches immediately (don't wait for project data)
const earlyInits = initEarlyComponents()

// Project data fetch — renders metadata, then inits data-dependent components
fetchProjectData().then((data) => {
  if (!data) {
    removeSkeletons()
    return
  }
  renderProjectMetadata(data)
  initComponents(data, earlyInits)
})

function fetchProjectData() {
  const url = `${baseUrl}/api/projects/${encodeURIComponent(projectId)}?attributes=ALL`
  return fetch(url, {
    method: 'GET',
    headers: { Accept: 'application/json' },
  })
    .then((response) => {
      if (!response.ok) {
        console.error('Failed to fetch project data:', response.status)
        return null
      }
      return response.json()
    })
    .catch((error) => {
      console.error('Error fetching project data:', error)
      return null
    })
}

const RETENTION_PROTECTED = -1
const MS_PER_DAY = 86400000
const RETENTION_WARN_DAYS = 7
const RETENTION_CAUTION_DAYS = 30

function formatFilesize(filesize) {
  return (filesize != null ? filesize.toFixed(2) : '0.00') + ' MB'
}

function initEarlyComponents() {
  // Shared languages fetch — used by both EditorNavigation and EditorModel
  const routing = document.getElementById('js-api-routing')
  const languagesUrl = routing ? routing.dataset.languages : '/languages'
  const languagesPromise = isMyProject
    ? fetch(languagesUrl)
        .then((r) => r.json())
        .catch(() => ({}))
    : null

  return { languagesPromise }
}

function removeSkeletons() {
  document.querySelectorAll('.js-skeleton').forEach((el) => el.remove())
}

function renderProjectMetadata(data) {
  // Screenshot — server-rendered, API updates if changed
  const thumbnail = document.getElementById('project-thumbnail-big')
  if (thumbnail) {
    updatePictureSources(thumbnail, data.screenshot, 'detail', thumbnail.getAttribute('src'))
    if (data.not_for_kids) {
      thumbnail.classList.add('blurred')
    }
    // Thumbnail badge overlays (lock + not-for-kids)
    const badgesContainer = document.getElementById('project-thumbnail-badges')
    if (badgesContainer) {
      if (data.private) {
        const lockIcon = document.createElement('i')
        lockIcon.className = 'material-icons project-thumbnail-badge project-thumbnail-badge--lock'
        lockIcon.textContent = 'lock'
        badgesContainer.appendChild(lockIcon)
      }
      if (data.not_for_kids) {
        const nfkIcon = document.createElement('i')
        nfkIcon.className = 'material-icons project-thumbnail-badge project-thumbnail-badge--nfk'
        nfkIcon.textContent = 'no_accounts'
        nfkIcon.title = projectElement.dataset.transNotForKids || 'Not for kids'
        badgesContainer.appendChild(nfkIcon)
      }
    }
    // In webview, wrap thumbnail with download link
    if (isWebview && data.download_url && !thumbnail.parentElement.closest('a')) {
      const link = document.createElement('a')
      link.href = data.download_url
      thumbnail.parentElement.insertBefore(link, thumbnail)
      link.appendChild(thumbnail)
    }
  }

  // Project name — server-rendered, API updates if changed
  const nameEl = document.getElementById('name')
  if (nameEl) {
    nameEl.textContent = data.name || ''
  }

  // Author — server-rendered, API updates if changed
  const authorLink = document.getElementById('project-owner-username')
  const authorText = document.getElementById('project-owner-username-text')
  if (authorLink && data.author_id) {
    const profilePath = projectElement.dataset.pathProfile.replace('USERID', data.author_id)
    authorLink.href = profilePath
    if (authorText) {
      authorText.textContent = data.author || ''
    }
  }

  // Age (uploaded_string)
  const ageEl = document.getElementById('project-age')
  if (ageEl) {
    ageEl.textContent = data.uploaded_string || ''
  }

  // Filesize
  const filesizeEl = document.getElementById('project-filesize')
  if (filesizeEl) {
    filesizeEl.textContent = formatFilesize(data.filesize)
  }

  // Retention badge
  renderRetentionBadge(data)

  // Not-for-kids indicator is now shown as a thumbnail badge overlay (see renderProjectMetadata)

  // Description
  const descEl = document.getElementById('description')
  if (descEl) {
    if (data.description) {
      descEl.innerHTML = escapeHtml(data.description).replace(/\n/g, '<br>')
    } else {
      descEl.textContent = projectElement.dataset.transNoDescription || 'No description available.'
    }
  }

  // Credits
  const creditsEl = document.getElementById('credits')
  if (creditsEl) {
    if (data.credits || data.scratch_id) {
      let creditsHtml = ''
      if (data.credits) {
        creditsHtml += escapeHtml(data.credits).replace(/\n/g, '<br>')
      }
      if (data.scratch_id) {
        const scratchText =
          projectElement.dataset.transImportedFromScratch ||
          'This project was imported from Scratch.'
        const scratchLink = projectElement.dataset.transScratchLink || 'Click here'
        creditsHtml += ` ${escapeHtml(scratchText)} <a href="https://scratch.mit.edu/projects/${encodeURIComponent(data.scratch_id)}" target="_blank" rel="noopener noreferrer">${escapeHtml(scratchLink)}</a>`
      }
      creditsEl.innerHTML = creditsHtml
    } else {
      creditsEl.textContent = projectElement.dataset.transNoCredits || 'No notes or credits.'
    }
  }

  // Update data attributes for downstream components
  projectElement.dataset.projectName = data.name || ''
  projectElement.dataset.hasDescription = data.description ? 'true' : 'false'
  projectElement.dataset.hasCredits = data.credits ? 'true' : 'false'
  projectDescriptionCreditsElement.dataset.hasDescription = data.description ? 'true' : 'false'
  projectDescriptionCreditsElement.dataset.hasCredits = data.credits ? 'true' : 'false'

  // Details section
  const detailsViews = document.getElementById('details-views')
  if (detailsViews) {
    const viewsTemplate = projectElement.dataset.transViews || '%views% views'
    detailsViews.textContent = viewsTemplate.replace('%views%', String(data.views ?? 0))
  }

  const detailsAge = document.getElementById('details-age')
  if (detailsAge) {
    detailsAge.textContent = data.uploaded_string || ''
  }

  const detailsFilesize = document.getElementById('details-filesize')
  if (detailsFilesize) {
    detailsFilesize.textContent = formatFilesize(data.filesize)
  }

  const detailsDownloads = document.getElementById('details-downloads')
  if (detailsDownloads) {
    const downloadsTemplate = projectElement.dataset.transDownloads || '%downloads% downloads'
    detailsDownloads.textContent = downloadsTemplate.replace(
      '%downloads%',
      String(data.downloads ?? 0),
    )
  }

  if (data.not_for_kids) {
    const detailsNfk = document.getElementById('details-not-for-kids')
    if (detailsNfk) {
      detailsNfk.classList.remove('d-none')
    }
  }

  // Tags and extensions
  renderTagsAndExtensions(data)

  // Download buttons
  renderDownloadButtons(data)

  // Recommended projects title (needs author name)
  const recommendedTitle = document.getElementById('recommended-projects-title')
  if (recommendedTitle && data.author) {
    const template = projectElement.dataset.transMoreFromUser || 'More from %username%'
    recommendedTitle.textContent = template.replace('__USERNAME__', data.author)
  }

  // Remove all skeleton placeholders now that content is rendered
  removeSkeletons()
}

function retentionBadgeSpan(colorClass, icon, text) {
  return `<span class="badge ${colorClass}">
      <i class="material-icons" style="font-size: 14px; vertical-align: middle;">${icon}</i>
      ${escapeHtml(text)}
    </span>`
}

function renderRetentionBadge(data) {
  const container = document.getElementById('retention-badge-container')
  if (!container) return

  const retentionDays = data.retention_days
  if (retentionDays == null) {
    container.classList.add('d-none')
    return
  }

  container.removeAttribute('style')
  container.classList.add('mt-2', 'd-flex', 'align-items-center', 'gap-1')

  let badgeHtml = ''

  if (retentionDays === RETENTION_PROTECTED) {
    const protectedText = projectElement.dataset.transRetentionProtected || 'Protected'
    badgeHtml = retentionBadgeSpan('bg-success-subtle text-success', 'verified', protectedText)
  } else if (data.retention_expiry) {
    const expiryDate = new Date(data.retention_expiry)
    const now = new Date()
    const daysLeft = Math.max(0, Math.ceil((expiryDate - now) / MS_PER_DAY))
    const daysLeftTemplate = projectElement.dataset.transRetentionDaysLeft || '__DAYS__ days left'
    const daysLeftText = daysLeftTemplate.replace('__DAYS__', String(daysLeft))

    if (daysLeft <= RETENTION_WARN_DAYS) {
      badgeHtml = retentionBadgeSpan('bg-danger-subtle text-danger', 'warning', daysLeftText)
    } else if (daysLeft <= RETENTION_CAUTION_DAYS) {
      badgeHtml = retentionBadgeSpan(
        'bg-warning-subtle text-warning',
        'hourglass_bottom',
        daysLeftText,
      )
    } else {
      badgeHtml = retentionBadgeSpan('bg-secondary-subtle text-secondary', 'schedule', daysLeftText)
    }
  }

  const tooltipText =
    retentionDays === RETENTION_PROTECTED
      ? projectElement.dataset.transRetentionTooltipProtected
      : projectElement.dataset.transRetentionTooltip

  badgeHtml += `<span class="retention-info-wrap">
    <span class="material-icons retention-info-icon">info_outline</span>
    <span class="retention-tooltip">${escapeHtml(tooltipText || '')}</span>
  </span>`

  container.innerHTML = badgeHtml
}

function renderTagsAndExtensions(data) {
  const wrapper = document.getElementById('tag-extension-wrapper')
  if (!wrapper) return

  const extensions = data.extensions || {}
  const extensionKeys = Object.keys(extensions)
  const tags = data.tags || {}
  const tagKeys = Object.keys(tags)
  const hasExtensions = extensionKeys.length > 0
  const hasTags = tagKeys.length > 0

  if (!hasTags && !hasExtensions) return

  const searchUrl = projectElement.dataset.searchUrl || '/search?q=__QUERY__'

  const renderPillSection = (id, titleOne, titleOther, items, keys) => {
    const title = keys.length === 1 ? titleOne : titleOther
    let sectionHtml = `<div id="${id}"><p>${escapeHtml(title)}:</p><div class="list">`
    keys.forEach((key) => {
      const url = searchUrl.replace('__QUERY__', encodeURIComponent(key))
      sectionHtml += `<a href="${url}"><span class="badge rounded-pill bg-primary">${escapeHtml(items[key])}</span></a>`
    })
    sectionHtml += '</div></div>'
    return sectionHtml
  }

  let html = '<div class="row"><div class="col-12"><div id="tag-extension-container">'

  if (hasExtensions) {
    html += renderPillSection(
      'extensions',
      projectElement.dataset.transExtensionsTitleOne || 'Extension',
      projectElement.dataset.transExtensionsTitleOther || 'Extensions',
      extensions,
      extensionKeys,
    )
  }

  if (hasTags) {
    html += renderPillSection(
      'tags',
      projectElement.dataset.transTagsTitleOne || 'Tag',
      projectElement.dataset.transTagsTitleOther || 'Tags',
      tags,
      tagKeys,
    )
  }

  html += '</div></div></div>'
  wrapper.innerHTML = html
}

function renderDownloadButtons(data) {
  const downloadUrl = data.download_url || ''
  const transDownload = projectElement.dataset.transDownload || 'Download'
  const transDownloading = projectElement.dataset.transDownloading || 'Downloading...'
  const transDownloaded = projectElement.dataset.transDownloaded || 'Downloaded'
  const transOpenInApp = projectElement.dataset.transOpenInApp || 'Open in App'
  const transNotSupportedTitle = projectElement.dataset.transUpdateAppHeaderDownload || ''
  const transNotSupportedText = projectElement.dataset.transUpdateAppTextDownload || ''
  const transDownloadError = projectElement.dataset.transDownloadError || ''

  if (!downloadUrl) return

  const buildButtonHtml = (suffix) => {
    let html = `<div id="downloadButtonWrapper${suffix}" class="download-button-wrapper">`

    // State 1: Default
    html += `<button id="projectDownloadButton${suffix}"
            class="btn btn-primary btn-download js-btn-project-download"
            data-path-url="${escapeAttr(downloadUrl)}"
            data-project-id="${escapeAttr(projectId)}"
            data-suffix="${escapeAttr(suffix)}"
            data-is-webview="${isWebview ? 'true' : 'false'}"
            data-is-supported="true"
            data-is-not-supported-title="${escapeAttr(transNotSupportedTitle)}"
            data-is-not-supported-text="${escapeAttr(transNotSupportedText)}"
            data-trans-downloading="${escapeAttr(transDownloading)}"
            data-trans-open-in-app="${escapeAttr(transOpenInApp)}"
            data-trans-download-error="${escapeAttr(transDownloadError)}"
    >
      <i class="material-icons align-bottom">get_app</i>
      <span>${escapeHtml(transDownload)}</span>
    </button>`

    // State 2: Progress
    html += `<div id="downloadProgress${suffix}" class="btn-download-progress d-none">
      <div class="download-progress-track">
        <div id="downloadProgressBar${suffix}" class="download-progress-bar"></div>
      </div>
      <span id="downloadProgressText${suffix}" class="download-progress-text">
        ${escapeHtml(transDownloading)} 0%
      </span>
      <button id="downloadCancelBtn${suffix}"
              class="btn-download-cancel js-btn-download-cancel"
              aria-label="Cancel download"
              data-suffix="${escapeAttr(suffix)}"
      >
        <i class="material-icons">close</i>
      </button>
    </div>`

    // State 3: Complete
    if (isWebview) {
      html += `<button id="downloadComplete${suffix}"
              class="btn btn-download btn-download-complete d-none js-btn-download-open"
              data-project-id="${escapeAttr(projectId)}"
              data-suffix="${escapeAttr(suffix)}"
      >
        <i class="material-icons align-bottom">open_in_new</i>
        <span>${escapeHtml(transOpenInApp)}</span>
      </button>`
    } else {
      html += `<button id="downloadComplete${suffix}"
              class="btn btn-download btn-download-complete d-none"
              disabled
      >
        <i class="material-icons align-bottom">check_circle</i>
        <span>${escapeHtml(transDownloaded)}</span>
      </button>`
    }

    html += '</div>'
    return html
  }

  const largeWrapper = document.getElementById('download-button-wrapper')
  if (largeWrapper) {
    largeWrapper.innerHTML = buildButtonHtml('')
  }

  const smallWrapper = document.getElementById('download-button-small-wrapper')
  if (smallWrapper) {
    smallWrapper.innerHTML = buildButtonHtml('-small')
  }
}

function initComponents(data, earlyInits) {
  let editorNavigation = null

  if (isMyProject) {
    // Initialize MDCTextField only if a proper mdc-text-field element exists.
    const commentMessageWrapper = document.querySelector('.comment-message')
    if (commentMessageWrapper) {
      const commentMdcRoot =
        commentMessageWrapper.querySelector('.mdc-text-field') ||
        (commentMessageWrapper.classList &&
        commentMessageWrapper.classList.contains('mdc-text-field')
          ? commentMessageWrapper
          : null)
      if (commentMdcRoot) {
        new MDCTextField(commentMdcRoot)
      }
    }

    const nameEditorTextFieldModel = new ProjectEditorTextFieldModel(
      projectId,
      'name',
      true,
      document.querySelector('#name').textContent.trim(),
    )
    new ProjectEditorTextField(nameEditorTextFieldModel)

    const descriptionEditorTextFieldModel = new ProjectEditorTextFieldModel(
      projectId,
      'description',
      projectDescriptionCreditsElement.dataset.hasDescription === 'true',
      document.querySelector('#description').textContent.trim(),
    )
    new ProjectEditorTextField(descriptionEditorTextFieldModel)

    const creditsEditorTextFieldModel = new ProjectEditorTextFieldModel(
      projectId,
      'credits',
      projectDescriptionCreditsElement.dataset.hasCredits === 'true',
      document.querySelector('#credits').textContent.trim(),
    )
    new ProjectEditorTextField(creditsEditorTextFieldModel)

    const projectEditorModel = new ProjectEditorModel(
      projectId,
      [nameEditorTextFieldModel, descriptionEditorTextFieldModel, creditsEditorTextFieldModel],
      earlyInits.languagesPromise,
    )
    const projectEditor = new ProjectEditor(
      projectDescriptionCreditsElement,
      projectId,
      projectEditorModel,
    )

    editorNavigation = new ProjectEditorNavigation(
      projectDescriptionCreditsElement,
      projectId,
      projectEditor,
    )
  }

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

  Project({
    projectId,
    projectName: data.name || '',
    userRole: projectElement.dataset.userRole,
    loginUrl: projectElement.dataset.loginUrl,
    apiReactionUrl: projectElement.dataset.apiReactionUrl,
    apiReactionsUrl: projectElement.dataset.apiReactionsUrl,
    apiReactionsUsersUrl: projectElement.dataset.apiReactionsUsersUrl,
    likeActionAdd: projectElement.dataset.constActionAdd,
    likeActionRemove: projectElement.dataset.constActionRemove,
    profileUrl: projectElement.dataset.pathProfile,
    wowWhite: projectElement.dataset.assetWowWhite,
    wowBlack: projectElement.dataset.assetWowBlack,
    reactionsText: projectElement.dataset.transReaction,
    downloadErrorText: projectElement.dataset.transDownloadError,
  })

  ProjectName(
    projectId,
    appLanguageElement.dataset.appLanguage,
    isMyProject,
    new CustomTranslationApi('name'),
    editorNavigation,
  )

  ProjectDescription(
    projectId,
    appLanguageElement.dataset.appLanguage,
    projectDescriptionCreditsElement.dataset.transMoreInfo,
    projectDescriptionCreditsElement.dataset.transLessInfo,
    isMyProject,
    new CustomTranslationApi('description'),
  )

  ProjectCredits(
    projectId,
    appLanguageElement.dataset.appLanguage,
    isMyProject,
    new CustomTranslationApi('credit'),
  )

  initProjectScreenshotUpload()
  initProjects()

  new TranslateComments(
    projectElement.dataset.translatedByLine,
    projectElement.dataset.googleTranslateDisplayName,
  )

  ProjectComments({
    showStep: 5,
    minAmountOfVisibleComments: 5,
    cancel: projectCommentsElement.dataset.transCancel,
    deleteIt: projectCommentsElement.dataset.transDeleteIt,
    areYouSure: projectCommentsElement.dataset.transAreYouSure,
    noWayOfReturn: projectCommentsElement.dataset.transNoWayOfReturn,
    deleteConfirmation: projectCommentsElement.dataset.transDeleteConfirmation,
    popUpDeletedTitle: projectCommentsElement.dataset.transPopUpDeletedTitle,
    popUpDeletedText: projectCommentsElement.dataset.transPopUpDeletedText,
    noAdminRightsMessage: projectCommentsElement.dataset.transNoAdminRightsMessage,
    defaultErrorMessage: projectCommentsElement.dataset.transDefaultErrorMessage,
  })

  new TranslateProject(
    projectElement.dataset.translatedByLine,
    projectElement.dataset.googleTranslateDisplayName,
    projectElement.dataset.projectId,
    projectElement.dataset.hasDescription === 'true',
    projectElement.dataset.hasCredits === 'true',
  )
}

function initProjectScreenshotUpload() {
  const addChangeListenerToFileInput = function (input) {
    input.onchange = async () => {
      const spinner = document.getElementById('upload-image-spinner')
      spinner.classList.remove('d-none')

      const file = input.files?.[0]
      const processed = await prepareImageFileForUpload(file)
      if (!processed.ok) {
        spinner.classList.add('d-none')
        showSnackbar(
          '#share-snackbar',
          projectConfiguration.messages.screenshotInvalid,
          SnackbarDuration.error,
        )
        return
      }

      try {
        const image = await readFileAsDataUrl(processed.file)
        const response = await window.fetch(
          `${baseUrl}/api/projects/${encodeURIComponent(projectElement.dataset.projectId)}`,
          {
            method: 'PATCH',
            credentials: 'same-origin',
            headers: { 'Content-type': 'application/json' },
            body: JSON.stringify({ screenshot: image }),
          },
        )

        if (response.status !== 204) {
          showSnackbar(
            '#share-snackbar',
            projectConfiguration.messages.screenshotInvalid,
            SnackbarDuration.error,
          )
          return
        }

        const imageElement = document.getElementById('project-thumbnail-big')
        if (imageElement) {
          const cacheBuster = 'x=' + new Date().getTime()
          const hasQuery = imageElement.src.includes('?')
          imageElement.src = `${imageElement.src}${hasQuery ? '&' : '?'}${cacheBuster}`
        }

        showSnackbar('#share-snackbar', projectConfiguration.messages.imageUploadSuccess)
      } catch {
        showSnackbar(
          '#share-snackbar',
          projectConfiguration.messages.screenshotInvalid,
          SnackbarDuration.error,
        )
      } finally {
        spinner.classList.add('d-none')
      }
    }
  }
  const changeButton = document.getElementById('change-project-thumbnail-button')
  if (changeButton) {
    changeButton.addEventListener('click', function () {
      const input = document.createElement('input')
      input.type = 'file'
      input.accept = 'image/*'
      input.style.display = 'none'
      document.body.appendChild(input)
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

function initProjects() {
  const recommendedProjectsElement = document.querySelector('#recommended-projects')
  document.querySelectorAll('.project-list', recommendedProjectsElement).forEach((element) => {
    const id = element.dataset.projectId
    const category = element.dataset.category
    const property = element.dataset.property
    const theme = element.dataset.theme
    const flavor = element.dataset.flavor
    const elBaseUrl = element.dataset.baseUrl

    let url = `${elBaseUrl}/api/projects/${id}/recommendations?category=${category}`

    if (flavor !== 'pocketcode' || category === 'example') {
      url += `&flavor=${flavor}`
    }

    new ProjectList(element, category, url, property, theme)
  })
}
