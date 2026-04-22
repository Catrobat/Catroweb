import { CustomTranslationApi } from '../Api/CustomTranslationApi'
import { escapeAttr } from '../Components/HtmlEscape'
import { showCustomTopBarTitle, showDefaultTopBarTitle } from '../Layout/TopBar'

export function ProjectEditorNavigation(projectDescriptionCredits, programId, programEditor) {
  const self = this

  this.programId = programId
  this.programEditor = programEditor

  this.body = document.body
  this.editTextNavigation = document.getElementById('edit-text-navigation')
  this.navigationLanguageList = document.getElementById('navigation-language-list')

  this.languages = {}

  const navBaseUrl = document.querySelector('.js-project')?.dataset?.baseUrl || ''
  this.customTranslationApi = new CustomTranslationApi('', navBaseUrl)

  this.translationsText = projectDescriptionCredits.dataset.transTranslations
  this.defaultText = projectDescriptionCredits.dataset.transDefault
  this.translationTitleText = projectDescriptionCredits.dataset.transTranslationTitle
  this.editDefaultText = projectDescriptionCredits.dataset.transEditDefault
  this.editTranslationText = projectDescriptionCredits.dataset.transEditTranslation
  this.createTranslationText = projectDescriptionCredits.dataset.transCreateTranslation

  document.getElementById('add-translation-button').addEventListener('click', () => {
    this.openEditor(null, true, false, this.createTranslationText)
  })

  this.renderDefaultButtonHtml = () => `
    <li>
      <div id="edit-default-button" class="text-icon-aligned edit-defined-translation" data-value="default">
        <span class="language-code"></span>
        <span class="language-name">${this.defaultText}</span>
        <span data-bs-toggle="tooltip" title="${escapeAttr(this.editDefaultText)}" class="catro-icon-button material-icons trailing-icon" style="font-size: 1.75rem;">edit</span>
      </div>
    </li>
  `

  // Render default button immediately so it's available before async fetches complete
  this.navigationLanguageList.innerHTML = this.renderDefaultButtonHtml()

  // Load languages and translations asynchronously
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', getLanguages)
  } else {
    getLanguages()
  }

  document.addEventListener('click', function (event) {
    const translationButton = event.target.closest('.edit-defined-translation')
    if (translationButton) {
      const language = translationButton.dataset.value
      const languageName = translationButton.dataset.language

      if (language === 'default') {
        self.openEditor(language, false, false, self.editDefaultText)
      } else {
        self.openEditor(
          language,
          false,
          true,
          self.translationTitleText.replace('%language%', languageName),
        )
      }
    }
  })

  this.show = () => {
    window.history.pushState(
      { type: 'ProjectEditorNavigation', id: programId, full: true },
      document.title,
      '#navigation',
    )

    window.addEventListener('popstate', this.popStateHandler)
    showCustomTopBarTitle(this.translationsText, function () {
      window.history.back()
    })

    this.body.classList.add('overflow-hidden')
    this.editTextNavigation.classList.remove('d-none')
  }

  this.popStateHandler = () => {
    this.close()
  }

  this.close = () => {
    window.removeEventListener('popstate', this.popStateHandler)
    showDefaultTopBarTitle()

    this.body.classList.remove('overflow-hidden')
    this.editTextNavigation.classList.add('d-none')
  }

  this.openEditor = (language, showLanguageSelect, showDeleteButton, headerText) => {
    window.removeEventListener('popstate', this.popStateHandler)

    this.programEditor.show(
      this.reopenNavigation,
      language,
      showLanguageSelect,
      showDeleteButton,
      headerText,
    )
  }

  this.reopenNavigation = () => {
    window.addEventListener('popstate', this.popStateHandler)

    showCustomTopBarTitle(this.translationsText, function () {
      window.history.back()
    })

    this.editTextNavigation.classList.remove('d-none')

    this.getTranslations()
  }

  function getLanguages() {
    const routing = document.getElementById('js-api-routing')
    const languagesUrl = routing ? routing.dataset.languages : '/languages'
    fetch(languagesUrl)
      .then((response) => response.json())
      .then((data) => {
        self.languages = data
        self.getTranslations()
      })
      .catch((error) => {
        console.error('Error fetching languages:', error)
      })
  }

  this.getTranslations = () => {
    this.customTranslationApi
      .getCustomTranslationLanguages(this.programId)
      .then(this.showTranslations)
      .catch((error) => {
        console.error('Error fetching translations:', error)
      })
  }

  this.showTranslations = (translationLanguages) => {
    let html = this.renderDefaultButtonHtml()

    translationLanguages.forEach((language) => {
      const langName = this.languages[language] || language
      html += `
        <li>
          <div id="edit-${escapeAttr(language)}-button" class="text-icon-aligned edit-defined-translation" data-value="${escapeAttr(language)}" data-language="${escapeAttr(langName)}">
            <span class="language-code">${escapeAttr(language)}</span>
            <span class="language-name">${escapeAttr(langName)}</span>
            <span data-bs-toggle="tooltip" title="${escapeAttr(this.editTranslationText.replace('%language%', langName))}" class="catro-icon-button material-icons trailing-icon" style="font-size: 1.75rem;">edit</span>
          </div>
        </li>
      `
    })

    this.navigationLanguageList.innerHTML = html
  }
}
