export class PasswordVisibilityToggle {
  constructor(selector = '.password-toggle') {
    for (const element of document.querySelectorAll(selector)) {
      new SinglePasswordVisibilityToggle(element).initListeners()
    }
  }
}

class SinglePasswordVisibilityToggle {
  constructor(ref) {
    this.toggleButton = ref
  }

  initListeners() {
    this.toggleButton.addEventListener('click', (event) => {
      event.preventDefault()
      this.toggleVisibility()
    })
  }

  toggleVisibility() {
    const passwordField = this.toggleButton.dataset.passwordField
      ? document.getElementById(this.toggleButton.dataset.passwordField)
      : this.toggleButton.parentElement.querySelector('input')
    if (!passwordField) return

    const icon = this.toggleButton.querySelector('.material-icons') || this.toggleButton
    if (passwordField.type === 'text') {
      passwordField.type = 'password'
      icon.textContent = 'visibility'
    } else if (passwordField.type === 'password') {
      passwordField.type = 'text'
      icon.textContent = 'visibility_off'
    }
  }
}
