import { ProjectList } from '../Project/ProjectList'
require('../Project/ProjectList.scss')

export class DefaultProjectLists {
  constructor(elementId) {
    this.containerElement = document.getElementById(elementId)
  }

  init() {
    if (!this.containerElement) {
      console.warn(`#${this.containerElement.id} can't be found in the DOM.`)
      return
    }

    const projectLists = this.containerElement.querySelectorAll('.project-list')

    projectLists.forEach((projectList) => {
      const { category, property, theme, flavor, baseUrl } = projectList.dataset

      let url = `${baseUrl}/api/projects?category=${category}`

      if (flavor !== 'pocketcode' || category === 'example') {
        url += `&flavor=${flavor}`
      }

      projectList.dataset.list = new ProjectList(
        projectList,
        category,
        url,
        property,
        theme,
      ).toString()
    })
  }
}
