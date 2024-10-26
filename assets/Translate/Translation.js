export class Translation {
  constructor(translatedByLine, googleTranslateDisplayName) {
    this.translatedByLine = translatedByLine
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
    fetch('../languages')
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

  openGoogleTranslatePage(text) {
    window.open(
      'https://translate.google.com/?q=' +
        encodeURIComponent(text) +
        '&sl=auto&tl=' +
        this.targetLanguage,
      '_self',
    )
  }

  setTranslationCredit(data, byLineElements) {
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

    if (this.isSourceLanguageFirst()) {
      byLineElements.firstLanguage.textContent = this.displayLanguageMap[data.source_language]
      byLineElements.secondLanguage.textContent = this.displayLanguageMap[data.target_language]
    } else {
      byLineElements.secondLanguage.textContent = this.displayLanguageMap[data.target_language]
      byLineElements.firstLanguage.textContent = this.displayLanguageMap[data.source_language]
    }
  }

  formatProvider(byLine, provider) {
    return byLine.replace(
      '%provider%',
      `<a href="${provider.link}" class="translation-credit" style="text-decoration: none">${provider.displayName}</a>`,
    )
  }
}

// eslint-disable-next-line no-unused-vars
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
