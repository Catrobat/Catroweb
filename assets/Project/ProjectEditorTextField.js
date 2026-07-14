export function ProjectEditorTextField(model) {
  this.editText = document.querySelector(`#edit-${model.programSection}-text`)
  this.editTextError = document.querySelector(`#edit-${model.programSection}-text-error`)
  this.textLoadingSpinner = document.querySelector(`#edit-${model.programSection}-loading-spinner`)

  this.editText.addEventListener('input', () => {
    model.setText(this.editText.value.trim())
  })

  model.setOnTextChanged((text) => {
    this.editText.value = text
  })

  model.setOnError((message) => {
    if (message !== '') {
      this.editText.error = true
      this.editText.errorText = message
      this.editTextError.style.display = 'block'
      this.editTextError.textContent = message
    } else {
      this.editText.error = false
      this.editText.errorText = ''
      this.editTextError.style.display = 'none'
    }
  })

  model.setOnEnabled((enabled) => {
    if (enabled) {
      this.editText.disabled = false
    } else {
      this.editText.disabled = true
    }
  })

  model.setOnLoading((loading) => {
    this.textLoadingSpinner.style.display = loading === true ? 'block' : 'none'
  })
}
