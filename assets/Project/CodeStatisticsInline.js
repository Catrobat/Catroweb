import { ApiFetch } from '../Api/ApiHelper'
import { escapeHtml } from '../Components/HtmlEscape'
import './CodeStatisticsInline.scss'
import pandaSvgUrl from './animations/panda.svg'
import penguinSvgUrl from './animations/penguin.svg'

const CHARACTERS = [
  { url: pandaSvgUrl, className: 'code-stats-animation--panda' },
  { url: penguinSvgUrl, className: 'code-stats-animation--penguin' },
]

const MAX_LEVELS = 3

const SCORE_KEYS = [
  { key: 'score_abstraction', transAttr: 'transAbstraction', icon: 'extension' },
  { key: 'score_parallelism', transAttr: 'transParallelism', icon: 'call_split' },
  { key: 'score_logical_thinking', transAttr: 'transLogicalThinking', icon: 'psychology' },
  { key: 'score_synchronization', transAttr: 'transSynchronization', icon: 'sync' },
  { key: 'score_flow_control', transAttr: 'transFlowControl', icon: 'loop' },
  { key: 'score_user_interactivity', transAttr: 'transUserInteractivity', icon: 'touch_app' },
  { key: 'score_data_representation', transAttr: 'transDataRepresentation', icon: 'storage' },
]

function normalizeScoreValue(value) {
  const numericValue = Number.parseInt(String(value ?? 0), 10)

  return Number.isNaN(numericValue) ? 0 : numericValue
}

/**
 * Map a 0–6 rubric score to a level count (0–3).
 * basic = 1 pt, developing = 2 pts, proficiency = 3 pts.
 * Thresholds: >= 1 -> level 1, >= 3 -> level 2, >= 6 -> level 3.
 */
function scoreToLevelCount(score) {
  if (score >= 6) return 3
  if (score >= 3) return 2
  if (score >= 1) return 1
  return 0
}

function animateNumber(el, from, to, duration) {
  if (from === to) {
    el.textContent = String(to)
    return Promise.resolve()
  }

  return new Promise((resolve) => {
    let current = from
    const steps = duration / 16
    const increment = (to - from) / steps

    function update() {
      current += increment
      if ((increment > 0 && current >= to) || (increment < 0 && current <= to)) {
        el.textContent = String(to)
        resolve()
        return
      }
      el.textContent = String(Math.round(current))
      requestAnimationFrame(update)
    }

    el.classList.add('score-animate-in')
    requestAnimationFrame(update)
  })
}

async function loadCharacterSvg(animEl, character) {
  try {
    const response = await fetch(character.url)
    if (!response.ok) return
    const svgText = await response.text()
    animEl.innerHTML = svgText
    animEl.classList.add(character.className)
  } catch (e) {
    console.error('Failed to load character SVG', e)
  }
}

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms))
}

function buildScoreArea(container) {
  return (
    '<div class="code-stats-score-area">' +
    '<div class="code-stats-animation" id="code-stats-animation"></div>' +
    '<div class="code-stats-total">' +
    '<span class="score-label">' +
    escapeHtml(container.dataset.transYourScore || 'Your score:') +
    '</span>' +
    '<span class="score-number" id="code-stats-total-number">0</span>' +
    '</div>' +
    '</div>'
  )
}

function buildTable(container) {
  const levelLabel = escapeHtml(container.dataset.transLevel || 'Level')
  return (
    '<div class="code-stats-table">' +
    '<div class="code-stats-table-header">' +
    '<span></span>' +
    '<span>' +
    levelLabel +
    '</span>' +
    '</div>' +
    '<div id="code-stats-detail-table"></div>' +
    '</div>'
  )
}

function createRow(label, score, icon) {
  const levelCount = scoreToLevelCount(score)
  const row = document.createElement('div')
  row.className = 'code-stats-row code-stats-row--level-' + levelCount

  let segmentsHtml = ''
  for (let i = 0; i < MAX_LEVELS; i++) {
    const filled = i < levelCount
    segmentsHtml +=
      '<div class="code-stats-segment' +
      (filled ? ' code-stats-segment--filled' : '') +
      '">' +
      '<div class="code-stats-segment__fill"></div>' +
      '</div>'
  }

  row.innerHTML =
    '<div class="code-stats-category">' +
    '<i class="material-icons code-stats-icon">' +
    escapeHtml(icon) +
    '</i>' +
    '<span class="code-stats-label">' +
    escapeHtml(label) +
    '</span>' +
    '</div>' +
    '<div class="code-stats-level">' +
    '<div class="code-stats-bar">' +
    segmentsHtml +
    '</div>' +
    '<span class="code-stats-fraction">' +
    levelCount +
    '/' +
    MAX_LEVELS +
    '</span>' +
    '</div>'

  return row
}

function createBonusRow(label, score) {
  const row = document.createElement('div')
  row.className = 'code-stats-row code-stats-row--bonus'
  row.innerHTML =
    '<div class="code-stats-category">' +
    '<i class="material-icons code-stats-icon">star</i>' +
    '<span class="code-stats-label">' +
    escapeHtml(label) +
    '</span>' +
    '</div>' +
    '<div class="code-stats-level">' +
    '<span class="code-stats-bonus-value">+' +
    escapeHtml(String(score)) +
    '</span>' +
    '</div>'
  return row
}

async function runAnimation(data, container) {
  const scores = SCORE_KEYS.map((s) => ({
    label: container.dataset[s.transAttr] || s.key,
    value: normalizeScoreValue(data[s.key]),
    icon: s.icon,
  }))

  const bonusScore = normalizeScoreValue(data.score_bonus)
  const baseTotal = normalizeScoreValue(data.score_total)
  const scoreBeforeBonus = baseTotal - bonusScore

  // Animate total score to base (before bonus)
  const totalEl = container.querySelector('#code-stats-total-number')
  const scorePromise = animateNumber(
    totalEl,
    0,
    bonusScore > 0 ? scoreBeforeBonus : baseTotal,
    1000,
  )

  // Load random character
  const animEl = container.querySelector('#code-stats-animation')
  const randomIndex = Math.floor(Math.random() * CHARACTERS.length)
  loadCharacterSvg(animEl, CHARACTERS[randomIndex])

  // Build category rows (without bonus — bonus gets its own phase)
  const tableEl = container.querySelector('#code-stats-detail-table')
  tableEl.innerHTML = ''

  const categoryRows = []
  for (let i = 0; i < scores.length; i++) {
    categoryRows.push(createRow(scores[i].label, scores[i].value, scores[i].icon))
  }

  // Stagger entrance for category rows
  for (let i = 0; i < categoryRows.length; i++) {
    categoryRows[i].style.animationDelay = i * 80 + 'ms'
    categoryRows[i].classList.add('code-stats-row-enter')
    tableEl.appendChild(categoryRows[i])
  }

  // Wait for row entrance animations to settle
  await sleep(categoryRows.length * 80 + 350)

  // Animate bar segments filling in (cascade across rows and segments)
  const rowEls = tableEl.querySelectorAll('.code-stats-row')
  for (let i = 0; i < rowEls.length; i++) {
    const fills = rowEls[i].querySelectorAll(
      '.code-stats-segment--filled .code-stats-segment__fill',
    )
    fills.forEach((fill, j) => {
      setTimeout(
        () => {
          fill.classList.add('code-stats-fill--active')
        },
        i * 120 + j * 180,
      )
    })
  }

  // Wait for score count-up and bar animations to finish
  await scorePromise
  await sleep(800)

  // --- Bonus phase: star burst + score bump ---
  if (bonusScore > 0) {
    // Show the star burst
    const scoreArea = container.querySelector('.code-stats-score-area')
    const starEl = document.createElement('div')
    starEl.className = 'code-stats-star'
    starEl.innerHTML =
      '<svg viewBox="0 0 24 24" class="star-svg">' +
      '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.27 5.82 22 7 14.14l-5-4.87 6.91-1.01z"/>' +
      '</svg>'
    scoreArea.appendChild(starEl)

    await sleep(600)

    // Bump score from base to final total
    totalEl.classList.add('score-bump')
    await animateNumber(totalEl, scoreBeforeBonus, baseTotal, 600)

    await sleep(200)
    totalEl.classList.remove('score-bump')

    // Add bonus row to table with entrance animation
    const bonusRow = createBonusRow(container.dataset.transBonus || 'Bonus', bonusScore)
    bonusRow.classList.add('code-stats-row-enter')
    tableEl.appendChild(bonusRow)

    await sleep(400)
  }

  // Final celebration pulse
  totalEl.classList.add('score-celebration')
}

async function loadStats(url, container, panel) {
  panel.innerHTML =
    '<div class="code-stats-loading">' +
    '<div class="code-stats-loading-spinner"></div>' +
    '<span>' +
    escapeHtml(container.dataset.transLoading || 'Loading...') +
    '</span>' +
    '</div>'

  try {
    const data = await new ApiFetch(url, 'GET', undefined, 'json').run()

    panel.innerHTML = buildScoreArea(container) + buildTable(container)

    await runAnimation(data, container)
    return true
  } catch (e) {
    console.error('Failed to load code statistics', e)
    panel.innerHTML =
      '<div class="code-stats-error">' +
      '<i class="material-icons">warning</i> ' +
      escapeHtml(container.dataset.transError || 'Could not load code statistics.') +
      '</div>'
    return false
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('code-statistics-inline')
  if (!container) return

  container.classList.remove('d-none')

  const toggleBtn = document.getElementById('code-stats-toggle')
  const panel = document.getElementById('code-stats-panel')
  const chevron = toggleBtn.querySelector('.project-section-toggle__chevron')
  const statsUrl = container.dataset.statsUrl
  let loaded = false

  toggleBtn.addEventListener('click', async () => {
    const isHidden = panel.classList.contains('d-none')

    if (isHidden) {
      panel.classList.remove('d-none')
      toggleBtn.setAttribute('aria-expanded', 'true')
      chevron.textContent = 'expand_less'
      if (!loaded && statsUrl) {
        loaded = await loadStats(statsUrl, container, panel)
      }
    } else {
      panel.classList.add('d-none')
      toggleBtn.setAttribute('aria-expanded', 'false')
      chevron.textContent = 'expand_more'
    }
  })
})
