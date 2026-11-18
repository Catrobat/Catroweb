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
  const resume = controls.querySelector('[data-code-resume]')
  const original = controls.querySelector('[data-code-original]')
  const status = controls.querySelector('[data-code-translation-status]')
  const languages = controls.querySelector('[data-code-languages]')
  const { dataset } = controls
  let translation
  let sourceSelect
  let targetSelect
  let continuation
  let activeRequest

  async function initializeLanguages() {
    if (sourceSelect && targetSelect) return
    translation ??= new Translation('%sourceLanguage% → %targetLanguage%')
    await Promise.all([translation.loadSupportedLanguages(), translation.displayLanguagesLoaded])
    sourceSelect = translation.createLanguageSelect('', 'source')
    sourceSelect.querySelector('option[value=""]').textContent = dataset.auto
    targetSelect = translation.createLanguageSelect(translation.targetLanguage, 'target')
    controls.querySelector('[data-code-source]').append(sourceSelect)
    controls.querySelector('[data-code-target]').append(targetSelect)
    languages.hidden = false
    for (const select of [sourceSelect, targetSelect]) {
      select.addEventListener('change', () => {
        resume.hidden = !matchesContinuation()
      })
    }
  }

  function matchesContinuation() {
    return (
      continuation?.canResume &&
      continuation.source === sourceSelect.value &&
      continuation.target === targetSelect.value
    )
  }

  function applyTranslations(translations, language) {
    const byKey = new Map(translations.map(({ key, text }) => [key, text]))
    for (const segment of segments) {
      const text = byKey.get(segment.element.dataset.cvTranslationKey)
      segment.element.textContent = text ?? segment.original
      if (text === undefined || !language) segment.element.removeAttribute('lang')
      else segment.element.lang = language
    }
  }

  async function requestTranslation(continueJob) {
    if (activeRequest) return
    const request = new AbortController()
    activeRequest = request
    translate.disabled = true
    resume.disabled = true
    status.textContent = dataset.loading

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
      const deadline = Date.now() + 120_000
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
        await new Promise((resolve) => setTimeout(resolve, delay * 1000))
      } while (Date.now() < deadline && !request.signal.aborted)

      if (request.signal.aborted) return
      if (
        response.status === 409 ||
        (response.status === 200 && response.headers.get(EXPORT_KEY_HEADER) !== exportKey)
      ) {
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

      applyTranslations(result.translations ?? [], result.target_language)
      original.hidden = false
      continuation = { source, target, canResume: result.can_resume }
      resume.hidden = !matchesContinuation()
      status.textContent = result.status === 'partial' ? dataset.partial : dataset.complete
    } catch {
      if (!request.signal.aborted) status.textContent = dataset.error
    } finally {
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
    original.hidden = true
    resume.hidden = true
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
