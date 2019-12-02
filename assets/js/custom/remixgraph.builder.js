String.prototype.trunc = String.prototype.trunc || function (n) { return (this.length > n) ? this.substr(0, n - 1) + '...' : this }

var NetworkDirector = function () {
  this.construct = function (builder) {
    builder.stepGroupNodes()
    builder.stepBuildNodes()
    builder.stepBuildEdges()
    builder.stepBuildNetwork()
    return builder.getResult()
  }
}

var NetworkBuilder = function (programID, remixGraphLayerId, remixGraphTranslations, remixData) {
  var self = this
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
      network               : self.network,
      nodes                 : self.nodes,
      edges                 : self.edges,
      unavailableNodes      : self.unavailableNodes,
      relationDescendantMap : self.relationDescendantMap,
      relationAncestorMap   : self.relationAncestorMap,
      backwardEdgeMap       : self.backwardEdgeMap,
      backwardReverseEdgeMap: self.backwardReverseEdgeMap,
      groupNodes            : self.groupNodes
    }
  }
  
  self.stepGroupNodes = function () {
    for (var relationIndex = 0; relationIndex < self.remixGraphData.catrobatForwardRelations.length; ++relationIndex)
    {
      var relationData = self.remixGraphData.catrobatForwardRelations[relationIndex]
      if (!(relationData.ancestor_id in self.relationDescendantMap))
      {
        self.relationDescendantMap[relationData.ancestor_id] = []
      }
      if (!(relationData.descendant_id in self.relationAncestorMap))
      {
        self.relationAncestorMap[relationData.descendant_id] = []
      }
      self.relationDescendantMap[relationData.ancestor_id].push(relationData.descendant_id)
      self.relationAncestorMap[relationData.descendant_id].push(relationData.ancestor_id)
    }
    
    for (var relationIndex = 0; relationIndex < self.remixGraphData.catrobatBackwardEdgeRelations.length; ++relationIndex)
    {
      var relationData = self.remixGraphData.catrobatBackwardEdgeRelations[relationIndex]
      if (!(relationData.ancestor_id in self.backwardEdgeMap))
      {
        self.backwardEdgeMap[relationData.ancestor_id] = []
      }
      if (!(relationData.descendant_id in self.backwardReverseEdgeMap))
      {
        self.backwardReverseEdgeMap[relationData.descendant_id] = []
      }
      self.backwardEdgeMap[relationData.ancestor_id].push(relationData.descendant_id)
      self.backwardReverseEdgeMap[relationData.descendant_id].push(relationData.ancestor_id)
    }
    
    for (var ancestorId in self.relationDescendantMap)
    {
      var descendantRelations = self.relationDescendantMap[ancestorId]
      var ancestorRelations = self.relationAncestorMap[ancestorId]
      var found = false
      var found2 = false
      for (var key in descendantRelations)
      {
        if (self.programID === descendantRelations[key])
        {
          found = true
          break
        }
      }
      
      for (var key in ancestorRelations)
      {
        if (self.programID === ancestorRelations[key])
        {
          found2 = true
          break
        }
      }
      
      if (!found && !found2)
      {
        self.groupNodes.push(ancestorId)
      }
    }
  }
  
  self.stepBuildNodes = function () {
    for (var nodeIndex = 0; nodeIndex < self.remixGraphData.catrobatNodes.length; ++nodeIndex)
    {
      var nodeId = self.remixGraphData.catrobatNodes[nodeIndex]
      /*
       var found = false;
       for (var key in groupNodes) {
       if (nodeId == groupNodes[key]) {
       found = true;
       break;
       }
       }
       if (found) {
       continue;
       }
       console.log("OK!");
       */
      
      var nodeData = {
        id         : CATROBAT_NODE_PREFIX + '_' + nodeId,
        //value: (nodeId == remixData.id) ? 3 : 2,
        borderWidth: (nodeId === self.programID) ? 6 : 3,
        size       : (nodeId === self.programID) ? 40 : 20,
        shape      : 'circularImage',
        image      : self.catrobatProgramThumbnails[nodeId]
      }
      if (nodeId in self.remixGraphData.catrobatNodesData)
      {
        var programData = self.remixGraphData.catrobatNodesData[nodeId]
        nodeData['label'] = programData.name.trunc(15)
        nodeData['name'] = programData.name
        nodeData['username'] = programData.username
      }
      else
      {
        self.unavailableNodes.push(nodeId)
      }
      self.nodesData.push(nodeData)
    }
    
    for (var nodeIndex = 0; nodeIndex < self.remixGraphData.scratchNodes.length; ++nodeIndex)
    {
      var unavailableProgramData = {
        name    : self.remixGraphTranslations.programNotAvailable,
        username: self.remixGraphTranslations.programUnknownUser
      }
      var nodeId = self.remixGraphData.scratchNodes[nodeIndex]
      var programData = unavailableProgramData
      var programImageUrl = IMAGE_NOT_AVAILABLE_URL
      
      if (nodeId in self.remixGraphData.scratchNodesData)
      {
        programData = self.remixGraphData.scratchNodesData[nodeId]
        programImageUrl = SCRATCH_BASE_IMAGE_URL_TEMPLATE.replace('{}', nodeId)
      }
      
      self.nodesData.push({
        id      : SCRATCH_NODE_PREFIX + '_' + nodeId,
        label   : '[Scratch] ' + programData.name.trunc(10),
        name    : '[Scratch] ' + programData.name,
        username: programData.username,
        shape   : 'circularImage',
        image   : programImageUrl//,
      })
    }
  }
  
  self.stepBuildEdges = function () {
    for (var edgeIndex = 0; edgeIndex < self.remixGraphData.catrobatForwardEdgeRelations.length; ++edgeIndex)
    {
      var edgeData = self.remixGraphData.catrobatForwardEdgeRelations[edgeIndex]
      /*
       var found = false;
       for (var key in groupNodes) {
       if (edgeData.ancestor_id == groupNodes[key] || edgeData.descendant_id == groupNodes[key]) {
       found = true;
       break;
       }
       }

       if (found) {
       continue;
       }
       */
      
      self.edgesData.push({
        from: CATROBAT_NODE_PREFIX + '_' + edgeData.ancestor_id,
        to  : CATROBAT_NODE_PREFIX + '_' + edgeData.descendant_id//,
//            value: (edgeData.ancestor_id == programID || edgeData.descendant_id == programID) ? 2 : 1
      })
    }
    
    for (var edgeIndex = 0; edgeIndex < self.remixGraphData.catrobatBackwardEdgeRelations.length; ++edgeIndex)
    {
      var edgeData = self.remixGraphData.catrobatBackwardEdgeRelations[edgeIndex]
      /*
       var found = false;
       for (var key in groupNodes) {
       if (edgeData.ancestor_id == groupNodes[key] || edgeData.descendant_id == groupNodes[key]) {
       found = true;
       break;
       }
       }

       if (found) {
       continue;
       }
       */
      
      self.edgesData.push({
        from: CATROBAT_NODE_PREFIX + '_' + edgeData.ancestor_id,
        to  : CATROBAT_NODE_PREFIX + '_' + edgeData.descendant_id//,
//            value: (edgeData.ancestor_id == programID || edgeData.descendant_id == programID) ? 2 : 1
      })
    }
    
    for (var edgeIndex = 0; edgeIndex < self.remixGraphData.scratchEdgeRelations.length; ++edgeIndex)
    {
      var edgeData = self.remixGraphData.scratchEdgeRelations[edgeIndex]
      /*
       if (edgeData.descendant_id in groupNodes) {
       continue;
       }
       */
      
      self.edgesData.push({
        from: SCRATCH_NODE_PREFIX + '_' + edgeData.ancestor_id,
        to  : CATROBAT_NODE_PREFIX + '_' + edgeData.descendant_id//,
//            value: (edgeData.ancestor_id == programID || edgeData.descendant_id == programID) ? 2 : 1
      })
    }
  }
  
  self.stepBuildNetwork = function () {
    var hasGraphCycles = (self.remixGraphData.catrobatBackwardEdgeRelations.length > 0)
    self.nodes.add(self.nodesData)
    self.edges.add(self.edgesData)
    
    if (!hasGraphCycles)
    {
      NETWORK_OPTIONS.layout = {hierarchical: {parentCentralization: true, sortMethod: 'directed'}}
    }
    else
    {
      NETWORK_OPTIONS.layout = {randomSeed: 42}
    }
    
    var data = {nodes: self.nodes, edges: self.edges}
    self.network = new vis.Network(document.getElementById(self.remixGraphLayerId), data, NETWORK_OPTIONS)
    self.network.setData(data)
  }
}
