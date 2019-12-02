function ProgramCredits (programId, showMoreButtonText, showLessButtonText,
                             statusCode_OK, statusCode_CREDITS_TOO_LONG,
                             statusCode_RUDE_WORD_IN_CREDITS)
{
  
  // Edit Credits
  $(function () {
    
    let credits = $('#credits')
    let editCreditsUI = $('#edit-credits-ui')
    let editCreditsButton = $('#edit-credits-button')
    let editCredits = $('#edit-credits')
    let editCreditsSubmitButton = $('#edit-credits-submit-button')
    let editCreditsError = $('#edit-credits-error')
    
    // set default visibilities
    initCreditsEdit()
    
    function initCreditsEdit ()
    {
      editCreditsUI.hide()
      credits.show()
    }
    
    editCreditsButton.click(function () {
      if (credits.is(':visible'))
      {
        credits.hide()
        editCreditsUI.show()
      }
      else
      {
        credits.show()
        editCreditsUI.hide()
      }
    })
    
    editCreditsSubmitButton.click(function () {
      let newCredits = editCredits.val().trim()
      if (newCredits === credits.text().trim())
      {
        editCreditsUI.hide()
        credits.show()
        return
      }
      
      let url = Routing.generate('edit_program_credits',
        {id: programId, newCredits: newCredits}, false);
      
      //let url = "/editProjectCredits/" + programId + "/" + newCredits;
      
      $.get(url, function (data) {
        if (data.statusCode === statusCode_OK)
        {
          location.reload()
        }
        else if (data.statusCode === statusCode_CREDITS_TOO_LONG ||
          data.statusCode === statusCode_RUDE_WORD_IN_CREDITS)
        {
          editCredits.addClass('danger')
          editCreditsError.show()
          editCreditsError.text(data.message)
        }
      })
    })
    
  })
  
  // Long Credits Collapse
  $(function () {
    let creditsFulltext = $('#creditsFulltext')
    let creditsPoints = $('#creditsPoints')
    let creditsShowMoreToggle = $('#creditsShowMoreToggle')
    let creditsShowMoreText = $('#creditsShowMoreText')
    creditsFulltext.hide()
    creditsPoints.show()
    creditsShowMoreToggle.click(function () {
      if (creditsFulltext.is(':visible'))
      {
        creditsFulltext.fadeOut()
        creditsPoints.show()
        creditsShowMoreText.text(showMoreButtonText)
        creditsShowMoreToggle.css('aria-expanded', false)
      }
      else
      {
        creditsFulltext.fadeIn()
        creditsPoints.hide()
        creditsShowMoreText.text(showLessButtonText)
        creditsShowMoreToggle.css('aria-expanded', true)
      }
    })
  })
  
}