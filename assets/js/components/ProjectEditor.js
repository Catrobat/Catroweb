import $ from 'jquery'
import { MDCSelect } from '@material/select'
import { ProjectEditorDialog } from '../custom/ProjectEditorDialog'
import { showCustomTopBarTitle } from '../layout/top_bar'
import { DIALOG } from './ProjectEditorModel'

export function ProjectEditor(projectDescriptionCredits, programId, model) {
  this.body = $('body')
  this.editTextUI = $('#edit-text-ui')
  this.languageSelectorList = $('#edit-language-selector-list')
  this.selectedLanguage = $('#edit-selected-language')
  this.textLoadingSpinner = $('#edit-loading-spinner')
  this.saveButton = $('#edit-submit-button')

  this.languageSelect = new MDCSelect(
    document.querySelector('#edit-language-selector'),
  )

  this.closeEditorDialog = new ProjectEditorDialog(
    projectDescriptionCredits.data('trans-close-editor-prompt'),
    projectDescriptionCredits.data('trans-save'),
    projectDescriptionCredits.data('trans-discard'),
  )

  this.confirmDeleteDialog = new ProjectEditorDialog(
    projectDescriptionCredits.data('trans-confirm-delete'),
    projectDescriptionCredits.data('trans-cancel'),
    projectDescriptionCredits.data('trans-delete'),
  )

  this.saveButton.on('click', model.save)

  $('#edit-delete-button').on('click', model.deleteTranslation)

  this.languageSelect.listen('MDCSelect:change', () => {
    if (!this.editTextUI.is(':visible')) {
      return
    }

    model.setLanguage(this.languageSelect.value)
  })

  $('.mdc-text-field__input').on('input', model.onInput)

  this.show = (
    navigationCallback,
    language,
    showLanguageSelect,
    showDeleteButton,
    headerText,
  ) => {
    this.navigationCallback = navigationCallback
    this.showLanguageSelect = showLanguageSelect
    this.showDeleteButton = showDeleteButton

    model.show(language)

    const languageSelectElement = $('#edit-language-selector')
    if (this.showLanguageSelect) {
      languageSelectElement.removeClass('d-none')
    } else {
      languageSelectElement.addClass('d-none')
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
      '#editor',
    )

    $(window).on('popstate', model.popStateHandler)
    showCustomTopBarTitle(headerText, function () {
      window.history.back()
    })

    this.body.addClass('overflow-hidden')
    this.editTextUI.removeClass('d-none')
  }

  // region private
  this.close = () => {
    $(window).off('popstate', model.popStateHandler)

    this.editTextUI.addClass('d-none')

    this.navigationCallback()
  }

  model.setOnLanguageList((languages) => {
    this.languageSelectorList.empty()

    for (const language in languages) {
      this.languageSelectorList
        .append(`<li class="mdc-list-item" data-value="${language}" role="option" tabindex="-1">\
        <span class="mdc-list-item__ripple"></span>\
        <span class="mdc-list-item__text">${languages[language]}</span>\
        </li>`)
    }

    this.languageSelect.layoutOptions()
  })

  model.setOnDialog((dialog) => {
    switch (dialog) {
      case DIALOG.CLOSE_EDITOR:
        this.closeEditorDialog.show(model.closeEditorResult)
        break
      case DIALOG.CONFIRM_DELETE:
        this.confirmDeleteDialog.show(model.deleteTranslationResult)
        break
    }
  })

  model.setOnButtonEnabled((enabled) =>
    this.saveButton.attr('disabled', !enabled),
  )

  model.setOnLanguageSelected((languageIndex) => {
    this.languageSelect.selectedIndex = languageIndex
  })

  model.setOnClose(() => this.close())

  model.setOnReload(() => window.location.reload())

  model.setOnUnauthorized(() => {
    window.location.href = '../login'
  })
  // end region
}
