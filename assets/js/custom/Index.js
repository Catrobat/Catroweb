/* eslint-env jquery */
/* global Swal */

/* eslint no-undef: "off" */
// eslint-disable-next-line no-unused-vars
function Index (clickStats, homepageClickStats, confirmButtonText) {
  const self = this
  self.clickStats = clickStats
  self.homepageClickStats = homepageClickStats
  self.confirmButtonText = confirmButtonText

  self.init = function (pathNewest, recommendedByPageId, pathGeneralProjects, pathMostDownloaded,
    pathMostViewed, pathScratchRemixes, pathRandom, pathExample) {
    const newest = new ProjectLoader('#newest', pathNewest)
    const recommended = new ProjectLoader('#recommended', pathGeneralProjects, undefined, recommendedByPageId)
    const mostDownloaded = new ProjectLoader('#mostDownloaded', pathMostDownloaded)
    const mostViewed = new ProjectLoader('#mostViewed', pathMostViewed)
    const scratchRemixes = new ProjectLoader('#scratchRemixes', pathScratchRemixes)
    const random = new ProjectLoader('#random', pathRandom)
    const example = new ProjectLoader('#example', pathExample)

    newest.init()
    recommended.init()
    mostDownloaded.init()
    mostViewed.init()
    scratchRemixes.init()
    random.init()
    example.init()
  }
  self.gamejamInit = function (pathGamejamSample, pathGamejamSubmission, pathRelated) {
    const sample = new ProjectLoader('#sample', pathGamejamSample)
    const submissions = new ProjectLoader('#submissions', pathGamejamSubmission)
    const related = new ProjectLoader('#related', pathRelated)

    sample.init()
    submissions.init()
    related.init()
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

  $(document).on('click', '.program', function () {
    const clickedProgramId = this.id.replace('program-', '')
    this.className += ' visited-program'
    const storedVisits = sessionStorage.getItem('visits')

    if (!storedVisits) {
      const newVisits = [clickedProgramId]
      sessionStorage.setItem('visits', JSON.stringify(newVisits))
    } else {
      const parsedVisits = JSON.parse(storedVisits)
      if (!($.inArray(clickedProgramId, parsedVisits) >= 0)) {
        parsedVisits.push(clickedProgramId)
        sessionStorage.setItem('visits', JSON.stringify(parsedVisits))
      }
    }
  })

  $(document).one('click', '#feature-slider > div > div > a', function (event) {
    event.preventDefault()
    const href = $(this).attr('href')
    const programID = ((href.indexOf('project') > 0) ? (href.split('project/')[1]).split('?')[0] : 0)
    const type = 'featured'
    self.performClickStatisticRequest(href, type, false, 0, programID)
  })

  $(document).one('click', '.rec-programs', function (event) {
    event.preventDefault()
    const isRecommendedProgram = $(this).hasClass('homepage-recommended-programs')
    const type = (isRecommendedProgram ? 'rec_homepage' : $(this).parent('.program').parent('.programs').parent().attr('id'))
    const href = $(this).attr('href')
    const recommendedProgramID = ((href.indexOf('project') > 0) ? (href.split('project/')[1]).split('?')[0] : 0)
    const userSpecificRecommendation = ((href.indexOf('rec_user_specific=') > 0) ? parseInt((href.split('rec_user_specific=')[1].match(/[0-9]+/))[0]) : 0)
    self.performClickStatisticRequest(href, type, isRecommendedProgram, userSpecificRecommendation, recommendedProgramID)
  })

  $(document).on('click', '#help-button', function () {
    Swal.fire({
      title: $(this).attr('data-help-title'),
      text: $(this).attr('data-help-description'),
      showCancelButton: false,
      confirmButtonText: self.confirmButtonText,
      closeOnConfirm: true,
      icon: 'question'
    }
    )
  })
}
