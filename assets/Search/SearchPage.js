import { controlTopBarSearchClearButton, showTopBarSearch } from '../Layout/TopBar'
import { ProjectList } from '../Project/ProjectList'
import { UserList } from '../User/UserList'
import './Search.scss'

class Search {
  constructor() {
    this.searchElement = document.querySelector('.js-search')
    this.query = this.searchElement.dataset.query
    this.searchInput = document.querySelector('#top-app-bar__search-input')
    this.oldQuery = this.searchInput.innerHTML = this.query
    this.searchInput.value = this.oldQuery
    showTopBarSearch()
    controlTopBarSearchClearButton()
    this.initProjects()
  }

  initProjects() {
    const searchProjects = document.querySelector('#search-projects')
    const searchUsers = document.querySelector('#search-users')
    const theme = this.searchElement.dataset.theme
    const baseUrl = this.searchElement.dataset.baseUrl
    const category = this.searchElement.dataset.projectCategory
    const property = this.searchElement.dataset.projectProperty
    const query = this.searchElement.dataset.query
    const projectString = this.searchElement.dataset.projectTranslated
    const noUsers = this.searchElement.dataset.noUsers
    const noProjects = this.searchElement.dataset.noProjects

    const projectUrl = `${baseUrl}/api/projects/search?query=${query}`
    const userUrl = `${baseUrl}/api/users/search?query=${query}`

    this.list = new ProjectList(
      searchProjects,
      category,
      projectUrl,
      property,
      theme,
      30,
      noProjects,
    )

    this.userList = new UserList(searchUsers, baseUrl, userUrl, theme, projectString, 30, noUsers)
  }
}

new Search()
