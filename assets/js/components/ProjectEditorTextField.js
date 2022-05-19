import $ from 'jquery'
import { MDCTextField } from '@material/textfield'

export function ProjectEditorTextField (model) {
  this.editText = $('#edit-' + model.programSection + '-text')
  this.editTextError = $('#edit-' + model.programSection + '-text-error')
  this.textLoadingSpinner = $('#edit-' + model.programSection + '-loading-spinner')
  this.selectedLanguage = $('#edit-selected-language')

  new MDCTextField(document.querySelector('#edit-' + model.programSection + '-mdc-text-field'))

  this.editText.on('input', () => {
    model.setText(this.editText.val().trim())
  })

  model.setOnTextChanged((text) => {
    this.editText.val(text)
  })

  model.setOnError((message) => {
    if (message !== '') {
      this.editText.addClass('danger')
      this.editTextError.show()
      this.editTextError.text(message)
    } else {
      this.editTextError.hide()
    }
  })

  model.setOnEnabled((enabled) => {
    if (enabled === true) {
      this.editText.removeAttr('disabled')
    } else {
      this.editText.attr('disabled', '')
    }
  })

  model.setOnLoading((loading) => {
    if (loading === true) {
      this.textLoadingSpinner.show()
    } else {
      this.textLoadingSpinner.hide()
    }
  })
}
