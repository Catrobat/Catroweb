import $ from 'jquery'
import { MDCTextField } from '@material/textfield'
import { CustomTranslationApi } from '../api/CustomTranslationApi'

export function ProjectEditorTextField (projectDescriptionCredits, programId, programSection, hasDefaultText) {
  const self = this
  this.programId = programId
  this.programSection = programSection
  this.hasDefaultText = hasDefaultText
  this.customTranslationApi = new CustomTranslationApi(programSection)

  this.editText = $('#edit-' + this.programSection + '-text')
  this.editTextError = $('#edit-' + this.programSection + '-text-error')
  this.textLoadingSpinner = $('#edit-' + this.programSection + '-loading-spinner')
  this.selectedLanguage = $('#edit-selected-language')

  this.initialText = $('#' + this.programSection).text().trim()

  new MDCTextField(document.querySelector('#edit-' + this.programSection + '-mdc-text-field'))

  this.pathEditName = projectDescriptionCredits.data('path-edit-program-name')
  this.pathEditDescription = projectDescriptionCredits.data('path-edit-program-description')
  this.pathEditCredits = projectDescriptionCredits.data('path-edit-program-credits')

  this.setText = () => {
    this.editText.val(this.initialText)
    this.lastSavedText = this.initialText
  }

  this.areChangesSaved = () => {
    return this.editText.val().trim() === this.lastSavedText.trim()
  }

  this.save = (languageSelected) => {
    hideError()
    const newText = this.editText.val().trim()
    if (this.areChangesSaved() || this.shouldDisable(languageSelected)) {
      // Return without saving
    } else if (languageSelected === '' || languageSelected === 'default') {
      let url
      if (this.programSection === 'name') {
        url = this.pathEditName
      } else if (this.programSection === 'description') {
        url = this.pathEditDescription
      } else if (this.programSection === 'credits') {
        url = this.pathEditCredits
      }

      return new Promise(function (resolve, reject) {
        $.ajax({
          url: url,
          type: 'put',
          data: { value: newText },
          success: function (data) {
            const statusCode = parseInt(data.statusCode)
            if (statusCode === 527 || statusCode === 707) {
              showError(data)
              reject(statusCode)
            } else {
              resolve(statusCode)
            }
          },
          error: function (error) {
            if (error.status === 422) {
              error.message = error.responseText
              showError(error)
              reject(error.status)
            } else if (error.status === 401) {
              reject(error.status)
            }
          }
        })
      })
    } else if (newText === '') {
      return this.customTranslationApi.deleteCustomTranslation(
        this.programId,
        languageSelected,
        hideError,
        showError
      )
    } else {
      return this.customTranslationApi.saveCustomTranslation(
        this.programId,
        newText,
        languageSelected,
        hideError,
        showError
      )
    }
  }

  this.getNewText = (language) => {
    hideError()
    if (language === 'default') {
      this.editText.removeAttr('disabled')
      this.editText.val(this.initialText)
      this.lastSavedText = this.initialText
      return
    } else if (this.shouldDisable(language)) {
      this.lastSavedText = this.initialText
      this.editText.val(this.initialText)
      this.editText.attr('disabled', '')
      return
    }

    this.editText.attr('disabled', '')
    this.editText.val('')
    this.textLoadingSpinner.show()

    this.customTranslationApi.getCustomTranslation(
      this.programId,
      language,
      getCustomTranslationSuccess,
      getCustomTranslationError
    )
  }

  this.getNewTextKeepChanges = (language) => {
    if (this.shouldDisable(language)) {
      this.lastSavedText = this.initialText
      this.editText.val(this.initialText)
      this.editText.attr('disabled', '')
    } else if (language === 'default') {
      this.editText.removeAttr('disabled')
      this.lastSavedText = this.initialText
    } else {
      this.customTranslationApi.getCustomTranslation(
        this.programId,
        language,
        setLastSavedText,
        setLastSavedTextError
      )
    }
  }

  // region private
  this.shouldDisable = (languageSelected) => {
    // User should not be able to define custom translation if field is not defined in default language
    return !this.hasDefaultText && !(languageSelected === '' || languageSelected === 'default')
  }

  function showError (error) {
    self.editText.addClass('danger')
    self.editTextError.show()
    self.editTextError.text(error.message)
  }

  function hideError () {
    self.editTextError.hide()
  }

  function getCustomTranslationSuccess (data) {
    self.textLoadingSpinner.hide()
    self.editText.val(data)
    self.lastSavedText = data
    self.editText.removeAttr('disabled')
  }

  function getCustomTranslationError () {
    self.textLoadingSpinner.hide()
    self.editText.val('')
    self.lastSavedText = ''
    self.editText.removeAttr('disabled')
  }

  function setLastSavedText (data) {
    self.lastSavedText = data
  }

  function setLastSavedTextError () {
    self.lastSavedText = ''
  }

  // end region
}
