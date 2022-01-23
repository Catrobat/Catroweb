import { showSnackbar } from '../components/snackbar'

export class CustomTranslationSnackbar {
  constructor (programSection) {
    this.programSection = programSection
  }

  show (text, language) {
    text = text.replace('%language%', language).replace('%section%', this.programSection)
    showSnackbar('#share-snackbar', text)
  }
}
