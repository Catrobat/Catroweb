/* global NetworkDirector */
/* global RemixGraph */
/* global NetworkBuilder */

// eslint-disable-next-line no-unused-vars
function RemixGraphHandler(
  programId,
  remixOk,
  remixBy,
  remixOpen,
  remixPath,
  remixNotAvailableTitle,
  remixNotAvailableDescription,
  remixNotAvailable,
  remixUnknownUser,
  pleaseWait,
  detailsUrlTemplate,
  programRemixGraphUrl,
  remixGraphCountUrl,
) {
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
    function blockEventListener(event) {
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
      programUnknownUser: self.remixUnknownUser,
    }

    document.addEventListener('DOMContentLoaded', function () {
      fetch(self.remixGraphCountUrl)
        .then((response) => response.json())
        .then((data) => {
          const numberOfRemixes = data.count
          const titleElement = document.getElementById('top-app-bar__title')
          if (titleElement) {
            titleElement.innerHTML += `(${numberOfRemixes})`
          }
        })

      const loadingAnimation = document.getElementById('remix-graph-spinner')
      const networkDirector = new NetworkDirector()
      const remixGraph = RemixGraph.getInstance()

      remixGraph.init(
        self.programId,
        'remix-graph-container',
        'remix-graph-layer',
        self.detailsUrlTemplate,
        remixGraphTranslations,
      )

      remixGraph.destroy()
      if (cachedRemixData != null) {
        document.addEventListener('gesturestart', blockEventListener)
        document.ontouchmove = blockEventListener
        const networkBuilder = new NetworkBuilder(
          self.programId,
          'remix-graph-layer',
          remixGraphTranslations,
          cachedRemixData,
        )
        const networkDescription = networkDirector.construct(networkBuilder)
        remixGraph.render(loadingAnimation, networkDescription)
      } else {
        fetch(self.programRemixGraphUrl)
          .then((response) => response.json())
          .then((remixData) => {
            cachedRemixData = remixData
            document.addEventListener('gesturestart', blockEventListener)
            document.ontouchmove = blockEventListener
            const networkBuilder = new NetworkBuilder(
              self.programId,
              'remix-graph-layer',
              remixGraphTranslations,
              remixData,
            )
            const networkDescription = networkDirector.construct(networkBuilder)
            remixGraph.render(loadingAnimation, networkDescription)
          })
          .catch((e) => {
            if (loadingAnimation) {
              loadingAnimation.style.display = 'none'
            }
            alert('Unable to fetch remix-graph!' + e)
          })
      }
    })
  }
}
