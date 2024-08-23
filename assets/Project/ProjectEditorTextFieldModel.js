import { CustomTranslationApi } from '../Api/CustomTranslationApi'

export function ProjectEditorTextFieldModel(
  projectId,
  sectionKey,
  hasDefaultLanguage,
  initialText,
) {
  this.text = ''
  this.lastSavedText = ''
  this.error = ''
  this.enabled = true

  this.programId = projectId
  this.programSection = sectionKey
  this.hasDefaultLanguage = hasDefaultLanguage
  this.initialText = initialText

  this.customTranslationApi = new CustomTranslationApi(sectionKey)

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

  this.collectChanges = (language) => {
    if (this.areChangesSaved() || !this.enabled) {
      return null
    }

    return { [this.programSection]: this.text }
  }

  this.handleTranslations = (language) => {
    if (this.areChangesSaved() || !this.enabled) {
      return null
    }
    if (this.text === '') {
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

      const getCustomTranslationSuccess = (data) => {
        this.setLoading(false)
        this.setText(data)
        this.onTextChanged(data)
        this.lastSavedText = data
        this.setEnabled(true)
      }

      const getCustomTranslationError = () => {
        this.setLoading(false)
        this.setText('')
        this.onTextChanged('')
        this.lastSavedText = ''
        this.setEnabled(true)
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
}
