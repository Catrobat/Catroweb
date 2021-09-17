import { ProjectLoader } from "./custom/ProjectLoader";

require('../styles/custom/search.scss')

const $search = $('.js-search')
let projectLoader = new ProjectLoader(
  $search.data('result-container'),
  $search.data('path-search')
)
projectLoader.searchResult($search.data('query'))


