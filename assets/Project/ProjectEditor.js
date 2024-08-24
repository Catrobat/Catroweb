import { MDCSelect } from '@material/select'
import { ProjectEditorDialog } from './ProjectEditorDialog'
import { showCustomTopBarTitle } from '../Layout/TopBar'
import { DIALOG } from './ProjectEditorModel'

export function ProjectEditor(projectDescriptionCredits, programId, model) {
  this.body = document.body
  this.editTextUI = document.getElementById('edit-text-ui')
  this.languageSelectorList = document.getElementById(
    'edit-language-selector-list',
  )
  this.saveButton = document.getElementById('edit-submit-button')

  this.languageSelect = new MDCSelect(
    document.querySelector('#edit-language-selector'),
  )

  this.closeEditorDialog = new ProjectEditorDialog(
    projectDescriptionCredits.dataset.transCloseEditorPrompt,
    projectDescriptionCredits.dataset.transSave,
    projectDescriptionCredits.dataset.transDiscard,
  )

  this.confirmDeleteDialog = new ProjectEditorDialog(
    projectDescriptionCredits.dataset.transConfirmDelete,
    projectDescriptionCredits.dataset.transCancel,
    projectDescriptionCredits.dataset.transDelete,
  )

  this.saveButton.addEventListener('click', model.save)

  document
    .getElementById('edit-delete-button')
    .addEventListener('click', model.deleteTranslation)

  this.languageSelect.listen('MDCSelect:change', () => {
    if (!this.editTextUI.classList.contains('d-none')) {
      model.setLanguage(this.languageSelect.value)
    }
  })

  Array.from(document.querySelectorAll('.mdc-text-field__input')).forEach(
    (input) => {
      input.addEventListener('input', model.onInput)
    },
  )

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

    const languageSelectElement = document.getElementById(
      'edit-language-selector',
    )
    if (this.showLanguageSelect) {
      languageSelectElement.classList.remove('d-none')
    } else {
      languageSelectElement.classList.add('d-none')
    }

    const deleteButtonElement = document.getElementById('edit-delete-button')
    if (this.showDeleteButton) {
      deleteButtonElement.classList.remove('d-none')
    } else {
      deleteButtonElement.classList.add('d-none')
    }

    window.history.pushState(
      { type: 'ProjectEditor', id: programId, full: true },
      document.title,
      '#editor',
    )

    window.addEventListener('popstate', model.popStateHandler)
    showCustomTopBarTitle(headerText, () => {
      window.history.back()
    })

    this.body.classList.add('overflow-hidden')
    this.editTextUI.classList.remove('d-none')
  }

  // region private
  this.close = () => {
    window.removeEventListener('popstate', model.popStateHandler)

    this.editTextUI.classList.add('d-none')

    this.navigationCallback()
  }

  model.setOnLanguageList((languages) => {
    this.languageSelectorList.innerHTML = ''

    for (const language in languages) {
      const listItem = document.createElement('li')
      listItem.classList.add('mdc-list-item')
      listItem.dataset.value = language
      listItem.setAttribute('role', 'option')
      listItem.setAttribute('tabindex', '-1')
      listItem.innerHTML = `
        <span class="mdc-list-item__ripple"></span>
        <span class="mdc-list-item__text">${languages[language]}</span>
      `
      this.languageSelectorList.appendChild(listItem)
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

  model.setOnButtonEnabled((enabled) => {
    if (enabled) {
      this.saveButton.removeAttribute('disabled')
    } else {
      this.saveButton.setAttribute('disabled', 'disabled')
    }
  })

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
