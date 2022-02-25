import $ from 'jquery'


require('../styles/custom/search.scss')

const $search = $('.js-search')

function Search () {
    const self = this
    self.query = ''

    self.init = function (q) {
        const searchInput = $('#top-app-bar__search-input')
        const oldQuery = searchInput.html(q).text()
        self.initSearch(oldQuery)
        $(document).ready(function () {
            // eslint-disable-next-line no-undef
            showTopBarSearch()
            searchInput.val(oldQuery)
            // eslint-disable-next-line no-undef
            controlTopBarSearchClearButton()
        })
    }

    self.initSearch = function (query) {
        self.query = query

        // check previous query string
        const oldQuery = sessionStorage.getItem(self.query)
        if (query === oldQuery) { // same search -> restore old session limits
            restoreParamsWithSessionStorage()
        }
        sessionStorage.setItem(self.query, query)

        const $searchResults = $('#search-results')
        $('.project-list.search-list', $searchResults).each(function () {
            const $t = $(this)
            const category = $t.data('category')
            const property = $t.data('property')

            /* eslint-disable no-undef */
            const url = baseUrl + '/api/'

            const list = new SearchList(this, category, url, query, property, self.performClickStatisticRequest, theme)

            // if (data.CatrobatProjects === undefined || data.CatrobatProjects.length === 0) {
            //   $('#search-progressbar').hide()
            //   $('#search-results-text').text(0)
            //   return
            // }
            // $('#search-results-text').text(data.CatrobatInformation.TotalProjects)
            // self.totalNumberOfFoundProjects = parseInt(data.CatrobatInformation.TotalProjects)

            /* eslint-enable no-undef */
            $t.data('list', list)
        })
    }
}
