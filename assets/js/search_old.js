import { ProjectLoader } from './custom/ProjectLoader'
import '../styles/custom/search.scss'

class SearchOld {
  constructor() {
    this.searchElement = document.querySelector('.js-search')
    this.resultContainer = this.searchElement.dataset.resultContainer
    this.pathSearch = this.searchElement.dataset.pathSearch
    this.query = this.searchElement.dataset.query
    this.projectLoader = new ProjectLoader(
      this.resultContainer,
      this.pathSearch,
    )
  }

  searchResult() {
    this.projectLoader.searchResult(this.query)
  }
}

new SearchOld().searchResult()
