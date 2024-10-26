import { CustomTranslationApi } from '../Api/CustomTranslationApi'
import { getCookie } from '../Security/CookieHelper'
import AcceptLanguage from '../Api/AcceptLanguage'

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

  this.routing = document.getElementById('js-api-routing')
  this.baseUrl = this.routing.dataset.baseUrl
  this.theme = this.routing.dataset.index

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
      Promise.all(this.textFieldModels.map((textField) => textField.delete(this.selectedLanguage)))
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
    if (this.selectedLanguage === '' || this.selectedLanguage === 'default') {
      // Update default project name, credits & description
      const requestData = this.textFieldModels
        .map((textField) => textField.collectChanges(this.selectedLanguage))
        .filter((update) => update !== null)
        .reduce((acc, curr) => ({ ...acc, ...curr }), {})

      if (requestData.length === 0) {
        return
      }

      fetch(`${this.baseUrl}/api/project/${this.programId}`, {
        method: 'PUT',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          Authorization: 'Bearer ' + getCookie('BEARER'),
          'Accept-Language': new AcceptLanguage().get(),
        },
        body: JSON.stringify(requestData),
      })
        .then((response) => {
          if (response.ok) {
            if (this.selectedLanguage === '' || this.selectedLanguage === 'default') {
              this.onReload()
            }
          } else if (response.status === 401) {
            window.location.href = `${this.baseUrl}/${this.theme}/login`
          } else if (response.status === 403) {
            window.location.href = `${this.baseUrl}/${this.theme}`
          } else if (response.status === 422) {
            response.json().then((json) => {
              this.textFieldModels.forEach((textField) => {
                if (json[textField.programSection]) {
                  textField.setError(json[textField.programSection])
                }
              })
            })
          }
        })
        .catch((reason) => {
          console.error('Unexpected error on updating project')
        })
    } else {
      Promise.all(
        this.textFieldModels.map((textFieldModel) =>
          textFieldModel.handleTranslations(this.selectedLanguage),
        ),
      )
        .catch((error) => {
          console.error(error)
        })
        .finally(() => this.onClose())
    }
  }

  this.deleteTranslation = () => {
    if (this.selectedLanguage !== '' && this.selectedLanguage !== 'default') {
      this.onDialog(DIALOG.CONFIRM_DELETE)
    }
  }

  // region private
  document.addEventListener('DOMContentLoaded', () => this.getLanguages())

  this.getLanguages = () => {
    const languagesPromise = fetch('../languages').then((response) => response.json())
    const definedLanguagesPromise = this.customTranslationApi.getCustomTranslationLanguages(
      this.programId,
    )

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
    return this.textFieldModels.every((textField) => textField.areChangesSaved())
  }

  this.fetchText = () => {
    for (const textField of this.textFieldModels) {
      textField.fetchText(this.selectedLanguage)
    }

    this.onButtonEnabled(false)
  }
  // end region
}
