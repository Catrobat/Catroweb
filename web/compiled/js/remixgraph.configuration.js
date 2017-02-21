/*
  Generated File by Grunt
  Sourcepath: web/js
*/
/**
 * ---------------------------------------------------------------------------------------------------------------------
 * REMIX GRAPH & NETWORK CONFIGURATION
 * ---------------------------------------------------------------------------------------------------------------------
 */
var SCRATCH_PROJECT_BASE_URL = 'https://scratch.mit.edu/projects/';
var SCRATCH_BASE_IMAGE_URL_TEMPLATE = 'https://cdn2.scratch.mit.edu/get_image/project/{}_140x140.png';
var IMAGE_NOT_AVAILABLE_URL = '/images/default/not_available.png';
var CATROBAT_NODE_PREFIX = 'catrobat';
var SCRATCH_NODE_PREFIX = 'scratch';
var NETWORK_OPTIONS = {
    nodes: {
        labelHighlightBold: false,
        borderWidth: 3,
        borderWidthSelected: 3,
        size: 20,
        color: {
            border: '#CCCCCC',
            background: '#FFFFFF',
            highlight: {
                border: '#FFFF00'//,
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
    layout: { improvedLayout: true },
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
            type: 'dynamic'
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
