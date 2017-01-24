/*
  Generated File by Grunt
  Sourcepath: web/js
*/
var clusterIndex = 0;
var clusters = [];
var lastClusterZoomLevel = 0;
var clusterFactor = 0.9;
var network = null;
var nodes = new vis.DataSet();
var edges = new vis.DataSet();
var unavailableNodes = [];
var closeButtonSelector = null;
var translations = null;

String.prototype.trunc = String.prototype.trunc || function(n){ return (this.length > n) ? this.substr(0,n-1)+'...' : this; };

function destroyNetwork() {
    nodes.clear();
    edges.clear();
    unavailableNodes = [];

    if (network !== null) {
        network.destroy();
        network = null;
    }
}

function injectContextMenuLayer(modalLayerId) {
    $('<div id="context-menu" class="context-menu-trigger" style="display:none;"></div>').appendTo("#" + modalLayerId);
}

function render(modalLayerId, remixGraphLayerId, closeButtonClassName, remixData, remixGraphTranslations) {
    translations = remixGraphTranslations;
    injectContextMenuLayer(modalLayerId);
    closeButtonSelector = $("." + closeButtonClassName);
    $("body").css("overflow", "hidden");

    var scratchBaseImageUrl = 'https://cdn2.scratch.mit.edu/get_image/project/{}_140x140.png';
    var nodesData = [];
    var edgesData = [];
    var hasGraphCycles = (remixData.remixGraph.catrobatBackwardEdgeRelations.length > 0);

    for (var nodeIndex = 0; nodeIndex < remixData.remixGraph.catrobatNodes.length; nodeIndex++) {
        var nodeId = parseInt(remixData.remixGraph.catrobatNodes[nodeIndex]);
        var nodeData = {
            id: "catrobat_" + nodeId,
            //value: (nodeId == remixData.id) ? 3 : 2,
            borderWidth: (nodeId == remixData.id) ? 6 : 3,
            size: (nodeId == remixData.id) ? 60 : 30,
            shape: 'circularImage',
            image: remixData.catrobatProgramThumbnails[nodeId]
        };
        if (nodeId in remixData.remixGraph.catrobatNodesData) {
            var programData = remixData.remixGraph.catrobatNodesData[nodeId];
            nodeData["label"] = programData.name.trunc(15);
            nodeData["name"] = programData.name.trunc(20);
            nodeData["username"] = programData.username;
        } else {
            unavailableNodes.push(nodeId);
        }
        nodesData.push(nodeData);
    }

    for (var nodeIndex = 0; nodeIndex < remixData.remixGraph.scratchNodes.length; nodeIndex++) {
        var nodeId = parseInt(remixData.remixGraph.scratchNodes[nodeIndex]);
        var programData = remixData.remixGraph.scratchNodesData[nodeId];
        nodesData.push({
            id: "scratch_" + nodeId,
            label: "[Scratch] " + programData.name.trunc(10),
            name: programData.name.trunc(20),
            username: programData.username,
            shape: 'circularImage',
            image: scratchBaseImageUrl.replace("{}", nodeId)//,
        });
    }

    for (var edgeIndex = 0; edgeIndex < remixData.remixGraph.catrobatForwardEdgeRelations.length; edgeIndex++) {
        var edgeData = remixData.remixGraph.catrobatForwardEdgeRelations[edgeIndex];
        edgesData.push({
            from: "catrobat_" + edgeData.ancestor_id,
            to: "catrobat_" + edgeData.descendant_id//,
//            value: (edgeData.ancestor_id == remixData.id || edgeData.descendant_id == remixData.id) ? 2 : 1
        });
    }

    for (var edgeIndex = 0; edgeIndex < remixData.remixGraph.catrobatBackwardEdgeRelations.length; edgeIndex++) {
        var edgeData = remixData.remixGraph.catrobatBackwardEdgeRelations[edgeIndex];
        edgesData.push({
            from: "catrobat_" + edgeData.ancestor_id,
            to: "catrobat_" + edgeData.descendant_id//,
//            value: (edgeData.ancestor_id == remixData.id || edgeData.descendant_id == remixData.id) ? 2 : 1
        });
    }

    for (var edgeIndex = 0; edgeIndex < remixData.remixGraph.scratchEdgeRelations.length; edgeIndex++) {
        var edgeData = remixData.remixGraph.scratchEdgeRelations[edgeIndex];
        edgesData.push({
            from: "scratch_" + edgeData.ancestor_id,
            to: "catrobat_" + edgeData.descendant_id//,
//            value: (edgeData.ancestor_id == remixData.id || edgeData.descendant_id == remixData.id) ? 2 : 1
        });
    }

    nodes.add(nodesData);
    edges.add(edgesData);

    var data = { nodes: nodes, edges: edges };
    var layoutOptions = { improvedLayout: true };

    if (!hasGraphCycles) {
        layoutOptions.hierarchical = {
            parentCentralization: true,
            sortMethod: "directed"
        };
    } else {
        layoutOptions.randomSeed = 42;
    }

    var options = {
        nodes: {
            labelHighlightBold: false,
            borderWidth: 3,
            borderWidthSelected: 3,
            size: 30,
            color: {
                border: '#CCCCCC',
                background: '#FFFFFF',
                highlight: {
                    border: '#CCCCCC'//,
                    //background: '#000000'
                }
            },
            font: {
                size: 10,
                color:'#CCCCCC'//,
//                background: '#FFFFFF'
            },
            shapeProperties: {
                useBorderWithImage: true
            }
        },
        layout: layoutOptions,
        edges: {
            labelHighlightBold: false,
            color: {
                color: '#ffffff',
                highlight: '#ffffff',
                //highlight: '#000000',
                hover: '#ffffff',
                opacity: 1.0
            },
            smooth: {
//            type: 'straightCross'
//            type: 'dynamic'
                type: 'horizontal'
            },
            arrows: { to: true }
        },
        //smoothCurves: { dynamic:false, type: "continuous" },
        physics:{
            adaptiveTimestep: false,
            stabilization: true
        },
        interaction: {
            dragNodes: false,
            dragView: true,
            hideEdgesOnDrag: true,
            hideNodesOnDrag: false,
            hover: false,
            hoverConnectedEdges: false,
            keyboard: {
                enabled: true,
                speed: { x: 20, y: 20, zoom: 0.1 },
                bindToWindow: true
            },
            multiselect: false,
            navigationButtons: true,
            selectable: true,
            selectConnectedEdges: false,
            zoomView: true
        }
    };
    network = new vis.Network(document.getElementById(remixGraphLayerId), data, options);
    network.setData(data);

    nodes.update([{ id: "catrobat_" + remixData.id, color: { border: '#FFFF00' } }]);

    var clusterOptionsByData = {
        processProperties: function(clusterOptions, childNodes) {
            clusterOptions.label = "[" + childNodes.length + "]";
            return clusterOptions;
        },
        clusterNodeProperties: { borderWidth: 3, shape: 'box', font: { size: 30 } }
    };
    //network.clusterByHubsize(undefined, clusterOptionsByData);
    var clusterOptionsByData = {
        /*processProperties: function (clusterOptions, childNodes) {
         clusterIndex = clusterIndex + 1;
         var childrenCount = 0;
         for (var i = 0; i < childNodes.length; i++) {
         childrenCount += childNodes[i].childrenCount || 1;
         }
         clusterOptions.childrenCount = childrenCount;
         clusterOptions.label = "# " + childrenCount + "";
         clusterOptions.font = {size: childrenCount*5+30}
         clusterOptions.id = 'cluster:' + clusterIndex;
         clusters.push({id:'cluster:' + clusterIndex, scale:scale});
         return clusterOptions;
         },*/
        clusterNodeProperties: {borderWidth: 3, shape: 'star', font: {size: 30}}
    };
    //network.clusterOutliers(clusterOptionsByData);

    /*
    network.on('dragStart', function(event) {
        event.stopPropagation();
    });
    */

    /*
    network.on("selectNode", function(params) {
        if (params.nodes.length == 1) {
            if (network.isCluster(params.nodes[0]) == true) {
                network.openCluster(params.nodes[0]);
                network.setOptions({physics:{stabilization:{fit: false}}});
                network.stabilize();
            }
        }
    });
    */

    network.on("click", onClick);

    /*
     // set the first initial zoom level
     network.once('initRedraw', function() {
     if (lastClusterZoomLevel === 0) {
     lastClusterZoomLevel = network.getScale();
     }
     });

     // we use the zoom event for our clustering
     network.on('zoom', function (params) {
     if (params.direction == '-') {
     if (params.scale < lastClusterZoomLevel*clusterFactor) {
     makeClusters(params.scale);
     lastClusterZoomLevel = params.scale;
     }
     }
     else {
     openClusters(params.scale);
     }
     });

     // if we click on a node, we want to open it up!
     network.on("selectNode", function (params) {
     if (params.nodes.length == 1) {
     if (network.isCluster(params.nodes[0]) == true) {
     network.openCluster(params.nodes[0])
     }
     }
     });
     */
    //clusterById();
    network.fit({ animation: false });
}

function clusterById() {
    var data = {
        nodes: nodes,
        edges: edges
    };
    network.setData(data);
    var colors = ['orange','lime','DarkViolet'];
    var clusterOptionsByData;
    for (var i = 0; i < colors.length; i++) {
        var color = colors[i];
        clusterOptionsByData = {
            joinCondition: function (childOptions) {
                var parts = childOptions.id.split("_");
                if (parts[0] == 'scratch') {
                    return false;
                }
                return parseInt(parts[1]) >= 100; // the color is fully defined in the node.
            },
            processProperties: function (clusterOptions, childNodes, childEdges) {
                var totalMass = 0;
                for (var i = 0; i < childNodes.length; i++) {
                    totalMass += childNodes[i].mass;
                }
                clusterOptions.mass = totalMass;
                return clusterOptions;
            },
            clusterNodeProperties: {
                id: 'cluster:' + color,
                borderWidth: 3,
                shape: 'database',
                color: color,
                label: '100 programs'
            }
        };
        network.cluster(clusterOptionsByData);
    }
}

function clusterize() {
    var clusterOptionsByData = {
        processProperties: function (clusterOptions, childNodes) {
            clusterIndex = clusterIndex + 1;
            var childrenCount = 0;
            for (var i = 0; i < childNodes.length; i++) {
                childrenCount += childNodes[i].childrenCount || 1;
            }
            clusterOptions.childrenCount = childrenCount;
            clusterOptions.label = "# " + childrenCount + "";
            clusterOptions.font = {size: childrenCount * 5 + 30}
            clusterOptions.id = 'cluster:' + clusterIndex;
            clusters.push({id: 'cluster:' + clusterIndex, scale: scale});
            return clusterOptions;
        },
        clusterNodeProperties: {borderWidth: 3, shape: 'database', font: {size: 30}}
    };
    network.clusterOutliers(clusterOptionsByData);
    // since we use the scale as a unique identifier, we do NOT want to fit after the stabilization
    network.setOptions({physics:{stabilization:{fit: false}}});
    network.stabilize();
}


//var lastTouchTime = null;
function onClick(params) {
    /*
    if (lastTouchTime != null && (params.event.timeStamp - lastTouchTime) < 1000) {
        params.stopPropagation();
        return;
    }*/

    // prevent multiple simultaneous clicks (needed for Google Chrome on Android)
    var overlayDiv = $("<div></div>").attr("id", "overlay").addClass("overlay");
    overlayDiv.appendTo("body");
    setTimeout("$('#overlay').remove();", 300);

    lastTouchTime = params.event.timeStamp;
    var selectedNodes = params.nodes;
    edges.forEach(function (edgeData) {
        edges.update([{
            id: edgeData.id,
            color: { opacity: 1.0 }
        }]);
    });

    if (selectedNodes.length == 0) {
        return;
    }

    var selectedNodeId = selectedNodes[0];
    var idParts = selectedNodeId.split("_");
    var nodeId = parseInt(idParts[1]);

    if ($.inArray(nodeId, unavailableNodes) != -1) {
        // TODO: translate
        //var overlayDiv = $("<div></div>").addClass("overlay");
        //overlayDiv.appendTo("body");
        //sweetAlert("Sorry...", "The program is not available any more!", "error");
        swal({
                title: translations.programNotAvailableErrorTitle,
                text: translations.programNotAvailableErrorDescription,
                type: "error",
                showCancelButton: false,
                confirmButtonText: translations.ok,
                closeOnConfirm: true
            },
            function() {
                $("#overlay").remove();
        });
        return;
    }

    /*
    var selectedNodeId = selection.nodes[0];
    var newColor = '#' + Math.floor((Math.random() * 255 * 255 * 255)).toString(16);
    nodes.update([{
        id: selectedNodeId,
        color: {
            border: '#FF0000',
            background: newColor
        }
    }]);
    */

    var domPosition = params["pointer"]["DOM"];
    var menuWidth = 220;
    var offsetX = (- menuWidth) / 2;
    var selectedNodeData = nodes.get(selectedNodeId);
    var selectedEdges = network.getConnectedEdges(selectedNodeId);

    /*
    swal({
            title: selectedNodeData["name"],
            text: "by " + selectedNodeData["username"],
            type: "info",
            showCancelButton: false,
            confirmButtonText: "Open",
            showLoaderOnConfirm: true,
            closeOnConfirm: false
        },
        function() {
            closeButtonSelector.click();
            swal("Loading...", "Please wait!", "info");
            var newUrlPrefix = (idParts[0] == 'catrobat')
                ? 'http://localhost:8888/pocketcode/program/'
                : 'https://scratch.mit.edu/projects/';
            window.location = newUrlPrefix + nodeId;
        }); */

    $.contextMenu('destroy');
    $.contextMenu({
        selector: '.context-menu-trigger',
        trigger: 'left',
        className: 'data-title',
        events: {
            show: function(opt) {
            },
            hide: function(opt) {}
        },
        callback: function(key, options) {
            var m = "clicked: " + key;
            window.console && console.log(m) || alert(m);
        },
        position: function(opt, x, y) {
            var windowWidth = $(window).width();
            if (windowWidth > 1024) {
                opt.$menu.css({ top: domPosition["y"], left: offsetX + domPosition["x"] });
            } else {
                var width = Math.max(windowWidth - 100, 200);
                var height = opt.$menu.css("height").replace("px", "");
                opt.$menu.css({
                    top: "50%",
                    left: "50%",
                    width: width,
                    maxWidth: width,
                    marginTop: -height/2,
                    marginLeft: -width/2,
                });
            }
        },
        items: {
            "title": {
                name: "<b>" + selectedNodeData["name"] + "</b>",
                isHtmlName: true,
                className: 'context-menu-item-title context-menu-not-selectable'
            },
            "subtitle": {
                name: translations.by + " " + selectedNodeData["username"],
                isHtmlName: true,
                className: 'context-menu-item-subtitle context-menu-not-selectable'
            },
            "sep1": "---------",
            "open": {
                name: translations.open,
                icon: "fa-external-link",
                callback: function () {
                    closeButtonSelector.click();
                    var newUrlPrefix = (idParts[0] == 'catrobat')
                        ? 'http://localhost:8888/pocketcode/program/'
                        : 'https://scratch.mit.edu/projects/';
                    window.location = newUrlPrefix + nodeId;
                }
            },
            "edges": {
                name: translations.showPaths,
                icon: "fa-retweet", // fa-level-down
                callback: function() {
                    edges.forEach(function (edgeData) {
                        if (selectedEdges.indexOf(edgeData.id) == -1) {
                            edges.update([{ id: edgeData.id, color: { opacity: 0.1 } }]);
                        } else {
                            edges.update([{ id: edgeData.id, color: { opacity: 1.0 } }]);
                        }
                    });
                }
            }
        }
    });
    $("#context-menu").click();
}

//----------------------------------------------------------------------------------------------------------------------
// make the clusters
function makeClusters(scale) {
    var clusterOptionsByData = {
        processProperties: function (clusterOptions, childNodes) {
            clusterIndex = clusterIndex + 1;
            var childrenCount = 0;
            for (var i = 0; i < childNodes.length; i++) {
                childrenCount += childNodes[i].childrenCount || 1;
            }
            clusterOptions.childrenCount = childrenCount;
            clusterOptions.label = "# " + childrenCount + "";
            clusterOptions.font = {size: childrenCount * 5 + 30}
            clusterOptions.id = 'cluster:' + clusterIndex;
            clusters.push({id: 'cluster:' + clusterIndex, scale: scale});
            return clusterOptions;
        },
        clusterNodeProperties: {borderWidth: 3, shape: 'database', font: {size: 30}}
    };
    network.clusterOutliers(clusterOptionsByData);
    // since we use the scale as a unique identifier, we do NOT want to fit after the stabilization
    network.setOptions({physics:{stabilization:{fit: false}}});
    network.stabilize();
}

// open them back up!
function openClusters(scale) {
    var newClusters = [];
    var declustered = false;
    for (var i = 0; i < clusters.length; i++) {
        if (clusters[i].scale < scale) {
            network.openCluster(clusters[i].id);
            lastClusterZoomLevel = scale;
            declustered = true;
        }
        else {
            newClusters.push(clusters[i])
        }
    }
    clusters = newClusters;
    if (declustered === true) {
        // since we use the scale as a unique identifier, we do NOT want to fit after the stabilization
        network.setOptions({physics:{stabilization:{fit: false}}});
        network.stabilize();
    }
}
