/* eslint-env jquery */

/* eslint no-undef: "off" */
// eslint-disable-next-line no-unused-vars
function Index (clickStats, homepageClickStats, confirmButtonText) {
  const self = this
  self.clickStats = clickStats
  self.homepageClickStats = homepageClickStats
  self.confirmButtonText = confirmButtonText

  self.init = function () {
    /* OLD ProjectLoader Recommended Projects
    take a look at old ProjectLoader.js at revision cc2019af76e735f73fcaae0fc2c4365843feaab3

    TWIG: recommendedByPageId = '{{ constant('App\\Catrobat\\RecommenderSystem\\RecommendedPageId::INDEX_PAGE') }}'
    TWIG: pathGeneralProjects = '{{ path('api_recsys_general_projects') }}'

    const recommended = new ProjectLoader('#recommended', pathGeneralProjects, undefined, recommendedByPageId)
    recommended.init()
     */

    const $homeProjects = $('#home-projects')
    $('.project-list', $homeProjects).each(function () {
      const $t = $(this)
      const category = $t.data('category')
      const property = $t.data('property')

      /* eslint-disable no-undef */
      let url = baseUrl + '/api/projects/?category=' + category

      /* eslint-disable no-undef */
      if (flavor !== 'pocketcode' || category === 'example') {
        // The pocketcode flavor must use projects from all flavors
        url += '&flavor=' + flavor
      }

      const list = new ProjectList(this, category, url, property, self.performClickStatisticRequest)
      /* eslint-enable no-undef */
      $t.data('list', list)
    })
  }

  self.performClickStatisticRequest = function (href, type, isRecommendedProgram, userSpecificRecommendation, programID) {
    let url = self.clickStats
    let params = {}
    if (!isRecommendedProgram) {
      url = self.homepageClickStats
      if (['featured', 'example', 'newest', 'mostDownloaded', 'mostViewed', 'scratchRemixes', 'random'].indexOf(type) === -1) {
        alert('You clicked at a program of an unknown section!')
        return
      }
      params = { type: type, programID: programID }
    } else {
      params = {
        type: type,
        recFromID: 0,
        recID: programID,
        isScratchProgram: 0,
        recIsUserSpecific: userSpecificRecommendation
      }
    }
    $.post(url, params, function (data) {
      if (data === 'error') {
        console.log('No click statistic is created!')
      }
    }).always(function () {
      window.location.href = href
    })
      .fail(function (data) {
        console.log(data)
      })
  }

  $(document).one('click', '#feature-slider > div > div > a', function (event) {
    event.preventDefault()
    const href = $(this).attr('href')
    const programID = ((href.indexOf('project') > 0) ? (href.split('project/')[1]).split('?')[0] : 0)
    const type = 'featured'
    self.performClickStatisticRequest(href, type, false, 0, programID)
  })

  // TODO: needs to be reworked if needed for recommender system (including the feature slider listener above!)
  /*
  $(document).one('click', '.rec-programs', function (event) {
    event.preventDefault()
    const isRecommendedProgram = $(this).hasClass('homepage-recommended-programs')
    const type = (isRecommendedProgram ? 'rec_homepage' : $(this).parent('.program').parent('.programs').parent().attr('id'))
    const href = $(this).attr('href')
    const recommendedProgramID = ((href.indexOf('project') > 0) ? (href.split('project/')[1]).split('?')[0] : 0)
    const userSpecificRecommendation = ((href.indexOf('rec_user_specific=') > 0) ? parseInt((href.split('rec_user_specific=')[1].match(/[0-9]+/))[0]) : 0)
    self.performClickStatisticRequest(href, type, isRecommendedProgram, userSpecificRecommendation, recommendedProgramID)
  })
   */

  self.showOauthPopup = function (firstOauthLoginUrl, informationText, title, okTranslation) {
    $.get(firstOauthLoginUrl,
      function (data) {
        if (data.first_login === true) {
          var isshow = localStorage.getItem('oauthSignIn')
          if (isshow == null) {
            localStorage.setItem('oauthSignIn', 1)
            Swal.fire({
              title: title,
              html: informationText,
              showCancelButton: false,
              confirmButtonText: okTranslation,
              icon: 'info'
            }
            )
          }
        }
      })
  }
}
