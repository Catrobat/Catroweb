import 'external-svg-loader'
import './components/tab_bar'
import './follower_overview'
import $ from 'jquery'
import { ProjectList } from './components/project_list'

require('../styles/custom/profile.scss')
require('../styles/components/achievements.scss')

initUserProjects()

function initUserProjects () {
  const $userProjects = $('#projects-section')
  $('.project-list', $userProjects).each(function () {
    const property = $(this).data('property')
    const theme = $(this).data('theme')
    const baseUrl = $(this).data('base-url')
    const userId = $(this).data('user-id')
    const emptyMessage = $(this).data('empty-message')

    const url = baseUrl + '/api/projects/user/' + userId

    const list = new ProjectList(this, 'user-projects', url, property, theme, 999, emptyMessage)
    $(this).data('list', list)
  })
}
