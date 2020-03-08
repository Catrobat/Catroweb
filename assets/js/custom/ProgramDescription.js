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

    // set default visibilities
    initDescriptionEdit()

    function initDescriptionEdit () {
      editDescriptionUI.hide()
      description.show()
    }

    editDescriptionButton.click(function () {
      if (description.is(':visible')) {
        description.hide()
        editDescriptionUI.show()
      } else {
        description.show()
        editDescriptionUI.hide()
      }
    })

    editDescriptionSubmitButton.click(function () {
      const newDescription = editDescription.val().trim()
      if (newDescription === description.text().trim()) {
        editDescriptionUI.hide()
        description.show()
        return
      }

      const url = Routing.generate('edit_program_description',
        { id: programId, newDescription: newDescription }, false)

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

  // Long Description Collapse
  $(function () {
    const descriptionFulltext = $('#descriptionFulltext')
    const descriptionPoints = $('#descriptionPoints')
    const descriptionShowMoreToggle = $('#descriptionShowMoreToggle')
    const descriptionShowMoreText = $('#descriptionShowMoreText')
    descriptionFulltext.hide()
    descriptionPoints.show()
    descriptionShowMoreToggle.click(function () {
      if (descriptionFulltext.is(':visible')) {
        descriptionFulltext.fadeOut()
        descriptionPoints.show()
        descriptionShowMoreText.text(showMoreButtonText)
        descriptionShowMoreToggle.css('aria-expanded', false)
      } else {
        descriptionFulltext.fadeIn()
        descriptionPoints.hide()
        descriptionShowMoreText.text(showLessButtonText)
        descriptionShowMoreToggle.css('aria-expanded', true)
      }
    })
  })
}
