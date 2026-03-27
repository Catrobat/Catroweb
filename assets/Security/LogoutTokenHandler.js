export class LogoutTokenHandler {
  constructor() {
    const routingDataset = document.getElementById('js-api-routing').dataset
    this.authenticationPath = routingDataset.authentication
    this.initListeners()
  }

  initListeners() {
    const self = this
    const logoutButton = document.getElementById('btn-logout')
    if (!logoutButton) {
      return
    }
    logoutButton.onclick = function () {
      fetch(self.authenticationPath, {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: {
          'X-Refresh': 'cookie',
        },
      }).finally(() => {
        if (logoutButton.dataset && logoutButton.dataset.logoutPath) {
          window.location.href = logoutButton.dataset.logoutPath
        }
      })
    }
  }
}
