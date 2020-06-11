/* eslint-env jquery */
/* global Routing */

// eslint-disable-next-line no-unused-vars
function ProgramCredits (programId, showMoreButtonText, showLessButtonText,
  statusCodeOk, statusCodeCreditsTooLong,
  statusCodeRudeWordInCredits) {
  // Edit Credits
  $(function () {
    const credits = $('#credits')
    const editCreditsUI = $('#edit-credits-ui')
    const editCreditsButton = $('i[id=edit-credits-button]')
    const editCredits = $('#edit-credits')
    const editCreditsSubmitButton = $('#edit-credits-submit-button')
    const editCreditsError = $('#edit-credits-error')
    const descriptionCreditsContainer = $('#description-credits-container')
    const showMoreToggle = $('#descriptionShowMoreToggle')
    const descriptionHeadline = $('#description-headline')

    editCreditsButton.click(function () {
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

    editCreditsSubmitButton.click(function () {
      const newCredits = editCredits.val().trim()
      if (newCredits === credits.text().trim()) {
        editCreditsUI.addClass('d-none')
        descriptionHeadline.show()
        descriptionCreditsContainer.show()
        handleShowMore()
        return
      }

      const url = Routing.generate('edit_program_credits',
        { id: programId, new_credits: newCredits }, false)

      // let url = "/editProjectCredits/" + programId + "/" + newCredits;

      $.get(url, function (data) {
        if (data.statusCode === statusCodeOk) {
          location.reload()
        } else if (data.statusCode === statusCodeCreditsTooLong ||
          data.statusCode === statusCodeRudeWordInCredits) {
          editCredits.addClass('danger')
          editCreditsError.show()
          editCreditsError.text(data.message)
        }
      })
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
