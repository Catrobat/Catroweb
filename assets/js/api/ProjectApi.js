/* global globalConfiguration */
/* global projectConfiguration */

import { ApiPutFetch } from './ApiHelper'
import MessageDialogs from '../components/MessageDialogs'

export default class ProjectApi {
  static update (id, data, successCallback, finalCallback) {
    new ApiPutFetch(
      '/api/project/' + id, data, 'Save Project', null, successCallback,
      {
        403: projectConfiguration.messages.forbidden,
        404: projectConfiguration.messages.notFound,
        500: function (response) {
          response.json()
            .then(function (data) {
              MessageDialogs.showErrorMessage(data.error)
            }).catch(function () {
              MessageDialogs.showErrorMessage(globalConfiguration.messages.unspecifiedErrorText)
            })
        }
      }, finalCallback
    ).run()
  }
}
