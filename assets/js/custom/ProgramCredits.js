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
    const editCreditsButton = $('#edit-credits-button')
    const editCredits = $('#edit-credits')
    const editCreditsSubmitButton = $('#edit-credits-submit-button')
    const editCreditsError = $('#edit-credits-error')

    // set default visibilities
    initCreditsEdit()

    function initCreditsEdit () {
      editCreditsUI.hide()
      credits.show()
    }

    editCreditsButton.click(function () {
      if (credits.is(':visible')) {
        credits.hide()
        editCreditsUI.show()
      } else {
        credits.show()
        editCreditsUI.hide()
      }
    })

    editCreditsSubmitButton.click(function () {
      const newCredits = editCredits.val().trim()
      if (newCredits === credits.text().trim()) {
        editCreditsUI.hide()
        credits.show()
        return
      }

      const url = Routing.generate('edit_program_credits',
        { id: programId, newCredits: newCredits }, false)

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

  // Long Credits Collapse
  $(function () {
    const creditsFulltext = $('#creditsFulltext')
    const creditsPoints = $('#creditsPoints')
    const creditsShowMoreToggle = $('#creditsShowMoreToggle')
    const creditsShowMoreText = $('#creditsShowMoreText')
    creditsFulltext.hide()
    creditsPoints.show()
    creditsShowMoreToggle.click(function () {
      if (creditsFulltext.is(':visible')) {
        creditsFulltext.fadeOut()
        creditsPoints.show()
        creditsShowMoreText.text(showMoreButtonText)
        creditsShowMoreToggle.css('aria-expanded', false)
      } else {
        creditsFulltext.fadeIn()
        creditsPoints.hide()
        creditsShowMoreText.text(showLessButtonText)
        creditsShowMoreToggle.css('aria-expanded', true)
      }
    })
  })
}
