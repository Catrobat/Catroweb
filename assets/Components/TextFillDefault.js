import TextFill from 'textfilljs'

export default function (container) {
  const containerElem =
    typeof container === 'string' ? document.querySelector(container) : container

  if (!containerElem) {
    console.error('Container element not found.')
    return
  }

  const style = window.getComputedStyle(containerElem)
  const maxFontPixels = parseFloat(style.fontSize)
  const minFontPixels = Math.round(maxFontPixels * 0.7)

  const html = containerElem.innerHTML
  containerElem.innerHTML = ''
  const span = document.createElement('span')
  span.innerHTML = html
  containerElem.appendChild(span)

  TextFill(containerElem, {
    maxFontPixels,
    minFontPixels,
    widthOnly: true,
    innerTag: 'span',
    fail: function () {
      containerElem.classList.add('force-word-break')
      containerElem.innerHTML = html
    },
    success: function () {
      containerElem.classList.remove('force-word-break')
      const newFontSize = window.getComputedStyle(span).fontSize
      containerElem.innerHTML = html
      if (parseFloat(newFontSize) < maxFontPixels) {
        containerElem.style.fontSize = newFontSize
      }
    },
  })
}
