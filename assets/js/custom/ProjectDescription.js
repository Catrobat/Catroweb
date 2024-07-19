export function ProjectDescription(
  programId,
  usersLanguage,
  showMoreButtonText,
  showLessButtonText,
  myProgram,
  customTranslationApi,
) {
  const description = document.getElementById('description')
  const descriptionCreditsContainer = document.getElementById(
    'description-credits-container',
  )
  const showMoreToggle = document.getElementById('descriptionShowMoreToggle')
  const descriptionShowMoreText = document.getElementById(
    'descriptionShowMoreText',
  )

  initShowMore()

  function initShowMore() {
    if (descriptionCreditsContainer.offsetHeight > 300) {
      showMoreToggle.classList.remove('d-none')
      descriptionCreditsContainer.style.height = '200px'
    }
  }

  if (!myProgram) {
    customTranslationApi.getCustomTranslation(
      programId,
      usersLanguage.substring(0, 2),
      setDescription,
    )

    function setDescription(value) {
      description.textContent = value
    }
  }

  showMoreToggle.addEventListener('click', () => {
    const icon = showMoreToggle.querySelector('i')
    if (icon.textContent === 'keyboard_arrow_up') {
      icon.textContent = 'keyboard_arrow_down'
    } else {
      icon.textContent = 'keyboard_arrow_up'
    }
    if (descriptionCreditsContainer.offsetHeight !== 200) {
      descriptionShowMoreText.textContent = showMoreButtonText
      showMoreToggle.setAttribute('aria-expanded', false)
      descriptionCreditsContainer.style.height = '200px'
    } else {
      descriptionShowMoreText.textContent = showLessButtonText
      showMoreToggle.setAttribute('aria-expanded', true)
      descriptionCreditsContainer.style.height = '100%'
    }
  })
}
