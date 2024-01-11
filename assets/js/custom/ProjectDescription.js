import $ from 'jquery'

export function ProjectDescription(
  programId,
  usersLanguage,
  showMoreButtonText,
  showLessButtonText,
  myProgram,
  customTranslationApi,
) {
  const description = $('#description')
  const descriptionCreditsContainer = $('#description-credits-container')
  const showMoreToggle = $('#descriptionShowMoreToggle')
  const descriptionShowMoreText = $('#descriptionShowMoreText')

  initShowMore()

  function initShowMore() {
    if (descriptionCreditsContainer.height() > 300) {
      showMoreToggle.removeClass('d-none')
      descriptionCreditsContainer.css({ height: '200px' })
    }
  }

  if (!myProgram) {
    customTranslationApi.getCustomTranslation(
      programId,
      usersLanguage.substring(0, 2),
      setDescription,
    )

    function setDescription(value) {
      description.text(value)
    }
  }

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
}
