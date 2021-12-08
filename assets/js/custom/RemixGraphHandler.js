/* eslint-env jquery */
/* global NetworkDirector */
/* global RemixGraph */
/* global NetworkBuilder */

// eslint-disable-next-line no-unused-vars
function RemixGraphHandler (programId, remixOk, remixBy, remixOpen, remixPath, remixNotAvailableTitle,
  remixNotAvailableDescription, remixNotAvailable, remixUnknownUser, pleaseWait,
  detailsUrlTemplate, programRemixGraphUrl, remixGraphCountUrl) {
  const self = this
  self.programId = programId
  self.remixOk = remixOk
  self.remixBy = remixBy
  self.remixOpen = remixOpen
  self.remixPath = remixPath
  self.remixNotAvailableTitle = remixNotAvailableTitle
  self.remixNotAvailableDescription = remixNotAvailableDescription
  self.remixNotAvailable = remixNotAvailable
  self.remixUnknownUser = remixUnknownUser
  self.pleaseWait = pleaseWait
  self.detailsUrlTemplate = detailsUrlTemplate
  self.programRemixGraphUrl = programRemixGraphUrl
  self.remixGraphCountUrl = remixGraphCountUrl

  self.init = function () {
    function blockEventListener (event) {
      event.preventDefault()
    }

    let cachedRemixData = null

    const remixGraphTranslations = {
      ok: self.remixOk,
      by: self.remixBy,
      open: self.remixOpen,
      showPaths: self.remixPath,
      programNotAvailableErrorTitle: self.remixNotAvailableTitle,
      programNotAvailableErrorDescription: self.remixNotAvailableDescription,
      programNotAvailable: self.remixNotAvailable,
      programUnknownUser: self.remixUnknownUser
    }

    $(document).ready(function () {
      $.ajax({
        url: self.remixGraphCountUrl,
        type: 'get',
        success: function (data) {
          const numberOfRemixes = data.count
          $('#top-app-bar__title').append('(' + numberOfRemixes + ')')
        }
      })

      const loadingAnimation = $('#remix-graph-spinner')
      const networkDirector = new NetworkDirector()
      const remixGraph = RemixGraph.getInstance()

      remixGraph.init(
        self.programId,
        'remix-graph-container',
        'remix-graph-layer',
        self.detailsUrlTemplate,
        remixGraphTranslations
      )

      remixGraph.destroy()
      if (cachedRemixData != null) {
        document.addEventListener('gesturestart', blockEventListener)
        document.ontouchmove = blockEventListener
        const networkBuilder = new NetworkBuilder(self.programId, 'remix-graph-layer', remixGraphTranslations, cachedRemixData)
        const networkDescription = networkDirector.construct(networkBuilder)
        remixGraph.render(loadingAnimation, networkDescription)
      } else {
        $.ajax({
          url: self.programRemixGraphUrl,
          type: 'get',
          success: function (remixData) {
            cachedRemixData = remixData
            document.addEventListener('gesturestart', blockEventListener)
            document.ontouchmove = blockEventListener
            const networkBuilder = new NetworkBuilder(self.programId, 'remix-graph-layer', remixGraphTranslations, remixData)
            const networkDescription = networkDirector.construct(networkBuilder)
            remixGraph.render(loadingAnimation, networkDescription)
          },
          error: function () {
            $('#remix-graph-spinner').hide()
            alert('Unable to fetch remix-graph!')
          }
        })
      }
    })
  }
}
