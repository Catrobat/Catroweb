import $ from 'jquery'
import { ByLineElementContainer, Translation } from './Translation'

export class TranslateProgram extends Translation {
  constructor(
    translatedByLine,
    googleTranslateDisplayName,
    programId,
    hasDescription,
    hasCredit,
  ) {
    super(translatedByLine, googleTranslateDisplayName)
    this.programId = programId
    this.hasDescription = hasDescription
    this.hasCredit = hasCredit
    this.ANIMATION_TIME = 400
    this._initListeners()
  }

  _initListeners() {
    const translateProgram = this

    if (document.getElementById('project-translation-button') == null) {
      return
    }

    $('#project-translation-button').on('click', function (event) {
      $(this).hide()

      if (translateProgram.isTranslationNotAvailable('#name-translation')) {
        $('#project-translation-loading-spinner').show()
        translateProgram.translateProgram()
      } else {
        translateProgram.openTranslatedProgram()
      }
    })

    $('#remove-project-translation-button').on('click', function (event) {
      $(this).hide()
      $('#project-translation-button').show()

      const $name = $('#name')
      const $nameTranslation = $('#name-translation')
      $name.removeClass('project-name').addClass('project-name-animation')
      $nameTranslation
        .removeClass('project-name')
        .addClass('project-name-animation')
      $nameTranslation.animate({ width: 'toggle' })
      $name.animate(
        { width: 'toggle' },
        translateProgram.ANIMATION_TIME,
        function () {
          $('#name')
            .removeClass('project-name-animation')
            .addClass('project-name')
          $('#name-translation')
            .removeClass('project-name-animation')
            .addClass('project-name')
        },
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

  setTranslatedProgramData(data) {
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
      '#project-translation-before-languages',
      '#project-translation-between-languages',
      '#project-translation-after-languages',
      '#project-translation-first-language',
      '#project-translation-second-language',
    )

    this.setTranslationCredit(data, byLineElements)
  }

  openTranslatedProgram() {
    $('#project-translation-loading-spinner').hide()
    $('#remove-project-translation-button').show()

    const $name = $('#name')
    const $nameTranslation = $('#name-translation')
    $name.removeClass('project-name').addClass('project-name-animation')
    $nameTranslation
      .removeClass('project-name')
      .addClass('project-name-animation')
    $name.animate({ width: 'toggle' })
    $nameTranslation.animate(
      { width: 'toggle' },
      this.ANIMATION_TIME,
      function () {
        $('#name')
          .removeClass('project-name-animation')
          .addClass('project-name')
        $('#name-translation')
          .removeClass('project-name-animation')
          .addClass('project-name')
      },
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

  programNotTranslated() {
    $('#project-translation-loading-spinner').hide()
    $('#project-translation-button').show()

    let text = document.getElementById('name').innerText

    if (this.hasDescription) {
      text += '\n\n' + document.getElementById('description').innerText
    }

    if (this.hasCredit) {
      text += '\n\n' + document.getElementById('credits').innerText
    }

    this.openGoogleTranslatePage(text)
  }

  translateProgram() {
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
      },
    })
  }
}
