import $ from 'jquery'
/* global Routing */

export function ProgramCredits (programId) {
  const credits = $('#credits')
  const editCreditsUI = $('#edit-credits-ui')
  const editCredits = $('#edit-credits')
  const editCreditsError = $('#edit-credits-error')
  const descriptionCreditsContainer = $('#description-credits-container')
  const showMoreToggle = $('#descriptionShowMoreToggle')
  const descriptionHeadline = $('#description-headline')

  $('.edit-credits-button').on('click', () => {
    if (editCreditsUI.hasClass('d-none')) {
      descriptionCreditsContainer.hide()
      descriptionHeadline.hide()
      editCreditsUI.removeClass('d-none')
      showMoreToggle.addClass('d-none')
    } else {
      descriptionCreditsContainer.show()
      descriptionHeadline.show()
      editCreditsUI.addClass('d-none')
      handleShowMore()
    }
  })

  $('#edit-credits-submit-button').on('click', () => {
    const newCredits = editCredits.val().trim()
    if (newCredits === credits.text().trim()) {
      editCreditsUI.addClass('d-none')
      descriptionHeadline.show()
      descriptionCreditsContainer.show()
      handleShowMore()
      return
    }

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
  })

  function handleShowMore () {
    if (descriptionCreditsContainer.height() === 200 || descriptionCreditsContainer.height() > 300) {
      showMoreToggle.removeClass('d-none')
    }
  }
}
