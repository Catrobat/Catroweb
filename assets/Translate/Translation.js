import { SnackbarDuration, showSnackbar } from '../Layout/Snackbar'

// Machine translation works per language, not per regional variant; these are the exceptions.
const SPECIAL_LANGUAGES = ['zh-CN', 'zh-TW', 'pt-BR', 'pt-PT']
// Variants LibreTranslate has a separate model for (LibreTranslateApi::LANGUAGE_CODES).
const EXACT_LANGUAGES = ['zh-CN', 'zh-TW', 'pt-BR']
// The project and the comment translators of a page share one request.
let supportedLanguagesRequest = null

export class Translation {
  constructor(translatedByLine, refreshLabel = 'Translate again') {
    this.translatedByLine = translatedByLine
    this.refreshLabel = refreshLabel
    // Languages the machine translation offers (loadSupportedLanguages); while empty, every language is offered.
    this.supportedLanguages = []
    this.displayLanguageMap = {}
    this.translatedByLineMap = {}
    this.targetLanguage = null

    this.setTargetLanguage()
    this.setDisplayLanguageMap()
    this.splitTranslatedByLine()
  }

  setTargetLanguage() {
    const appLanguageElement = document.getElementById('app-language')
    const siteLanguage = appLanguageElement.dataset.appLanguage.replace('_', '-')
    // The API takes "xx" or "xx-YY": "sr-Latn" becomes "sr", 3-letter languages (kab, chr) have no model.
    const [language, region = ''] = siteLanguage.split('-')
    let code = /^[A-Z]{2}$/.test(region) ? siteLanguage : language
    if (language.length !== 2) code = 'en'
    // Without a model for the site language every attempt would fail; English is the closest useful default and
    // the dropdown still offers every supported language.
    this.targetLanguage = this.isSupported(code) ? code : 'en'
  }

  /**
   * Fetches the languages the machine translation offers on the first translate click, so rendering a page never
   * waits for LibreTranslate; a failed request is tried again on the next click. Resolves when the target language
   * is set accordingly.
   */
  loadSupportedLanguages() {
    supportedLanguagesRequest ??= fetch(
      document.querySelector('[data-machine-translation-languages-url]')?.dataset
        .machineTranslationLanguagesUrl,
    )
      .then((response) => {
        if (!response.ok) throw new Error(`Languages failed: ${response.status}`)
        return response.json()
      })
      .then((languages) => (Array.isArray(languages) ? languages : []))
      .catch(() => {
        supportedLanguagesRequest = null
        return []
      })
    return supportedLanguagesRequest.then((languages) => {
      // Empty while LibreTranslate is unreachable: then every language is offered.
      this.supportedLanguages = languages
      this.setTargetLanguage()
    })
  }

  setDisplayLanguageMap() {
    const routing = document.getElementById('js-api-routing')
    const languagesUrl = routing ? routing.dataset.languages : '/languages'
    this.displayLanguagesLoaded = fetch(languagesUrl)
      .then((response) => response.json())
      .then((data) => {
        this.displayLanguageMap = data
      })
      .catch((error) => console.error('Error fetching languages:', error))
  }

  splitTranslatedByLine() {
    let firstLanguage = '%sourceLanguage%'
    let secondLanguage = '%targetLanguage%'
    if (!this.isSourceLanguageFirst()) {
      firstLanguage = '%targetLanguage%'
      secondLanguage = '%sourceLanguage%'
    }

    this.translatedByLineMap = {
      before: this.translatedByLine.substring(0, this.translatedByLine.indexOf(firstLanguage)),
      between: this.translatedByLine.substring(
        this.translatedByLine.indexOf(firstLanguage) + firstLanguage.length,
        this.translatedByLine.indexOf(secondLanguage),
      ),
      after: this.translatedByLine.substring(
        this.translatedByLine.indexOf(secondLanguage) + secondLanguage.length,
      ),
    }
  }

  isSourceLanguageFirst() {
    return (
      this.translatedByLine.indexOf('%sourceLanguage%') <
      this.translatedByLine.indexOf('%targetLanguage%')
    )
  }

  isTranslationNotAvailable(elementId) {
    return document.querySelector(elementId).getAttribute('lang') !== this.targetLanguage
  }

  showTranslationFailed() {
    const message = document.querySelector('[data-trans-machine-translation-failed]')?.dataset
      .transMachineTranslationFailed
    showSnackbar('#share-snackbar', message, SnackbarDuration.error)
  }

  /**
   * Renders the "Machine translated from X to Y" line. X and Y are selectors; changing one shows a
   * refresh button that calls onRefresh(sourceLanguage, targetLanguage), which returns a promise.
   */
  setTranslationCredit(data, byLineElements, onRefresh) {
    byLineElements.before.textContent = this.translatedByLineMap.before
    byLineElements.between.textContent = this.translatedByLineMap.between
    byLineElements.after.textContent = this.translatedByLineMap.after

    const [sourceElement, targetElement] = this.isSourceLanguageFirst()
      ? [byLineElements.firstLanguage, byLineElements.secondLanguage]
      : [byLineElements.secondLanguage, byLineElements.firstLanguage]

    const sourceSelect = this.createLanguageSelect(data.source_language, 'source')
    const targetSelect = this.createLanguageSelect(data.target_language, 'target')
    sourceSelect.id = `${sourceElement.id}-select`
    targetSelect.id = `${targetElement.id}-select`
    sourceElement.replaceChildren(sourceSelect)
    targetElement.replaceChildren(targetSelect)

    let refreshButton = byLineElements.after.nextElementSibling
    if (!refreshButton?.classList.contains('translation-refresh-button')) {
      refreshButton = document.createElement('button')
      refreshButton.type = 'button'
      refreshButton.className =
        'translation-refresh-button btn btn-sm btn-link p-0 ms-1 align-baseline'
      refreshButton.setAttribute('aria-label', this.refreshLabel)
      refreshButton.title = this.refreshLabel
      refreshButton.innerHTML = '<span class="material-icons align-middle">refresh</span>'
      byLineElements.after.after(refreshButton)
    }
    refreshButton.style.display = 'none'
    refreshButton.onclick = () => {
      refreshButton.style.display = 'none'
      onRefresh(sourceSelect.value, targetSelect.value).then(() => {
        // A new translation renders new selectors. Still here: it failed or was superseded, so the
        // by-line goes back to the languages of the translation that is still shown.
        if (!sourceSelect.isConnected) return
        sourceSelect.value = data.source_language
        targetSelect.value = data.target_language
        syncDisabled()
        refreshButton.style.display = 'none'
      })
    }

    // A language cannot be picked on both sides.
    const syncDisabled = () => {
      for (const option of sourceSelect.options)
        option.disabled = option.value === targetSelect.value
      for (const option of targetSelect.options)
        option.disabled = option.value === sourceSelect.value
    }
    syncDisabled()
    for (const select of [sourceSelect, targetSelect]) {
      select.addEventListener('change', () => {
        syncDisabled()
        refreshButton.style.display = ''
      })
    }

    // The language names may still be loading; fill them in once they are there.
    this.displayLanguagesLoaded?.then(() => {
      this.fillLanguageOptions(sourceSelect)
      this.fillLanguageOptions(targetSelect)
      syncDisabled()
    })
  }

  createLanguageSelect(selectedLanguage, side) {
    const select = document.createElement('select')
    select.className = `translation-language-select translation-${side}-language form-select form-select-sm d-inline-block w-auto`
    const option = document.createElement('option')
    option.value = selectedLanguage
    option.textContent = this.displayLanguageMap[selectedLanguage] || selectedLanguage
    select.appendChild(option)
    this.fillLanguageOptions(select)
    return select
  }

  fillLanguageOptions(select) {
    const selected = select.value
    const languages = Object.keys(this.displayLanguageMap).filter(
      (code) =>
        code === selected ||
        ((code.length === 2 || SPECIAL_LANGUAGES.includes(code)) && this.isSupported(code)),
    )
    if (languages.length === 0) return

    if (!languages.includes(selected)) languages.push(selected)
    select.replaceChildren(
      ...languages.map((code) => {
        const option = document.createElement('option')
        option.value = code
        option.textContent = this.displayLanguageMap[code] || code
        option.selected = code === selected
        return option
      }),
    )
  }

  isSupported(code) {
    return (
      this.supportedLanguages.length === 0 ||
      this.supportedLanguages.includes(code) ||
      // Regional variants with their own model must be listed themselves; others are served by the language.
      (!EXACT_LANGUAGES.includes(code) && this.supportedLanguages.includes(code.slice(0, 2)))
    )
  }
}

export class ByLineElementContainer {
  constructor(
    beforeElement,
    betweenElement,
    afterElement,
    firstLanguageElement,
    secondLanguageElement,
  ) {
    this.before = beforeElement
    this.between = betweenElement
    this.after = afterElement
    this.firstLanguage = firstLanguageElement
    this.secondLanguage = secondLanguageElement
  }
}
