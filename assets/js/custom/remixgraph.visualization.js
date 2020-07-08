/* eslint-env jquery */
/* global vis */
/* global Swal */
/* global SCRATCH_PROJECT_BASE_URL */
/* global CATROBAT_NODE_PREFIX */
/* global SCRATCH_NODE_PREFIX */
/* global NETWORK_OPTIONS */

// eslint-disable-next-line no-unused-vars
const RemixGraph = (function () {
  let instance = null

  return {
    getInstance: function () {
      if (instance == null) {
        instance = new _InternalRemixGraph()
      }
      return instance
    }
  }
})()

const _InternalRemixGraph = function () {
  const self = this
  self.programID = 0
  self.recommendedByPageID = 0
  self.remixGraphLayerId = null
  self.network = null
  self.nodes = null
  self.edges = null
  self.unavailableNodes = null
  self.relationDescendantMap = null
  self.relationAncestorMap = null
  self.backwardEdgeMap = null
  self.backwardReverseEdgeMap = null
  self.closeButtonSelector = null
  self.programDetailsUrlTemplate = null
  self.clickStatisticUrl = null
  self.remixGraphTranslations = null

  self.init = function (programID, recommendedByPageID, modalLayerId, remixGraphLayerId,
    programDetailsUrlTemplate, clickStatisticUrl, remixGraphTranslations) {
    self.reset()
    self.programID = programID
    self.recommendedByPageID = recommendedByPageID
    self.remixGraphLayerId = remixGraphLayerId
    self.remixGraphTranslations = remixGraphTranslations
    self.clickStatisticUrl = clickStatisticUrl
    self.programDetailsUrlTemplate = programDetailsUrlTemplate
    $('<div id="context-menu" class="context-menu-trigger" style="display:none;"></div>').appendTo('#' + modalLayerId)
  }

  self.getNodes = function () { return self.nodes } // accessed by behat tests
  self.getEdges = function () { return self.edges } // accessed by behat tests

  self.reset = function () {
    self.network = null
    self.nodes = new vis.DataSet()
    self.edges = new vis.DataSet()
    self.unavailableNodes = []
  }

  self.destroy = function () {
    self.reset()

    if (self.network !== null) {
      self.network.destroy()
      self.network = null
    }
  }

  self.render = function (loadingAnimation, networkDescription) {
    loadingAnimation.show()
    $('body').css('overflow', 'hidden')
    self.network = networkDescription.network
    self.nodes = networkDescription.nodes
    self.edges = networkDescription.edges
    self.unavailableNodes = networkDescription.unavailableNodes
    self.relationDescendantMap = networkDescription.relationDescendantMap
    self.relationAncestorMap = networkDescription.relationAncestorMap
    self.backwardEdgeMap = networkDescription.backwardEdgeMap
    self.backwardReverseEdgeMap = networkDescription.backwardReverseEdgeMap
    self.nodes.update([{ id: CATROBAT_NODE_PREFIX + '_' + self.programID, color: { border: '#FFFF00' } }])
    self.network.on('click', self.onClick)
    self.network.on('afterDrawing', function () {
      loadingAnimation.hide()
      setTimeout(function () { loadingAnimation.hide() }, 1000)
    })
    self.network.fit({ animation: false })
  }

  self.onClick = function (params) {
    // prevent multiple simultaneous clicks (needed for Google Chrome on Android)
    const overlayDiv = $('<div></div>').attr('id', 'overlay').addClass('overlay')
    overlayDiv.appendTo('body')
    // eslint-disable-next-line no-implied-eval
    setTimeout('$(\'#overlay\').remove();', 300)

    // lastTouchTime = params.event.timeStamp;
    const selectedNodes = params.nodes
    self.edges.forEach(function (edgeData) {
      self.nodes.update([
        {
          id: edgeData.from,
          borderWidth: NETWORK_OPTIONS.nodes.borderWidth,
          color: NETWORK_OPTIONS.nodes.color
        }
      ])
      self.nodes.update([
        {
          id: edgeData.to,
          borderWidth: NETWORK_OPTIONS.nodes.borderWidth,
          color: NETWORK_OPTIONS.nodes.color
        }
      ])
      self.edges.update([{ id: edgeData.id, color: NETWORK_OPTIONS.edges.color }])
    })

    if (selectedNodes.length === 0) {
      return
    }

    const selectedNodeId = selectedNodes[0]
    const idParts = selectedNodeId.split('_')
    const nodeId = parseInt(idParts[1])

    if ($.inArray(nodeId, self.unavailableNodes) !== -1) {
      Swal.fire({
        title: self.remixGraphTranslations.programNotAvailableErrorTitle,
        text: self.remixGraphTranslations.programNotAvailableErrorDescription,
        icon: 'error',
        showCancelButton: false,
        confirmButtonText: self.remixGraphTranslations.ok,
        closeOnConfirm: true
      },
      function () {
        self.network.selectNodes([])
        $('#overlay').remove()
      })
      return
    }

    const domPosition = params.pointer.DOM
    const menuWidth = 220
    const offsetX = (-menuWidth) / 2
    const selectedNodeData = self.nodes.get(selectedNodeId)

    $.contextMenu('destroy')
    const contextMenuItems = {
      title: {
        name: '<b>' + selectedNodeData.name + '</b>',
        isHtmlName: true,
        className: 'context-menu-item-title context-menu-not-selectable'
      },
      subtitle: {
        name: self.remixGraphTranslations.by + ' ' + selectedNodeData.username,
        isHtmlName: true,
        className: 'context-menu-item-subtitle context-menu-not-selectable'
      }
    }

    if (self.edges.length > 0) {
      contextMenuItems.sep1 = '---------'
    }

    if (nodeId !== self.programID) {
      contextMenuItems.open = {
        name: self.remixGraphTranslations.open,
        icon: function (opt, $itemElement, itemKey, item) {
          $itemElement.html('<span class="material-icons">open_in_new</span><span class="text">' + item.name + '</span>')
          return 'context-menu-icon-material'
        },
        callback: function () {
          self.performClickStatisticRequest(nodeId, (idParts[0] !== CATROBAT_NODE_PREFIX))

          const newUrlPrefix = (idParts[0] === CATROBAT_NODE_PREFIX)
            ? self.programDetailsUrlTemplate.replace('0', '')
            : SCRATCH_PROJECT_BASE_URL

          const queryString = (idParts[0] === CATROBAT_NODE_PREFIX)
            ? ('?rec_by_page_id=' + self.recommendedByPageID + '&rec_by_program_id=' + self.programID)
            : ''
          window.location = newUrlPrefix + nodeId + queryString
        }
      }
    }

    if (self.edges.length > 0) {
      contextMenuItems.edges = {
        name: self.remixGraphTranslations.showPaths,
        icon: function (opt, $itemElement, itemKey, item) {
          $itemElement.html('<span class="material-icons">repeat</span><span class="text">' + item.name + '</span>')
          return 'context-menu-icon-material'
        },
        callback: function () { self.highlightPathEdgesOfSelectedNode(nodeId) }
      }
    }

    $.contextMenu({
      selector: '.context-menu-trigger',
      trigger: 'left',
      className: 'data-title',
      events: {
        show: function (opt) {
        },
        hide: function () {
          self.network.selectNodes([])
        }
      },
      callback: function (key) {
        const m = 'clicked: ' + key
        // eslint-disable-next-line no-mixed-operators
        window.console && console.log(m) || alert(m)
      },
      position: function (opt) {
        const windowWidth = $(window).width()
        if (windowWidth > 767) {
          const menuWidth = 260; const minMarginLeft = 10; const minMarginRight = 10
          const menuOffsetX = Math.max(Math.min((offsetX + domPosition.x), (windowWidth - menuWidth - minMarginRight)), minMarginLeft)
          opt.$menu.css({ top: domPosition.y, left: menuOffsetX, width: menuWidth })
        } else {
          const width = Math.max(windowWidth - 40, 320)
          const height = opt.$menu.css('height').replace('px', '')
          opt.$menu.css({
            top: '50%',
            left: '50%',
            width: width,
            maxWidth: width,
            marginTop: -height / 2,
            marginLeft: -width / 2
          })
        }
      },
      items: contextMenuItems
    })
    $('#context-menu').click()
  }

  self.highlightPathEdgesOfSelectedNode = function (nodeId) {
    self.edges.forEach(function (edgeData) {
      let isFromIdConnectingAncestorOrDescendant = false
      let isToIdConnectingAncestorOrDescendant = false
      const fromId = parseInt(edgeData.from.split('_')[1])
      const toId = parseInt(edgeData.to.split('_')[1])

      if (edgeData.from.startsWith(CATROBAT_NODE_PREFIX) && edgeData.to.startsWith(CATROBAT_NODE_PREFIX)) {
        isFromIdConnectingAncestorOrDescendant = (($.inArray(fromId, self.relationAncestorMap[nodeId]) !== -1) || ($.inArray(fromId, self.relationDescendantMap[nodeId]) !== -1))
        isToIdConnectingAncestorOrDescendant = (($.inArray(toId, self.relationAncestorMap[nodeId]) !== -1) || ($.inArray(toId, self.relationDescendantMap[nodeId]) !== -1))
      } else if (edgeData.from.startsWith(SCRATCH_NODE_PREFIX) && edgeData.to.startsWith(CATROBAT_NODE_PREFIX)) {
        isFromIdConnectingAncestorOrDescendant = true
        isToIdConnectingAncestorOrDescendant = (($.inArray(toId, self.relationAncestorMap[nodeId]) !== -1) || ($.inArray(toId, self.relationDescendantMap[nodeId]) !== -1))
      }

      if (isFromIdConnectingAncestorOrDescendant && isToIdConnectingAncestorOrDescendant) {
        self.highlightNode(edgeData.from)
        self.highlightNode(edgeData.to)
        self.highlightEdge(edgeData.id)
      } else {
        self.unhighlightEdge(edgeData.id)
      }
    })
  }

  self.highlightNode = function (nodeId) {
    self.nodes.update([{ id: nodeId, borderWidth: 9, color: { border: '#FFFF00' } }])
  }

  self.highlightEdge = function (edgeId) {
    self.edges.update([{ id: edgeId, color: { color: '#FFFF00', opacity: 1.0 } }])
  }

  self.unhighlightEdge = function (edgeId) {
    self.edges.update([{ id: edgeId, color: { opacity: 0.05 } }])
  }

  self.performClickStatisticRequest = function (recommendedProgramID, isScratchProgram) {
    const type = 'rec_remix_graph'
    const params = {
      type: type,
      recFromID: self.programID,
      recID: recommendedProgramID,
      isScratchProgram: (isScratchProgram ? 1 : 0)
    }
    $.ajaxSetup({ async: false })
    $.post(self.clickStatisticUrl, params, function (data) {
      if (data === 'error') {
        console.log('No click statistic is created!')
      }
    }).fail(function (data) {
      console.log(data)
    })
  }
}
