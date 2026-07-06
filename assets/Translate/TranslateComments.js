import { ByLineElementContainer, Translation } from './Translation'

export class TranslateComments extends Translation {
  constructor(translatedByLine, googleTranslateDisplayName) {
    super(translatedByLine, googleTranslateDisplayName)
    this._initListeners()
  }

  _initListeners() {
    const commentsWrapper = document.querySelector('#comments-wrapper')

    if (commentsWrapper) {
      commentsWrapper.addEventListener('click', (event) => {
        const button = event.target.closest('.comment-translation-button')
        if (button) {
          event.stopPropagation()
          const commentId = button.id.substring('comment-translation-button-'.length)
          const matchingContainer = document.querySelector(
            `.comment-translation[data-translate-comment-id="translate-comment-${commentId}"]`,
          )
          const translateCommentUrl = matchingContainer.dataset.pathTranslateComment

          button.style.display = 'none'

          if (this.isTranslationNotAvailable(`#comment-text-translation-${commentId}`)) {
            document.getElementById(
              `comment-translation-loading-spinner-${commentId}`,
            ).style.display = 'block'
            this.translateComment(translateCommentUrl, commentId)
          } else {
            this.openTranslatedComment(commentId)
          }
        }

        const removeButton = event.target.closest('.remove-comment-translation-button')
        if (removeButton) {
          event.stopPropagation()
          const commentId = removeButton.id.substring('remove-comment-translation-button-'.length)
          removeButton.style.display = 'none'
          document.getElementById(`comment-translation-button-${commentId}`).style.display = 'block'
          document.getElementById(`comment-translation-wrapper-${commentId}`).style.display = 'none'
          document.getElementById(`comment-text-wrapper-${commentId}`).style.display = 'block'
        }
      })
    }
  }

  setTranslatedCommentData(commentId, data) {
    const commentTextTranslation = document.getElementById(`comment-text-translation-${commentId}`)
    commentTextTranslation.textContent = data.translation
    commentTextTranslation.setAttribute('lang', data.target_language)

    const byLineElements = new ByLineElementContainer(
      document.getElementById(`comment-translation-before-languages-${commentId}`),
      document.getElementById(`comment-translation-between-languages-${commentId}`),
      document.getElementById(`comment-translation-after-languages-${commentId}`),
      document.getElementById(`comment-translation-first-language-${commentId}`),
      document.getElementById(`comment-translation-second-language-${commentId}`),
    )

    this.setTranslationCredit(data, byLineElements)
  }

  openTranslatedComment(commentId) {
    document.getElementById(`comment-translation-loading-spinner-${commentId}`).style.display =
      'none'
    document.getElementById(`remove-comment-translation-button-${commentId}`).style.display =
      'block'
    document.getElementById(`comment-translation-wrapper-${commentId}`).style.display = 'block'
    document.getElementById(`comment-text-wrapper-${commentId}`).style.display = 'none'
  }

  commentNotTranslated(commentId) {
    document.getElementById(`comment-translation-loading-spinner-${commentId}`).style.display =
      'none'
    document.getElementById(`comment-translation-button-${commentId}`).style.display = 'block'
    this.openGoogleTranslatePage(document.getElementById(`comment-text-${commentId}`).innerText)
  }

  translateComment(translateCommentUrl, commentId) {
    fetch(`${translateCommentUrl}?target_language=${this.targetLanguage}`, {
      method: 'GET',
    })
      .then((response) => response.json())
      .then((data) => {
        this.setTranslatedCommentData(commentId, data)
        this.openTranslatedComment(commentId)
      })
      .catch(() => {
        this.commentNotTranslated(commentId)
      })
  }
}
