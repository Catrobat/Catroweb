import { OAuthHandler } from '../Security/OAuthHandler'
import { FeaturedBanner } from './FeaturedBanner'
import { ProjectList } from '../Project/ProjectList'
import { DefaultProjectLists } from './DefaultProjectLists'
import { MaintenanceHandler } from './MaintenanceHandler'
require('./IndexPage.scss')

document.addEventListener('DOMContentLoaded', () => {
  new FeaturedBanner('featured-slider').init()
  new DefaultProjectLists('home-projects').init()

  const studiosEl = document.getElementById('popular-studios')
  if (studiosEl) {
    new ProjectList(
      studiosEl,
      studiosEl.dataset.category,
      studiosEl.dataset.url,
      studiosEl.dataset.property,
      studiosEl.dataset.theme,
      10,
    )
  }

  new OAuthHandler().showOAuthFirstLoginInformationIfNecessary()
  new MaintenanceHandler()
})
