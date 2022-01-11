import $ from 'jquery'
import { Translation, ByLineElementContainer } from './Translation'

export class TranslateProgram extends Translation {
  constructor (translatedByLine, programId, hasDescription, hasCredit) {
    super(translatedByLine)
    this.programId = programId
    this.hasDescription = hasDescription
    this.hasCredit = hasCredit
    this.ANIMATION_TIME = 400
    this._initListeners()
  }

  _initListeners () {
    const translateProgram = this

    if (document.getElementById('program-translation-button') == null) {
      return
    }

    $('#program-translation-button').on('click', function (event) {
      $(this).hide()

      if (translateProgram.isTranslationNotAvailable('#name-translation')) {
        $('#program-translation-loading-spinner').show()
        translateProgram.translateProgram()
      } else {
        translateProgram.openTranslatedProgram()
      }
    })

    $('#remove-program-translation-button').on('click', function (event) {
      $(this).hide()
      $('#program-translation-button').show()

      const $name = $('#name')
      const $nameTranslation = $('#name-translation')
      $name.removeClass('program-name').addClass('program-name-animation')
      $nameTranslation.removeClass('program-name').addClass('program-name-animation')
      $nameTranslation.animate({ width: 'toggle' })
      $name.animate({ width: 'toggle' }, translateProgram.ANIMATION_TIME,
        function () {
          $('#name').removeClass('program-name-animation').addClass('program-name')
          $('#name-translation').removeClass('program-name-animation').addClass('program-name')
        }
      )

      if (translateProgram.hasDescription) {
        $('#description').slideDown()
        $('#description-translation').slideUp()
      }

      $('#credits-translation-wrapper').slideUp()
      if (translateProgram.hasCredit) {
        $('#credits').slideDown()
      }
    })
  }

  setTranslatedProgramData (data) {
    const $nameTranslation = $('#name-translation')
    $nameTranslation.attr('lang', data.target_language)
    $nameTranslation.text(data.translated_title)

    if (this.hasDescription) {
      $('#description-translation').text(data.translated_description)
    }

    if (this.hasCredit) {
      $('#credits-translation').text(data.translated_credit)
    }

    const byLineElements = new ByLineElementContainer(
      '#program-translation-before-languages',
      '#program-translation-between-languages',
      '#program-translation-after-languages',
      '#program-translation-first-language',
      '#program-translation-second-language'
    )

    this.setTranslationCredit(data, byLineElements)
  }

  openTranslatedProgram () {
    $('#program-translation-loading-spinner').hide()
    $('#remove-program-translation-button').show()

    const $name = $('#name')
    const $nameTranslation = $('#name-translation')
    $name.removeClass('program-name').addClass('program-name-animation')
    $nameTranslation.removeClass('program-name').addClass('program-name-animation')
    $name.animate({ width: 'toggle' })
    $nameTranslation.animate({ width: 'toggle' }, this.ANIMATION_TIME,
      function () {
        $('#name').removeClass('program-name-animation').addClass('program-name')
        $('#name-translation').removeClass('program-name-animation').addClass('program-name')
      }
    )

    if (this.hasDescription) {
      $('#description-translation').slideDown()
      $('#description').slideUp()
    }

    $('#credits-translation-wrapper').slideDown()
    if (this.hasCredit) {
      $('#credits').slideUp()
    }
  }

  programNotTranslated () {
    $('#program-translation-loading-spinner').hide()
    $('#program-translation-button').show()

    let text = document.getElementById('name').innerText

    if (this.hasDescription) {
      text += '\n\n' + document.getElementById('description').innerText
    }

    if (this.hasCredit) {
      text += '\n\n' + document.getElementById('credits').innerText
    }

    this.openGoogleTranslatePage(text)
  }

  translateProgram () {
    const self = this
    $.ajax({
      url: '../translate/project/' + self.programId,
      type: 'get',
      data: { target_language: self.targetLanguage },
      success: function (data) {
        self.setTranslatedProgramData(data)
        self.openTranslatedProgram()
      },
      error: function () {
        self.programNotTranslated()
      }
    })
  }
}
