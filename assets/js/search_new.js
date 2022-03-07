import $ from 'jquery'
import { controlTopBarSearchClearButton, showTopBarSearch } from './layout/top_bar'
import { ProjectList } from './components/project_list'
import { UserList } from './components/user_list'

require('../styles/custom/search.scss')

$(() => {
  const $search = $('.js-search')
  const query = $search.data('query')
  const searchInput = $('#top-app-bar__search-input')
  const oldQuery = searchInput.html(query).text()
  searchInput.val(oldQuery)
  showTopBarSearch()
  controlTopBarSearchClearButton()

  initProjects()
})
function initProjects () {
  const $search = $('.js-search')

  const $searchProjects = $('#search-projects')
  const $searchUsers = $('#search-users')
  const theme = $search.data('theme')
  const baseUrl = $search.data('base-url')
  const category = $search.data('project-category')
  const property = $search.data('project-property')
  const query = $search.data('query')
  const projectString = $search.data('project-translated')
  const noUsers = $search.data('no-users')
  const noProjects = $search.data('no-projects')

  const projectUrl = baseUrl + '/api/projects/search?query=' + query
  const userUrl = baseUrl + '/api/users/search?query=' + query

  const list = new ProjectList($searchProjects[0], category, projectUrl, property, theme, 30, noProjects)
  $(this).data('list', list)

  const userList = new UserList($searchUsers[0], baseUrl, userUrl, theme, projectString, 30, noUsers)
  $(this).data('list', userList)
}
