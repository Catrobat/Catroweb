import $ from 'jquery'
/* global Routing */

export function ProgramCredits (programId, usersLanguage, defaultText, translationSaved, translationDeleted, myProgram,
  creditsSelect, closeEditorDialog, keepOrDiscardDialog, customTranslationSnackbar, customTranslationApi) {
  const credits = $('#credits')
  const editCreditsUI = $('#edit-credits-ui')
  const editCredits = $('#edit-credits')
  const editCreditsButton = $('#edit-credits-button')
  const closeCreditsEditorButton = $('#close-credits-editor-button')
  const creditsLoadingSpinner = $('#credits-loading-spinner')
  const creditLanguageSelectorList = $('#credit-language-selector-list')
  const creditsSelectedText = $('#credits-selected-text')
  const editCreditsSubmitButton = $('#edit-credits-submit-button')
  const editCreditsError = $('#edit-credits-error')
  const descriptionCreditsContainer = $('#description-credits-container')
  const showMoreToggle = $('#descriptionShowMoreToggle')
  const descriptionHeadline = $('#description-headline')

  const noop = () => {}

  let languages = {}
  let previousCreditsIndex = 0
  let lastSavedCredits = credits.text().trim()

  $(document).ready(function () {
    if (myProgram) {
      getLanguages()
    } else {
      customTranslationApi.getCustomTranslation(
        programId,
        usersLanguage.substring(0, 2),
        setCredits,
        noop
      )
    }
  })

  function getLanguages () {
    $.ajax({
      url: '../languages',
      type: 'get',
      success: function (data) {
        languages = data
        populateSelector()
      }
    })
  }

  function populateSelector () {
    creditLanguageSelectorList.empty()
    creditLanguageSelectorList.append('<li class="mdc-list-item" data-value="default" role="option" tabindex="-1">' +
        '<span class="mdc-list-item__ripple"></span>' +
        '<span class="mdc-list-item__text">' + defaultText + '</span>' +
        '</li>')

    for (const language in languages) {
      if (language.length <= 2) {
        creditLanguageSelectorList.append(`<li class="mdc-list-item" data-value="${language}" role="option" tabindex="-1">\
          <span class="mdc-list-item__ripple"></span>\
          <span class="mdc-list-item__text">${languages[language]}</span>\
          </li>`)
      }
    }

    creditsSelect.layoutOptions()
    resetCreditsEditor()
  }

  function setCredits (value) {
    credits.text(value)
  }

  function getCustomTranslationSuccess (data) {
    creditsLoadingSpinner.hide()
    editCredits.val(data)
    lastSavedCredits = data
    editCredits.removeAttr('disabled')
  }

  function getCustomTranslationError () {
    creditsLoadingSpinner.hide()
    editCredits.val('')
    lastSavedCredits = ''
    editCredits.removeAttr('disabled')
  }

  function getCustomTranslation () {
    editCredits.attr('disabled', '')
    editCredits.val('')
    creditsLoadingSpinner.show()

    customTranslationApi.getCustomTranslation(
      programId,
      creditsSelect.value,
      getCustomTranslationSuccess,
      getCustomTranslationError
    )
  }

  function deleteCustomTranslationSuccess () {
    customTranslationSnackbar.show(translationDeleted, creditsSelectedText.text())
    closeCreditsEditor()
  }

  function saveCustomTranslationSuccess () {
    customTranslationSnackbar.show(translationSaved, creditsSelectedText.text())
    closeCreditsEditor()
  }

  function showCreditsError (error) {
    editCredits.addClass('danger')
    editCreditsError.show()
    editCreditsError.text(error.message)
  }

  function saveCredits () {
    const newCredits = editCredits.val().trim()
    if (newCredits === credits.text().trim()) {
      closeCreditsEditor()
      return
    }

    const languageSelected = creditsSelect.value
    if (languageSelected === '' || languageSelected === 'default') {
      const url = Routing.generate('edit_program_credits', { id: programId, new_credits: newCredits }, false)

      $.get(url, function (data) {
        if (parseInt(data.statusCode) === 200) {
          location.reload()
        } else if (parseInt(data.statusCode) === 707) {
          editCredits.addClass('danger')
          editCreditsError.show()
          editCreditsError.text(data.message)
        }
      })
    } else if (newCredits === '') {
      customTranslationApi.deleteCustomTranslation(
        programId,
        languageSelected,
        deleteCustomTranslationSuccess,
        showCreditsError
      )
    } else {
      customTranslationApi.saveCustomTranslation(
        programId,
        newCredits,
        languageSelected,
        saveCustomTranslationSuccess,
        showCreditsError
      )
    }
  }

  function keepOrDiscardChangesResult (result) {
    if (result.isConfirmed) {
      previousCreditsIndex = creditsSelect.selectedIndex
      lastSavedCredits = credits.text().trim()
    } else if (result.isDenied) {
      previousCreditsIndex = creditsSelect.selectedIndex
      getCustomTranslation()
    } else if (result.isDismissed) {
      creditsSelect.selectedIndex = previousCreditsIndex
    }
  }

  function closeEditorDialogResult (result) {
    if (result.isConfirmed) {
      saveCredits()
    } else if (result.isDenied) {
      closeCreditsEditor()
    }
  }

  function areChangesSaved () {
    return editCredits.val().trim() === lastSavedCredits.trim()
  }

  function resetCreditsEditor () {
    creditsSelect.selectedIndex = 0
    previousCreditsIndex = 0
    const defaultCredits = credits.text().trim()
    editCredits.val(defaultCredits)
    lastSavedCredits = defaultCredits
  }

  function closeCreditsEditor () {
    descriptionCreditsContainer.show()
    descriptionHeadline.show()
    editCreditsUI.addClass('d-none')
    resetCreditsEditor()
    handleShowMore()
  }

  editCreditsButton.on('click', () => {
    descriptionCreditsContainer.hide()
    descriptionHeadline.hide()
    editCreditsUI.removeClass('d-none')
    showMoreToggle.addClass('d-none')
  })

  closeCreditsEditorButton.on('click', () => {
    if (areChangesSaved()) {
      closeCreditsEditor()
    } else {
      closeEditorDialog.show(closeEditorDialogResult)
    }
  })

  editCreditsSubmitButton.on('click', () => {
    saveCredits()
  })

  if (myProgram) {
    creditsSelect.listen('MDCSelect:change', () => {
      if (!editCredits.is(':visible') || creditsSelect.selectedIndex === previousCreditsIndex) {
        return
      }

      if (areChangesSaved()) {
        previousCreditsIndex = creditsSelect.selectedIndex
        getCustomTranslation()
      } else {
        keepOrDiscardDialog.show(keepOrDiscardChangesResult)
      }
    })
  }

  function handleShowMore () {
    if (descriptionCreditsContainer.height() === 200 || descriptionCreditsContainer.height() > 300) {
      showMoreToggle.removeClass('d-none')
    }
  }
}
