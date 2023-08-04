import $ from 'jquery'
import { ByLineElementContainer, Translation } from './Translation'

export class TranslateComments extends Translation {
  constructor(translatedByLine, googleTranslateDisplayName) {
    super(translatedByLine, googleTranslateDisplayName)
    this._initListeners()
  }

  _initListeners() {
    const translateComments = this
    $('.comment-translation-button').on('click', function (event) {
      event.stopPropagation()
      const commentId = $(this)
        .attr('id')
        .substring('comment-translation-button-'.length)
      const translateCommentUrl = $('.comment-translation').data(
        'path-translate-comment',
      )

      $(this).hide()

      if (
        translateComments.isTranslationNotAvailable(
          '#comment-text-translation-' + commentId,
        )
      ) {
        $('#comment-translation-loading-spinner-' + commentId).show()
        translateComments.translateComment(translateCommentUrl, commentId)
      } else {
        translateComments.openTranslatedComment(commentId)
      }
    })

    $('.remove-comment-translation-button').on('click', function (event) {
      event.stopPropagation()
      const commentId = $(this)
        .attr('id')
        .substring('remove-comment-translation-button-'.length)
      $(this).hide()
      $('#comment-translation-button-' + commentId).show()
      $('#comment-translation-wrapper-' + commentId).slideUp()
      $('#comment-text-wrapper-' + commentId).slideDown()
    })
  }

  setTranslatedCommentData(commentId, data) {
    $('#comment-text-translation-' + commentId).text(data.translation)
    $('#comment-text-translation-' + commentId).attr(
      'lang',
      data.target_language,
    )

    const byLineElements = new ByLineElementContainer(
      '#comment-translation-before-languages-' + commentId,
      '#comment-translation-between-languages-' + commentId,
      '#comment-translation-after-languages-' + commentId,
      '#comment-translation-first-language-' + commentId,
      '#comment-translation-second-language-' + commentId,
    )

    this.setTranslationCredit(data, byLineElements)
  }

  openTranslatedComment(commentId) {
    $('#comment-translation-loading-spinner-' + commentId).hide()
    $('#remove-comment-translation-button-' + commentId).show()
    $('#comment-translation-wrapper-' + commentId).slideDown()
    $('#comment-text-wrapper-' + commentId).slideUp()
  }

  commentNotTranslated(commentId) {
    $('#comment-translation-loading-spinner-' + commentId).hide()
    $('#comment-translation-button-' + commentId).show()
    this.openGoogleTranslatePage(
      document.getElementById('comment-text-' + commentId).innerText,
    )
  }

  translateComment(translateCommentUrl, commentId) {
    const self = this
    $.ajax({
      url: translateCommentUrl,
      type: 'get',
      data: { target_language: self.targetLanguage },
      success: function (data) {
        self.setTranslatedCommentData(commentId, data)
        self.openTranslatedComment(commentId)
      },
      error: function () {
        self.commentNotTranslated(commentId)
      },
    })
  }
}
