import { ByLineElementContainer, Translation } from './Translation'

export class TranslateComments extends Translation {
  constructor(translatedByLine, googleTranslateDisplayName) {
    super(translatedByLine, googleTranslateDisplayName)
    this._initListeners()
  }

  _initListeners() {
    const translateComments = this
    document
      .querySelectorAll('.comment-translation-button')
      .forEach((button) => {
        button.addEventListener('click', function (event) {
          event.stopPropagation()
          const commentId = this.id.substring(
            'comment-translation-button-'.length,
          )
          const matchingContainer = document.querySelector(
            '.comment-translation[data-translate-comment-id="translate-comment-' +
              commentId +
              '"]',
          )
          const translateCommentUrl =
            matchingContainer.dataset.pathTranslateComment

          this.style.display = 'none'

          if (
            translateComments.isTranslationNotAvailable(
              '#comment-text-translation-' + commentId,
            )
          ) {
            document.getElementById(
              'comment-translation-loading-spinner-' + commentId,
            ).style.display = 'block'
            translateComments.translateComment(translateCommentUrl, commentId)
          } else {
            translateComments.openTranslatedComment(commentId)
          }
        })
      })

    document
      .querySelectorAll('.remove-comment-translation-button')
      .forEach((button) => {
        button.addEventListener('click', function (event) {
          event.stopPropagation()
          const commentId = this.id.substring(
            'remove-comment-translation-button-'.length,
          )
          this.style.display = 'none'
          document.getElementById(
            'comment-translation-button-' + commentId,
          ).style.display = 'block'
          document.getElementById(
            'comment-translation-wrapper-' + commentId,
          ).style.display = 'none'
          document.getElementById(
            'comment-text-wrapper-' + commentId,
          ).style.display = 'block'
        })
      })
  }

  setTranslatedCommentData(commentId, data) {
    const commentTextTranslation = document.getElementById(
      'comment-text-translation-' + commentId,
    )
    commentTextTranslation.textContent = data.translation
    commentTextTranslation.setAttribute('lang', data.target_language)

    const byLineElements = new ByLineElementContainer(
      document.getElementById(
        'comment-translation-before-languages-' + commentId,
      ),
      document.getElementById(
        'comment-translation-between-languages-' + commentId,
      ),
      document.getElementById(
        'comment-translation-after-languages-' + commentId,
      ),
      document.getElementById(
        'comment-translation-first-language-' + commentId,
      ),
      document.getElementById(
        'comment-translation-second-language-' + commentId,
      ),
    )

    this.setTranslationCredit(data, byLineElements)
  }

  openTranslatedComment(commentId) {
    document.getElementById(
      'comment-translation-loading-spinner-' + commentId,
    ).style.display = 'none'
    document.getElementById(
      'remove-comment-translation-button-' + commentId,
    ).style.display = 'block'
    document.getElementById(
      'comment-translation-wrapper-' + commentId,
    ).style.display = 'block'
    document.getElementById('comment-text-wrapper-' + commentId).style.display =
      'none'
  }

  commentNotTranslated(commentId) {
    document.getElementById(
      'comment-translation-loading-spinner-' + commentId,
    ).style.display = 'none'
    document.getElementById(
      'comment-translation-button-' + commentId,
    ).style.display = 'block'
    this.openGoogleTranslatePage(
      document.getElementById('comment-text-' + commentId).innerText,
    )
  }

  translateComment(translateCommentUrl, commentId) {
    const self = this
    fetch(translateCommentUrl + '?target_language=' + self.targetLanguage, {
      method: 'GET',
    })
      .then((response) => response.json())
      .then((data) => {
        self.setTranslatedCommentData(commentId, data)
        self.openTranslatedComment(commentId)
      })
      .catch((e) => {
        self.commentNotTranslated(commentId)
      })
  }
}
