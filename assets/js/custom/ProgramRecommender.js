/* eslint-env jquery */

/* eslint no-undef: "off" */
// eslint-disable-next-line no-unused-vars
function ProgramRecommender (programId, pathStats) {
  const self = this
  self.programId = programId
  self.pathStats = pathStats

  self.init = function () {
    $(document).ready(function () {
      $(document).on('click', '.rec-programs', function (event) {
        event.preventDefault()
        const href = $(this).attr('href')
        let clickType = 'no type'
        let additions = 0

        if (href.indexOf('tag') > 0) {
          clickType = 'tags'
          additions = (href.match(/[0-9]+/))[0]
        } else if (href.indexOf('extension') > 0) {
          clickType = 'extensions'
          var list = href.split('/')
          additions = list[list.length - 1]
        } else if (href.indexOf('project') > 0) {
          clickType = 'project'
          additions = (href.split('project/')[1]).split('?')[0]

          const containerElement = $(this).parent('.program').parent('.programs').parent()
          if (containerElement.attr('id') === 'specific-programs-recommendations') {
            clickType = 'rec_specific_programs'
          }
        }
        $.ajaxSetup({ async: false })
        $.post(self.pathStats, {
          type: clickType,
          recFromID: self.programId,
          recID: additions
        }, function (data) {
          if (data === 'error') {
            console.log('No click statistic is created!')
          }
        }).fail(function (data) {
          console.log(data)
        })
        window.location.href = href
      })
    })
  }
}
