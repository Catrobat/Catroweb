export function ProjectCredits(
  programId,
  usersLanguage,
  myProgram,
  customTranslationApi,
) {
  const credits = document.getElementById('credits')

  if (!myProgram) {
    customTranslationApi.getCustomTranslation(
      programId,
      usersLanguage.substring(0, 2),
      setCredits,
    )

    function setCredits(value) {
      credits.textContent = value
    }
  }
}
