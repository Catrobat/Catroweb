// The start screen, from the first moment on: the project's picture and name, what the player is doing while it
// loads, then the play button (browsers only play sound after a click). Runs before app.js (build.sh puts it into
// index.html) and starts downloading code.xml while the engine loads. The player drives it through
// window.catrobatStart (StartScreen, ProjectLoader). ?autostart (tests) and the demo have none.
;(() => {
  const id = location.hash.slice(1)
  if (!/^[A-Za-z0-9-]+$/.test(id) || id === 'check' || location.search.includes('autostart')) {
    return
  }
  // As ProjectLoader.assetUrl: on play.<domain> the project lives on share.<domain>, elsewhere on the same host.
  const share = location.host.startsWith('play.')
    ? `${location.protocol}//share.${location.host.slice('play.'.length)}`
    : ''
  const asset = (path) => `${share}/project-files/${id}/assets/${path}`

  const state = {
    phase: 'player',
    xmlBytes: 0,
    done: 0,
    total: -1,
    bytes: 0,
    clicked: false,
    failed: false,
  }
  let landscape = false

  const style = document.createElement('style')
  style.textContent = `
#start-screen { position: fixed; inset: 0; z-index: 10; display: grid; place-items: center; overflow: hidden;
  background: #0b1d22; color: #fff; font: 16px/1.4 system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; }
#start-screen .backdrop { position: absolute; inset: -48px; background: center / cover no-repeat;
  filter: blur(28px) brightness(.45) saturate(1.3); opacity: 0; transition: opacity .6s; }
#start-screen .card { position: relative; display: flex; flex-direction: column; align-items: center; gap: 18px;
  width: min(100% - 32px, 520px); text-align: center; }
#start-screen .picture { display: block; max-width: 100%; max-height: 40vh; max-height: 40dvh; border-radius: 16px;
  box-shadow: 0 12px 48px rgba(0,0,0,.6); opacity: 0; transition: opacity .6s; }
#start-screen .picture.shown, #start-screen .backdrop.shown { opacity: 1; }
#start-screen .picture:not([src]) { display: none; }
#start-screen h1 { margin: 0; font-size: clamp(20px, 5.5vw, 30px); font-weight: 700; overflow-wrap: anywhere;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
  text-shadow: 0 2px 12px rgba(0,0,0,.6); }
#start-screen .play { position: relative; width: 96px; height: 96px; border: 0; border-radius: 50%; padding: 0;
  display: grid; place-items: center; cursor: pointer; color: #fff;
  background: linear-gradient(145deg, #1fc8d8, #007f8f); box-shadow: 0 8px 32px rgba(0,180,200,.45);
  transition: transform .15s, opacity .3s, box-shadow .15s; }
#start-screen .play svg { width: 40px; height: 40px; margin-left: 6px; }
#start-screen .play:hover:not(:disabled) { transform: scale(1.07); box-shadow: 0 10px 40px rgba(0,200,220,.65); }
#start-screen .play:focus-visible { outline: 3px solid #fff; outline-offset: 4px; }
#start-screen .play:disabled { opacity: .45; cursor: progress; }
#start-screen .play.ready { animation: catrobat-pulse 2s ease-out infinite; }
#start-screen .play.clicked { animation: none; cursor: progress; }
#start-screen .play.clicked svg { opacity: 0; }
#start-screen .play.clicked::after { content: ''; position: absolute; inset: 26px; border-radius: 50%;
  border: 4px solid rgba(255,255,255,.35); border-top-color: #fff; animation: catrobat-spin .9s linear infinite; }
#start-screen .status { min-height: 1.4em; font-size: 15px; opacity: .9; text-shadow: 0 1px 6px rgba(0,0,0,.6); }
#start-screen .bar { width: min(280px, 70vw); height: 4px; border-radius: 2px; background: rgba(255,255,255,.2);
  overflow: hidden; }
#start-screen .bar div { height: 100%; width: 0; border-radius: 2px; background: #3fd8e6; transition: width .2s; }
#start-screen .bar.unknown div { width: 30%; animation: catrobat-slide 1.2s ease-in-out infinite; }
#start-screen .bar.hidden { visibility: hidden; }
#start-screen a { color: #7fe9f2; }
#start-screen .needs { max-height: 30vh; max-height: 30dvh; overflow-y: auto; font-size: 14px; text-align: start;
  text-shadow: 0 1px 6px rgba(0,0,0,.6); }
#start-screen .needs .missing { margin-top: 8px; padding: 10px 14px; border-radius: 12px; background: rgba(0,0,0,.4); }
#start-screen .needs ul { margin: 6px 0; padding-inline-start: 20px; }
#start-screen p { margin: 0; }
#fullscreen-button { position: fixed; z-index: 10; top: max(12px, env(safe-area-inset-top));
  right: max(12px, env(safe-area-inset-right)); width: 44px; height: 44px; padding: 10px; border: 0;
  border-radius: 12px; background: rgba(0,0,0,.5); color: #fff; cursor: pointer;
  animation: catrobat-fade .4s 3s forwards; }
#fullscreen-button:hover, #fullscreen-button:focus-visible { animation: none; opacity: 1; }
#fullscreen-button:focus-visible { outline: 3px solid #fff; outline-offset: 2px; }
#fullscreen-button[hidden] { display: none; }
@keyframes catrobat-pulse { 0% { box-shadow: 0 0 0 0 rgba(63,216,230,.6), 0 8px 32px rgba(0,180,200,.45); }
  70%, 100% { box-shadow: 0 0 0 22px rgba(63,216,230,0), 0 8px 32px rgba(0,180,200,.45); } }
@keyframes catrobat-spin { to { transform: rotate(360deg); } }
@keyframes catrobat-slide { from { transform: translateX(-100%); } to { transform: translateX(340%); } }
@keyframes catrobat-fade { to { opacity: .35; } }
@media (prefers-reduced-motion: reduce) { #start-screen .play.ready, #start-screen .bar.unknown div { animation: none; } }
`
  document.head.append(style)

  const overlay = document.createElement('div')
  overlay.id = 'start-screen'
  overlay.setAttribute('aria-busy', 'true')
  overlay.innerHTML = `<div class="backdrop"></div><div class="card">
<img class="picture" alt=""><h1></h1><div class="needs" dir="auto" hidden></div>
<button class="play" type="button" aria-label="Play" disabled>
<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M7 4.5v15a1 1 0 0 0 1.5.86l12-7.5a1 1 0 0 0 0-1.72l-12-7.5A1 1 0 0 0 7 4.5z"/></svg>
</button><div class="status" role="status" aria-live="polite"></div><div class="bar"><div></div></div></div>`
  const backdrop = overlay.querySelector('.backdrop')
  const picture = overlay.querySelector('.picture')
  const name = overlay.querySelector('h1')
  const button = overlay.querySelector('.play')
  const status = overlay.querySelector('.status')
  const bar = overlay.querySelector('.bar')
  const needs = overlay.querySelector('.needs')
  document.body.append(overlay)

  // Catroid's own words in the browser's language (scripts/export_strings.py --start), English until they are in
  // and where Catroid has none. text() is also the player's (catroid/BrowserHandoff).
  const strings = fetch('strings/locales.json')
    .then((response) => response.json())
    .then((locales) => {
      const alias = { zh: 'zh-cn', nb: 'no' }
      const wanted = (navigator.languages || [navigator.language]).flatMap((language) => {
        const tag = language.toLowerCase()
        const base = tag.split('-')[0]
        return [tag, base, alias[base]]
      })
      const tag = wanted.find((candidate) => locales.includes(candidate)) || 'en'
      return fetch(`strings/${tag}.json`).then(async (response) => ({
        tag,
        texts: await response.json(),
      }))
    })
    .catch(() => null)
  let texts = {}
  const text = (name, english) => texts[name] ?? english
  window.catrobatText = text
  strings.then((loaded) => {
    if (loaded) {
      texts = loaded.texts
      overlay.lang = loaded.tag
      if (!state.clicked) {
        button.setAttribute('aria-label', text('play', 'Play'))
      }
      render()
    }
  })

  const megabytes = (bytes) => `${(bytes / 1048576).toFixed(1)} MB`

  function render() {
    if (state.failed) {
      return
    }
    let line
    let progress = null // null: unknown, so the bar moves on its own
    if (state.phase === 'files' && state.total > 0 && state.done < state.total) {
      line = `${text('status_downloading', 'Downloading files…')} ${state.done}/${state.total} (${megabytes(state.bytes)})`
      progress = state.done / state.total
    } else if (state.phase === 'files') {
      line = state.clicked
        ? text('please_wait', 'Starting…')
        : text('main_menu_play', 'Ready to play')
      progress = state.clicked ? null : 1
    } else {
      line = {
        player: () => text('loading', 'Loading the player…'),
        project: () =>
          `${text('status_downloading', 'Downloading the project…')}${state.xmlBytes ? ` ${megabytes(state.xmlBytes)}` : ''}`,
        preparing: () => text('loading', 'Reading the project…'),
        sounds: () => text('loading', 'Preparing the sounds…'),
        starting: () => text('please_wait', 'Starting…'),
      }[state.phase]()
    }
    if (status.textContent !== line) {
      status.textContent = line
    }
    bar.classList.toggle('unknown', progress === null)
    bar.classList.toggle('hidden', progress === 1)
    bar.firstChild.style.width = progress === null ? '' : `${Math.round(progress * 100)}%`
  }
  render()

  // Calls back once the page has shown the change: the player's next step may keep the browser busy for a while.
  function afterPaint(callback) {
    let called = false
    const once = () => {
      if (!called) {
        called = true
        callback()
      }
    }
    requestAnimationFrame(() => setTimeout(once, 0))
    setTimeout(once, 100) // no animation frames in a background tab
  }

  // ProjectLoader.load takes code.xml from here; it downloads while app.js does.
  window.catrobatCodeXml = fetch(asset('code.xml')).then(async (response) => {
    if (!response.ok) {
      throw new Error(`Could not load the project: HTTP ${response.status}`)
    }
    if (!response.body) {
      return response.text()
    }
    const reader = response.body.getReader()
    const decoder = new TextDecoder()
    let text = ''
    for (;;) {
      const { done, value } = await reader.read()
      if (done) {
        return text + decoder.decode()
      }
      state.xmlBytes += value.length
      text += decoder.decode(value, { stream: true })
      render()
    }
  })
  window.catrobatCodeXml.catch(() => {}) // reported when the player reads it

  // Share's API has the name at once; it serves its own screenshots without CORS, so the picture is the project's
  // own screenshot from its files, as a blob (the player's CSP allows blob: images, not share's).
  const showPicture = (url) => {
    picture.onload = () => {
      picture.classList.add('shown')
      backdrop.style.backgroundImage = `url("${url}")`
      backdrop.classList.add('shown')
    }
    picture.src = url
  }
  const project = fetch(`${share}/api/projects/${id}`)
    .then((response) => (response.ok ? response.json() : null))
    .catch(() => null)
  project.then((data) => {
    if (data?.name && !name.textContent) {
      name.textContent = data.name
    }
  })
  ;(async () => {
    for (const file of ['manual_screenshot.png', 'automatic_screenshot.png', 'screenshot.png']) {
      const response = await fetch(asset(file))
      if (response.ok) {
        showPicture(URL.createObjectURL(await response.blob()))
        return
      }
    }
    // Many projects keep no screenshot in their files; share's thumbnail is an <img>, which needs no CORS.
    const thumbnail = (await project)?.screenshot?.detail?.webp_2x
    if (thumbnail?.startsWith(`${share}/`) || thumbnail?.startsWith('/')) {
      showPicture(thumbnail)
    }
  })().catch(() => {})

  // What the project needs (Project.getRequiredResources, ids from Brick.java): the sensors it reads, so people know
  // it is played on a phone, and what the browser cannot do, for which there is the app. Playing anyway is up to them.
  const USES = {
    13: 'prestage_no_acceleration_sensor_available',
    14: 'prestage_no_inclination_sensor_available',
    15: 'prestage_no_compass_sensor_available',
    18: 'prestage_no_gps_sensor_available',
    24: 'formula_editor_sensor_loudness',
    9: 'prestage_no_vibration_available',
  }
  const MISSING = {
    4: 'prestage_no_face_detection_available',
    5: 'prestage_no_text_detection_available',
    27: 'prestage_no_pose_detection_available',
    28: 'prestage_no_object_detection_available',
    8: 'prestage_no_flash_available',
    11: 'prestage_no_back_camera_available',
    12: 'prestage_no_front_camera_available',
    17: 'prestage_no_camera_available',
    16: 'formula_editor_device_nfc',
    2: 'formula_editor_device_lego_nxt',
    20: 'formula_editor_device_lego_ev3',
    10: 'formula_editor_device_phiro',
    6: 'formula_editor_device_arduino',
    7: 'formula_editor_device_raspberry',
    22: 'formula_editor_device_cast',
    23: 'formula_editor_speech_recognition',
    29: 'preference_title_multiplayer_variables_enabled',
  }

  function showNeeds(resources, { tag, texts }) {
    // "\u2022 Compass\n" (StageResourceHolder's list) -> "Compass"; "loudness" (a formula sensor) -> "Loudness"
    const name = (key) => {
      const text = texts[key].replace(/^\s*\u2022\s*/, '').trim()
      return text.charAt(0).toLocaleUpperCase(tag) + text.slice(1)
    }
    const uses = new Set()
    const missing = new Set()
    for (const resource of resources) {
      if (resource === 9 && !navigator.vibrate) {
        missing.add(name(USES[9])) // iPhones cannot vibrate from a page
      } else if (USES[resource]) {
        uses.add(name(USES[resource]))
      } else if (MISSING[resource]) {
        missing.add(name(MISSING[resource]))
      }
    }
    needs.lang = tag
    if (uses.size) {
      const line = document.createElement('p')
      line.textContent = `${texts.formula_editor_device_sensors}: ${[...uses].join(', ')}`
      needs.append(line)
    }
    if (missing.size) {
      const box = document.createElement('div')
      box.className = 'missing'
      const title = document.createElement('strong')
      title.textContent = texts.prestage_resource_not_available_title
      const text = document.createElement('p')
      text.textContent = texts.prestage_resource_not_available_text.trim()
      const list = document.createElement('ul')
      for (const item of missing) {
        list.append(Object.assign(document.createElement('li'), { textContent: item }))
      }
      const app = document.createElement('a')
      app.href = 'https://play.google.com/store/apps/details?id=org.catrobat.catroid'
      app.target = '_blank'
      app.rel = 'noopener'
      app.textContent = `${texts.app_name}: ${texts.google_play_store_logo}`
      box.append(title, text, list, app)
      needs.append(box)
    }
    needs.hidden = !needs.firstChild
  }

  // The click also goes full screen and, where the browser allows it (Android), turns the screen to the project's
  // orientation. iPhones have no full screen for pages; there the game stays in the browser window.
  function goFullScreen() {
    const root = document.documentElement
    if (root.requestFullscreen) {
      root
        .requestFullscreen({ navigationUI: 'hide' })
        .then(() => screen.orientation?.lock?.(landscape ? 'landscape' : 'portrait'))
        .catch(() => {})
    }
  }

  window.catrobatStart = {
    phase(phase, shown) {
      state.phase = phase
      render()
      afterPaint(shown)
    },

    files(done, total, bytes) {
      Object.assign(state, { phase: 'files', done, total, bytes })
      render()
    },

    ready(title, isLandscape, resources, started) {
      landscape = isLandscape
      strings.then((texts) => texts && showNeeds(resources, texts))
      if (!name.textContent) {
        name.textContent = title
      }
      button.disabled = false
      button.classList.add('ready')
      button.focus()
      button.onclick = () => {
        button.onclick = null
        button.classList.add('clicked')
        button.setAttribute('aria-label', text('please_wait', 'Starting'))
        state.clicked = true
        render()
        // iPhones send motion and orientation only after asking, which a page may do only inside a click.
        if (resources.some((resource) => resource >= 13 && resource <= 15)) {
          for (const motion of [window.DeviceMotionEvent, window.DeviceOrientationEvent]) {
            motion?.requestPermission?.().catch(() => {})
          }
        }
        goFullScreen()
        started()
      }
    },

    fail(message) {
      state.failed = true
      overlay.removeAttribute('aria-busy')
      button.remove()
      bar.remove()
      status.textContent = text(
        'error_load_project',
        'This project cannot be played in the browser.',
      )
      const details = document.createElement('p')
      details.textContent = message || ''
      const back = document.createElement('a')
      back.href = `${share}/app/project/${id}`
      back.textContent = text('stage_dialog_back', 'Back to the project')
      status.after(details, back)
      back.focus()
    },

    // The stage is there. Leaving full screen (Esc) leaves a small button to go back.
    close() {
      overlay.remove()
      if (!document.fullscreenEnabled) {
        return
      }
      const again = document.createElement('button')
      again.id = 'fullscreen-button'
      again.type = 'button'
      again.title = 'Full screen'
      again.setAttribute('aria-label', 'Full screen')
      again.innerHTML =
        '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></svg>'
      again.onclick = () => {
        again.blur() // the game's keys must not press it again
        goFullScreen()
      }
      // Hidden is display: none, so the button fades again each time it comes back.
      const update = () => {
        again.hidden = Boolean(document.fullscreenElement)
      }
      document.addEventListener('fullscreenchange', update)
      update()
      document.body.append(again)
    },
  }
})()
