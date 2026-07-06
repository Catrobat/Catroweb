import { OAuthHandler } from '../Security/OAuthHandler'
import { DefaultProjectLists } from './DefaultProjectLists'
import { FeaturedBanner } from './FeaturedBanner'
import { MaintenanceHandler } from './MaintenanceHandler'
import './IndexPage.scss'

document.addEventListener('DOMContentLoaded', () => {
  new FeaturedBanner('featured-slider').init()
  new DefaultProjectLists('home-projects').init()

  new OAuthHandler().showOAuthFirstLoginInformationIfNecessary()
  new MaintenanceHandler()
})
