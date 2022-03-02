import $ from 'jquery'

export function ProgramCredits (programId, usersLanguage, myProgram, customTranslationApi) {
  const credits = $('#credits')

  if (!myProgram) {
    customTranslationApi.getCustomTranslation(
      programId,
      usersLanguage.substring(0, 2),
      setCredits
    )

    function setCredits (value) {
      credits.text(value)
    }
  }
}
