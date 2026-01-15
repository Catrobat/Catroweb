import { CustomTranslationApi } from '../Api/CustomTranslationApi'
import { showCustomTopBarTitle, showDefaultTopBarTitle } from '../Layout/TopBar'

export function ProjectEditorNavigation(projectDescriptionCredits, programId, programEditor) {
  const self = this

  this.programId = programId
  this.programEditor = programEditor

  this.body = document.body
  this.editTextNavigation = document.getElementById('edit-text-navigation')
  this.navigationLanguageList = document.getElementById('navigation-language-list')

  this.languages = {}

  this.customTranslationApi = new CustomTranslationApi()

  this.translationsText = projectDescriptionCredits.dataset.transTranslations
  this.defaultText = projectDescriptionCredits.dataset.transDefault
  this.translationTitleText = projectDescriptionCredits.dataset.transTranslationTitle
  this.editDefaultText = projectDescriptionCredits.dataset.transEditDefault
  this.editTranslationText = projectDescriptionCredits.dataset.transEditTranslation
  this.createTranslationText = projectDescriptionCredits.dataset.transCreateTranslation

  document.getElementById('add-translation-button').addEventListener('click', () => {
    this.openEditor(null, true, false, this.createTranslationText)
  })

  document.addEventListener('DOMContentLoaded', getLanguages)

  document.addEventListener('click', function (event) {
    if (event.target && event.target.matches('.edit-defined-translation')) {
      const language = event.target.dataset.value
      const languageName = event.target.dataset.language

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
    this.navigationLanguageList.innerHTML = '' // Clear existing content
    this.navigationLanguageList.innerHTML += `
      <li>
        <div id="edit-default-button" class="text-icon-aligned edit-defined-translation" data-value="default">
          <span class="language-code"></span>
          <span class="language-name">${this.defaultText}</span>
          <span data-bs-toggle="tooltip" title="${this.editDefaultText}" class="catro-icon-button material-icons trailing-icon" style="font-size: 1.75rem;">edit</span>
        </div>
      </li>
    `

    translationLanguages.forEach((language) => {
      this.navigationLanguageList.innerHTML += `
        <li>
          <div id="edit-${language}-button" class="text-icon-aligned edit-defined-translation" data-value="${language}" data-language="${this.languages[language]}">
            <span class="language-code">${language}</span>
            <span class="language-name">${this.languages[language]}</span>
            <span data-bs-toggle="tooltip" title="${this.editTranslationText.replace('%language%', this.languages[language])}" class="catro-icon-button material-icons trailing-icon" style="font-size: 1.75rem;">edit</span>
          </div>
        </li>
      `
    })
  }
}
