import $ from 'jquery'

export class PasswordVisibilityToggle {
  constructor (selector = '.password-toggle') {
    for (const element of document.querySelectorAll(selector)) {
      new SinglePasswordVisibilityToggle(element).initListeners()
    }
  }
}

class SinglePasswordVisibilityToggle {
  constructor (ref) {
    this.$toggleButton = $(ref)
  }

  initListeners () {
    const thisInstance = this
    this.$toggleButton.on('click', function (event) {
      event.preventDefault()
      thisInstance.toggleVisibility()
    })
  }

  toggleVisibility () {
    const passwordField = this.$toggleButton.parent().find('input')
    if (passwordField.attr('type') === 'text') {
      passwordField.attr('type', 'password')
      this.$toggleButton.text('visibility')
    } else if (passwordField.attr('type') === 'password') {
      passwordField.attr('type', 'text')
      this.$toggleButton.text('visibility_off')
    }
  }
}
