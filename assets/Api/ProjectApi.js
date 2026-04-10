/* global globalConfiguration */
/* global projectConfiguration */

import { ApiPutFetch } from './ApiHelper'
import MessageDialogs from '../Components/MessageDialogs'

export default class ProjectApi {
  constructor() {
    this.baseUrl = document.querySelector('#js-api-routing').dataset.baseUrl
  }

  updateProject(id, data, successCallback, finalCallback) {
    const msg403 =
      typeof projectConfiguration !== 'undefined'
        ? projectConfiguration.messages.forbidden
        : 'Forbidden'
    const msg404 =
      typeof projectConfiguration !== 'undefined'
        ? projectConfiguration.messages.notFound
        : 'Not found'
    new ApiPutFetch(
      this.baseUrl + '/api/project/' + id,
      data,
      'Save Project',
      null,
      successCallback,
      {
        403: msg403,
        404: msg404,
        500: function (response) {
          response
            .json()
            .then(function (data) {
              MessageDialogs.showErrorMessage(data.error)
            })
            .catch(function () {
              const fallback =
                typeof globalConfiguration !== 'undefined'
                  ? globalConfiguration.messages.unspecifiedErrorText
                  : 'An error occurred'
              MessageDialogs.showErrorMessage(fallback)
            })
        },
      },
      finalCallback,
    ).run()
  }
}
