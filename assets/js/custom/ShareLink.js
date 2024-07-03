import Clipboard from 'clipboard'
import { showSnackbar } from '../components/snackbar'

export function shareLink(
  themeDisplayName,
  checkOutTranslation,
  url,
  shareSuccess,
  shareError,
  copy,
  clipboardSuccess,
  clipboardFail,
) {
  const shareButton = document.querySelector('#top-app-bar__btn-share')
  const shareButtonText = document.querySelector('#top-app-bar__btn-share-text')
  if (navigator.share) {
    shareButton.addEventListener('click', function () {
      navigator
        .share({
          title: themeDisplayName,
          text: checkOutTranslation,
          url,
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
    shareButtonText.textContent = copy
    const cb = new Clipboard('#top-app-bar__btn-share')
    cb.on('success', function () {
      showSnackbar('#share-snackbar', clipboardSuccess)
    })
    cb.on('error', function () {
      showSnackbar('#share-snackbar', clipboardFail)
    })
  }
}
