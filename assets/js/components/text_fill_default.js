import $ from 'jquery'
import TextFill from 'textfilljs'

export default function (container) {
  const maxFontPixels = parseFloat($(container).css('font-size'))
  const minFontPixels = Math.round(maxFontPixels * 0.7)

  const html = $(container).html()
  $(container).empty()
  const $span = $('<span/>').html(html)
  $(container).append($span)

  TextFill(container, {
    maxFontPixels,
    minFontPixels,
    widthOnly: true,
    innerTag: 'span',
    fail: function () {
      $(container).addClass('force-word-break')
      $(container).html(html)
    },
    success: function () {
      $(container).removeClass('force-word-break')
      const newFontSize = $span.css('font-size')
      $(container).html(html)
      if (parseFloat(newFontSize) < maxFontPixels) {
        $(container).css('font-size', newFontSize)
      }
    }
  })
}
