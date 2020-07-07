/* eslint-env jquery */

/* eslint no-undef: "off" */
// eslint-disable-next-line no-unused-vars
function ProgramRecommender (program, programId, recs, specificRecommender, moreFromThisUser,
  programUserId, remixOk, remixBy, remixOpen, remixPath, remixNotAvailableTitle,
  remixNotAvailableDecription, remixNotAvailable, remixUnknownUser, pleaseWait, remixGraphPageId,
  detailsUrlTemplate, pathStats, programRemixGraphUrl) {
  const self = this
  self.program = program
  self.programId = programId
  self.recs = recs
  self.specificRecommender = specificRecommender
  self.moreFromThisUser = moreFromThisUser
  self.programUserId = programUserId
  self.remixOk = remixOk
  self.remixBy = remixBy
  self.remixOpen = remixOpen
  self.remixPath = remixPath
  self.remixNotAvailableTitle = remixNotAvailableTitle
  self.remixNotAvailableDescription = remixNotAvailableDecription
  self.remixNotAvailable = remixNotAvailable
  self.remixUnknownUser = remixUnknownUser
  self.pleaseWait = pleaseWait
  self.remixGraphPageId = remixGraphPageId
  self.detailsUrlTemplate = detailsUrlTemplate
  self.pathStats = pathStats
  self.programRemixGraphUrl = programRemixGraphUrl

  self.init = function () {
    self.program.getApkStatus()
    self.program.createLinks()
    function blockEventListener (event) {
      event.preventDefault()
    }
    self.recs.initRecsys()
    self.specificRecommender.init()
    self.moreFromThisUser.initMoreFromThisUser(self.programUserId, self.programId)
    var cachedRemixData = null
    var remixGraphTranslations = {
      ok: self.remixOk,
      by: self.remixBy,
      open: self.remixPath,
      showPaths: self.remixPath,
      programNotAvailableErrorTitle: self.remixNotAvailableTitle,
      programNotAvailableErrorDescription: self.remixNotAvailableDescription,
      programNotAvailable: self.remixNotAvailable,
      programUnknownUser: self.remixUnknownUser
    }
    $(document).ready(function () {
      var loadingAnimation = new LoadingAnimation('#177f8d', self.pleaseWait)
      var recommendedByRemixGraphPageId = self.remixGraphPageId
      var networkDirector = new NetworkDirector()
      var remixGraph = RemixGraph.getInstance()
      remixGraph.init(self.programId, recommendedByRemixGraphPageId, 'remix-graph-modal', 'remix-graph-layer', 'close-button',
        self.detailsUrlTemplate, self.pathStats, remixGraphTranslations)

      $('#remix-graph-modal-link').animatedModal({
        modalTarget: 'remix-graph-modal',
        animatedIn: 'zoomInUp',
        animatedOut: 'bounceOutDown',
        color: '#177f8d',
        beforeOpen: function () {
          remixGraph.destroy()
          if (cachedRemixData != null) {
            document.addEventListener('gesturestart', blockEventListener)
            document.ontouchmove = blockEventListener
            var networkBuilder = new NetworkBuilder(self.programId, 'remix-graph-layer', remixGraphTranslations, cachedRemixData)
            var networkDescription = networkDirector.construct(networkBuilder)
            remixGraph.render(loadingAnimation, networkDescription)
          } else {
            $.ajax({
              url: self.programRemixGraphUrl,
              type: 'get',
              success: function (remixData) {
                cachedRemixData = remixData
                document.addEventListener('gesturestart', blockEventListener)
                document.ontouchmove = blockEventListener
                var networkBuilder = new NetworkBuilder(self.programId, 'remix-graph-layer', remixGraphTranslations, remixData)
                var networkDescription = networkDirector.construct(networkBuilder)
                remixGraph.render(loadingAnimation, networkDescription)
              },
              error: function () {
                alert('Unable to fetch remix-graph!')
              }
            })
          }
          console.log('The animation was called')
        },
        afterOpen: function () {
          loadingAnimation.show()
          console.log('The animation is completed')
        },
        beforeClose: function () {
          console.log('The animation was called')
          loadingAnimation.hide()
          document.removeEventListener('gesturestart', blockEventListener)
          document.ontouchmove = null
          location.reload()
        }
      })
      $(document).on('click', '#remix-graph-button', function () {
        $('#remix-graph-modal-link').click()
      })
      $(document).on('click', '#remix-graph-button-small', function () {
        $('#remix-graph-modal-link').click()
      })
    })
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
