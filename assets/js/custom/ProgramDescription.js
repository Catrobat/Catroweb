import $ from 'jquery'
/* global Routing */

export function ProgramDescription (programId, showMoreButtonText, showLessButtonText) {
  const description = $('#description')
  const editDescriptionUI = $('#edit-description-ui')
  const editDescriptionButton = $('#edit-description-button')
  const editDescription = $('#edit-description')
  const editDescriptionSubmitButton = $('#edit-description-submit-button')
  const editDescriptionError = $('#edit-description-error')
  const descriptionCreditsContainer = $('#description-credits-container')
  const showMoreToggle = $('#descriptionShowMoreToggle')
  const descriptionShowMoreText = $('#descriptionShowMoreText')

  initShowMore()

  function initShowMore () {
    if (descriptionCreditsContainer.height() > 300) {
      showMoreToggle.removeClass('d-none')
      descriptionCreditsContainer.css({ height: '200px' })
    }
  }

  editDescriptionButton.on('click', () => {
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

  editDescriptionSubmitButton.on('click', () => {
    const newDescription = editDescription.val().trim()
    if (newDescription === description.text().trim()) {
      editDescriptionUI.addClass('d-none')
      descriptionCreditsContainer.show()
      handleShowMore()
      return
    }

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
  })

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
