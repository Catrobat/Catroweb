/* eslint-env jquery */

/* eslint no-undef: "off" */
// eslint-disable-next-line no-unused-vars
function ProgramShare (themeDisplayName, checkOutProject, url, shareSuccess, shareError, copy,
  clipboardSuccess, clipboardFail) {
  const self = this
  self.themeDisplayName = themeDisplayName
  self.checkOutProject = checkOutProject
  self.url = url
  self.shareSuccess = shareSuccess
  self.shareError = shareError
  self.copy = copy
  self.clipboardSuccess = clipboardSuccess
  self.clipboardFail = clipboardFail
  self.clipboard = function () {
    if (navigator.share) {
      $('#top-app-bar__btn-share').on('click', function () {
        navigator.share({
          title: self.themeDisplayName,
          text: self.checkOutProject,
          url: self.url
        })
          .then(() => {
            showSnackbar('#share-snackbar', self.shareSuccess)
          })
        // eslint-disable-next-line handle-callback-err
          .catch((error) => {
            showSnackbar('#share-snackbar', self.shareError)
          })
      })
    } else {
      // Web Share API is still very limited - provide copy action as fallback
      $('#top-app-bar__btn-share-text').text(self.copy)
      const cb = new ClipboardJS('#top-app-bar__btn-share')
      cb.on('success', function () {
        showSnackbar('#share-snackbar', self.clipboardSuccess)
      })
      cb.on('error', function () {
        showSnackbar('#share-snackbar', self.clipboardFail)
      })
    }
  }
}
