export function ProjectName(
  programId,
  usersLanguage,
  myProgram,
  customTranslationApi,
  editorNavigation,
) {
  const name = document.querySelector('#name')
  const editProgramButton = document.querySelector('#edit-project-button')

  if (myProgram) {
    editProgramButton?.addEventListener('click', () => {
      editorNavigation.show()
    })
  } else {
    customTranslationApi.getCustomTranslation(
      programId,
      usersLanguage.substring(0, 2),
      setName,
    )

    function setName(value) {
      name.textContent = value
    }
  }
}
