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
    const passwordField = this.toggleButton.parentElement.querySelector('input')
    if (passwordField.getAttribute('type') === 'text') {
      passwordField.setAttribute('type', 'password')
      this.toggleButton.textContent = 'visibility'
    } else if (passwordField.getAttribute('type') === 'password') {
      passwordField.setAttribute('type', 'text')
      this.toggleButton.textContent = 'visibility_off'
    }
  }
}
