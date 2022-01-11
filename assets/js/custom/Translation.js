import $ from 'jquery'

export class Translation {
  constructor (translatedByLine) {
    this.translatedByLine = translatedByLine
    this.providerMap = {
      itranslate: 'iTranslate'
    }
    this.displayLanguageMap = {}
    this.translatedByLineMap = {}
    this.targetLanguage = null

    this.setTargetLanguage()
    this.setDisplayLanguageMap()
    this.splitTranslatedByLine()
  }

  setTargetLanguage () {
    let decodedCookie
    try {
      decodedCookie = document.cookie
        .split(';')
        .map(v => v.split('='))
        .reduce((acc, v) => {
          acc[decodeURIComponent(v[0].trim())] = decodeURIComponent(v[1].trim())
          return acc
        }, {})
    } catch (e) {
      console.error("Can't decode cookie")
    }

    if (decodedCookie !== undefined && decodedCookie.hl !== undefined) {
      this.targetLanguage = decodedCookie.hl.replace('_', '-')
    } else {
      this.targetLanguage = document.documentElement.lang
    }
  }

  setDisplayLanguageMap () {
    const self = this
    $.ajax({
      url: '../languages',
      type: 'get',
      success: function (data) {
        self.displayLanguageMap = data
      }
    })
  }

  splitTranslatedByLine () {
    const self = this
    let firstLanguage = '%sourceLanguage%'
    let secondLanguage = '%targetLanguage%'
    if (!this.isSourceLanguageFirst()) {
      firstLanguage = '%targetLanguage%'
      secondLanguage = '%sourceLanguage%'
    }

    self.translatedByLineMap = {
      before: self.translatedByLine.substring(0, self.translatedByLine.indexOf(firstLanguage)),
      between: self.translatedByLine.substring(self.translatedByLine.indexOf(firstLanguage) + firstLanguage.length, self.translatedByLine.indexOf(secondLanguage)),
      after: self.translatedByLine.substring(self.translatedByLine.indexOf(secondLanguage) + secondLanguage.length)
    }
  }

  isSourceLanguageFirst () {
    return this.translatedByLine.indexOf('%sourceLanguage%') < this.translatedByLine.indexOf('%targetLanguage%')
  }

  isTranslationNotAvailable (elementId) {
    return $(elementId).attr('lang') !== this.targetLanguage
  }

  openGoogleTranslatePage (text) {
    window.open(
      'https://translate.google.com/?q=' + encodeURIComponent(text) + '&sl=auto&tl=' + this.targetLanguage,
      '_self'
    )
  }

  setTranslationCredit (data, byLineElements) {
    $(byLineElements.before).text(this.translatedByLineMap.before.replace('%provider%', this.providerMap[data.provider]))
    $(byLineElements.between).text(this.translatedByLineMap.between.replace('%provider%', this.providerMap[data.provider]))
    $(byLineElements.after).text(this.translatedByLineMap.after.replace('%provider%', this.providerMap[data.provider]))

    if (this.isSourceLanguageFirst()) {
      $(byLineElements.firstLanguage).text(this.displayLanguageMap[data.source_language])
      $(byLineElements.secondLanguage).text(this.displayLanguageMap[data.target_language])
    } else {
      $(byLineElements.secondLanguage).text(this.displayLanguageMap[data.target_language])
      $(byLineElements.firstLanguage).text(this.displayLanguageMap[data.source_language])
    }
  }
}

// eslint-disable-next-line no-unused-vars
export class ByLineElementContainer {
  constructor (beforeElement, betweenElement, afterElement, firstLanguageElement, secondLanguageElement) {
    this.before = beforeElement
    this.between = betweenElement
    this.after = afterElement
    this.firstLanguage = firstLanguageElement
    this.secondLanguage = secondLanguageElement
  }
}
