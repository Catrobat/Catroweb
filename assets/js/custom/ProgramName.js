import $ from 'jquery'

export function ProgramName (programId, usersLanguage, myProgram, editor, editorConfig, customTranslationApi) {
  const name = $('#name')
  const editNameButton = $('#edit-name-button')
  const descriptionCreditsContainer = $('#description-credits-container')
  const showMoreToggle = $('#descriptionShowMoreToggle')

  if (myProgram) {
    const descriptionHeadline = $('#description-headline')

    editNameButton.on('click', () => {
      editNameButton.hide()
      descriptionCreditsContainer.hide()
      descriptionHeadline.hide()
      showMoreToggle.addClass('d-none')

      editor.show(
        editorConfig,
        name.text().trim(),
        closeNameEditor
      )
    })

    function closeNameEditor () {
      editNameButton.show()
      descriptionCreditsContainer.show()
      descriptionHeadline.show()
      handleShowMore()
    }
  } else {
    customTranslationApi.getCustomTranslation(
      programId,
      usersLanguage.substring(0, 2),
      setName
    )

    function setName (value) {
      name.text(value)
    }
  }

  function handleShowMore () {
    if (descriptionCreditsContainer.height() === 200 || descriptionCreditsContainer.height() > 300) {
      showMoreToggle.removeClass('d-none')
    }
  }
}
