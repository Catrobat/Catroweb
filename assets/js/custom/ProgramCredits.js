import $ from 'jquery'

export function ProgramCredits (programId, usersLanguage, myProgram, editor, editorConfig, customTranslationApi) {
  const credits = $('#credits')
  const descriptionCreditsContainer = $('#description-credits-container')
  const showMoreToggle = $('#descriptionShowMoreToggle')

  if (myProgram) {
    const descriptionHeadline = $('#description-headline')

    $('#edit-credits-button').on('click', () => {
      descriptionCreditsContainer.hide()
      descriptionHeadline.hide()
      showMoreToggle.addClass('d-none')

      editor.show(
        editorConfig,
        credits.text().trim(),
        closeCreditsEditor
      )
    })

    function closeCreditsEditor () {
      descriptionCreditsContainer.show()
      descriptionHeadline.show()
      handleShowMore()
    }
  } else {
    customTranslationApi.getCustomTranslation(
      programId,
      usersLanguage.substring(0, 2),
      setCredits
    )

    function setCredits (value) {
      credits.text(value)
    }
  }

  function handleShowMore () {
    if (descriptionCreditsContainer.height() === 200 || descriptionCreditsContainer.height() > 300) {
      showMoreToggle.removeClass('d-none')
    }
  }
}
