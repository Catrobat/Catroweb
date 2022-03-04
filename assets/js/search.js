import $ from 'jquery'
import { ProjectLoader } from './custom/ProjectLoader'

require('../styles/custom/search.scss')

const $search = $('.js-search')
const projectLoader = new ProjectLoader(
    $search.data('result-container'),
    $search.data('path-search')
)
projectLoader.searchResult($search.data('query'))