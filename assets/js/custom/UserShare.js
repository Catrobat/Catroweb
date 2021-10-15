import $ from 'jquery'
import Clipboard from 'clipboard'
import { showSnackbar } from '../components/snackbar'

export function shareUser (
  themeDisplayName,
  checkOutProject,
  url,
  shareSuccess,
  shareError,
  copy,
  clipboardSuccess,
  clipboardFail
) {
  if (navigator.share) {
    $('#top-app-bar__btn-share').on('click', function () {
      navigator.share({
        title: themeDisplayName,
        text: checkOutProject,
        url: url
      })
        .then(() => {
          showSnackbar('#share-snackbar', shareSuccess)
        })
        .catch((e) => {
          console.error(e)
          showSnackbar('#share-snackbar', shareError)
        })
    })
  } else {
    // Web Share API is still very limited - provide copy action as fallback
    $('#top-app-bar__btn-share-text').text(copy)
    const cb = new Clipboard('#top-app-bar__btn-share')
    cb.on('success', function () {
      showSnackbar('#share-snackbar', clipboardSuccess)
    })
    cb.on('error', function () {
      showSnackbar('#share-snackbar', clipboardFail)
    })
  }
}
