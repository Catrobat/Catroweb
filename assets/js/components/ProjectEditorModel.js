import { CustomTranslationApi } from '../api/CustomTranslationApi'

export const DIALOG = {
  CLOSE_EDITOR: 'close_editor',
  CONFIRM_DELETE: 'confirm_delete',
}

export function ProjectEditorModel(programId, textFieldModels) {
  this.programId = programId
  this.textFieldModels = textFieldModels

  this.languages = {}
  this.definedLanguages = {}
  this.selectedLanguage = ''

  this.customTranslationApi = new CustomTranslationApi()

  this.setOnLanguageList = (onLanguageList) => {
    this.onLanguageList = onLanguageList
  }

  this.setOnDialog = (onDialog) => {
    this.onDialog = onDialog
  }

  this.setOnButtonEnabled = (onButtonEnabled) => {
    this.onButtonEnabled = onButtonEnabled
  }

  this.setOnLanguageSelected = (onLanguageSelected) => {
    this.onLanguageSelected = onLanguageSelected
  }

  this.setOnClose = (onClose) => {
    this.onClose = onClose
  }

  this.setOnReload = (onReload) => {
    this.onReload = onReload
  }

  this.setOnUnauthorized = (onUnauthorized) => {
    this.onUnauthorized = onUnauthorized
  }

  this.setLanguage = (language) => {
    this.selectedLanguage = language
  }

  this.onInput = () => {
    this.onButtonEnabled(!this.areChangesSaved())
  }

  this.show = (language) => {
    if (language === null) {
      this.selectedLanguage = Object.keys(this.languages)[0]
      this.onLanguageSelected(0)
    } else {
      this.selectedLanguage = language
    }
    this.fetchText()
  }

  this.popStateHandler = () => {
    if (this.areChangesSaved()) {
      this.onClose()
    } else {
      this.onDialog(DIALOG.CLOSE_EDITOR)
    }
  }

  this.closeEditorResult = (result) => {
    if (result.isConfirmed) {
      this.save()
    } else if (result.isDenied) {
      this.onClose()
    }
  }

  this.deleteTranslationResult = (result) => {
    if (result.isDenied) {
      Promise.all(
        this.textFieldModels.map((textField) =>
          textField.delete(this.selectedLanguage),
        ),
      )
        .then((results) => {
          if (results.length === this.textFieldModels.length) {
            this.onClose()
          }
        })
        .catch((reason) => {
          for (const error of reason) {
            if (error === 401) {
              this.onUnauthorized()
            }
          }
        })
    }
  }

  this.save = () => {
    Promise.all(
      this.textFieldModels.map((textField) =>
        textField.save(this.selectedLanguage),
      ),
    )
      .then((results) => {
        if (
          this.selectedLanguage === '' ||
          this.selectedLanguage === 'default'
        ) {
          this.onReload()
        } else if (results.length === this.textFieldModels.length) {
          this.onClose()
        }
      })
      .catch((reason) => {
        for (const error of reason) {
          if (error === 401) {
            this.onUnauthorized()
          }
        }
      })
  }

  this.deleteTranslation = () => {
    if (this.selectedLanguage !== '' && this.selectedLanguage !== 'default') {
      this.onDialog(DIALOG.CONFIRM_DELETE)
    }
  }

  // region private
  document.addEventListener('DOMContentLoaded', () => this.getLanguages())

  this.getLanguages = () => {
    const languagesPromise = fetch('../languages').then((response) =>
      response.json(),
    )
    const definedLanguagesPromise =
      this.customTranslationApi.getCustomTranslationLanguages(this.programId)

    Promise.all([languagesPromise, definedLanguagesPromise]).then((results) => {
      this.languages = results[0]
      this.definedLanguages = results[1]
      this.filterLanguages()
      this.onLanguageList(this.languages)
    })
  }

  this.filterLanguages = () => {
    const specialLanguages = ['zh-CN', 'zh-TW', 'pt-BR', 'pt-PT']

    for (const language in this.languages) {
      if (
        this.definedLanguages.includes(language) ||
        (language.length !== 2 && !specialLanguages.includes(language))
      ) {
        delete this.languages[language]
      }
    }
  }

  this.areChangesSaved = () => {
    return this.textFieldModels.every((textField) =>
      textField.areChangesSaved(),
    )
  }

  this.fetchText = () => {
    for (const textField of this.textFieldModels) {
      textField.fetchText(this.selectedLanguage)
    }

    this.onButtonEnabled(false)
  }
  // end region
}
