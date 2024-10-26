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
  const image = document.getElementById(projectId)
  if (index === -1) {
    clickedProjectsAdd.push(projectId)
    image.classList.add('green-background')
  } else {
    clickedProjectsAdd.splice(index, 1)
    image.classList.remove('green-background')
  }
  document.getElementById('clicked-projects_own_projects').value =
    clickedProjectsAdd.length > 0 ? JSON.stringify(clickedProjectsAdd) : ''

  console.log(document.getElementById('clicked-projects_own_projects').value)
}

function handleImageClickRemove(projectId) {
  const index = clickedProjectsRemove.indexOf(projectId)
  const image = document.getElementById(projectId)
  if (index === -1) {
    clickedProjectsAdd.push(projectId)
    image.classList.add('red-background')
  } else {
    clickedProjectsAdd.splice(index, 1)
    image.classList.remove('red-background')
  }

  document.getElementById('clicked-projects_own_and_studio_projects').value =
    clickedProjectsRemove.length > 0 ? JSON.stringify(clickedProjectsRemove) : ''
}
