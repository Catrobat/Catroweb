export default class {
  get() {
    try {
      return document.getElementById('app-language').dataset.appLanguage
    } catch (e) {
      return 'en'
    }
  }
}
