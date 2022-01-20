import $ from 'jquery'
/* global Routing */

export function ProgramDescription (programId, showMoreButtonText, showLessButtonText, defaultText,
  translationSaved, translationDeleted, myProgram, descriptionSelect, closeEditorDialog,
  keepOrDiscardDialog, customTranslationSnackbar, customTranslationApi) {
  const description = $('#description')
  const editDescriptionUI = $('#edit-description-ui')
  const editDescriptionButton = $('#edit-description-button')
  const closeDescriptionEditorButton = $('#close-description-editor-button')
  const editDescription = $('#edit-description')
  const descriptionLoadingSpinner = $('#description-loading-spinner')
  const descriptionLanguageSelectorList = $('#description-language-selector-list')
  const descriptionSelectedText = $('#description-selected-text')
  const editDescriptionSubmitButton = $('#edit-description-submit-button')
  const editDescriptionError = $('#edit-description-error')
  const descriptionCreditsContainer = $('#description-credits-container')
  const showMoreToggle = $('#descriptionShowMoreToggle')
  const descriptionShowMoreText = $('#descriptionShowMoreText')

  let languages = {}
  let previousDescriptionIndex = 0
  let lastSavedDescription = description.text().trim()

  initShowMore()

  function initShowMore () {
    if (descriptionCreditsContainer.height() > 300) {
      showMoreToggle.removeClass('d-none')
      descriptionCreditsContainer.css({ height: '200px' })
    }
  }

  $(document).ready(function () {
    if (myProgram) {
      getLanguages()
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
    descriptionLanguageSelectorList.empty()
    descriptionLanguageSelectorList.append('<li class="mdc-list-item" data-value="default" role="option" tabindex="-1">' +
        '<span class="mdc-list-item__ripple"></span>' +
        '<span class="mdc-list-item__text">' + defaultText + '</span>' +
        '</li>')

    for (const language in languages) {
      if (language.length <= 2) {
        descriptionLanguageSelectorList.append(`<li class="mdc-list-item" data-value="${language}" role="option" tabindex="-1">\
          <span class="mdc-list-item__ripple"></span>\
          <span class="mdc-list-item__text">${languages[language]}</span>\
          </li>`)
      }
    }

    descriptionSelect.layoutOptions()
    resetDescriptionEditor()
  }

  function getCustomTranslationSuccess (data) {
    descriptionLoadingSpinner.hide()
    editDescription.val(data)
    lastSavedDescription = data
    editDescription.removeAttr('disabled')
  }

  function getCustomTranslationError () {
    descriptionLoadingSpinner.hide()
    editDescription.val('')
    lastSavedDescription = ''
    editDescription.removeAttr('disabled')
  }

  function getCustomTranslation () {
    editDescription.attr('disabled', '')
    editDescription.val('')
    descriptionLoadingSpinner.show()

    customTranslationApi.getCustomTranslation(
      programId,
      descriptionSelect.value,
      getCustomTranslationSuccess,
      getCustomTranslationError
    )
  }

  function deleteCustomTranslationSuccess () {
    customTranslationSnackbar.show(translationDeleted, descriptionSelectedText.text())
    closeDescriptionEditor()
  }

  function saveCustomTranslationSuccess () {
    customTranslationSnackbar.show(translationSaved, descriptionSelectedText.text())
    closeDescriptionEditor()
  }

  function showDescriptionError (error) {
    editDescriptionError.show()
    editDescriptionError.text(error.message)
  }

  function saveDescription () {
    const newDescription = editDescription.val().trim()
    if (newDescription === description.text().trim()) {
      closeDescriptionEditor()
      return
    }

    const languageSelected = descriptionSelect.value
    if (languageSelected === '' || languageSelected === 'default') {
      const url = Routing.generate('edit_program_description', { id: programId, new_description: newDescription }, false)
      $.get(url, function (data) {
        if (parseInt(data.statusCode) === 200) {
          location.reload()
        } else if (parseInt(data.statusCode) === 527) {
          editDescription.addClass('danger')
          editDescriptionError.show()
          editDescriptionError.text(data.message)
        }
      })
    } else if (newDescription === '') {
      customTranslationApi.deleteCustomTranslation(
        programId,
        languageSelected,
        deleteCustomTranslationSuccess,
        showDescriptionError
      )
    } else {
      customTranslationApi.saveCustomTranslation(
        programId,
        newDescription,
        languageSelected,
        saveCustomTranslationSuccess,
        showDescriptionError
      )
    }
  }

  function keepOrDiscardChangesResult (result) {
    if (result.isConfirmed) {
      previousDescriptionIndex = descriptionSelect.selectedIndex
      lastSavedDescription = description.text().trim()
    } else if (result.isDenied) {
      previousDescriptionIndex = descriptionSelect.selectedIndex
      getCustomTranslation()
    } else if (result.isDismissed) {
      descriptionSelect.selectedIndex = previousDescriptionIndex
    }
  }

  function closeEditorDialogResult (result) {
    if (result.isConfirmed) {
      saveDescription()
    } else if (result.isDenied) {
      closeDescriptionEditor()
    }
  }

  function areChangesSaved () {
    return editDescription.val().trim() === lastSavedDescription.trim()
  }

  function resetDescriptionEditor () {
    descriptionSelect.selectedIndex = 0
    previousDescriptionIndex = 0
    const defaultDescription = description.text().trim()
    editDescription.val(defaultDescription)
    lastSavedDescription = defaultDescription
  }

  function closeDescriptionEditor () {
    closeDescriptionEditorButton.hide()
    editDescriptionButton.show()
    descriptionCreditsContainer.show()
    editDescriptionUI.addClass('d-none')
    resetDescriptionEditor()
    handleShowMore()
  }

  editDescriptionButton.on('click', () => {
    closeDescriptionEditorButton.show()
    editDescriptionButton.hide()
    descriptionCreditsContainer.hide()
    editDescriptionUI.removeClass('d-none')
    showMoreToggle.addClass('d-none')
  })

  closeDescriptionEditorButton.on('click', () => {
    if (areChangesSaved()) {
      closeDescriptionEditor()
    } else {
      closeEditorDialog.show(closeEditorDialogResult)
    }
  })

  editDescriptionSubmitButton.on('click', () => {
    saveDescription()
  })

  if (myProgram) {
    descriptionSelect.listen('MDCSelect:change', () => {
      if (!editDescription.is(':visible') || descriptionSelect.selectedIndex === previousDescriptionIndex) {
        return
      }

      if (areChangesSaved()) {
        previousDescriptionIndex = descriptionSelect.selectedIndex
        getCustomTranslation()
      } else {
        keepOrDiscardDialog.show(keepOrDiscardChangesResult)
      }
    })
  }

  showMoreToggle.on('click', () => {
    const icon = showMoreToggle.find('i')
    if (icon.text() === 'keyboard_arrow_up') {
      icon.text('keyboard_arrow_down')
    } else {
      icon.text('keyboard_arrow_up')
    }
    if (descriptionCreditsContainer.height() !== 200) {
      descriptionShowMoreText.text(showMoreButtonText)
      showMoreToggle.attr('aria-expanded', false)
      descriptionCreditsContainer.css({ height: '200px' })
    } else {
      descriptionShowMoreText.text(showLessButtonText)
      showMoreToggle.attr('aria-expanded', true)
      descriptionCreditsContainer.css({ height: '100%' })
    }
  })

  function handleShowMore () {
    if (descriptionCreditsContainer.height() === 200 || descriptionCreditsContainer.height() > 300) {
      showMoreToggle.removeClass('d-none')
    }
  }
}
