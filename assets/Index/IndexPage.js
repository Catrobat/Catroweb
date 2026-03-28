import { OAuthHandler } from '../Security/OAuthHandler'
import { FeaturedProjects } from './FeaturedProjects'
import { DefaultProjectLists } from './DefaultProjectLists'
import { MaintenanceHandler } from './MaintenanceHandler'
require('./IndexPage.scss')

document.addEventListener('DOMContentLoaded', () => {
  new FeaturedProjects('featured-slider').init()
  new DefaultProjectLists('home-projects').init()
  new OAuthHandler().showOAuthFirstLoginInformationIfNecessary()
  new MaintenanceHandler()
})
