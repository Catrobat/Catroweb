import $ from 'jquery'
import { CustomTranslationApi } from '../api/CustomTranslationApi'
import {
  showCustomTopBarTitle,
  showDefaultTopBarTitle,
} from '../layout/top_bar'

export function ProjectEditorNavigation(
  projectDescriptionCredits,
  programId,
  programEditor,
) {
  const self = this

  this.programId = programId
  this.programEditor = programEditor

  this.body = $('body')
  this.editTextNavigation = $('#edit-text-navigation')
  this.navigationLanguageList = $('#navigation-language-list')

  this.languages = {}

  this.customTranslationApi = new CustomTranslationApi()

  this.translationsText = projectDescriptionCredits.data('trans-translations')
  this.defaultText = projectDescriptionCredits.data('trans-default')
  this.translationTitleText = projectDescriptionCredits.data(
    'trans-translation-title',
  )
  this.editDefaultText = projectDescriptionCredits.data('trans-edit-default')
  this.editTranslationText = projectDescriptionCredits.data(
    'trans-edit-translation',
  )
  this.createTranslationText = projectDescriptionCredits.data(
    'trans-create-translation',
  )

  $('#add-translation-button').on('click', () => {
    this.openEditor(null, true, false, this.createTranslationText)
  })

  $(document).ready(getLanguages)

  $(document).on('click', '.edit-defined-translation', function () {
    const language = $(this).data('value')
    const languageName = $(this).data('language')

    if (language === 'default') {
      self.openEditor(language, false, false, self.editDefaultText)
    } else {
      self.openEditor(
        language,
        false,
        true,
        self.translationTitleText.replace('%language%', languageName),
      )
    }
  })

  this.show = () => {
    window.history.pushState(
      { type: 'ProjectEditorNavigation', id: programId, full: true },
      $(this).text(),
      '#navigation',
    )

    $(window).on('popstate', this.popStateHandler)
    showCustomTopBarTitle(this.translationsText, function () {
      window.history.back()
    })

    this.body.addClass('overflow-hidden')
    this.editTextNavigation.removeClass('d-none')
  }

  // region private

  this.popStateHandler = () => {
    this.close()
  }

  this.close = () => {
    $(window).off('popstate', this.popStateHandler)
    showDefaultTopBarTitle()

    this.body.removeClass('overflow-hidden')
    this.editTextNavigation.addClass('d-none')
  }

  this.openEditor = (
    language,
    showLanguageSelect,
    showDeleteButton,
    headerText,
  ) => {
    $(window).off('popstate', this.popStateHandler)

    this.programEditor.show(
      this.reopenNavigation,
      language,
      showLanguageSelect,
      showDeleteButton,
      headerText,
    )
  }

  this.reopenNavigation = () => {
    $(window).on('popstate', this.popStateHandler)

    showCustomTopBarTitle(this.translationsText, function () {
      window.history.back()
    })

    this.editTextNavigation.removeClass('d-none')

    this.getTranslations()
  }

  function getLanguages() {
    $.ajax({
      url: '../languages',
      type: 'get',
      success: function (data) {
        self.languages = data
        self.getTranslations()
      },
    })
  }

  this.getTranslations = () => {
    this.customTranslationApi
      .getCustomTranslationLanguages(this.programId)
      .then(this.showTranslations)
  }

  this.showTranslations = (translationLanguages) => {
    this.navigationLanguageList.empty()
    this.navigationLanguageList.append(
      '<li>' +
        '<div id="edit-default-button" class="text-icon-aligned edit-defined-translation" data-value="default">' +
        '<span class="language-code"></span>' +
        '<span class="language-name">' +
        this.defaultText +
        '</span>' +
        '<span data-bs-toggle="tooltip" title="' +
        this.editDefaultText +
        '" class="catro-icon-button material-icons trailing-icon " style="font-size: 1.75rem;">edit</span>' +
        '</div>' +
        '</li>',
    )

    for (const language of translationLanguages) {
      this.navigationLanguageList.append(
        `<li>\
          <div id="edit-${language}-button" class="text-icon-aligned edit-defined-translation" data-value="${language}" data-language="${
            this.languages[language]
          }">\
            <span class="language-code">${language}</span>\
            <span class="language-name">${this.languages[language]}</span>\
            <span data-bs-toggle="tooltip" title="${this.editTranslationText.replace(
              '%language%',
              this.languages[language],
            )}" class="catro-icon-button material-icons trailing-icon " style="font-size: 1.75rem;">edit</span>\
          </div>\
        </li>`,
      )
    }
  }

  // end region
}
