/**
 * Copy text to clipboard with fallback for non-HTTPS environments.
 * Tries: Web Share API → Clipboard API → textarea execCommand fallback.
 */
export function shareOrCopy(url, onSuccess = () => {}) {
  if (navigator.share) {
    navigator.share({ url }).catch(() => {})
    return
  }

  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard
      .writeText(url)
      .then(onSuccess)
      .catch(() => {
        fallbackCopy(url)
        onSuccess()
      })
    return
  }

  fallbackCopy(url)
  onSuccess()
}

function fallbackCopy(text) {
  const textarea = document.createElement('textarea')
  textarea.value = text
  textarea.style.position = 'fixed'
  textarea.style.opacity = '0'
  document.body.appendChild(textarea)
  textarea.select()
  document.execCommand('copy')
  document.body.removeChild(textarea)
}
