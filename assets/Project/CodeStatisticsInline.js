import { ApiFetch } from '../Api/ApiHelper'
import { escapeHtml } from '../Components/HtmlEscape'
import './CodeStatisticsInline.scss'
import pandaSvgUrl from './animations/panda.svg'
import penguinSvgUrl from './animations/penguin.svg'

const CHARACTERS = [
  { url: pandaSvgUrl, className: 'code-stats-animation--panda' },
  { url: penguinSvgUrl, className: 'code-stats-animation--penguin' },
]

const SCORE_KEYS = [
  { key: 'score_abstraction', transAttr: 'transAbstraction' },
  { key: 'score_parallelism', transAttr: 'transParallelism' },
  { key: 'score_logical_thinking', transAttr: 'transLogicalThinking' },
  { key: 'score_synchronization', transAttr: 'transSynchronization' },
  { key: 'score_flow_control', transAttr: 'transFlowControl' },
  { key: 'score_user_interactivity', transAttr: 'transUserInteractivity' },
  { key: 'score_data_representation', transAttr: 'transDataRepresentation' },
]

const BONUS_POINTS = 5

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
  return (
    '<div class="code-stats-table">' +
    '<div class="code-stats-table-header">' +
    '<span></span>' +
    '<span>' +
    escapeHtml(container.dataset.transPoints || 'points') +
    '</span>' +
    '</div>' +
    '<div id="code-stats-detail-table"></div>' +
    '</div>'
  )
}

function createRow(label, value, extraClass) {
  const row = document.createElement('div')
  row.className = 'code-stats-row' + (extraClass ? ' ' + extraClass : '')
  row.innerHTML =
    '<div class="code-stats-category">' +
    escapeHtml(label) +
    '</div>' +
    '<div class="code-stats-value">' +
    escapeHtml(String(value)) +
    '</div>'
  return row
}

async function runAnimation(data, container) {
  const scores = SCORE_KEYS.map((s) => ({
    label: container.dataset[s.transAttr] || s.key,
    value: data[s.key] || 0,
  }))

  const baseTotal = scores.reduce((sum, s) => sum + s.value, 0)
  const finalTotal = baseTotal + BONUS_POINTS

  // Animate total score (base only first)
  const totalEl = container.querySelector('#code-stats-total-number')
  const scorePromise = animateNumber(totalEl, 0, baseTotal, 900)

  // Load random character (runs in parallel with score animation)
  const animEl = container.querySelector('#code-stats-animation')
  const randomIndex = Math.floor(Math.random() * CHARACTERS.length)
  loadCharacterSvg(animEl, CHARACTERS[randomIndex])

  // Build category rows with staggered entrance
  const tableEl = container.querySelector('#code-stats-detail-table')
  tableEl.innerHTML = ''

  for (let i = 0; i < scores.length; i++) {
    const row = createRow(scores[i].label, scores[i].value)
    row.style.animationDelay = i * 60 + 'ms'
    row.classList.add('code-stats-row-enter')
    tableEl.appendChild(row)
  }

  // Wait for score animation + row stagger to settle
  await scorePromise
  await sleep(500)

  // --- Bonus points phase ---
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

  // Animate score from base to final
  totalEl.classList.add('score-bump')
  await animateNumber(totalEl, baseTotal, finalTotal, 600)

  await sleep(200)
  totalEl.classList.remove('score-bump')

  // Add bonus row to table
  const bonusLabel = container.dataset.transBonus || 'Bonus'
  const bonusRow = createRow(bonusLabel, '+' + BONUS_POINTS, 'code-stats-row-bonus')
  bonusRow.classList.add('code-stats-row-enter')
  tableEl.appendChild(bonusRow)
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
  const chevron = toggleBtn.querySelector('.code-stats-chevron')
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
