import 'external-svg-loader'
import './components/tab_bar'
import './follower_overview'

import { shareLink } from './custom/ShareLink'
import { ProjectList } from './components/project_list'

import $ from 'jquery'

require('../styles/custom/profile.scss')
require('../styles/components/achievements.scss')

const $userShare = $('.js-user-share')

shareLink(
  $userShare.data('theme-display-name'),
  $userShare.data('trans-check-out-user'),
  $userShare.data('user-url'),
  $userShare.data('trans-share-success'),
  $userShare.data('trans-share-error'),
  $userShare.data('trans-copy'),
  $userShare.data('trans-clipboard-success'),
  $userShare.data('trans-clipboard-fail'),
)

initUserProjects()

function initUserProjects() {
  const $userProjects = $('#projects-section')
  $('.project-list', $userProjects).each(function () {
    const property = $(this).data('property')
    const theme = $(this).data('theme')
    const baseUrl = $(this).data('base-url')
    const userId = $(this).data('user-id')
    const emptyMessage = $(this).data('empty-message')

    const url = baseUrl + '/api/projects/user/' + userId

    const list = new ProjectList(
      this,
      'user-projects',
      url,
      property,
      theme,
      999,
      emptyMessage,
    )
    $(this).data('list', list)
  })
}
