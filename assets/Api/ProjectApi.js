/* global globalConfiguration */
/* global projectConfiguration */

import { ApiPutFetch } from './ApiHelper'
import MessageDialogs from '../Components/MessageDialogs'

export default class ProjectApi {
  constructor() {
    this.baseUrl = document.querySelector('#js-api-routing').dataset.baseUrl
  }

  updateProject(id, data, successCallback, finalCallback) {
    new ApiPutFetch(
      this.baseUrl + '/api/project/' + id,
      data,
      'Save Project',
      null,
      successCallback,
      {
        403: projectConfiguration.messages.forbidden,
        404: projectConfiguration.messages.notFound,
        500: function (response) {
          response
            .json()
            .then(function (data) {
              MessageDialogs.showErrorMessage(data.error)
            })
            .catch(function () {
              MessageDialogs.showErrorMessage(globalConfiguration.messages.unspecifiedErrorText)
            })
        },
      },
      finalCallback,
    ).run()
  }
}
