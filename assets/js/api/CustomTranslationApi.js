import $ from 'jquery'

export class CustomTranslationApi {
  constructor (programSection) {
    if (programSection === 'credits') {
      this.programSection = 'credit'
    } else {
      this.programSection = programSection
    }
  }

  getCustomTranslation (programId, language, successCallback, errorCallback = () => {}) {
    $.ajax({
      url: '../translate/custom/project/' + programId + '?field=' + this.programSection + '&language=' + language,
      type: 'get',
      success: successCallback,
      error: errorCallback
    })
  }

  deleteCustomTranslation (programId, language, successCallback, errorCallback) {
    const self = this
    return new Promise(function (resolve, reject) {
      $.ajax({
        url: '../translate/custom/project/' + programId + '?field=' + self.programSection + '&language=' + language,
        type: 'delete',
        success: function (data) {
          successCallback(language)
          resolve()
        },
        error: function (error) {
          errorCallback(error)
          reject(error.status)
        }
      })
    })
  }

  saveCustomTranslation (programId, text, language, successCallback, errorCallback) {
    const self = this
    return new Promise(function (resolve, reject) {
      $.ajax({
        url: '../translate/custom/project/' + programId + '?field=' + self.programSection + '&text=' + text + '&language=' + language,
        type: 'put',
        success: function (data) {
          successCallback(language)
          resolve()
        },
        error: function (error) {
          errorCallback(error)
          reject(error.status)
        }
      })
    })
  }

  getCustomTranslationLanguages (programId, successCallback, errorCallback = () => {}) {
    $.ajax({
      url: '../translate/custom/project/' + programId + '/list',
      type: 'get',
      success: successCallback,
      error: errorCallback
    })
  }
}
