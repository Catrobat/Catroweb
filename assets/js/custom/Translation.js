// eslint-disable-next-line no-unused-vars
function Translation (textElements, srcLang) {
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

  window.location.href = 'https://translate.google.com/?q=' + encodeURIComponent(text)
}
