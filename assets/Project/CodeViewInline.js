import { ApiFetch } from '../Api/ApiHelper'
import { escapeHtml } from '../Components/HtmlEscape'
import './CodeViewInline.scss'

const COLORS = {
  event: '#CF5717',
  control: '#E89025',
  motion: '#408AC5',
  sound: '#9553AF',
  looks: '#6B9C49',
  data: '#D32F2F',
  pen: '#2E7D32',
  device: '#FFC107',
  user_defined: '#03A9F4',
  lego: '#FFEB3B',
  embroidery: '#E91E63',
  extension: '#03A9F4',
  unknown: '#9E9E9E',
}

const BRANCH_LABELS = {
  if_branch: 'if',
  else_branch: 'else',
  loop_body: 'repeat',
}

function categoryClass(category) {
  return COLORS[category] ? category : 'unknown'
}

function renderBrick(brick) {
  const cat = categoryClass(brick.category || 'unknown')
  const commented = brick.commented_out ? ' cv-block--commented' : ''
  const text = escapeHtml(brick.display_text || brick.type || '')

  let childrenHtml = ''
  if (brick.children && typeof brick.children === 'object') {
    const branches = Object.keys(brick.children)
    for (let i = 0; i < branches.length; i++) {
      const key = branches[i]
      const bricks = brick.children[key]
      if (!Array.isArray(bricks)) continue

      childrenHtml += '<div class="cv-block__children">'
      if (branches.length > 1) {
        const label = BRANCH_LABELS[key] || key
        childrenHtml += '<div class="cv-branch-label">' + escapeHtml(label) + '</div>'
      }
      for (let j = 0; j < bricks.length; j++) {
        childrenHtml += renderBrick(bricks[j])
      }
      childrenHtml += '</div>'
    }
  }

  return '<div class="cv-block cv-block--' + cat + commented + '">' + text + childrenHtml + '</div>'
}

function renderScript(script) {
  const cat = categoryClass(script.category || 'event')
  const commented = script.commented_out ? ' cv-block--commented' : ''
  const text = escapeHtml(script.display_text || script.type || '')

  let bricksHtml = ''
  if (Array.isArray(script.bricks) && script.bricks.length > 0) {
    bricksHtml += '<div class="cv-block__children">'
    for (let i = 0; i < script.bricks.length; i++) {
      bricksHtml += renderBrick(script.bricks[i])
    }
    bricksHtml += '</div>'
  }

  return (
    '<div class="cv-block cv-block--' +
    cat +
    commented +
    '">' +
    '<strong>' +
    text +
    '</strong>' +
    bricksHtml +
    '</div>'
  )
}

function renderScriptsTab(scripts) {
  if (!Array.isArray(scripts) || scripts.length === 0) {
    return '<div class="cv-status"><span>--</span></div>'
  }

  let html = ''
  for (let i = 0; i < scripts.length; i++) {
    html += renderScript(scripts[i])
  }
  return html
}

function renderAssetTab(assets, renderItem) {
  if (!Array.isArray(assets) || assets.length === 0) {
    return '<div class="cv-status"><span>--</span></div>'
  }

  let html = '<ul class="cv-asset-list">'
  for (let i = 0; i < assets.length; i++) {
    html += renderItem(assets[i])
  }
  html += '</ul>'
  return html
}

function renderLookItem(look) {
  const name = escapeHtml(look.name || '')
  const url = look.url || ''
  const thumb = url
    ? '<img class="cv-asset-thumb" src="' + escapeHtml(url) + '" alt="' + name + '" loading="lazy">'
    : '<i class="material-icons">image</i>'
  return '<li class="cv-asset-item">' + thumb + '<span>' + name + '</span></li>'
}

function renderSoundItem(sound) {
  const name = escapeHtml(sound.name || '')
  const url = sound.url || ''
  const player = url
    ? '<audio class="cv-asset-audio" preload="none" controls src="' + escapeHtml(url) + '"></audio>'
    : ''
  return (
    '<li class="cv-asset-item">' +
    '<i class="material-icons">music_note</i>' +
    '<span>' +
    name +
    '</span>' +
    player +
    '</li>'
  )
}

function arrayLen(arr) {
  return Array.isArray(arr) ? arr.length : 0
}

function getSpriteThumbUrl(obj) {
  if (Array.isArray(obj.looks) && obj.looks.length > 0 && obj.looks[0].url) {
    return obj.looks[0].url
  }
  return ''
}

function renderSpriteCard(obj, expanded, container) {
  const name = escapeHtml(obj.name || '')
  const scriptCount = arrayLen(obj.scripts)
  const looksCount = arrayLen(obj.looks)
  const soundsCount = arrayLen(obj.sounds)
  const scriptsLabel = escapeHtml(container.dataset.transScripts || 'Scripts')
  const looksLabel = looksCount > 0 ? escapeHtml(container.dataset.transLooks || 'Looks') : ''
  const soundsLabel = soundsCount > 0 ? escapeHtml(container.dataset.transSounds || 'Sounds') : ''

  const parts = [scriptCount + ' ' + scriptsLabel]
  if (looksCount > 0) parts.push(looksCount + ' ' + looksLabel)
  if (soundsCount > 0) parts.push(soundsCount + ' ' + soundsLabel)
  const badge = parts.join(', ')

  const expandedAttr = expanded ? 'true' : 'false'
  const bodyClass = expanded ? '' : ' d-none'
  const hasTabs = looksCount > 0 || soundsCount > 0

  const thumbUrl = getSpriteThumbUrl(obj)
  const thumbHtml = thumbUrl
    ? '<img class="cv-sprite__thumb" src="' + escapeHtml(thumbUrl) + '" alt="" loading="lazy">'
    : '<i class="material-icons cv-sprite__icon">category</i>'

  let html =
    '<div class="cv-sprite">' +
    '<div class="cv-sprite__header" role="button" tabindex="0" aria-expanded="' +
    expandedAttr +
    '">' +
    thumbHtml +
    '<span class="cv-sprite__name">' +
    name +
    '</span>' +
    '<span class="cv-sprite__badge">' +
    escapeHtml(badge) +
    '</span>' +
    '<i class="material-icons cv-sprite__chevron">expand_more</i>' +
    '</div>' +
    '<div class="cv-sprite__body' +
    bodyClass +
    '">'

  if (hasTabs) {
    html +=
      '<div class="cv-tabs">' +
      '<button type="button" class="cv-tab cv-tab--active" data-tab="scripts">' +
      scriptsLabel +
      '</button>'
    if (looksCount > 0) {
      html += '<button type="button" class="cv-tab" data-tab="looks">' + looksLabel + '</button>'
    }
    if (soundsCount > 0) {
      html += '<button type="button" class="cv-tab" data-tab="sounds">' + soundsLabel + '</button>'
    }
    html += '</div>'

    html +=
      '<div class="cv-tab-panel cv-tab-panel--active" data-tab-panel="scripts">' +
      renderScriptsTab(obj.scripts) +
      '</div>'
    if (looksCount > 0) {
      html +=
        '<div class="cv-tab-panel" data-tab-panel="looks">' +
        renderAssetTab(obj.looks, renderLookItem) +
        '</div>'
    }
    if (soundsCount > 0) {
      html +=
        '<div class="cv-tab-panel" data-tab-panel="sounds">' +
        renderAssetTab(obj.sounds, renderSoundItem) +
        '</div>'
    }
  } else {
    html += renderScriptsTab(obj.scripts)
  }

  html += '</div>'

  // Render group children (nested sprites)
  if (obj.is_group && Array.isArray(obj.children) && obj.children.length > 0) {
    html += '<div class="cv-group">'
    for (let i = 0; i < obj.children.length; i++) {
      html += renderSpriteCard(obj.children[i], false, container)
    }
    html += '</div>'
  }

  html += '</div>'
  return html
}

function renderScene(scene, showHeader, expanded, container) {
  let html = '<div class="cv-scene">'

  if (showHeader) {
    const expandedAttr = expanded ? 'true' : 'false'
    html +=
      '<div class="cv-scene__header" role="button" tabindex="0" aria-expanded="' +
      expandedAttr +
      '">' +
      '<i class="material-icons">expand_more</i>' +
      '<span>' +
      escapeHtml(scene.name || '') +
      '</span>' +
      '</div>'
  }

  const bodyHidden = showHeader && !expanded ? ' d-none' : ''
  html += '<div class="cv-scene__body' + bodyHidden + '">'

  const objects = scene.objects || []
  for (let i = 0; i < objects.length; i++) {
    html += renderSpriteCard(objects[i], i === 0, container)
  }

  html += '</div></div>'
  return html
}

function renderCodeView(data, container) {
  const scenes = data.scenes || []
  if (scenes.length === 0) {
    return (
      '<div class="cv-status">' +
      '<i class="material-icons">info</i>' +
      '<span>' +
      escapeHtml(container.dataset.transEmpty || 'This project has no code.') +
      '</span>' +
      '</div>'
    )
  }

  const showSceneHeaders = scenes.length > 1

  // Expand/Collapse All toolbar
  let html =
    '<div class="cv-toolbar">' +
    '<button type="button" class="cv-toolbar__btn" data-cv-expand="all">' +
    '<i class="material-icons">unfold_more</i>' +
    '</button>' +
    '<button type="button" class="cv-toolbar__btn" data-cv-expand="none">' +
    '<i class="material-icons">unfold_less</i>' +
    '</button>' +
    '</div>'

  for (let i = 0; i < scenes.length; i++) {
    html += renderScene(scenes[i], showSceneHeaders, i === 0, container)
  }
  return html
}

function handleToggle(e) {
  // Expand/Collapse All
  const expandBtn = e.target.closest('[data-cv-expand]')
  if (expandBtn) {
    const panel = expandBtn.closest('.code-view-panel')
    if (!panel) return
    const expandAll = expandBtn.dataset.cvExpand === 'all'
    panel.querySelectorAll('.cv-sprite__header').forEach((h) => {
      const body = h.nextElementSibling
      if (!body) return
      h.setAttribute('aria-expanded', String(expandAll))
      body.classList.toggle('d-none', !expandAll)
    })
    panel.querySelectorAll('.cv-scene__header').forEach((h) => {
      const body = h.nextElementSibling
      if (!body) return
      h.setAttribute('aria-expanded', String(expandAll))
      body.classList.toggle('d-none', !expandAll)
    })
    return
  }

  // Tab switching
  const tab = e.target.closest('.cv-tab')
  if (tab) {
    const tabName = tab.dataset.tab
    const sprite = tab.closest('.cv-sprite__body')
    if (!sprite || !tabName) return

    sprite.querySelectorAll('.cv-tab').forEach((t) => t.classList.remove('cv-tab--active'))
    sprite
      .querySelectorAll('.cv-tab-panel')
      .forEach((p) => p.classList.remove('cv-tab-panel--active'))
    tab.classList.add('cv-tab--active')
    const targetPanel = sprite.querySelector('[data-tab-panel="' + tabName + '"]')
    if (targetPanel) targetPanel.classList.add('cv-tab-panel--active')
    return
  }

  // Sprite header toggle
  const spriteHeader = e.target.closest('.cv-sprite__header')
  if (spriteHeader) {
    const body = spriteHeader.nextElementSibling
    if (!body) return
    const isExpanded = spriteHeader.getAttribute('aria-expanded') === 'true'
    spriteHeader.setAttribute('aria-expanded', String(!isExpanded))
    body.classList.toggle('d-none')
    return
  }

  // Scene header toggle
  const sceneHeader = e.target.closest('.cv-scene__header')
  if (sceneHeader) {
    const body = sceneHeader.nextElementSibling
    if (!body) return
    const isExpanded = sceneHeader.getAttribute('aria-expanded') === 'true'
    sceneHeader.setAttribute('aria-expanded', String(!isExpanded))
    body.classList.toggle('d-none')
    return
  }

  // Retry button
  const retryBtn = e.target.closest('.cv-retry-btn')
  if (retryBtn) {
    const url = retryBtn.dataset.url
    if (url) {
      loadCodeView(url, document.getElementById('code-view-inline'), e.currentTarget)
    }
  }
}

function bindToggleEvents(panel) {
  panel.addEventListener('click', handleToggle)
  panel.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      const header = e.target.closest('.cv-sprite__header, .cv-scene__header')
      if (header) {
        e.preventDefault()
        handleToggle(e)
      }
    }
  })
}

async function loadCodeView(url, container, panel) {
  panel.innerHTML =
    '<div class="cv-status">' +
    '<div class="cv-spinner"></div>' +
    '<span>' +
    escapeHtml(container.dataset.transLoading || 'Loading code view...') +
    '</span>' +
    '</div>'

  try {
    const data = await new ApiFetch(url, 'GET', undefined, 'json').run()
    panel.innerHTML = renderCodeView(data, container)
    bindToggleEvents(panel)
    return true
  } catch (e) {
    console.error('Failed to load code view', e)
    panel.innerHTML =
      '<div class="cv-status">' +
      '<i class="material-icons">warning</i>' +
      '<span>' +
      escapeHtml(container.dataset.transError || 'Could not load code view.') +
      '</span>' +
      '<button type="button" class="cv-retry-btn" data-url="' +
      escapeHtml(url) +
      '">' +
      escapeHtml(container.dataset.transRetry || 'Try again') +
      '</button>' +
      '</div>'
    bindToggleEvents(panel)
    return false
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('code-view-inline')
  if (!container) return

  const toggleBtn = document.getElementById('code-view-toggle')
  const panel = document.getElementById('code-view-panel')
  const chevron = toggleBtn.querySelector('.project-section-toggle__chevron')
  const codeUrl = container.dataset.codeUrl
  let loaded = false

  toggleBtn.addEventListener('click', async () => {
    const isHidden = panel.classList.contains('d-none')

    if (isHidden) {
      panel.classList.remove('d-none')
      toggleBtn.setAttribute('aria-expanded', 'true')
      chevron.textContent = 'expand_less'
      if (!loaded && codeUrl) {
        loaded = await loadCodeView(codeUrl, container, panel)
      }
    } else {
      panel.classList.add('d-none')
      toggleBtn.setAttribute('aria-expanded', 'false')
      chevron.textContent = 'expand_more'
    }
  })
})
