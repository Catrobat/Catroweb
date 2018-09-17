/*
  Generated File by Grunt
  Sourcepath: web/js
*/
function ProgramDescription (programId, showMoreButtonText, showLessButtonText,
                             statusCode_OK, statusCode_DESCRIPTION_TOO_LONG,
                             statusCode_RUDE_WORD_IN_DESCRIPTION)
{
  
  // Edit Description
  $(function () {
    
    let description = $('#description')
    let editDescriptionUI = $('#edit-description-ui')
    let editDescriptionButton = $('#edit-description-button')
    let editDescription = $('#edit-description')
    let editDescriptionSubmitButton = $('#edit-description-submit-button')
    let editDescriptionError = $('#edit-description-error')
    
    // set default visibilities
    initDescriptionEdit()
    
    function initDescriptionEdit ()
    {
      editDescriptionUI.hide()
      description.show()
    }
    
    editDescriptionButton.click(function () {
      if (description.is(':visible'))
      {
        description.hide()
        editDescriptionUI.show()
      }
      else
      {
        description.show()
        editDescriptionUI.hide()
      }
    })
    
    editDescriptionSubmitButton.click(function () {
      let newDescription = editDescription.val().trim()
      if (newDescription === description.text().trim())
      {
        editDescriptionUI.hide()
        description.show()
        return
      }
      
      let url = Routing.generate('edit_program_description',
        {id: programId, newDescription: newDescription}, false)
      
      $.get(url, function (data) {
        if (data.statusCode === statusCode_OK)
        {
          location.reload()
        }
        else if (data.statusCode === statusCode_DESCRIPTION_TOO_LONG ||
          data.statusCode === statusCode_RUDE_WORD_IN_DESCRIPTION)
        {
          editDescription.addClass('danger')
          editDescriptionError.show()
          editDescriptionError.text(data.message)
        }
      })
    })
    
  })
  
  // Long Description Collapse
  $(function () {
    let descriptionFulltext = $('#descriptionFulltext')
    let descriptionPoints = $('#descriptionPoints')
    let descriptionShowMoreToggle = $('#descriptionShowMoreToggle')
    let descriptionShowMoreText = $('#descriptionShowMoreText')
    descriptionFulltext.hide()
    descriptionPoints.show()
    descriptionShowMoreToggle.click(function () {
      if (descriptionFulltext.is(':visible'))
      {
        descriptionFulltext.fadeOut()
        descriptionPoints.show()
        descriptionShowMoreText.text(showMoreButtonText)
        descriptionShowMoreToggle.css('aria-expanded', false)
      }
      else
      {
        descriptionFulltext.fadeIn()
        descriptionPoints.hide()
        descriptionShowMoreText.text(showLessButtonText)
        descriptionShowMoreToggle.css('aria-expanded', true)
      }
    })
  })
  
}