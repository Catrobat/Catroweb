// Machine translation works per language, not per regional variant; these are the exceptions.
const SPECIAL_LANGUAGES = ['zh-CN', 'zh-TW', 'pt-BR', 'pt-PT']

export class Translation {
  constructor(translatedByLine, googleTranslateDisplayName, refreshLabel = 'Translate again') {
    this.translatedByLine = translatedByLine
    this.refreshLabel = refreshLabel
    this.providerMap = {
      itranslate: {
        displayName: 'iTranslate',
        link: 'https://itranslate.com',
      },
      google: {
        displayName: googleTranslateDisplayName,
        link: 'https://translate.google.com',
      },
    }
    this.displayLanguageMap = {}
    this.translatedByLineMap = {}
    this.targetLanguage = null

    this.setTargetLanguage()
    this.setDisplayLanguageMap()
    this.splitTranslatedByLine()
  }

  setTargetLanguage() {
    const appLanguageElement = document.getElementById('app-language')
    this.targetLanguage = appLanguageElement.dataset.appLanguage.replace('_', '-')
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

  openGoogleTranslatePage(text, sourceLanguage = 'auto', targetLanguage = this.targetLanguage) {
    window.open(
      'https://translate.google.com/?q=' +
        encodeURIComponent(text) +
        '&sl=' +
        encodeURIComponent(sourceLanguage) +
        '&tl=' +
        encodeURIComponent(targetLanguage),
      '_self',
    )
  }

  /**
   * Renders the "Translated by … from X to Y" line. X and Y are selectors; changing one shows a
   * refresh button that calls onRefresh(sourceLanguage, targetLanguage).
   */
  setTranslationCredit(data, byLineElements, onRefresh = null) {
    byLineElements.before.innerHTML = this.formatProvider(
      this.translatedByLineMap.before,
      this.providerMap[data.provider],
    )
    byLineElements.between.innerHTML = this.formatProvider(
      this.translatedByLineMap.between,
      this.providerMap[data.provider],
    )
    byLineElements.after.innerHTML = this.formatProvider(
      this.translatedByLineMap.after,
      this.providerMap[data.provider],
    )

    const [sourceElement, targetElement] = this.isSourceLanguageFirst()
      ? [byLineElements.firstLanguage, byLineElements.secondLanguage]
      : [byLineElements.secondLanguage, byLineElements.firstLanguage]

    if (!onRefresh) {
      sourceElement.textContent = this.displayLanguageMap[data.source_language]
      targetElement.textContent = this.displayLanguageMap[data.target_language]
      return
    }

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
      onRefresh(sourceSelect.value, targetSelect.value)
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
      (code) => code.length === 2 || SPECIAL_LANGUAGES.includes(code) || code === selected,
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

  formatProvider(byLine, provider) {
    return byLine.replace(
      '%provider%',
      `<a href="${provider.link}" class="translation-credit" style="text-decoration: none">${provider.displayName}</a>`,
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
