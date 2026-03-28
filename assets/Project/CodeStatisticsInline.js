import './CodeStatisticsInline.scss'

const CATEGORY_ICONS = {
  abstraction: 'layers',
  parallelism: 'call_split',
  synchronization: 'sync',
  logicalThinking: 'psychology',
  flowControl: 'repeat',
  userInteractivity: 'touch_app',
  dataRepresentation: 'storage',
}

/**
 * Simple SVG animations for the score display. Each function accepts
 * a CSS color string and returns an SVG markup string.
 */
const SCORE_ANIMATIONS = [
  // Star burst
  (color) =>
    `<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
      <style>
        @keyframes star-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes star-scale { 0%,100% { transform: scale(1); } 50% { transform: scale(1.15); } }
        .star-group { animation: star-spin 8s linear infinite; transform-origin: 50px 50px; }
        .star-inner { animation: star-scale 2s ease-in-out infinite; transform-origin: 50px 50px; }
      </style>
      <g class="star-group">
        <g class="star-inner">
          <polygon points="50,8 61,38 93,38 67,56 76,87 50,70 24,87 33,56 7,38 39,38"
                   fill="${color}" opacity="0.85"/>
          <polygon points="50,20 57,40 78,40 61,52 67,73 50,62 33,73 39,52 22,40 43,40"
                   fill="${color}" opacity="0.5"/>
        </g>
      </g>
      <circle cx="50" cy="50" r="4" fill="${color}" opacity="0.9"/>
    </svg>`,

  // Rocket
  (color) =>
    `<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
      <style>
        @keyframes rocket-float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
        @keyframes flame-flicker { 0%,100% { opacity: 0.8; transform: scaleY(1); } 50% { opacity: 1; transform: scaleY(1.3); } }
        .rocket-body { animation: rocket-float 2s ease-in-out infinite; }
        .flame { animation: flame-flicker 0.4s ease-in-out infinite; transform-origin: 50px 80px; }
      </style>
      <g class="rocket-body">
        <path d="M50 15 C50 15 65 35 65 55 L65 65 L35 65 L35 55 C35 35 50 15 50 15Z"
              fill="${color}" opacity="0.9"/>
        <circle cx="50" cy="45" r="6" fill="white" opacity="0.9"/>
        <rect x="30" y="58" width="10" height="12" rx="2" fill="${color}" opacity="0.7"/>
        <rect x="60" y="58" width="10" height="12" rx="2" fill="${color}" opacity="0.7"/>
        <ellipse class="flame" cx="45" cy="75" rx="4" ry="8" fill="#ff6b35" opacity="0.8"/>
        <ellipse class="flame" cx="55" cy="75" rx="4" ry="8" fill="#ff6b35" opacity="0.8"
                 style="animation-delay: 0.2s"/>
        <ellipse class="flame" cx="50" cy="78" rx="3" ry="6" fill="#ffd700" opacity="0.9"/>
      </g>
    </svg>`,

  // Trophy
  (color) =>
    `<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
      <style>
        @keyframes trophy-shine { 0%,100% { opacity: 0; } 50% { opacity: 0.6; } }
        @keyframes trophy-bounce { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-4px); } }
        .trophy { animation: trophy-bounce 2.5s ease-in-out infinite; }
        .shine { animation: trophy-shine 3s ease-in-out infinite; }
      </style>
      <g class="trophy">
        <rect x="40" y="70" width="20" height="6" rx="1" fill="${color}" opacity="0.7"/>
        <rect x="35" y="76" width="30" height="5" rx="2" fill="${color}" opacity="0.8"/>
        <rect x="46" y="60" width="8" height="12" rx="1" fill="${color}" opacity="0.7"/>
        <path d="M30 20 L30 45 C30 55 40 62 50 62 C60 62 70 55 70 45 L70 20 Z"
              fill="${color}" opacity="0.85"/>
        <path d="M30 25 C20 25 15 35 20 45 C23 50 28 48 30 45"
              fill="${color}" opacity="0.5"/>
        <path d="M70 25 C80 25 85 35 80 45 C77 50 72 48 70 45"
              fill="${color}" opacity="0.5"/>
        <circle class="shine" cx="42" cy="35" r="5" fill="white" opacity="0"/>
      </g>
    </svg>`,

  // Lightning bolt
  (color) =>
    `<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
      <style>
        @keyframes bolt-pulse { 0%,100% { opacity: 0.85; filter: brightness(1); }
          50% { opacity: 1; filter: brightness(1.2); } }
        @keyframes spark { 0%,100% { opacity: 0; transform: scale(0); }
          50% { opacity: 0.7; transform: scale(1); } }
        .bolt { animation: bolt-pulse 1.5s ease-in-out infinite; }
        .spark1 { animation: spark 2s ease-in-out infinite; }
        .spark2 { animation: spark 2s ease-in-out 0.5s infinite; }
        .spark3 { animation: spark 2s ease-in-out 1s infinite; }
      </style>
      <g class="bolt">
        <polygon points="55,8 30,50 48,50 42,92 72,42 52,42"
                 fill="${color}" opacity="0.9"/>
      </g>
      <circle class="spark1" cx="25" cy="30" r="3" fill="${color}" opacity="0"/>
      <circle class="spark2" cx="78" cy="55" r="2.5" fill="${color}" opacity="0"/>
      <circle class="spark3" cx="35" cy="75" r="2" fill="${color}" opacity="0"/>
    </svg>`,
]

function escapeHtml(str) {
  const div = document.createElement('div')
  div.textContent = str
  return div.innerHTML
}

function animateNumber(el, target) {
  if (target === 0) {
    el.textContent = '0'
    return
  }

  let current = 0
  const duration = 900
  const steps = duration / 16
  const increment = target / steps

  function update() {
    current = Math.min(current + increment, target)
    el.textContent = Math.round(current)
    if (current < target) {
      requestAnimationFrame(update)
    }
  }

  el.classList.add('score-animate-in')
  requestAnimationFrame(update)
}

function getThemeColor() {
  return (
    getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#00acc1'
  )
}

function renderStats(data, container) {
  const scores = {
    abstraction: data.score_abstraction || 0,
    parallelism: data.score_parallelism || 0,
    synchronization: data.score_synchronization || 0,
    logicalThinking: data.score_logical_thinking || 0,
    flowControl: data.score_flow_control || 0,
    userInteractivity: data.score_user_interactivity || 0,
    dataRepresentation: data.score_data_representation || 0,
  }

  const total = Object.values(scores).reduce((a, b) => a + b, 0)
  const maxScore = Math.max(...Object.values(scores), 1)

  // Animate total number
  const totalEl = container.querySelector('#code-stats-total-number')
  animateNumber(totalEl, total)

  // Show random SVG animation
  const animEl = container.querySelector('#code-stats-animation')
  const color = getThemeColor()
  const randomIndex = Math.floor(Math.random() * SCORE_ANIMATIONS.length)
  animEl.innerHTML = SCORE_ANIMATIONS[randomIndex](color)

  // Build category rows
  const tableEl = container.querySelector('#code-stats-detail-table')
  tableEl.innerHTML = ''

  for (const [key, value] of Object.entries(scores)) {
    // Build the data-trans attribute name from camelCase key
    const transAttrKey = 'trans' + key.charAt(0).toUpperCase() + key.slice(1)
    const label = container.dataset[transAttrKey] || key
    const icon = CATEGORY_ICONS[key] || 'star'
    const percentage = Math.round((value / maxScore) * 100)

    const row = document.createElement('div')
    row.className = 'code-stats-row'
    row.innerHTML =
      '<div class="code-stats-category">' +
      '<i class="material-icons">' +
      escapeHtml(icon) +
      '</i>' +
      '<span>' +
      escapeHtml(label) +
      '</span>' +
      '</div>' +
      '<div class="code-stats-bar">' +
      '<div class="code-stats-bar-fill"></div>' +
      '</div>' +
      '<div class="code-stats-value">' +
      escapeHtml(String(value)) +
      '</div>'
    tableEl.appendChild(row)

    // Trigger bar animation after a frame
    requestAnimationFrame(() => {
      const fill = row.querySelector('.code-stats-bar-fill')
      if (fill) {
        fill.style.width = percentage + '%'
      }
    })
  }
}

async function loadStats(url, container, panel) {
  // Show loading state
  panel.innerHTML =
    '<div class="code-stats-loading">' +
    '<i class="material-icons">hourglass_empty</i>' +
    '<span>' +
    escapeHtml(container.dataset.transLoading || 'Loading...') +
    '</span>' +
    '</div>'

  try {
    const response = await fetch(url)
    if (!response.ok) {
      throw new Error('HTTP ' + response.status)
    }
    const data = await response.json()

    // Render the full panel content
    panel.innerHTML =
      '<div class="code-stats-score-area">' +
      '<div class="code-stats-animation" id="code-stats-animation"></div>' +
      '<div class="code-stats-total">' +
      '<span class="score-number" id="code-stats-total-number">0</span>' +
      '<span class="score-label">' +
      escapeHtml(container.dataset.transTotalPoints || 'Total Points') +
      '</span>' +
      '</div>' +
      '</div>' +
      '<div class="code-stats-table" id="code-stats-detail-table"></div>'

    renderStats(data, container)
  } catch (e) {
    console.error('Failed to load code statistics', e)
    panel.innerHTML =
      '<div class="code-stats-error">' +
      '<i class="material-icons">warning</i> ' +
      escapeHtml(container.dataset.transError || 'Could not load code statistics.') +
      '</div>'
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('code-statistics-inline')
  if (!container) {
    return
  }

  // Show the section (it starts hidden with d-none)
  container.classList.remove('d-none')

  const toggleBtn = document.getElementById('code-stats-toggle')
  const panel = document.getElementById('code-stats-panel')
  const statsUrl = container.dataset.statsUrl
  let loaded = false

  toggleBtn.addEventListener('click', async () => {
    const isHidden = panel.classList.contains('d-none')

    if (isHidden) {
      panel.classList.remove('d-none')
      toggleBtn.setAttribute('aria-expanded', 'true')
      if (!loaded && statsUrl) {
        loaded = true
        await loadStats(statsUrl, container, panel)
      }
    } else {
      panel.classList.add('d-none')
      toggleBtn.setAttribute('aria-expanded', 'false')
    }
  })
})
