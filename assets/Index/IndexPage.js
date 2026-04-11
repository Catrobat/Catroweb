import { OAuthHandler } from '../Security/OAuthHandler'
import { FeaturedBanner } from './FeaturedBanner'
import { DefaultProjectLists } from './DefaultProjectLists'
import { MaintenanceHandler } from './MaintenanceHandler'
require('./IndexPage.scss')

document.addEventListener('DOMContentLoaded', () => {
  new FeaturedBanner('featured-slider').init()
  new DefaultProjectLists('home-projects').init()

  new OAuthHandler().showOAuthFirstLoginInformationIfNecessary()
  new MaintenanceHandler()
})
