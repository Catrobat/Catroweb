/* eslint-env jquery */
/* global Routing */

// eslint-disable-next-line no-unused-vars
function ProgramDescription (programId, showMoreButtonText, showLessButtonText,
  statusCodeOk, statusCodeDescriptionTooLong,
  statusCodeRudeWordInDescription) {
  // Edit Description
  $(function () {
    const description = $('#description')
    const editDescriptionUI = $('#edit-description-ui')
    const editDescriptionButton = $('#edit-description-button')
    const editDescription = $('#edit-description')
    const editDescriptionSubmitButton = $('#edit-description-submit-button')
    const editDescriptionError = $('#edit-description-error')
    const descriptionCreditsContainer = $('#description-credits-container')
    const showMoreToggle = $('#descriptionShowMoreToggle')

    initShowMore()

    function initShowMore () {
      if (descriptionCreditsContainer.height() > 300) {
        showMoreToggle.removeClass('d-none')
        descriptionCreditsContainer.css({ height: '200px' })
      }
    }

    editDescriptionButton.click(function () {
      if (editDescriptionUI.hasClass('d-none')) {
        descriptionCreditsContainer.hide()
        editDescriptionUI.removeClass('d-none')
        showMoreToggle.addClass('d-none')
      } else {
        descriptionCreditsContainer.show()
        editDescriptionUI.addClass('d-none')
        handleShowMore()
      }
    })

    editDescriptionSubmitButton.click(function () {
      const newDescription = editDescription.val().trim()
      if (newDescription === description.text().trim()) {
        editDescriptionUI.addClass('d-none')
        descriptionCreditsContainer.show()
        handleShowMore()
        return
      }

      const url = Routing.generate('edit_program_description',
        { id: programId, new_description: newDescription }, false)

      $.get(url, function (data) {
        if (data.statusCode === statusCodeOk) {
          location.reload()
        } else if (data.statusCode === statusCodeDescriptionTooLong ||
          data.statusCode === statusCodeRudeWordInDescription) {
          editDescription.addClass('danger')
          editDescriptionError.show()
          editDescriptionError.text(data.message)
        }
      })
    })
  })

  // Description Credits container
  $(function () {
    const showMoreToggle = $('#descriptionShowMoreToggle')
    const descriptionShowMoreText = $('#descriptionShowMoreText')
    const descriptionCreditsContainer = $('#description-credits-container')
    showMoreToggle.click(function () {
      if ($(this).find('i').text() === 'keyboard_arrow_up') {
        $(this).find('i').text('keyboard_arrow_down')
      } else {
        $(this).find('i').text('keyboard_arrow_up')
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
  })
  function handleShowMore () {
    const descriptionCreditsContainer = $('#description-credits-container')
    const showMoreToggle = $('#descriptionShowMoreToggle')

    if (descriptionCreditsContainer.height() === 200 || descriptionCreditsContainer.height() > 300) {
      showMoreToggle.removeClass('d-none')
    }
  }
}
