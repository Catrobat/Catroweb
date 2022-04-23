import $ from 'jquery'
import { MDCSelect } from '@material/select'
import { ProgramEditorDialog } from '../custom/ProgramEditorDialog'
import { showSnackbar } from './snackbar'
import { showCustomTopBarTitle } from '../layout/top_bar'

export function ProjectEditor (projectDescriptionCredits, programId, textFields) {
  const self = this

  this.programId = programId
  this.textFields = textFields

  this.body = $('body')
  this.editTextUI = $('#edit-text-ui')
  this.languageSelectorList = $('#edit-language-selector-list')
  this.selectedLanguage = $('#edit-selected-language')
  this.textLoadingSpinner = $('#edit-loading-spinner')
  this.saveButton = $('#edit-submit-button')

  this.languageSelect = new MDCSelect(document.querySelector('#edit-language-selector'))
  this.languages = {}
  this.previousIndex = 0

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

  this.confirmDeleteDialog = new ProgramEditorDialog(
    projectDescriptionCredits.data('trans-confirm-delete'),
    projectDescriptionCredits.data('trans-cancel'),
    projectDescriptionCredits.data('trans-delete')
  )

  this.defaultText = projectDescriptionCredits.data('trans-default')
  this.translationUpdated = projectDescriptionCredits.data('trans-translation-updated')
  this.translationDeleted = projectDescriptionCredits.data('trans-translation-deleted')
  this.cannotDelete = projectDescriptionCredits.data('trans-cannot-delete')

  this.saveButton.on('click', () => { this.save() })

  $('#edit-delete-button').on('click', () => { this.deleteTranslation() })

  this.languageSelect.listen('MDCSelect:change', () => {
    if (!this.editTextUI.is(':visible') || this.languageSelect.selectedIndex === this.previousIndex) {
      return
    }

    if (this.areChangesSaved()) {
      this.previousIndex = this.languageSelect.selectedIndex
      this.getNewText()
    } else {
      this.keepOrDiscardDialog.show(keepOrDiscardChangesResult)
    }
  })

  $('.mdc-text-field__input').on('input', function () {
    disableButtons(self.areChangesSaved())
  })

  $(document).ready(getLanguages)

  this.show = (navigationCallback, language, showLanguageSelect, showDeleteButton, headerText) => {
    this.navigationCallback = navigationCallback
    this.showLanguageSelect = showLanguageSelect
    this.showDeleteButton = showDeleteButton

    if (language !== null) {
      this.languageSelect.value = language
      this.languageSelect.emit('change')
    }

    this.getNewText()

    const langaugeSelectElement = $('#edit-language-selector')
    if (this.showLanguageSelect) {
      langaugeSelectElement.removeClass('d-none')
    } else {
      langaugeSelectElement.addClass('d-none')
    }

    const deleteButtonElement = $('#edit-delete-button')
    if (this.showDeleteButton) {
      deleteButtonElement.removeClass('d-none')
    } else {
      deleteButtonElement.addClass('d-none')
    }

    window.history.pushState(
      { type: 'ProjectEditor', id: programId, full: true },
      $(this).text(),
      '#editor'
    )

    $(window).on('popstate', this.popStateHandler)
    showCustomTopBarTitle(headerText, function () {
      window.history.back()
    })

    this.body.addClass('overflow-hidden')
    this.editTextUI.removeClass('d-none')
  }

  // region private
  this.popStateHandler = function () {
    if (self.areChangesSaved()) {
      self.close()
    } else {
      self.closeEditorDialog.show(closeEditorDialogResult)
    }
  }

  this.close = () => {
    $(window).off('popstate', this.popStateHandler)

    if (this.reloadScreen) {
      window.location.reload()
    }

    this.editTextUI.addClass('d-none')

    this.reset()
    this.navigationCallback()
  }

  this.reset = () => {
    this.languageSelect.selectedIndex = 0
    this.previousIndex = 0
    this.getNewText()
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
    return this.textFields.every(textField => textField.areChangesSaved())
  }

  function disableButtons (disable) {
    self.saveButton.attr('disabled', disable)
  }

  function keepOrDiscardChangesResult (result) {
    if (result.isConfirmed) {
      self.previousIndex = self.languageSelect.selectedIndex
      for (const textField of self.textFields) {
        textField.getNewTextKeepChanges(self.languageSelect.value)
      }
    } else if (result.isDenied) {
      self.previousIndex = self.languageSelect.selectedIndex
      self.getNewText()
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
    Promise.all([
      this.textFields[0].save(this.languageSelect.value),
      this.textFields[1].save(this.languageSelect.value),
      this.textFields[2].save(this.languageSelect.value)
    ]).then(function (results) {
      if (self.languageSelect.value === '' || self.languageSelect.value === 'default') {
        self.reloadScreen = true
      }

      const updatedText = self.translationUpdated.replace('%language%', self.selectedLanguage.text())
      showSnackbar('#share-snackbar', updatedText)

      if (results.length === self.textFields.length) {
        self.close()
      }
    }).catch(function (reason) {
      for (const error of reason) {
        if (error === 401) {
          window.location.href = '../login'
        }
      }
    })
  }

  this.deleteTranslation = () => {
    const languageSelected = this.languageSelect.value
    if (languageSelected === '' || languageSelected === 'default') {
      showSnackbar('#share-snackbar', this.cannotDelete)
    } else {
      this.confirmDeleteDialog.show(deleteTranslationResult)
    }
  }

  function deleteTranslationResult (result) {
    if (result.isDenied) {
      const languageSelected = self.languageSelect.value
      Promise.all([
        self.textFields[0].delete(languageSelected),
        self.textFields[1].delete(languageSelected),
        self.textFields[2].delete(languageSelected)
      ]).then(function (results) {
        const deletedText = self.translationDeleted.replace('%language%', self.selectedLanguage.text())
        showSnackbar('#share-snackbar', deletedText)

        if (results.length === self.textFields.length) {
          self.close()
        }
      }).catch(function (reason) {
        for (const error of reason) {
          if (error === 401) {
            window.location.href = '../login'
          }
        }
      })
    }
  }

  self.getNewText = () => {
    for (const textField of this.textFields) {
      textField.getNewText(this.languageSelect.value)
    }

    disableButtons(true)
  }

  // end region
}
