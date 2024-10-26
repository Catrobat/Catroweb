/* global vis */
/* global CATROBAT_NODE_PREFIX */
/* global SCRATCH_NODE_PREFIX */
/* global NETWORK_OPTIONS */
/* global SCRATCH_BASE_IMAGE_URL_TEMPLATE */
/* global IMAGE_NOT_AVAILABLE_URL */

// eslint-disable-next-line no-extend-native
String.prototype.trunc =
  String.prototype.trunc ||
  function (n) {
    return this.length > n ? this.substr(0, n - 1) + '...' : this
  }

// eslint-disable-next-line no-unused-vars
const NetworkDirector = function () {
  this.construct = function (builder) {
    builder.stepGroupNodes()
    builder.stepBuildNodes()
    builder.stepBuildEdges()
    builder.stepBuildNetwork()
    return builder.getResult()
  }
}

// eslint-disable-next-line no-unused-vars
const NetworkBuilder = function (programID, remixGraphLayerId, remixGraphTranslations, remixData) {
  const self = this
  self.network = null
  self.nodes = new vis.DataSet()
  self.edges = new vis.DataSet()
  self.unavailableNodes = []
  self.relationDescendantMap = {}
  self.relationAncestorMap = {}
  self.backwardEdgeMap = {}
  self.backwardReverseEdgeMap = {}
  self.groupNodes = []

  self.programID = programID
  self.remixGraphLayerId = remixGraphLayerId
  self.remixGraphTranslations = remixGraphTranslations
  self.remixGraphData = remixData.remixGraph
  self.catrobatProgramThumbnails = remixData.catrobatProgramThumbnails
  self.nodesData = []
  self.edgesData = []

  self.getResult = function () {
    return {
      network: self.network,
      nodes: self.nodes,
      edges: self.edges,
      unavailableNodes: self.unavailableNodes,
      relationDescendantMap: self.relationDescendantMap,
      relationAncestorMap: self.relationAncestorMap,
      backwardEdgeMap: self.backwardEdgeMap,
      backwardReverseEdgeMap: self.backwardReverseEdgeMap,
      groupNodes: self.groupNodes,
    }
  }

  self.stepGroupNodes = function () {
    for (
      let relationIndex = 0;
      relationIndex < self.remixGraphData.catrobatForwardRelations.length;
      ++relationIndex
    ) {
      const relationData = self.remixGraphData.catrobatForwardRelations[relationIndex]
      if (!(relationData.ancestor_id in self.relationDescendantMap)) {
        self.relationDescendantMap[relationData.ancestor_id] = []
      }
      if (!(relationData.descendant_id in self.relationAncestorMap)) {
        self.relationAncestorMap[relationData.descendant_id] = []
      }
      self.relationDescendantMap[relationData.ancestor_id].push(relationData.descendant_id)
      self.relationAncestorMap[relationData.descendant_id].push(relationData.ancestor_id)
    }

    for (
      let relationIndex = 0;
      relationIndex < self.remixGraphData.catrobatBackwardEdgeRelations.length;
      ++relationIndex
    ) {
      const relationData = self.remixGraphData.catrobatBackwardEdgeRelations[relationIndex]
      if (!(relationData.ancestor_id in self.backwardEdgeMap)) {
        self.backwardEdgeMap[relationData.ancestor_id] = []
      }
      if (!(relationData.descendant_id in self.backwardReverseEdgeMap)) {
        self.backwardReverseEdgeMap[relationData.descendant_id] = []
      }
      self.backwardEdgeMap[relationData.ancestor_id].push(relationData.descendant_id)
      self.backwardReverseEdgeMap[relationData.descendant_id].push(relationData.ancestor_id)
    }

    for (const ancestorId in self.relationDescendantMap) {
      const descendantRelations = self.relationDescendantMap[ancestorId]
      const ancestorRelations = self.relationAncestorMap[ancestorId]
      let found = false
      let found2 = false
      for (const key in descendantRelations) {
        if (self.programID === descendantRelations[key]) {
          found = true
          break
        }
      }

      for (const key in ancestorRelations) {
        if (self.programID === ancestorRelations[key]) {
          found2 = true
          break
        }
      }

      if (!found && !found2) {
        self.groupNodes.push(ancestorId)
      }
    }
  }

  self.stepBuildNodes = function () {
    for (let nodeIndex = 0; nodeIndex < self.remixGraphData.catrobatNodes.length; ++nodeIndex) {
      const nodeId = self.remixGraphData.catrobatNodes[nodeIndex]

      const nodeData = {
        id: CATROBAT_NODE_PREFIX + '_' + nodeId,
        // value: (nodeId == remixData.id) ? 3 : 2,
        borderWidth: nodeId === self.programID ? 6 : 3,
        size: nodeId === self.programID ? 40 : 20,
        shape: 'circularImage',
        image: self.catrobatProgramThumbnails[nodeId],
      }
      if (nodeId in self.remixGraphData.catrobatNodesData) {
        const programData = self.remixGraphData.catrobatNodesData[nodeId]
        nodeData.label = programData.name.trunc(15)
        nodeData.name = programData.name
        nodeData.username = programData.username
      } else {
        self.unavailableNodes.push(nodeId)
      }
      self.nodesData.push(nodeData)
    }

    for (let nodeIndex = 0; nodeIndex < self.remixGraphData.scratchNodes.length; ++nodeIndex) {
      const unavailableProgramData = {
        name: self.remixGraphTranslations.programNotAvailable,
        username: self.remixGraphTranslations.programUnknownUser,
      }
      const nodeId = self.remixGraphData.scratchNodes[nodeIndex]
      let programData = unavailableProgramData
      let programImageUrl = IMAGE_NOT_AVAILABLE_URL

      if (nodeId in self.remixGraphData.scratchNodesData) {
        programData = self.remixGraphData.scratchNodesData[nodeId]
        programImageUrl = SCRATCH_BASE_IMAGE_URL_TEMPLATE.replace('{}', nodeId)
      }

      self.nodesData.push({
        id: SCRATCH_NODE_PREFIX + '_' + nodeId,
        label: '[Scratch] ' + programData.name.trunc(10),
        name: '[Scratch] ' + programData.name,
        username: programData.username,
        shape: 'circularImage',
        image: programImageUrl, //,
      })
    }
  }

  self.stepBuildEdges = function () {
    for (
      let edgeIndex = 0;
      edgeIndex < self.remixGraphData.catrobatForwardEdgeRelations.length;
      ++edgeIndex
    ) {
      const edgeData = self.remixGraphData.catrobatForwardEdgeRelations[edgeIndex]

      self.edgesData.push({
        from: CATROBAT_NODE_PREFIX + '_' + edgeData.ancestor_id,
        to: CATROBAT_NODE_PREFIX + '_' + edgeData.descendant_id, //,
        //            value: (edgeData.ancestor_id == programID || edgeData.descendant_id == programID) ? 2 : 1
      })
    }

    for (
      let edgeIndex = 0;
      edgeIndex < self.remixGraphData.catrobatBackwardEdgeRelations.length;
      ++edgeIndex
    ) {
      const edgeData = self.remixGraphData.catrobatBackwardEdgeRelations[edgeIndex]

      self.edgesData.push({
        from: CATROBAT_NODE_PREFIX + '_' + edgeData.ancestor_id,
        to: CATROBAT_NODE_PREFIX + '_' + edgeData.descendant_id, //,
      })
    }

    for (
      let edgeIndex = 0;
      edgeIndex < self.remixGraphData.scratchEdgeRelations.length;
      ++edgeIndex
    ) {
      const edgeData = self.remixGraphData.scratchEdgeRelations[edgeIndex]
      self.edgesData.push({
        from: SCRATCH_NODE_PREFIX + '_' + edgeData.ancestor_id,
        to: CATROBAT_NODE_PREFIX + '_' + edgeData.descendant_id, //,
      })
    }
  }

  self.stepBuildNetwork = function () {
    const hasGraphCycles = self.remixGraphData.catrobatBackwardEdgeRelations.length > 0
    self.nodes.add(self.nodesData)
    self.edges.add(self.edgesData)

    if (!hasGraphCycles) {
      NETWORK_OPTIONS.layout = {
        hierarchical: { parentCentralization: true, sortMethod: 'directed' },
      }
    } else {
      NETWORK_OPTIONS.layout = { randomSeed: 42 }
    }

    const data = { nodes: self.nodes, edges: self.edges }
    self.network = new vis.Network(
      document.getElementById(self.remixGraphLayerId),
      data,
      NETWORK_OPTIONS,
    )
    self.network.setData(data)
  }
}
