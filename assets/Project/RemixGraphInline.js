import Swal from 'sweetalert2'
import { ApiFetch } from '../Api/ApiHelper'
import { escapeHtml } from '../Components/HtmlEscape'
import './RemixGraphInline.scss'

const ACTIVE_NODE_BORDER = '#00acc1'
const DEFAULT_NODE_BORDER = '#c8d1da'
const DEFAULT_EDGE_COLOR = 'rgba(31, 41, 55, 0.38)'
const ACTIVE_EDGE_COLOR = '#00acc1'
const MIN_ZOOM = 0.35
const MAX_ZOOM = 2.4
const DESKTOP_BREAKPOINT = 768

const EMPTY_DATASET_ADAPTER = {
  get: () => undefined,
  getIds: () => [],
  forEach: () => {},
  update: () => {},
}

const remixGraphTestState = {
  nodes: EMPTY_DATASET_ADAPTER,
  edges: EMPTY_DATASET_ADAPTER,
}

if (typeof window !== 'undefined') {
  window.RemixGraph = {
    getInstance() {
      return {
        getNodes() {
          return remixGraphTestState.nodes
        },
        getEdges() {
          return remixGraphTestState.edges
        },
      }
    },
  }
}

let visNetworkPromise = null

function loadVisNetwork() {
  if (!visNetworkPromise) {
    visNetworkPromise = import(
      /* webpackChunkName: "remix-graph-network" */ 'vis-network/standalone'
    )
  }

  return visNetworkPromise
}

function truncateLabel(value, maxLength) {
  if (typeof value !== 'string') {
    return ''
  }

  if (value.length <= maxLength) {
    return value
  }

  return value.slice(0, Math.max(0, maxLength - 3)) + '...'
}

function createStatusMarkup(iconMarkup, message, extraClass = '') {
  return (
    '<div class="' +
    ['remix-graph-loading', extraClass].filter(Boolean).join(' ') +
    '">' +
    iconMarkup +
    '<span>' +
    escapeHtml(message) +
    '</span>' +
    '</div>'
  )
}

function getNodeDisplayName(node, container) {
  const fallbackName =
    container.dataset.transProjectNotAvailable || container.dataset.transTitle || 'Remix graph'
  const name = node.name || fallbackName

  return node.source === 'scratch' ? '[Scratch] ' + name : name
}

function createNetworkNode(node, currentProjectId, container) {
  const isCurrent =
    node.source === 'catrobat' && String(node.projectId) === String(currentProjectId)
  const isUnavailable = node.available === false
  const displayName = getNodeDisplayName(node, container)
  const username = node.username || container.dataset.transProjectUnknownUser || 'Unknown user'
  const labelLimit = node.source === 'scratch' ? 18 : 15

  return {
    id: node.id,
    projectId: String(node.projectId),
    source: node.source,
    unavailable: isUnavailable,
    name: isUnavailable ? undefined : displayName,
    username: isUnavailable ? undefined : username,
    displayName,
    displayUsername: username,
    label: truncateLabel(displayName, labelLimit),
    image: node.thumbnailUrl || container.dataset.notAvailableImageUrl,
    shape: 'circularImage',
    borderWidth: isCurrent ? 6 : 3,
    baseBorderWidth: isCurrent ? 6 : 3,
    size: isCurrent ? 40 : 20,
    baseSize: isCurrent ? 40 : 20,
    color: {
      border: isCurrent ? ACTIVE_NODE_BORDER : DEFAULT_NODE_BORDER,
      background: '#ffffff',
      highlight: {
        border: ACTIVE_NODE_BORDER,
        background: '#ffffff',
      },
    },
    font: {
      color: '#203040',
      face: 'Roboto, sans-serif',
      size: 11,
    },
    shadow: false,
  }
}

function createNetworkEdge(edge) {
  return {
    id: edge.id,
    from: edge.from,
    to: edge.to,
    width: 1.6,
    color: {
      color: DEFAULT_EDGE_COLOR,
      highlight: DEFAULT_EDGE_COLOR,
      hover: DEFAULT_EDGE_COLOR,
      opacity: 1,
    },
    arrows: {
      to: {
        enabled: true,
        scaleFactor: 0.72,
      },
    },
  }
}

function createNetworkOptions(hasCycles) {
  if (hasCycles) {
    return {
      autoResize: true,
      layout: {
        improvedLayout: true,
        randomSeed: 42,
      },
      physics: {
        enabled: true,
        solver: 'barnesHut',
        stabilization: {
          enabled: true,
          iterations: 180,
          fit: true,
        },
        barnesHut: {
          springLength: 130,
          damping: 0.18,
          gravitationalConstant: -2200,
        },
      },
      edges: {
        smooth: {
          type: 'dynamic',
        },
      },
      interaction: {
        dragNodes: false,
        dragView: true,
        hideEdgesOnDrag: true,
        hover: false,
        hoverConnectedEdges: false,
        keyboard: {
          enabled: true,
          bindToWindow: true,
          speed: { x: 24, y: 24, zoom: 0.08 },
        },
        multiselect: false,
        navigationButtons: false,
        selectConnectedEdges: false,
        zoomView: true,
      },
    }
  }

  return {
    autoResize: true,
    layout: {
      improvedLayout: true,
      hierarchical: {
        enabled: true,
        direction: 'UD',
        sortMethod: 'directed',
        nodeSpacing: 170,
        treeSpacing: 220,
        levelSeparation: 160,
        parentCentralization: true,
        blockShifting: true,
        edgeMinimization: true,
      },
    },
    physics: {
      enabled: false,
    },
    edges: {
      smooth: {
        type: 'cubicBezier',
        forceDirection: 'vertical',
        roundness: 0.4,
      },
    },
    interaction: {
      dragNodes: false,
      dragView: true,
      hideEdgesOnDrag: true,
      hover: false,
      hoverConnectedEdges: false,
      keyboard: {
        enabled: true,
        bindToWindow: true,
        speed: { x: 24, y: 24, zoom: 0.08 },
      },
      multiselect: false,
      navigationButtons: false,
      selectConnectedEdges: false,
      zoomView: true,
    },
  }
}

function buildIncomingEdgeMap(edges) {
  const incomingEdges = new Map()

  edges.forEach((edge) => {
    if (!incomingEdges.has(edge.to)) {
      incomingEdges.set(edge.to, [])
    }

    incomingEdges.get(edge.to).push(edge)
  })

  return incomingEdges
}

function hasCyclesInCatrobatEdges(edges) {
  const adjacency = new Map()
  const catrobatEdges = edges.filter((edge) => {
    return edge.from.startsWith('catrobat_') && edge.to.startsWith('catrobat_')
  })

  catrobatEdges.forEach((edge) => {
    if (!adjacency.has(edge.from)) {
      adjacency.set(edge.from, [])
    }

    adjacency.get(edge.from).push(edge.to)
  })

  const visited = new Set()
  const visiting = new Set()

  function visit(nodeId) {
    if (visiting.has(nodeId)) {
      return true
    }

    if (visited.has(nodeId)) {
      return false
    }

    visiting.add(nodeId)

    const neighbors = adjacency.get(nodeId) || []
    for (const neighbor of neighbors) {
      if (visit(neighbor)) {
        return true
      }
    }

    visiting.delete(nodeId)
    visited.add(nodeId)

    return false
  }

  for (const nodeId of adjacency.keys()) {
    if (visit(nodeId)) {
      return true
    }
  }

  return false
}

function resetGraphAppearance(state) {
  if (!state.nodes || !state.edges) {
    return
  }

  state.nodes.getIds().forEach((nodeId) => {
    const node = state.nodes.get(nodeId)
    if (!node) {
      return
    }

    state.nodes.update([
      {
        id: nodeId,
        borderWidth: node.baseBorderWidth,
        size: node.baseSize,
        color: {
          border: node.baseSize > 20 ? ACTIVE_NODE_BORDER : DEFAULT_NODE_BORDER,
          background: '#ffffff',
          highlight: {
            border: ACTIVE_NODE_BORDER,
            background: '#ffffff',
          },
        },
      },
    ])
  })

  state.edges.getIds().forEach((edgeId) => {
    state.edges.update([
      {
        id: edgeId,
        width: 1.6,
        color: {
          color: DEFAULT_EDGE_COLOR,
          highlight: DEFAULT_EDGE_COLOR,
          hover: DEFAULT_EDGE_COLOR,
          opacity: 1,
        },
      },
    ])
  })
}

function highlightNode(state, nodeId) {
  const node = state.nodes.get(nodeId)
  if (!node) {
    return
  }

  state.nodes.update([
    {
      id: nodeId,
      borderWidth: Math.max(node.baseBorderWidth + 2, 5),
      size: Math.max(node.baseSize + 8, 28),
      color: {
        border: ACTIVE_NODE_BORDER,
        background: '#ffffff',
        highlight: {
          border: ACTIVE_NODE_BORDER,
          background: '#ffffff',
        },
      },
    },
  ])
}

function highlightEdge(state, edgeId) {
  state.edges.update([
    {
      id: edgeId,
      width: 3,
      color: {
        color: ACTIVE_EDGE_COLOR,
        highlight: ACTIVE_EDGE_COLOR,
        hover: ACTIVE_EDGE_COLOR,
        opacity: 1,
      },
    },
  ])
}

function highlightPathToNode(state, nodeId, visited = new Set()) {
  highlightNode(state, nodeId)

  const incomingEdges = state.incomingEdges.get(nodeId) || []
  incomingEdges.forEach((edge) => {
    highlightEdge(state, edge.id)
    highlightNode(state, edge.from)

    if (!visited.has(edge.from)) {
      visited.add(edge.from)
      highlightPathToNode(state, edge.from, visited)
    }
  })
}

function closeActionMenu(state, preserveSelection = false) {
  state.elements.menu.classList.add('d-none')
  state.elements.backdrop.classList.add('d-none')
  state.elements.menu.innerHTML = ''

  if (!preserveSelection && state.network) {
    if (typeof state.network.unselectAll === 'function') {
      state.network.unselectAll()
    } else {
      state.network.selectNodes([])
    }
  }
}

function positionActionMenu(menu, pointer) {
  if (window.innerWidth < DESKTOP_BREAKPOINT) {
    menu.style.top = ''
    menu.style.left = ''

    return
  }

  const menuRect = menu.getBoundingClientRect()
  const margin = 12
  const left = Math.min(
    Math.max(pointer.x - menuRect.width / 2, margin),
    window.innerWidth - menuRect.width - margin,
  )
  const top = Math.min(
    Math.max(pointer.y + 18, margin),
    window.innerHeight - menuRect.height - margin,
  )

  menu.style.left = left + 'px'
  menu.style.top = top + 'px'
}

function showUnavailableDialog(container) {
  Swal.fire({
    title: container.dataset.transProjectNotAvailableTitle,
    text: container.dataset.transProjectNotAvailableDescription,
    icon: 'error',
    allowOutsideClick: false,
    showCancelButton: false,
    confirmButtonText: container.dataset.transOk || 'OK',
    customClass: {
      confirmButton: 'btn btn-primary',
    },
    buttonsStyling: false,
  })
}

function buildMenuAction(icon, label, action) {
  return (
    '<button type="button" class="remix-graph-menu__action" data-action="' +
    action +
    '">' +
    '<i class="material-icons">' +
    escapeHtml(icon) +
    '</i>' +
    '<span>' +
    escapeHtml(label) +
    '</span>' +
    '</button>'
  )
}

function showActionMenu(state, node, pointer) {
  const actions = []
  if (node.projectId !== String(state.projectId)) {
    actions.push(buildMenuAction('open_in_new', state.container.dataset.transOpen, 'open'))
  }

  if ((state.incomingEdges.get(node.id) || []).length > 0) {
    actions.push(buildMenuAction('route', state.container.dataset.transShowPaths, 'paths'))
  }

  if (actions.length === 0) {
    closeActionMenu(state)
    return
  }

  state.elements.menu.innerHTML =
    '<div class="remix-graph-menu__surface">' +
    '<div class="remix-graph-menu__header">' +
    '<div class="remix-graph-menu__title">' +
    escapeHtml(node.displayName || node.name || '') +
    '</div>' +
    '<div class="remix-graph-menu__subtitle">' +
    escapeHtml((state.container.dataset.transBy || 'by') + ' ' + (node.displayUsername || '')) +
    '</div>' +
    '</div>' +
    '<div class="remix-graph-menu__actions">' +
    actions.join('') +
    '</div>' +
    '</div>'

  state.elements.menu.querySelectorAll('[data-action]').forEach((button) => {
    button.addEventListener('click', () => {
      const action = button.dataset.action

      if (action === 'open') {
        closeActionMenu(state)
        const targetUrl =
          node.source === 'scratch'
            ? state.container.dataset.scratchProjectBaseUrl + '/' + node.projectId
            : state.container.dataset.projectUrlTemplate.replace('__PROJECT_ID__', node.projectId)
        window.location.assign(targetUrl)
        return
      }

      if (action === 'paths') {
        closeActionMenu(state, true)
        resetGraphAppearance(state)
        highlightPathToNode(state, node.id)
      }
    })
  })

  state.elements.backdrop.classList.remove('d-none')
  state.elements.menu.classList.remove('d-none')

  requestAnimationFrame(() => {
    positionActionMenu(state.elements.menu, pointer)
  })
}

function renderLoadingState(state) {
  state.elements.status.classList.remove('d-none')
  state.elements.content.classList.add('d-none')
  state.elements.status.innerHTML = createStatusMarkup(
    '<div class="remix-graph-loading-spinner"></div>',
    state.container.dataset.transLoading || 'Loading remix graph...',
  )
}

function renderErrorState(state) {
  state.elements.status.classList.remove('d-none')
  state.elements.content.classList.add('d-none')
  state.elements.status.innerHTML = createStatusMarkup(
    '<i class="material-icons">warning</i>',
    state.container.dataset.transError || 'Could not load the remix graph.',
    'remix-graph-error',
  )
}

function renderEmptyState(state) {
  state.elements.status.classList.remove('d-none')
  state.elements.content.classList.add('d-none')
  state.elements.status.innerHTML = createStatusMarkup(
    '<i class="material-icons">info</i>',
    state.container.dataset.transEmpty || 'No remix graph data is available for this project yet.',
    'remix-graph-empty',
  )
}

function updateSummary(state, graphData) {
  state.elements.remixCount.textContent = String(graphData.remixCount ?? 0)
  state.elements.projectCount.textContent = String(graphData.projectCount ?? 0)
  state.elements.scratchCount.textContent = String(graphData.scratchCount ?? 0)
}

function adjustZoom(state, factor) {
  if (!state.network) {
    return
  }

  const currentScale = state.network.getScale()
  const currentPosition = state.network.getViewPosition()
  const nextScale = Math.min(MAX_ZOOM, Math.max(MIN_ZOOM, currentScale * factor))

  state.network.moveTo({
    position: currentPosition,
    scale: nextScale,
    animation: {
      duration: 180,
      easingFunction: 'easeInOutQuad',
    },
  })
}

function resetViewport(state) {
  closeActionMenu(state)
  resetGraphAppearance(state)

  if (state.network) {
    state.network.fit({
      animation: {
        duration: 240,
        easingFunction: 'easeInOutQuad',
      },
    })
  }
}

function bindControls(state) {
  state.elements.zoomIn.addEventListener('click', () => adjustZoom(state, 1.18))
  state.elements.zoomOut.addEventListener('click', () => adjustZoom(state, 1 / 1.18))
  state.elements.reset.addEventListener('click', () => resetViewport(state))
  state.elements.backdrop.addEventListener('click', () => closeActionMenu(state))

  window.addEventListener('resize', () => {
    if (!state.elements.panel.classList.contains('d-none') && state.network) {
      closeActionMenu(state)
      requestAnimationFrame(() => {
        state.network.redraw()
      })
    }
  })

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeActionMenu(state)
    }
  })
}

async function loadAndRenderGraph(state) {
  renderLoadingState(state)

  try {
    const [graphData, visModule] = await Promise.all([
      new ApiFetch(state.graphUrl, 'GET', undefined, 'json').run(),
      loadVisNetwork(),
    ])

    if (!Array.isArray(graphData.nodes) || graphData.nodes.length === 0) {
      renderEmptyState(state)
      state.loaded = true
      return
    }

    const { DataSet, Network } = visModule
    const edgeData = graphData.edges.map((edge) => createNetworkEdge(edge))
    const nodes = new DataSet(
      graphData.nodes.map((node) => createNetworkNode(node, state.projectId, state.container)),
    )
    const edges = new DataSet(edgeData)

    state.nodes = nodes
    state.edges = edges
    state.incomingEdges = buildIncomingEdgeMap(edges.get())

    remixGraphTestState.nodes = nodes
    remixGraphTestState.edges = edges

    const hasCycles = hasCyclesInCatrobatEdges(graphData.edges)

    state.network = new Network(
      state.elements.network,
      { nodes, edges },
      createNetworkOptions(hasCycles),
    )

    state.network.on('click', (params) => {
      closeActionMenu(state)
      resetGraphAppearance(state)

      if (!params.nodes || params.nodes.length === 0) {
        return
      }

      const selectedNode = nodes.get(params.nodes[0])
      if (!selectedNode) {
        return
      }

      if (selectedNode.unavailable) {
        showUnavailableDialog(state.container)
        return
      }

      const canvasRect = state.elements.network.getBoundingClientRect()
      showActionMenu(state, selectedNode, {
        x: canvasRect.left + params.pointer.DOM.x,
        y: canvasRect.top + params.pointer.DOM.y,
      })
    })

    state.network.on('dragStart', () => closeActionMenu(state))
    state.network.on('zoom', () => closeActionMenu(state))

    if (hasCycles) {
      state.network.once('stabilizationIterationsDone', () => {
        state.network.setOptions({ physics: false })
      })
    }

    updateSummary(state, graphData)
    state.elements.status.classList.add('d-none')
    state.elements.content.classList.remove('d-none')

    requestAnimationFrame(() => {
      state.network.fit({ animation: false })
    })

    state.loaded = true
  } catch (error) {
    console.error('Failed to load remix graph', error)
    renderErrorState(state)
    state.loaded = false
  } finally {
    state.loadPromise = null
  }
}

function createState(container) {
  return {
    container,
    projectId: container.dataset.projectId,
    graphUrl: container.dataset.graphUrl,
    loadPromise: null,
    loaded: false,
    network: null,
    nodes: EMPTY_DATASET_ADAPTER,
    edges: EMPTY_DATASET_ADAPTER,
    incomingEdges: new Map(),
    elements: {
      toggle: document.getElementById('remix-graph-toggle'),
      panel: document.getElementById('remix-graph-panel'),
      status: document.getElementById('remix-graph-status'),
      content: document.getElementById('remix-graph-content'),
      network: document.getElementById('remix-graph-network'),
      menu: document.getElementById('remix-graph-menu'),
      backdrop: document.getElementById('remix-graph-menu-backdrop'),
      zoomIn: document.getElementById('remix-graph-zoom-in'),
      zoomOut: document.getElementById('remix-graph-zoom-out'),
      reset: document.getElementById('remix-graph-reset'),
      remixCount: document.getElementById('remix-graph-remix-count'),
      projectCount: document.getElementById('remix-graph-project-count'),
      scratchCount: document.getElementById('remix-graph-scratch-count'),
    },
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('remix-graph-inline')
  if (!container) {
    return
  }

  const state = createState(container)
  bindControls(state)

  state.elements.toggle.addEventListener('click', async () => {
    const isHidden = state.elements.panel.classList.contains('d-none')

    if (isHidden) {
      state.elements.panel.classList.remove('d-none')
      state.elements.toggle.setAttribute('aria-expanded', 'true')

      if (state.loaded) {
        requestAnimationFrame(() => {
          state.network?.redraw()
        })
        return
      }

      if (!state.loadPromise) {
        state.loadPromise = loadAndRenderGraph(state)
      }

      await state.loadPromise
      return
    }

    closeActionMenu(state)
    state.elements.panel.classList.add('d-none')
    state.elements.toggle.setAttribute('aria-expanded', 'false')
  })
})
