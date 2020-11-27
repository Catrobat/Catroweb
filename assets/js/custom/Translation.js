/* eslint-env jquery */

function Translation(hasDescription, hasCredit) {
  const elementsToTranslate = [document.getElementById('name')]

  if (hasDescription) {
    elementsToTranslate.push(document.getElementById('description'))
  }
  if (hasCredit) {
    elementsToTranslate.push(document.getElementById('credits'))
  }

  translation(
    document.getElementById('googleTranslateLink'),
    elementsToTranslate
  )

  $('.comment-translate-button').each(function() {
      const commentId = $(this).attr('id').substring('comment-translate-button-'.length)
      translation(this, document.getElementById("comment-text-" + commentId))
    }
  )

  function translation (buttonElement, textElements) {
    let text = ''
    if (Array.isArray(textElements)) {
      const array = []
      for (let i = 0; i < textElements.length; i++) {
        array.push(textElements[i].innerText)
      }

      text = array.join('\n')
    } else {
      text = textElements.innerText
    }

    buttonElement.setAttribute('href', 'https://translate.google.com/?q=' + encodeURIComponent(text))
  }

}