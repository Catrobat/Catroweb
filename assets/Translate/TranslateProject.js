import { ByLineElementContainer, Translation } from './Translation'

export class TranslateProject extends Translation {
  constructor(
    translatedByLine,
    projectId,
    hasDescription,
    hasCredit,
    baseUrl = '',
    refreshLabel = undefined,
  ) {
    super(translatedByLine, refreshLabel)
    this.projectId = projectId
    this.baseUrl = baseUrl
    this.hasDescription = hasDescription
    this.hasCredit = hasCredit
    this.ANIMATION_TIME = 400
    this._initListeners()
  }

  _initListeners() {
    const translateProject = this

    if (document.getElementById('project-translation-button') == null) {
      return
    }

    document.getElementById('project-translation-button').addEventListener('click', function () {
      this.style.display = 'none'

      if (translateProject.isTranslationNotAvailable('#name-translation')) {
        document.getElementById('project-translation-loading-spinner').style.display = 'block'
        translateProject.translateProject()
      } else {
        translateProject.openTranslatedProject()
      }
    })

    document
      .getElementById('remove-project-translation-button')
      .addEventListener('click', function () {
        this.style.display = 'none'
        document.getElementById('project-translation-button').style.display = 'block'

        const name = document.getElementById('name')
        const nameTranslation = document.getElementById('name-translation')
        name.classList.remove('project-name')
        name.classList.add('project-name-animation')
        nameTranslation.classList.remove('project-name')
        nameTranslation.classList.add('project-name-animation')
        nameTranslation.style.display = 'none'
        name.style.display = 'block'

        setTimeout(() => {
          name.classList.remove('project-name-animation')
          name.classList.add('project-name')
          nameTranslation.classList.remove('project-name-animation')
          nameTranslation.classList.add('project-name')
        }, translateProject.ANIMATION_TIME)

        if (translateProject.hasDescription) {
          document.getElementById('description').style.display = 'block'
          document.getElementById('description-translation').style.display = 'none'
        }

        document.getElementById('credits-translation-wrapper').style.display = 'none'
        if (translateProject.hasCredit) {
          document.getElementById('credits').style.display = 'block'
        }
      })
  }

  setTranslatedProjectData(data) {
    const nameTranslation = document.getElementById('name-translation')
    nameTranslation.setAttribute('lang', data.target_language)
    nameTranslation.textContent = data.translated_title

    if (this.hasDescription) {
      document.getElementById('description-translation').textContent = data.translated_description
    }

    if (this.hasCredit) {
      document.getElementById('credits-translation').textContent = data.translated_credit
    }

    const byLineElements = new ByLineElementContainer(
      document.getElementById('project-translation-before-languages'),
      document.getElementById('project-translation-between-languages'),
      document.getElementById('project-translation-after-languages'),
      document.getElementById('project-translation-first-language'),
      document.getElementById('project-translation-second-language'),
    )

    this.setTranslationCredit(data, byLineElements, (sourceLanguage, targetLanguage) => {
      document.getElementById('project-translation-loading-spinner').style.display = 'block'
      this.translateProject(sourceLanguage, targetLanguage)
    })
  }

  openTranslatedProject() {
    document.getElementById('project-translation-loading-spinner').style.display = 'none'
    document.getElementById('remove-project-translation-button').style.display = 'block'

    const name = document.getElementById('name')
    const nameTranslation = document.getElementById('name-translation')
    name.classList.remove('project-name')
    name.classList.add('project-name-animation')
    nameTranslation.classList.remove('project-name')
    nameTranslation.classList.add('project-name-animation')
    name.style.display = 'none'
    nameTranslation.style.display = 'block'

    setTimeout(() => {
      name.classList.remove('project-name-animation')
      name.classList.add('project-name')
      nameTranslation.classList.remove('project-name-animation')
      nameTranslation.classList.add('project-name')
    }, this.ANIMATION_TIME)

    if (this.hasDescription) {
      document.getElementById('description-translation').style.display = 'block'
      document.getElementById('description').style.display = 'none'
    }

    document.getElementById('credits-translation-wrapper').style.display = 'block'
    if (this.hasCredit) {
      document.getElementById('credits').style.display = 'none'
    }
  }

  projectNotTranslated() {
    document.getElementById('project-translation-loading-spinner').style.display = 'none'
    // A failed "translate again" keeps the translation that is already shown.
    if (document.getElementById('remove-project-translation-button').style.display !== 'block') {
      document.getElementById('project-translation-button').style.display = 'block'
    }
    this.showTranslationFailed()
  }

  translateProject(sourceLanguage = null, targetLanguage = this.targetLanguage) {
    const params = new URLSearchParams({ target_language: targetLanguage })
    if (sourceLanguage) params.set('source_language', sourceLanguage)
    const url = `${this.baseUrl}/api/projects/${this.projectId}/translation?${params}`
    // 202: the server translates in the background (about 1 s per 30 characters); ask again until it is done.
    // Every 3 s stays below the 30 requests a minute per IP.
    const deadline = Date.now() + 120_000

    const poll = () =>
      fetch(url, { method: 'GET' }).then((response) => {
        if (response.status === 202 && Date.now() < deadline) {
          return new Promise((resolve) => setTimeout(resolve, 3000)).then(poll)
        }
        if (!response.ok || response.status === 202) {
          throw new Error(`Translation failed: ${response.status}`)
        }
        return response.json()
      })

    poll()
      .then((data) => {
        this.setTranslatedProjectData(data)
        this.openTranslatedProject()
      })
      .catch(() => {
        this.projectNotTranslated()
      })
  }
}
