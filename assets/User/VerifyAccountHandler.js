import AcceptLanguage from '../Api/AcceptLanguage'
import { showSnackbar, SnackbarDuration } from '../Layout/Snackbar'

export default class {
  init() {
    const routingDataset = document.getElementById('js-api-routing').dataset
    const baseUrl = routingDataset.index
    const btn = document.getElementById('btn-verify-account')
    btn.addEventListener('click', () => {
      btn.setAttribute('disabled', 'disabled')
      fetch(baseUrl + 'verify', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'Accept-Language': new AcceptLanguage().get(),
        },
      }).then((response) => {
        switch (response.status) {
          case 204:
            showSnackbar('#share-snackbar', btn.dataset.success)
            break
          case 401:
            window.location.href = baseUrl + 'login'
            break
          case 403:
            showSnackbar('#share-snackbar', btn.dataset.failed, SnackbarDuration.error)
            break
        }
      })
    })
  }
}
