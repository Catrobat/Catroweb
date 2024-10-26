import { ByLineElementContainer, Translation } from './Translation'

export class TranslateProgram extends Translation {
  constructor(translatedByLine, googleTranslateDisplayName, programId, hasDescription, hasCredit) {
    super(translatedByLine, googleTranslateDisplayName)
    this.programId = programId
    this.hasDescription = hasDescription
    this.hasCredit = hasCredit
    this.ANIMATION_TIME = 400
    this._initListeners()
  }

  _initListeners() {
    const translateProgram = this

    if (document.getElementById('project-translation-button') == null) {
      return
    }

    document
      .getElementById('project-translation-button')
      .addEventListener('click', function (event) {
        this.style.display = 'none'

        if (translateProgram.isTranslationNotAvailable('#name-translation')) {
          document.getElementById('project-translation-loading-spinner').style.display = 'block'
          translateProgram.translateProgram()
        } else {
          translateProgram.openTranslatedProgram()
        }
      })

    document
      .getElementById('remove-project-translation-button')
      .addEventListener('click', function (event) {
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
        }, translateProgram.ANIMATION_TIME)

        if (translateProgram.hasDescription) {
          document.getElementById('description').style.display = 'block'
          document.getElementById('description-translation').style.display = 'none'
        }

        document.getElementById('credits-translation-wrapper').style.display = 'none'
        if (translateProgram.hasCredit) {
          document.getElementById('credits').style.display = 'block'
        }
      })
  }

  setTranslatedProgramData(data) {
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

    this.setTranslationCredit(data, byLineElements)
  }

  openTranslatedProgram() {
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

  programNotTranslated() {
    document.getElementById('project-translation-loading-spinner').style.display = 'none'
    document.getElementById('project-translation-button').style.display = 'block'

    let text = document.getElementById('name').innerText

    if (this.hasDescription) {
      text += '\n\n' + document.getElementById('description').innerText
    }

    if (this.hasCredit) {
      text += '\n\n' + document.getElementById('credits').innerText
    }

    this.openGoogleTranslatePage(text)
  }

  translateProgram() {
    const self = this
    fetch('../translate/project/' + self.programId + '?target_language=' + self.targetLanguage, {
      method: 'GET',
    })
      .then((response) => response.json())
      .then((data) => {
        self.setTranslatedProgramData(data)
        self.openTranslatedProgram()
      })
      .catch(() => {
        self.programNotTranslated()
      })
  }
}
