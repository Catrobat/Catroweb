/* global profileID */
/* global apiUserPrograms */

import 'external-svg-loader'
import './components/tab_bar'
import { ProjectLoader } from './custom/ProjectLoader'
import './follower_overview'
import { shareUser } from './custom/UserShare'
import $ from 'jquery'

require('../styles/custom/profile.scss')
require('../styles/components/achievements.scss')

const programs = new ProjectLoader('#user-programs', apiUserPrograms)
const $projectShare = $('.js-user-share')
programs.loadProjects(profileID)

shareUser(
  $projectShare.data('theme-display-name'),
  $projectShare.data('trans-check-out-project'),
  $projectShare.data('project-url'),
  $projectShare.data('trans-share-success'),
  $projectShare.data('trans-share-error'),
  $projectShare.data('trans-copy'),
  $projectShare.data('trans-clipboard-success'),
  $projectShare.data('trans-clipboard-fail')
)
