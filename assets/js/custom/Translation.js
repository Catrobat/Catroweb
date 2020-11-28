// eslint-disable-next-line no-unused-vars
function Translation (textElements, srcLang) {
  let text = ''
  if (Array.isArray(textElements)) {
    const array = []
    for (let i = 0; i < textElements.length; i++) {
      array.push(textElements[i].innerText)
    }

    text = array.join('\n\n')
  } else {
    text = textElements.innerText
  }

  //let url = 'https://translate.google.com/?sl=auto&tl=' + srcLang + '&text=' + encodeURIComponent(text)
  let url = 'https://translate.google.com/?q=' + encodeURIComponent(text) + '&sl=auto&tl=' + srcLang 
  window.open(url, '_blank', 'noreferrer')
  //window.location.href = 'https://translate.google.com/?q=' + encodeURIComponent(text)
}
