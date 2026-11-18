import { Tooltip } from 'bootstrap'
import { Translation } from '../Translate/Translation'

const EXPORT_KEY_HEADER = 'X-Code-Export-Key'

export function bindCodeViewTranslation(container, panel, exportKey, reload) {
  const toolbar = panel.querySelector('.cv-toolbar')
  const segments = Array.from(panel.querySelectorAll('[data-cv-translation-key]'), (element) => ({
    element,
    original: element.textContent,
  }))
  if (!exportKey || !toolbar || segments.length === 0) return

  const template = container.querySelector('[data-code-translation-template]')
  const controls = template.content.firstElementChild.cloneNode(true)
  toolbar.append(controls)

  const translate = controls.querySelector('[data-code-translate]')
  const translateLabel = translate.querySelector('span')
  const resume = controls.querySelector('[data-code-resume]')
  const original = controls.querySelector('[data-code-original]')
  const status = controls.querySelector('[data-code-translation-status]')
  const languages = controls.querySelector('[data-code-languages]')
  const { dataset } = controls
  const languageNames =
    typeof Intl.DisplayNames === 'function'
      ? new Intl.DisplayNames(document.documentElement.lang.replaceAll('_', '-') || undefined, {
          type: 'language',
        })
      : null
  let translation
  let sourceSelect
  let targetSelect
  let displayed
  let continuation
  let activeRequest

  async function initializeLanguages() {
    if (sourceSelect && targetSelect) return
    translation ??= new Translation('%sourceLanguage% → %targetLanguage%')
    await Promise.all([translation.loadSupportedLanguages(), translation.displayLanguagesLoaded])
    sourceSelect = translation.createLanguageSelect('', 'source')
    sourceSelect.id = 'code-view-source-language'
    const auto = sourceSelect.querySelector('option[value=""]')
    auto.textContent = dataset.auto
    sourceSelect.prepend(auto)
    targetSelect = translation.createLanguageSelect(translation.targetLanguage, 'target')
    targetSelect.id = 'code-view-target-language'
    controls.querySelector('[data-code-source]').append(sourceSelect)
    controls.querySelector('[data-code-target]').append(targetSelect)
    languages.hidden = false
    for (const select of [sourceSelect, targetSelect]) {
      for (const option of select.options) {
        if (option.value) option.textContent = languageName(option.value)
      }
      select.addEventListener('change', updateSelection)
    }
  }

  function languageName(language) {
    return languageNames?.of(language) || translation.displayLanguageMap[language] || language
  }

  function matchesContinuation() {
    return (
      continuation?.canResume &&
      continuation.source === sourceSelect.value &&
      continuation.target === targetSelect.value
    )
  }

  function updateSelection() {
    const targetChanged = displayed && displayed.target !== targetSelect.value
    const sourceChanged = displayed && displayed.source !== sourceSelect.value
    const pending = Boolean(targetChanged || sourceChanged)
    translate.classList.toggle('btn-primary', pending)
    translate.classList.toggle('btn-outline-primary', !pending)
    if (targetChanged) {
      translateLabel.textContent = dataset.translateTo.replace('%language%', () =>
        languageName(targetSelect.value),
      )
    } else {
      translateLabel.textContent = sourceChanged ? dataset.translateAgain : dataset.translate
    }
    resume.hidden = !matchesContinuation()
    if (displayed) {
      const message = pending
        ? dataset.stillShowing
        : displayed.partial
          ? dataset.partialInLanguage
          : dataset.complete
      status.textContent = message.replace('%language%', () => languageName(displayed.language))
    }
  }

  function applyTranslations(translations, language) {
    const byKey = new Map(translations.map(({ key, text }) => [key, text]))
    for (const segment of segments) {
      const { element } = segment
      const text = byKey.get(element.dataset.cvTranslationKey)
      segment.tooltip?.dispose()
      segment.tooltip = null
      element.textContent = text ?? segment.original
      if (text === undefined || !language) {
        element.removeAttribute('lang')
        continue
      }
      element.lang = language
      if (['list', 'variable'].includes(element.dataset.cvTranslationKind)) {
        const name = document.createElement('button')
        name.type = 'button'
        name.className = 'cv-translation-name'
        name.textContent = text
        element.replaceChildren(name)
        const tooltip = new Tooltip(name, {
          title: segment.original,
          container: document.body,
          trigger: 'hover focus click',
        })
        name.addEventListener('keydown', (event) => {
          if (event.key === 'Escape') tooltip.hide()
        })
        segment.tooltip = tooltip
      }
    }
  }

  async function requestTranslation(continueJob) {
    if (activeRequest) return
    const request = new AbortController()
    activeRequest = request
    translate.disabled = true
    resume.disabled = true
    status.textContent = dataset.loading
    const deadline = Date.now() + 120_000
    const timeout = setTimeout(() => {
      if (activeRequest !== request) return
      request.abort()
      status.textContent = dataset.error
    }, deadline - Date.now())

    try {
      await initializeLanguages()
      if (request.signal.aborted) return
      const source = sourceSelect.value
      const target = targetSelect.value
      const url = new URL(container.dataset.translationUrl, window.location.href)
      url.searchParams.set('target_language', target)
      if (source) url.searchParams.set('source_language', source)
      if (continueJob) url.searchParams.set('resume', 'true')
      sourceSelect.disabled = true
      targetSelect.disabled = true
      let response
      do {
        response = await fetch(url, {
          credentials: 'same-origin',
          headers: { [EXPORT_KEY_HEADER]: exportKey },
          signal: request.signal,
        })
        if (response.status !== 202) break
        url.searchParams.delete('resume')
        url.searchParams.set('poll', 'true')
        const delay = Math.max(1, Math.min(10, Number(response.headers.get('Retry-After')) || 2))
        await new Promise((resolve) =>
          setTimeout(resolve, Math.min(delay * 1000, Math.max(0, deadline - Date.now()))),
        )
      } while (Date.now() < deadline && !request.signal.aborted)

      if (request.signal.aborted) return
      if (
        response.status === 409 ||
        (response.status === 200 && response.headers.get(EXPORT_KEY_HEADER) !== exportKey)
      ) {
        applyTranslations([])
        await reload()
        return
      }
      if (response.status === 422) {
        status.textContent = dataset.unsupported
        return
      }
      if (response.status !== 200) {
        status.textContent = dataset.error
        return
      }
      const result = await response.json()
      if (request.signal.aborted) return
      if (result.status === 'source_required') {
        status.textContent = dataset.sourceRequired
        sourceSelect.disabled = false
        sourceSelect.focus()
        return
      }

      if (result.target_language) {
        applyTranslations(result.translations ?? [], result.target_language)
        original.hidden = false
        displayed = {
          source,
          target,
          language: result.target_language,
          partial: result.status === 'partial',
        }
      }
      continuation = { source, target, canResume: result.can_resume }
      updateSelection()
      if (!displayed) status.textContent = dataset.partial
    } catch {
      if (!request.signal.aborted) status.textContent = dataset.error
    } finally {
      clearTimeout(timeout)
      if (activeRequest === request) {
        activeRequest = null
        translate.disabled = false
        resume.disabled = false
        if (sourceSelect) sourceSelect.disabled = false
        if (targetSelect) targetSelect.disabled = false
      }
    }
  }

  translate.addEventListener('click', () => requestTranslation(false))
  resume.addEventListener('click', () => requestTranslation(true))
  original.addEventListener('click', () => {
    activeRequest?.abort()
    applyTranslations([])
    displayed = null
    continuation = null
    original.hidden = true
    updateSelection()
    status.textContent = ''
    if (activeRequest) {
      activeRequest = null
      translate.disabled = false
      resume.disabled = false
      sourceSelect.disabled = false
      targetSelect.disabled = false
    }
    translate.focus()
  })
}
