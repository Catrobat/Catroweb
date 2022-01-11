import $ from 'jquery'

export class CustomTranslationApi {
  constructor (programSection) {
    this.programSection = programSection
  }

  getCustomTranslation (programId, language, successCallback, errorCallback) {
    $.ajax({
      url: '../translate/custom/project/' + programId + '?field=' + this.programSection + '&language=' + language,
      type: 'get',
      success: successCallback,
      error: errorCallback
    })
  }

  deleteCustomTranslation (programId, language, successCallback, errorCallback) {
    $.ajax({
      url: '../translate/custom/project/' + programId + '?field=' + this.programSection + '&language=' + language,
      type: 'delete',
      success: successCallback,
      error: errorCallback
    })
  }

  saveCustomTranslation (programId, text, language, successCallback, errorCallback) {
    $.ajax({
      url: '../translate/custom/project/' + programId + '?field=' + this.programSection + '&text=' + text + '&language=' + language,
      type: 'put',
      success: successCallback,
      error: errorCallback
    })
  }
}
