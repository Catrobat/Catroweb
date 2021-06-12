/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
function Translation (translatedByLine) {
  const providerMap = {
    itranslate: 'iTranslate'
  }
  let displayLanguageMap = {}
  let translatedByLineMap = {}
  let targetLanguage = null

  $(document).ready(function () {
    setTargetLanguage()
    getDisplayLanguageMap()
    splitTranslatedByLine()
  })

  $(document).on('click', '.comment-translation-button', function () {
    const commentId = $(this).attr('id').substring('comment-translation-button-'.length)

    $(this).hide()

    if (isTranslationNotAvailable(commentId)) {
      $('#comment-translation-loading-spinner-' + commentId).show()
      translateComment(commentId)
    } else {
      openTranslatedComment(commentId)
    }
  })

  $(document).on('click', '.remove-comment-translation-button', function () {
    const commentId = $(this).attr('id').substring('remove-comment-translation-button-'.length)
    $(this).hide()
    $('#comment-translation-button-' + commentId).show()
    $('#comment-translation-wrapper-' + commentId).slideUp()
    $('#comment-text-wrapper-' + commentId).slideDown()
  })

  function setTargetLanguage () {
    const decodedCookie = document.cookie
      .split(';')
      .map(v => v.split('='))
      .reduce((acc, v) => {
        acc[decodeURIComponent(v[0].trim())] = decodeURIComponent(v[1].trim())
        return acc
      }, {})

    if (decodedCookie.hl !== undefined) {
      targetLanguage = decodedCookie.hl.replace('_', '-')
    } else {
      targetLanguage = document.documentElement.lang
    }
  }

  function getDisplayLanguageMap () {
    $.ajax({
      url: '../languages',
      type: 'get',
      success: function (data) {
        displayLanguageMap = data
      }
    })
  }

  function splitTranslatedByLine () {
    let firstLanguage = '%sourceLanguage%'
    let secondLanguage = '%targetLanguage%'
    if (!isSourceLanguageFirst()) {
      firstLanguage = '%targetLanguage%'
      secondLanguage = '%sourceLanguage%'
    }

    translatedByLineMap = {
      before: translatedByLine.substring(0, translatedByLine.indexOf(firstLanguage)),
      between: translatedByLine.substring(translatedByLine.indexOf(firstLanguage) + firstLanguage.length, translatedByLine.indexOf(secondLanguage)),
      after: translatedByLine.substring(translatedByLine.indexOf(secondLanguage) + secondLanguage.length)
    }
  }

  function isTranslationNotAvailable (commentId) {
    return $('#comment-text-translation-' + commentId).attr('lang') !== targetLanguage
  }

  function isSourceLanguageFirst () {
    return translatedByLine.indexOf('%sourceLanguage%') < translatedByLine.indexOf('%targetLanguage%')
  }

  function openGoogleTranslatePage (commentId) {
    const text = document.getElementById('comment-text-' + commentId).innerText
    window.open(
      'https://translate.google.com/?q=' + encodeURIComponent(text) + '&sl=auto&tl=' + targetLanguage,
      '_self'
    )
  }

  function setTranslatedCommentData (commentId, data) {
    $('#comment-text-translation-' + commentId).text(data.translation)
    $('#comment-text-translation-' + commentId).attr('lang', data.target_language)

    $('#comment-translation-before-languages-' + commentId).text(translatedByLineMap.before.replace('%provider%', providerMap[data.provider]))
    $('#comment-translation-between-languages-' + commentId).text(translatedByLineMap.between.replace('%provider%', providerMap[data.provider]))
    $('#comment-translation-after-languages-' + commentId).text(translatedByLineMap.after.replace('%provider%', providerMap[data.provider]))

    if (isSourceLanguageFirst()) {
      $('#comment-translation-first-language-' + commentId).text(displayLanguageMap[data.source_language])
      $('#comment-translation-second-language-' + commentId).text(displayLanguageMap[data.target_language])
    } else {
      $('#comment-translation-first-language-' + commentId).text(displayLanguageMap[data.target_language])
      $('#comment-translation-second-language-' + commentId).text(displayLanguageMap[data.source_language])
    }
  }

  function openTranslatedComment (commentId) {
    $('#comment-translation-loading-spinner-' + commentId).hide()
    $('#remove-comment-translation-button-' + commentId).show()
    $('#comment-translation-wrapper-' + commentId).slideDown()
    $('#comment-text-wrapper-' + commentId).slideUp()
  }

  function commentNotTranslated (commentId) {
    $('#comment-translation-loading-spinner-' + commentId).hide()
    $('#comment-translation-button-' + commentId).show()
    openGoogleTranslatePage(commentId)
  }

  function translateComment (commentId) {
    $.ajax({
      url: '../translate/comment/' + commentId,
      type: 'get',
      data: { target_language: targetLanguage },
      success: function (data) {
        setTranslatedCommentData(commentId, data)
        openTranslatedComment(commentId)
      },
      error: function () {
        commentNotTranslated(commentId)
      }
    })
  }
}
