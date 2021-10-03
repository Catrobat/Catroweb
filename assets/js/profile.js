/* global profileID */
/* global apiUserPrograms */

import 'external-svg-loader'
import './components/tab_bar'
import { ProjectLoader } from './custom/ProjectLoader'
import './follower_overview'

require('../styles/custom/profile.scss')
require('../styles/components/achievements.scss')

const programs = new ProjectLoader('#user-programs', apiUserPrograms)
programs.loadProjects(profileID)
