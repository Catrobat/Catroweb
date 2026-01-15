import { MDCTextField } from '@material/textfield'

export function ProjectEditorTextField(model) {
  this.editText = document.querySelector('#edit-' + model.programSection + '-text')
  this.editTextError = document.querySelector('#edit-' + model.programSection + '-text-error')
  this.textLoadingSpinner = document.querySelector(
    '#edit-' + model.programSection + '-loading-spinner',
  )

  const editorTextMdcRoot = document.querySelector(
    '#edit-' + model.programSection + '-mdc-text-field',
  )
  if (editorTextMdcRoot) {
    new MDCTextField(editorTextMdcRoot)
  }

  this.editText.addEventListener('input', () => {
    model.setText(this.editText.value.trim())
  })

  model.setOnTextChanged((text) => {
    this.editText.value = text
  })

  model.setOnError((message) => {
    if (message !== '') {
      this.editText.classList.add('danger')
      this.editTextError.style.display = 'block'
      this.editTextError.textContent = message
    } else {
      this.editText.classList.remove('danger')
      this.editTextError.style.display = 'none'
    }
  })

  model.setOnEnabled((enabled) => {
    if (enabled) {
      this.editText.removeAttribute('disabled')
    } else {
      this.editText.setAttribute('disabled', 'disabled')
    }
  })

  model.setOnLoading((loading) => {
    this.textLoadingSpinner.style.display = loading === true ? 'block' : 'none'
  })
}
