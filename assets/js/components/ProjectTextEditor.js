import $ from 'jquery'
import { MDCSelect } from '@material/select'
import { ProgramEditorDialog } from '../custom/ProgramEditorDialog'
import { CustomTranslationApi } from '../api/CustomTranslationApi'

export function ProjectTextEditor (projectDescriptionCredits, defaultText, programId) {
  const self = this
  this.defaultText = defaultText
  this.programId = programId

  this.editTextUI = $('#edit-text-ui')
  this.editText = $('#edit-text')
  this.editTextError = $('#edit-text-error')
  this.languageSelectorList = $('#edit-language-selector-list')
  this.selectedLanguage = $('#edit-selected-language')
  this.textLoadingSpinner = $('#edit-loading-spinner')

  this.languageSelect = new MDCSelect(document.querySelector('#edit-language-selector'))
  this.languages = {}
  this.previousIndex = 0

  this.translationSaved = projectDescriptionCredits.data('trans-translation-saved')
  this.translationDeleted = projectDescriptionCredits.data('trans-translation-deleted')

  this.pathEditName = projectDescriptionCredits.data('path-edit-program-name')
  this.pathEditDescription = projectDescriptionCredits.data('path-edit-program-description')
  this.pathEditCredits = projectDescriptionCredits.data('path-edit-program-credits')

  this.closeEditorDialog = new ProgramEditorDialog(
    projectDescriptionCredits.data('trans-close-editor-prompt'),
    projectDescriptionCredits.data('trans-save'),
    projectDescriptionCredits.data('trans-discard')
  )

  this.keepOrDiscardDialog = new ProgramEditorDialog(
    projectDescriptionCredits.data('trans-save-on-language-change'),
    projectDescriptionCredits.data('trans-keep'),
    projectDescriptionCredits.data('trans-discard')
  )

  $('#edit-submit-button').on('click', () => { this.save() })
  $('#edit-close-button').on('click', () => {
    if (this.areChangesSaved()) {
      this.close()
    } else {
      this.closeEditorDialog.show(closeEditorDialogResult)
    }
  })

  this.languageSelect.listen('MDCSelect:change', () => {
    if (!this.editText.is(':visible') || this.languageSelect.selectedIndex === this.previousIndex) {
      return
    }

    if (this.areChangesSaved()) {
      this.previousIndex = this.languageSelect.selectedIndex
      this.getCustomTranslation()
    } else {
      this.keepOrDiscardDialog.show(keepOrDiscardChangesResult)
    }
  })

  $(document).ready(getLanguages)

  this.show = (config, initialText, closedCallback) => {
    this.programSection = config.programSection
    this.customTranslationApi = new CustomTranslationApi(config.programSection)
    this.editText.val(initialText)
    this.lastSavedText = initialText
    this.editText.maxLength = config.maxLength
    this.snackbar = config.snackbar
    this.closedCallback = closedCallback
    $('#edit-headline').text(config.headline)
    $('#edit-close-button').attr('title', config.closeText)
    $('#edit-text-instruction').text(config.instruction)

    const langaugeSelectElement = $('#edit-language-selector')
    if (config.showLanguageSelect) {
      langaugeSelectElement.removeClass('d-none')
    } else {
      langaugeSelectElement.addClass('d-none')
    }

    this.editTextUI.removeClass('d-none')
  }

  // region private
  this.close = () => {
    self.editTextError.hide()
    this.editTextUI.addClass('d-none')
    this.reset()
    this.closedCallback()
  }

  this.reset = () => {
    this.languageSelect.selectedIndex = 0
    this.previousIndex = 0
  }

  function getLanguages () {
    $.ajax({
      url: '../languages',
      type: 'get',
      success: function (data) {
        self.languages = data
        self.populateSelector()
      }
    })
  }

  this.populateSelector = () => {
    this.languageSelectorList.empty()
    this.languageSelectorList.append('<li class="mdc-list-item" data-value="default" role="option" tabindex="-1">' +
      '<span class="mdc-list-item__ripple"></span>' +
      '<span class="mdc-list-item__text">' + this.defaultText + '</span>' +
      '</li>')

    for (const language in this.languages) {
      if (language.length <= 2) {
        this.languageSelectorList.append(`<li class="mdc-list-item" data-value="${language}" role="option" tabindex="-1">\
          <span class="mdc-list-item__ripple"></span>\
          <span class="mdc-list-item__text">${this.languages[language]}</span>\
          </li>`)
      }
    }

    this.languageSelect.layoutOptions()
    this.reset()
  }

  this.areChangesSaved = () => {
    return this.editText.val().trim() === this.lastSavedText.trim()
  }

  function keepOrDiscardChangesResult (result) {
    if (result.isConfirmed) {
      self.previousIndex = self.languageSelect.selectedIndex
      self.lastSavedText = self.editText.text().trim()
    } else if (result.isDenied) {
      self.previousIndex = self.languageSelect.selectedIndex
      self.getCustomTranslation()
    } else if (result.isDismissed) {
      self.languageSelect.selectedIndex = self.previousIndex
    }
  }

  function closeEditorDialogResult (result) {
    if (result.isConfirmed) {
      self.save()
    } else if (result.isDenied) {
      self.close()
    }
  }

  this.save = () => {
    const newText = this.editText.val().trim()
    if (this.areChangesSaved()) {
      this.close()
      return
    }

    const languageSelected = this.languageSelect.value
    if (languageSelected === '' || languageSelected === 'default') {
      let url
      if (this.programSection === 'name') {
        url = this.pathEditName
      } else if (this.programSection === 'description') {
        url = this.pathEditDescription
      } else if (this.programSection === 'credit') {
        url = this.pathEditCredits
      }

      $.ajax({
        url: url,
        type: 'put',
        data: { value: newText },
        success: function (data) {
          const statusCode = parseInt(data.statusCode)
          if (statusCode === 527 || statusCode === 707) {
            showError(data)
          } else {
            window.location.reload()
          }
        },
        error: function (error) {
          if (error.status === 422) {
            error.message = error.responseText
            showError(error)
          } else if (error.status === 401) {
            window.location.href = '../login'
          }
        }
      })
    } else if (newText === '') {
      this.customTranslationApi.deleteCustomTranslation(
        this.programId,
        languageSelected,
        deleteCustomTranslationSuccess,
        showError
      )
    } else {
      this.customTranslationApi.saveCustomTranslation(
        this.programId,
        newText,
        languageSelected,
        saveCustomTranslationSuccess,
        showError
      )
    }
  }

  function showError (error) {
    self.editText.addClass('danger')
    self.editTextError.show()
    self.editTextError.text(error.message)
  }

  function deleteCustomTranslationSuccess () {
    self.snackbar.show(self.translationDeleted, self.selectedLanguage.text())
    self.close()
  }

  function saveCustomTranslationSuccess () {
    self.snackbar.show(self.translationSaved, self.selectedLanguage.text())
    self.close()
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

  self.getCustomTranslation = () => {
    this.editText.attr('disabled', '')
    this.editText.val('')
    this.textLoadingSpinner.show()

    this.customTranslationApi.getCustomTranslation(
      this.programId,
      this.languageSelect.value,
      getCustomTranslationSuccess,
      getCustomTranslationError
    )
  }

  // end region
}
