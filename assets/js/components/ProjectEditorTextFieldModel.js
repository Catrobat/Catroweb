import $ from 'jquery'
import { CustomTranslationApi } from '../api/CustomTranslationApi'

export function ProjectEditorTextFieldModel(
  projectDescriptionCredits,
  programId,
  programSection,
  hasDefaultLanguage,
  initialText,
) {
  const self = this
  this.text = ''
  this.lastSavedText = ''
  this.error = ''
  this.enabled = true

  this.programId = programId
  this.programSection = programSection
  this.hasDefaultLanguage = hasDefaultLanguage
  this.initialText = initialText

  this.customTranslationApi = new CustomTranslationApi(programSection)

  this.pathEditName = projectDescriptionCredits.data('path-edit-project-name')
  this.pathEditDescription = projectDescriptionCredits.data(
    'path-edit-project-description',
  )
  this.pathEditCredits = projectDescriptionCredits.data(
    'path-edit-project-credits',
  )

  this.setOnTextChanged = (onTextChanged) => {
    this.onTextChanged = onTextChanged
  }

  this.setOnError = (onError) => {
    this.onError = onError
  }

  this.setOnEnabled = (onEnabled) => {
    this.onEnabled = onEnabled
  }

  this.setOnLoading = (onLoading) => {
    this.onLoading = onLoading
  }

  this.save = (language) => {
    this.clearError()

    if (this.areChangesSaved() || !this.enabled) {
      return null
    } else if (language === '' || language === 'default') {
      let url
      if (this.programSection === 'name') {
        url = this.pathEditName
      } else if (this.programSection === 'description') {
        url = this.pathEditDescription
      } else if (this.programSection === 'credits') {
        url = this.pathEditCredits
      }

      return new Promise((resolve, reject) => {
        $.ajax({
          url,
          type: 'put',
          data: { value: this.text },
          success: function (data) {
            const statusCode = parseInt(data.statusCode)
            if (statusCode === 527 || statusCode === 707) {
              self.setError(data.message)
              reject(statusCode)
            } else {
              resolve(statusCode)
            }
          },
          error: function (error) {
            if (error.status === 422) {
              self.setError(error.responseText)
              reject(error.status)
            } else if (error.status === 401) {
              reject(error.status)
            }
          },
        })
      })
    } else if (this.text === '') {
      return this.delete(language)
    } else {
      return this.customTranslationApi.saveCustomTranslation(
        this.programId,
        this.text,
        language,
        this.clearError,
        this.setError,
      )
    }
  }

  this.delete = (language) => {
    this.clearError()

    return this.customTranslationApi.deleteCustomTranslation(
      this.programId,
      language,
      this.clearError,
      this.setError,
    )
  }

  this.setText = (text) => {
    this.text = text
  }

  this.fetchText = (language) => {
    this.clearError()

    if (language === 'default') {
      this.setEnabled(true)
      this.setText(this.initialText)
      this.onTextChanged(this.initialText)
      this.lastSavedText = this.initialText
    } else if (this.shouldDisable(language)) {
      this.setEnabled(false)
      this.setText(this.initialText)
      this.onTextChanged(this.initialText)
      this.lastSavedText = this.initialText
    } else {
      this.setEnabled(false)
      this.onTextChanged('')
      this.setLoading(true)

      function getCustomTranslationSuccess(data) {
        self.setLoading(false)
        self.setText(data)
        self.onTextChanged(data)
        self.lastSavedText = data
        self.setEnabled(true)
      }

      function getCustomTranslationError() {
        self.setLoading(false)
        self.setText('')
        self.onTextChanged('')
        self.lastSavedText = ''
        self.setEnabled(true)
      }

      this.customTranslationApi.getCustomTranslation(
        this.programId,
        language,
        getCustomTranslationSuccess,
        getCustomTranslationError,
      )
    }
  }

  this.areChangesSaved = () => {
    return this.text === this.lastSavedText
  }

  // region private
  this.setError = (error) => {
    this.error = error
    this.onError(error)
  }

  this.clearError = () => {
    this.setError('')
  }

  this.setEnabled = (enabled) => {
    this.enabled = enabled
    this.onEnabled(enabled)
  }

  this.setLoading = (loading) => {
    this.loading = loading
    this.onLoading(loading)
  }

  this.shouldDisable = (languageSelected) => {
    // User should not be able to define custom translation if field is not defined in default language
    return (
      !this.hasDefaultLanguage &&
      languageSelected !== '' &&
      languageSelected !== 'default'
    )
  }
  // end region
}
