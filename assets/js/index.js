import { Carousel } from 'bootstrap'
import { ProjectList } from './components/project_list'
import { OAuthHandler } from './security/OAuthHandler'
import './components/maintaince_information'
require('../styles/index.scss')

document.addEventListener('DOMContentLoaded', () => {
  initFeatureSlider()
  initHomeProjects()
  const oAuthHandler = new OAuthHandler()
  oAuthHandler.showOAuthFirstLoginInformationIfNecessary()
})

function initFeatureSlider() {
  const featureSlider = document.getElementById('feature-slider')
  if (featureSlider) {
    new Carousel(featureSlider)
  } else {
    console.warn("#feature-slider can't be found in the dom.")
  }
}

function initHomeProjects() {
  const homeProjects = document.getElementById('home-projects')
  const projectLists = homeProjects.querySelectorAll('.project-list')

  projectLists.forEach((projectList) => {
    const category = projectList.dataset.category
    const property = projectList.dataset.property
    const theme = projectList.dataset.theme
    const flavor = projectList.dataset.flavor
    const baseUrl = projectList.dataset.baseUrl

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
