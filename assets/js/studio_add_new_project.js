const clickedProjectsAdd = []
const clickedProjectsRemove = []
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.addStudioProjects').forEach((el) => {
    el.addEventListener('click', (event) => {
      const projectId = event.target.id

      handleImageClickAdd(projectId)
    })
  })

  document.querySelectorAll('.removeStudioProjects').forEach((el) => {
    el.addEventListener('click', (event) => {
      const projectId = event.target.id
      handleImageClickRemove(projectId)
    })
  })
})
function handleImageClickAdd(projectId) {
  const index = clickedProjectsAdd.indexOf(projectId)

  if (index === -1) {
    clickedProjectsAdd.push(projectId)
  } else {
    clickedProjectsAdd.splice(index, 1)
  }
  document.getElementById('clicked-projects_own_projects').value =
    clickedProjectsAdd.length > 0 ? JSON.stringify(clickedProjectsAdd) : ''

  console.log(document.getElementById('clicked-projects_own_projects').value)
}

function handleImageClickRemove(projectId) {
  const index = clickedProjectsRemove.indexOf(projectId)

  if (index === -1) {
    clickedProjectsRemove.push(projectId)
  } else {
    clickedProjectsRemove.splice(index, 1)
  }

  document.getElementById('clicked-projects_own_and_studio_projects').value =
    clickedProjectsRemove.length > 0 ? JSON.stringify(clickedProjectsRemove) : ''
}
